<?php
session_start();
require '../config/connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../../public/index.php");
    exit;
}

date_default_timezone_set('Asia/Jakarta');
$user_id = $_SESSION['user_id'];
$nominal = $_POST['nominal'];
$tipe = $_POST['tipe'];
$ket = !empty($_POST['ket']) ? $_POST['ket'] : '-';
$tanggal = !empty($_POST['tanggal']) ? $_POST['tanggal'] : date('Y-m-d H:i:s');
$kategori = $_POST['kategori'];
$aset = $_POST['aset'];

$query = $conn->prepare("
    INSERT INTO transactions (user_id, ket, nominal, tipe, tanggal, kategori, aset)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

$query->bind_param(
    "isissss",
    $user_id,
    $ket,
    $nominal,
    $tipe,
    $tanggal,
    $kategori,
    $aset
);

$success = $query->execute();

if (isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Transaction added successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add transaction: ' . $conn->error]);
    }
    exit;
}

if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
    header("Location: " . $_SERVER['HTTP_REFERER']);
} else {
    header("Location: ../../../public/dashboard.php");
}
