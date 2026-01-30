<?php
session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: ./index.php");
        exit;
    }

    require '../src/php/config/connection.php';
    require '../src/php/functions/finance.php';
    require '../src/php/functions/chart.php';

    $data = getDashboardData($conn, $_SESSION['user_id']);
    $total_saldo = $data['saldo'];
    $pemasukan_bulan_ini = $data['masuk'];
    $pengeluaran_bulan_ini = $data['keluar'];

    $chartData = getWeeklyExpenseCharts($conn, $_SESSION['user_id']);
    $transaksi_terakhir = getLastTransactions($conn, $_SESSION['user_id']);

    include '../src/php/config/connection.php';
    if (!isset($_SESSION['login']) && isset($_COOKIE['remember_token'])) {
        $token = $_COOKIE['remember_token'];

        $query = mysqli_query(
            $conn,
            "SELECT * FROM users WHERE remember_token='$token'"
        );

        if ($user = mysqli_fetch_assoc($query)) {
            $_SESSION['login'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FinTrack | Dashboard</title>
    <link rel="stylesheet" href="./css/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="./js/main.js"></script>
</head>

<script>
        const chartLabels = <?= json_encode($chartData['labels']); ?>;
        const chartIncomeData = <?= json_encode($chartData['income']); ?>;
        const chartExpenseData = <?= json_encode($chartData['expense']); ?>;
</script>

<body class="font-['Inter'] bg-slate-50 text-slate-800">
    <!-- Session Success Message -->
    <?php if (isset($_SESSION['success'])): ?>
        <div id="successModal" class="fixed flex top-20 right-1 -translate-x-1 items-center justify-end z-50 mr-4 pointer-events-none">
            <div class="w-full max-w-md bg-white border rounded-2xl shadow-xl p-5 text-center pointer-events-auto relative">
                <div class="text-emerald-500 text-xl mb-3">
                    <i class="fa-solid fa-circle-check fa-3x"></i>
                    <h2 class="text-lg font-bold mb-2">Login Success</h2>
                    <p class="text-sm text-gray-400 mb-5">
                        <?= $_SESSION['success']; ?>
                    </p>
                </div>
            </div>
            <?php unset($_SESSION['success']); endif; ?>
        </div>

    <div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-200 hidden md:flex flex-col">
            <div class="p-6 flex items-center gap-2">
                <div class="flex gap-2 flex-row">
                    <img src="../src/img/logo.png" alt="Logo" class="w-10 h-10 object-contain rounded-md">
                    <div class="flex flex-col items-center md:items-start">
                        <h1 class="text-2xl font-bold tracking-tight text-emerald-950">FinTrack</h1>
                        <span class="text-xs text-gray-400">Track Every Worth Precisely</span>
                    </div>
                </div>
            </div>
            <nav class="flex-1 px-4 space-y-1 mt-4">
                <a href="./dashboard.php" class="block px-4 py-2 rounded-lg text-gray-700 hover:bg-emerald-100 hover:text-emerald-900 font-medium">
                    <i class="fa-solid fa-chart-line mr-2"></i> Dashboard
                </a>
                <a href="./transactions.php" class="block px-4 py-2 rounded-lg text-gray-700 hover:bg-emerald-100 hover:text-emerald-900 font-medium">
                    <i class="fa-solid fa-wallet mr-2"></i> Transactions
                </a>
                <a href="#" class="block px-4 py-2 rounded-lg text-gray-700 hover:bg-emerald-100 hover:text-emerald-900 font-medium">
                    <i class="fa-solid fa-cog mr-2"></i> Settings
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 h-screen overflow-y-auto">
            <!-- Header -->
            <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between sticky top-0 z-10">
                <h2 class="text-2xl font-bold text-slate-800">Overview</h2>
                <!-- Profile -->
                <button class="flex items-center gap-2">
                    <span class="hidden sm:block text-sm">Hello, Brian</span>
                    <img src="" alt="Profile" class="w-9 h-9 sm:w-10 sm:h-10 rounded-full object-cover bg-gray-300">
                </button>
            </header>

            <!-- Dashboard Content -->
            <div class="p-6 max-w-7xl mx-auto space-y-6">
                <!-- Cards Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Card 1 -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <p class="text-sm text-slate-500">Balance</p>
                        <h3 class="text-3xl font-bold text-slate-800"><?= formatRupiah($total_saldo) ?></h3>
                    </div>
                    <!-- Card 2 -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <p class="text-sm text-slate-500">Income (This Month)</p>
                        <h3 class="text-2xl font-bold text-emerald-600"><?= formatRupiah($pemasukan_bulan_ini) ?></h3>
                    </div>
                    <!-- Card 3 -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <p class="text-sm text-slate-500">Expense (This Month)</p>
                        <h3 class="text-2xl font-bold text-rose-600"><?= formatRupiah($pengeluaran_bulan_ini) ?></h3>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 lg:col-span-2">
                        <h3 class="text-lg font-bold mb-4">Statistik Mingguan</h3>
                        <div class="h-64 relative">
                            <canvas id="expenseChart"></canvas>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <h3 class="text-lg font-bold mb-4">Transaksi Terakhir</h3>
                        <div class="space-y-4">
                            <?php foreach($transaksi_terakhir as $trx): ?>
                            <div class="flex items-center justify-between border-b border-gray-50 pb-2">
                                <div>
                                    <p class="font-semibold text-sm"><?= $trx['ket'] ?></p>
                                    <p class="text-xs text-slate-400"><?= $trx['tanggal'] ?></p>
                                </div>
                                <span class="font-bold text-sm <?= $trx['tipe'] == 'masuk' ? 'text-emerald-600' : 'text-rose-600' ?>">
                                    <?= formatRupiah($trx['nominal']) ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button id="btnLihatSemua" class="w-full mt-4 py-2 border rounded-lg text-sm text-slate-600 hover:bg-gray-50">Lihat Semua</button>
                    </div>
                </div>

                <!-- Quick Add Button -->
                <div>
                    <button id="openModal" class="fixed bottom-6 right-6 bg-emerald-600 text-white w-14 h-14 rounded-full shadow-lg text-3xl hover:bg-emerald-700 transition">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </div>

                <!-- Quick Add Modal -->
                <div id="transactionModal" class="min-h-screen fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center px-4 z-40 hidden">
                    <div class="bg-white rounded-xl w-96 p-6">
                        <h2 class="text-2xl font-bold mb-4 text-emerald-800">Add Transaction</h2>

                        <form action="../src/php/transactions/store.php" method="POST" class="space-y-4">
                            <!-- Input Nominal -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nominal</label>
                                <input
                                    type="text"
                                    id="nominalInput"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 text-lg font-semibold"
                                    placeholder="Rp 0"
                                    autocomplete="off"
                                    required
                                >
                                <input type="hidden" name="nominal" id="nominalHidden">
                            </div>

                            <select name="tipe" class="w-full border rounded-lg mb-4 p-2" required>
                                <option value="" disabled selected>Select Type</option>
                                <option value="masuk">Income</option>
                                <option value="keluar">Expense</option>
                            </select>
                            <input
                                type="text"
                                class="w-full mb-4 px-3 py-4 border rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                name="ket"
                                placeholder="Description"
                                required
                            >
                            <div class="flex justify-end gap-2">
                                <button type="button" id="closeModal" class="px-4 py-2 border rounded-lg text-slate-600 hover:bg-gray-50">Cancel</button>
                                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">Add</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>