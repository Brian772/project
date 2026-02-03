<?php
session_start();
require '../config/connection.php';
require '../functions/chart.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$filter = $_GET['filter'] ?? 'week';
$validFilters = ['day', 'week', 'month', 'year'];

if (!in_array($filter, $validFilters)) {
    $filter = 'week';
}

$chartData = getChartData($conn, $_SESSION['user_id'], $filter);

header('Content-Type: application/json');
echo json_encode($chartData);
