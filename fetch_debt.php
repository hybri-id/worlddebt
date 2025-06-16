<?php
// fetch_debt.php - Fetch debts from DB with filtering and returns JSON
require_once 'db.php';

$countryFilter = isset($_GET['country']) ? trim($_GET['country']) : '';

try {
	
    if ($countryFilter === '') {
		// Fetch all countries
        // Calculate debt as % of GDP (avoid division by zero)
        $sql = "SELECT country, debt, interest_per_second, population, gdp, UNIX_TIMESTAMP(last_updated) AS last_updated_unix,
                IF(gdp > 0, (debt / gdp) * 100, 0) AS debt_percent_gdp
                FROM debts ORDER BY country ASC";
        $stmt = $pdo->query($sql);
    } else {
		// Fetch filtered country (partial match)
        $sql = "SELECT country, debt, interest_per_second, population, gdp, UNIX_TIMESTAMP(last_updated) AS last_updated_unix,
                IF(gdp > 0, (debt / gdp) * 100, 0) AS debt_percent_gdp
                FROM debts WHERE country LIKE ? ORDER BY country ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['%' . $countryFilter . '%']);
    }
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($data);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
exit;