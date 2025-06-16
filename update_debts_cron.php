<?php
// update_debts_cron.php
// This script updates all debts in DB based on interest_per_second and elapsed time
require_once 'db.php';

try {
    // Fetch all debts with current values
    $stmt = $pdo->query("SELECT id, debt, interest_per_second, last_updated FROM debts");
    $debts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $now = new DateTimeImmutable("now", new DateTimeZone("UTC"));

    // Prepare update statement once
    $updateStmt = $pdo->prepare("UPDATE debts SET debt = ?, last_updated = ? WHERE id = ?");

    $updatedCount = 0;
    foreach ($debts as $debt) {
        $lastUpdated = new DateTimeImmutable($debt['last_updated'], new DateTimeZone("UTC"));
        $elapsedSeconds = $now->getTimestamp() - $lastUpdated->getTimestamp();

        if ($elapsedSeconds <= 0) {
            // No update needed if no time elapsed or data is ahead
            continue;
        }

        // Calculate new debt
        $newDebt = bcadd($debt['debt'], bcmul($debt['interest_per_second'], $elapsedSeconds, 10), 0); // bc math for precision and no decimals

        // Convert newDebt to string/int — we store it as BIGINT UNSIGNED so round down and cast to int
        $newDebtInt = (int)floor($newDebt);

        // Update DB row only if debt changed
        if ($newDebtInt !== (int)$debt['debt']) {
            $updateStmt->execute([
                $newDebtInt,
                $now->format('Y-m-d H:i:s'),
                $debt['id']
            ]);
            $updatedCount++;
        }
    }

    echo "Updated debts rows: $updatedCount\n";
} catch (PDOException $e) {
    http_response_code(500);
    echo "Error updating debts: " . $e->getMessage() . "\n";
    exit(1);
}
?>