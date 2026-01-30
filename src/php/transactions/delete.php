<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../../public/index.php");
    exit;
}

require '../config/connection.php';
require '../functions/transactions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../../public/transactions.php");
    exit;
}

$transaction_id = $_POST['id'] ?? null;

if (!$transaction_id) {
    $_SESSION['error'] = "Transaction ID required";
    header("Location: ../../../public/transactions.php");
    exit;
}

$success = deleteTransaction($conn, $transaction_id, $_SESSION['user_id']);

if ($success) {
    $_SESSION['success'] = "Transaction deleted successfully!";
} else {
    $_SESSION['error'] = "Failed to delete transaction";
}

header("Location: ../../../public/transactions.php");
exit;
?>
