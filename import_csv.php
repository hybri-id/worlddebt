<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Show upload form (same as before)
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Import Debt CSV</title></head>
<body>
<h2>Import Debt Data CSV</h2>
<form method="post" enctype="multipart/form-data" aria-label="CSV upload form">
    <label for="csvFile">Select CSV file (must have Country,Debt,Interest Per Second,Population,GDP,Last Updated columns):</label><br><br>
    <input type="file" name="csvFile" id="csvFile" accept=".csv" required><br><br>
    <button type="submit">Upload and Import</button>
</form>
<p><a href="index.php">Back to Dashboard</a></p>
</body>
</html>
HTML;
    exit;
}

if (!isset($_FILES['csvFile']) || $_FILES['csvFile']['error'] !== UPLOAD_ERR_OK) {
    die("Error uploading file.");
}

$filename = $_FILES['csvFile']['tmp_name'];

$handle = fopen($filename, 'r');
if ($handle === false) {
    die("Failed to open uploaded file.");
}

$header = fgetcsv($handle);
$expectedHeaders = ['Country', 'Debt', 'Interest Per Second', 'Population', 'GDP', 'Last Updated'];
$headerLower = array_map('strtolower', $header);
$expectedLower = array_map('strtolower', $expectedHeaders);
if ($headerLower !== $expectedLower) {
    fclose($handle);
    die("CSV header does not match expected columns: " . implode(", ", $expectedHeaders));
}

// Prepare insert/update UPSERT statement for MySQL 5.7+ with UNIQUE key on country
// Assuming 'country' column has UNIQUE constraint in your debts table
$stmt = $pdo->prepare("INSERT INTO debts (country, debt, interest_per_second, population, gdp, last_updated) 
VALUES (?, ?, ?, ?, ?, ?)
ON DUPLICATE KEY UPDATE 
    debt = VALUES(debt),
    interest_per_second = VALUES(interest_per_second),
    population = VALUES(population),
    gdp = VALUES(gdp),
    last_updated = VALUES(last_updated)");

$processedCount = 0;
$errors = [];
$rowNum = 1;

while (($row = fgetcsv($handle)) !== false) {
    $rowNum++;
    if (count($row) < 6) {
        $errors[] = "Row $rowNum: Invalid column count.";
        continue;
    }

    list($country, $debt, $interestPerSecond, $population, $gdp, $lastUpdated) = $row;

    $country = trim($country);
    $debt = is_numeric($debt) ? $debt : 0;
    $interestPerSecond = is_numeric($interestPerSecond) ? $interestPerSecond : 0;
    $population = is_numeric($population) ? $population : 0;
    $gdp = is_numeric($gdp) ? $gdp : 0;
    $lastUpdated = trim($lastUpdated);

    if ($country === '') {
        $errors[] = "Row $rowNum: Empty country name.";
        continue;
    }

    // Validate date format loosely (optional)
    if (strtotime($lastUpdated) === false) {
        $errors[] = "Row $rowNum: Invalid Last Updated date format.";
        continue;
    }

    try {
        $stmt->execute([
            $country,
            $debt,
            $interestPerSecond,
            $population,
            $gdp,
            $lastUpdated
        ]);
        $processedCount++;
    } catch (PDOException $e) {
        $errors[] = "Row $rowNum: Database error - " . $e->getMessage();
    }
}

fclose($handle);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Import CSV Results</title>
</head>
<body>
<h2>Import Results</h2>
<p>Rows imported or updated: <?php echo $processedCount; ?></p>
<?php if ($errors): ?>
<h3>Errors:</h3>
<ul>
<?php foreach ($errors as $error): ?>
    <li><?php echo htmlspecialchars($error); ?></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
<p><a href="index.php">Back to Dashboard</a></p>
</body>
</html>