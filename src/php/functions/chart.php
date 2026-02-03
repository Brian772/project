<?php
function getChartData($conn, $user_id, $filter = 'week') {
    $labels = [];
    $incomeData = [];
    $expenseData = [];

    if ($filter == 'week') {
        // Last 7 days
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        for ($i = 0; $i <= 6; $i++) {
            $date = date('Y-m-d', strtotime("+$i days"));
            $dayOfWeek = date('w', strtotime($date));
            $labels[] = $days[$dayOfWeek];

            $qI = $conn->prepare("SELECT SUM(nominal) as total FROM transactions WHERE user_id=? AND tipe='masuk' AND DATE(tanggal)=?");
            $qI->bind_param("is", $user_id, $date);
            $qI->execute();
            $incomeData[] = (int)($qI->get_result()->fetch_assoc()['total'] ?? 0);

            $qE = $conn->prepare("SELECT SUM(nominal) as total FROM transactions WHERE user_id=? AND tipe='keluar' AND DATE(tanggal)=?");
            $qE->bind_param("is", $user_id, $date);
            $qE->execute();
            $expenseData[] = (int)($qE->get_result()->fetch_assoc()['total'] ?? 0);
        }
    } elseif ($filter == 'month') {
        // Current Month (Daily)
        $daysInMonth = date('t');
        $currentMonth = date('m');
        $currentYear = date('Y');
        
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = sprintf("%04d-%02d-%02d", $currentYear, $currentMonth, $d);
            $date = date('Y-m-d', strtotime($date));

            $labels[] = $d;

            $qI = $conn->prepare("SELECT SUM(nominal) as total FROM transactions WHERE user_id=? AND tipe='masuk' AND DATE(tanggal)=?");
            $qI->bind_param("is", $user_id, $date);
            $qI->execute();
            $incomeData[] = (int)($qI->get_result()->fetch_assoc()['total'] ?? 0);

            $qE = $conn->prepare("SELECT SUM(nominal) as total FROM transactions WHERE user_id=? AND tipe='keluar' AND DATE(tanggal)=?");
            $qE->bind_param("is", $user_id, $date);
            $qE->execute();
            $expenseData[] = (int)($qE->get_result()->fetch_assoc()['total'] ?? 0);
        }
    } elseif ($filter == 'year') {
        // Current Year (Monthly)
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
        $currentYear = date('Y');

        for ($m = 1; $m <= 12; $m++) {
            $labels[] = $months[$m-1];
            
            $qI = $conn->prepare("SELECT SUM(nominal) as total FROM transactions WHERE user_id=? AND tipe='masuk' AND MONTH(tanggal)=? AND YEAR(tanggal)=?");
            $qI->bind_param("iii", $user_id, $m, $currentYear);
            $qI->execute();
            $incomeData[] = (int)($qI->get_result()->fetch_assoc()['total'] ?? 0);

            $qE = $conn->prepare("SELECT SUM(nominal) as total FROM transactions WHERE user_id=? AND tipe='keluar' AND MONTH(tanggal)=? AND YEAR(tanggal)=?");
            $qE->bind_param("iii", $user_id, $m, $currentYear);
            $qE->execute();
            $expenseData[] = (int)($qE->get_result()->fetch_assoc()['total'] ?? 0);
        }
    }

    return [
        'labels' => $labels,
        'income' => $incomeData,
        'expense' => $expenseData
    ];
}