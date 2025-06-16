<?php
// country.php
require_once 'db.php';

$country = isset($_GET['country']) ? trim($_GET['country']) : '';
if ($country === '') {
    header("Location: index.php");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT country, debt, interest_per_second, population, gdp, UNIX_TIMESTAMP(last_updated) AS last_updated_unix FROM debts WHERE country = ?");
    $stmt->execute([$country]);
    $debtData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$debtData) {
        header("HTTP/1.0 404 Not Found");
        echo "<h1>Country not found</h1>";
        exit;
    }

    $population = (int)$debtData['population'];
    $gdp = (float)$debtData['gdp'];

    $interestPerYear = $debtData['interest_per_second'] * 60 * 60 * 24 * 365;
    $debtPerCitizen = ($population > 0) ? ($debtData['debt'] / $population) : 0;
    $debtPercentGDP = ($gdp > 0) ? ($debtData['debt'] / $gdp) * 100 : 0;

} catch (PDOException $e) {
    header("HTTP/1.0 500 Internal Server Error");
    echo "<h1>Database error</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}

function formatCurrency($num) {
    return number_format($num, 0, '.', ',');
}
function formatPercent($num, $decimals = 2) {
    return number_format($num, $decimals) . '%';
}
function formatNumber($num) {
    return number_format($num, 0, '.', ',');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=0.7" />
<title><?php echo htmlspecialchars($debtData['country']); ?> Debt Details</title>
<link rel="icon" type="image/png" href="img/favicon-96x96.png" sizes="96x96" />
<link rel="icon" type="image/svg+xml" href="img/favicon.svg" />
<link rel="shortcut icon" href="img/favicon.ico" />
<link rel="apple-touch-icon" sizes="180x180" href="img/apple-touch-icon.png" />
<meta name="apple-mobile-web-app-title" content="World Debt" />
<link rel="manifest" href="site.webmanifest" />
<style>
/* Use previous country.php styling here */
body {font-family: Arial, sans-serif; margin: 0; background: #f9fafb; color: #222; display: grid;}
header {background: #1976d2; color: white; padding: 20px; text-align: center;}
header h1 {margin: 0; font-weight: normal;}
main {max-width: 800px; margin: 30px auto; padding: 0 15px;}
.debt-landing {background: white; border-radius: 8px; padding: 30px 25px; box-shadow: 0 1px 6px rgb(0 0 0 / 0.1); margin-bottom: 40px; text-align: center;}
.debt-landing h2 {margin: 0 0 15px 0; font-weight: normal;}
.debt-landing .debt-amount {font-size: 3em; font-weight: bold; color: #1976d2;}
.resume {background: white; border-radius: 8px; box-shadow: 0 1px 6px rgb(0 0 0 / 0.1); padding: 20px 25px; max-height: 360px; overflow-y: auto;}
.resume h3 {text-align: center; color: #1976d2; margin-bottom: 20px; font-weight: normal;}
.resume dl {margin: 0; display: grid; grid-template-columns: auto 1fr; row-gap: 14px; column-gap: 12px;}
.resume dt {font-weight: bold; color: #444;}
.resume dd {margin: 0; color: #666; text-align: right;}
a.back-link {display: inline-block; margin: 25px auto; background: #1976d2; color: white; padding: 10px 18px; border-radius: 5px; text-decoration: none; font-weight: bold;}
a.back-link:hover {background: #135ba1;}
a.back-link:hover {background: #135ba1;}
footer {
	text-align: center;  /* Center the text */
	margin-top: 20px;    /* Space above the footer */
	padding: 10px 0;     /* Vertical padding */
	background-color: #f1f1f1; /* Background color of the footer */
	position: fixed;   /* Relative positioning for better layout control */
	width: 100%;          /* Full width */
	bottom: 0;            /* Stick to the bottom */
}

footer p {
	margin: 0;            /* Remove default margin */
	color: #333;          /* Text color */
	font-size: 0.9rem;    /* Font size */
}
a {
    color: #1976d2;
}
</style>
</head>
<body>
<header>
    <h1><?php echo htmlspecialchars($debtData['country']); ?> Debt Details</h1>
</header>
<main>
    <a href="index.php" class="back-link" aria-label="Back to World Debt Dashboard">&larr; Back to Dashboard</a>

    <section class="debt-landing" aria-labelledby="debtTitle">
        <h2 id="debtTitle">Current Debt</h2>
        <div class="debt-amount" id="dynamicDebt"><?php echo formatCurrency($debtData['debt']); ?> USD</div>
    </section>

    <section class="resume" tabindex="0" aria-labelledby="summaryTitle" aria-describedby="scrollHint">
        <h3 id="summaryTitle">Country Debt Summary</h3>
        <p id="scrollHint" style="font-size: 0.9em; color: #666; text-align:center; margin-bottom:15px;">
            Scroll down for details
        </p>
        <dl>
            <dt>Interest per Year:</dt>
            <dd><?php echo formatCurrency($interestPerYear); ?> USD</dd>

            <dt>Interest per Second:</dt>
            <dd><?php echo number_format($debtData['interest_per_second'], 4); ?> USD</dd>

            <dt>Debt per Citizen:</dt>
            <dd><?php echo formatCurrency($debtPerCitizen); ?> USD</dd>

            <dt>Debt as % of GDP:</dt>
            <dd><?php echo formatPercent($debtPercentGDP); ?></dd>

            <dt>GDP (Gross Domestic Product):</dt>
            <dd><?php echo formatCurrency($gdp); ?> USD</dd>

            <dt>Population:</dt>
            <dd><?php echo formatNumber($population); ?></dd>
        </dl>
    </section>
</main>

<script>
(function(){
    const dynamicDebtElem = document.getElementById('dynamicDebt');
    const baseDebt = Number(<?php echo json_encode($debtData['debt']); ?>);
    const interestPerSecond = Number(<?php echo json_encode($debtData['interest_per_second']); ?>);
    const lastUpdatedUnix = Number(<?php echo json_encode($debtData['last_updated_unix']); ?>);

    function formatCurrency(num) {
        return num.toLocaleString('en-US', {style: 'currency', currency: 'USD', maximumFractionDigits: 0});
    }

    function updateDebt() {
        const nowUnix = Math.floor(Date.now() / 1000);
        const elapsed = nowUnix - lastUpdatedUnix;
        const newDebt = baseDebt + interestPerSecond * elapsed;
        dynamicDebtElem.textContent = formatCurrency(newDebt) + ' USD';
    }

    updateDebt();
    setInterval(updateDebt, 1000);
})();
</script>

<footer>
    <p>Copyright &copy; 2023-<span id="currentYear"></span> <a href="javascript:void(0)">Kylo_Rey</a>. All Rights Reserved.</p>
</footer>
<script>
//Copyright footer current year
var currentYear = new Date().getFullYear();
document.getElementById('currentYear').textContent = currentYear;
</script>
</body>
</html>