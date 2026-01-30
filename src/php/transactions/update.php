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
$nominal = $_POST['nominal'] ?? 0;
$tipe = $_POST['tipe'] ?? '';
$ket = $_POST['ket'] ?? '';
$tanggal = $_POST['tanggal'] ?? date('Y-m-d H:i:s');
$kategori = $_POST['kategori'] ?? '';
$aset = $_POST['aset'] ?? '';

if (!$transaction_id || !$nominal || !$tipe || !$ket) {
    $_SESSION['error'] = "Please fill all required fields";
    header("Location: ../../../public/transactions.php");
    exit;
}

$data = [
    'nominal' => intval($nominal),
    'tipe' => $tipe,
    'ket' => $ket,
    'tanggal' => $tanggal,
    'kategori' => $kategori,
    'aset' => $aset
];

$success = updateTransaction($conn, $transaction_id, $_SESSION['user_id'], $data);

if ($success) {
    $_SESSION['success'] = "Transaction updated successfully!";
} else {
    $_SESSION['error'] = "Failed to update transaction";
}

header("Location: ../../../public/transactions.php");
exit;
?>
