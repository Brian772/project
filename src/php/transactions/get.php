<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require '../../config/connection.php';
require '../functions/transactions.php';

$transaction_id = $_GET['id'] ?? null;

if (!$transaction_id) {
    echo json_encode(['error' => 'Transaction ID required']);
    exit;
}

$transaction = getTransactionById($conn, $transaction_id, $_SESSION['user_id']);

if (!$transaction) {
    echo json_encode(['error' => 'Transaction not found']);
    exit;
}

echo json_encode($transaction);
?>
