<?php
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function getDashboardData($conn, $user_id) {
    $q = $conn->prepare("
        SELECT SUM(
            CASE WHEN tipe='masuk' THEN nominal ELSE -nominal END
            ) AS saldo
            FROM transactions WHERE user_id=?
        ");
    $q->bind_param("i", $user_id);
    $q->execute();
    $saldo = $q->get_result()->fetch_assoc() ['saldo'] ?? 0;

    //pemasukan bulan ini
    $q = $conn->prepare("
        SELECT SUM(nominal) AS pemasukan
        FROM transactions
        WHERE user_id=? AND tipe='masuk' AND MONTH(tanggal)=MONTH(CURRENT_DATE()) AND YEAR(tanggal)=YEAR(CURRENT_DATE())
        ");
    $q->bind_param("i", $user_id);
    $q->execute();
    $masuk = $q->get_result()->fetch_assoc() ['pemasukan'] ?? 0;

    //pengeluaran bulan ini
    $q = $conn->prepare("
        SELECT SUM(nominal) AS pengeluaran
        FROM transactions
        WHERE user_id=? AND tipe='keluar' AND MONTH(tanggal)=MONTH(CURRENT_DATE()) AND YEAR(tanggal)=YEAR(CURRENT_DATE())
        ");
    $q->bind_param("i", $user_id);
    $q->execute();
    $keluar = $q->get_result()->fetch_assoc() ['pengeluaran'] ?? 0;

    return [
        'saldo' => $saldo,
        'masuk' => $masuk,
        'keluar' => $keluar
    ];
}

function getLastTransactions($conn, $user_id, $limit = 5) {
    $q = $conn->prepare("
        SELECT ket, tanggal, nominal, tipe, kategori
        FROM transactions
        WHERE user_id=?
        ORDER BY tanggal DESC
        LIMIT ?
    ");
    $q->bind_param("ii", $user_id, $limit);
    $q->execute();
    $result = $q->get_result();
    $transactions = [];
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
    return $transactions;
}
