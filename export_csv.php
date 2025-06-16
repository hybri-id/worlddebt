<?php
// export_csv.php
require_once 'db.php';

try {
    $stmt = $pdo->query("SELECT country, debt, interest_per_second, population, gdp FROM debts ORDER BY country ASC");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

if (!$data || count($data) == 0) {
    die("No data to export.");
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=world_debt_export_' . date('Ymd_His') . '.csv');

$output = fopen('php://output', 'w');

fputcsv($output, ['Country', 'Debt', 'Interest Per Second', 'Population', 'GDP', 'Debt as % of GDP']);

foreach ($data as $row) {
    $debtPercentGDP = ($row['gdp'] > 0) ? ($row['debt'] / $row['gdp']) * 100 : 0;
    fputcsv($output, [
        $row['country'],
        $row['debt'],
        $row['interest_per_second'],
        $row['population'],
        $row['gdp'],
        number_format($debtPercentGDP, 2) . '%',
    ]);
}

fclose($output);
exit;
?>