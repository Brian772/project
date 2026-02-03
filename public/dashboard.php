<?php
session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: ./index.php");
        exit;
    }

    require '../src/php/config/connection.php';
    require '../src/php/functions/finance.php';
    require '../src/php/functions/chart.php';
    require '../src/php/functions/settings.php';

    $data = getDashboardData($conn, $_SESSION['user_id']);
    $total_saldo = $data['saldo'];
    $pemasukan_bulan_ini = $data['masuk'];
    $pengeluaran_bulan_ini = $data['keluar'];

    $chartData = getChartData($conn, $_SESSION['user_id'], 'week');
    $transaksi_terakhir = getLastTransactions($conn, $_SESSION['user_id']);

    $userSettings = getUserSettings($conn, $_SESSION['user_id']);

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
<html lang="en" class="">
<script>
    // Apply dark mode immediately to avoid flash
    if (localStorage.getItem('darkMode') === 'enabled') {
        document.documentElement.classList.add('dark');
    }
</script>
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
        const userId = <?= json_encode($_SESSION['user_id']); ?>;
        const chartLabels = <?= json_encode($chartData['labels']); ?>;
        const chartIncomeData = <?= json_encode($chartData['income']); ?>;
        const chartExpenseData = <?= json_encode($chartData['expense']); ?>;
</script>

<body class="font-['Inter'] bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100">
    <!-- Session Success Message -->
    <?php if (isset($_SESSION['success'])): ?>
        <div id="successModal" class="fixed flex top-6 right-6 items-center justify-end z-50 pointer-events-none animate-slide-in">
            <div class="bg-white dark:bg-slate-800 border border-emerald-100 dark:border-emerald-900 rounded-xl shadow-lg p-4 flex items-center gap-4 pointer-events-auto min-w-[300px]">
                <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-check text-emerald-600 dark:text-emerald-400 text-lg"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800 dark:text-slate-100 text-sm">Login Success</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400"><?= $_SESSION['success']; ?></p>
                </div>
            </div>
        </div>
        <?php unset($_SESSION['success']); endif;?>

    <div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
        <aside class="w-64 bg-white dark:bg-slate-800 border-r border-gray-200 dark:border-slate-700 hidden md:flex flex-col">
            <div class="p-6 flex items-center gap-2">
                <div class="flex gap-2 flex-row">
                    <img src="../src/img/logo.png" alt="Logo" class="w-10 h-10 object-contain rounded-md">
                    <div class="flex flex-col items-center md:items-start">
                        <h1 class="text-2xl font-bold tracking-tight text-emerald-950 dark:text-emerald-400">FinTrack</h1>
                        <span class="text-xs text-gray-400 dark:text-slate-500">Track Every Worth Precisely</span>
                    </div>
                </div>
            </div>
            <nav class="flex-1 px-4 space-y-1 mt-4">
                <a href="./dashboard.php" class="block px-4 py-2 rounded-lg bg-emerald-100 dark:bg-slate-700 text-emerald-900 dark:text-emerald-400 font-medium">
                    <i class="fa-solid fa-chart-line mr-2"></i> Dashboard
                </a>
                <a href="./transactions.php" class="block px-4 py-2 rounded-lg text-gray-700 dark:text-slate-300 hover:bg-emerald-100 dark:hover:bg-slate-700 hover:text-emerald-900 dark:hover:text-emerald-400 font-medium">
                    <i class="fa-solid fa-wallet mr-2"></i> Transactions
                </a>
                <a href="./settings.php" class="block px-4 py-2 rounded-lg text-gray-700 dark:text-slate-300 hover:bg-emerald-100 dark:hover:bg-slate-700 hover:text-emerald-900 dark:hover:text-emerald-400 font-medium">
                    <i class="fa-solid fa-cog mr-2"></i> Settings
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 h-screen overflow-y-auto bg-slate-50 dark:bg-slate-900">
            <!-- Header -->
            <header class="bg-white dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700 px-6 py-4 flex items-center justify-between sticky top-0 z-10">
                <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Dashboard</h2>
                <!-- Profile -->
                <button class="flex items-center gap-2">
                    <span class="hidden sm:block text-sm dark:text-slate-300">Hello, <?= htmlspecialchars($userSettings['name'] ?? 'User') ?></span>
                    <?php if (!empty($userSettings['profile_picture'])): ?>
                        <img src="../src/uploads/<?= $userSettings['profile_picture'] ?>" alt="Profile" class="w-9 h-9 sm:w-10 sm:h-10 rounded-full object-cover bg-gray-300">
                    <?php else: ?>
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-emerald-600 flex items-center justify-center text-white font-semibold">
                            <?= strtoupper(substr($userSettings['name'] ?? 'U', 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </button>
            </header>

            <!-- Dashboard Content -->
            <div class="p-6 max-w-7xl mx-auto space-y-6">
                <!-- Cards Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Balance Card -->
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700">
                        <p class="text-sm text-gray-600 dark:text-slate-400">Balance</p>
                        <h3 class="text-3xl font-bold text-slate-800 dark:text-slate-100"><?= formatRupiah($total_saldo) ?></h3>
                    </div>
                    <!-- Income (This Month) Card -->
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700">
                        <p class="text-sm text-gray-600 dark:text-slate-400">Income (This Month)</p>
                        <h3 class="text-3xl font-bold text-emerald-600 dark:text-emerald-400"><?= formatRupiah($pemasukan_bulan_ini) ?></h3>
                    </div>
                    <!-- Expense (This Month) Card -->
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700">
                        <p class="text-sm text-gray-600 dark:text-slate-400">Expense (This Month)</p>
                        <h3 class="text-3xl font-bold text-rose-600 dark:text-rose-400"><?= formatRupiah($pengeluaran_bulan_ini) ?></h3>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 lg:col-span-2">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold">Statistik</h3>
                            <div class="relative">
                                <button id="filterBtn" class="flex items-center gap-2 px-3 py-2 bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-700 dark:hover:bg-slate-700 rounded-lg hover:bg-gray-50">
                                    <span id="filterText">This Week</span>
                                    <i class="fa-solid fa-chevron-down text-sm"></i>
                                </button>
                                <ul
                                    id="filterMenu"
                                    class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 dark:border-slate-700 border rounded-lg shadow-lg hidden z-10"
                                >
                                    <li data-value="week"  class="px-4 py-2 dark:text-dark hover:bg-gray-100 dark:hover:bg-slate-700 cursor-pointer">This Week</li>
                                    <li data-value="month" class="px-4 py-2 dark:text-dark hover:bg-gray-100 dark:hover:bg-slate-700 cursor-pointer">This Month</li>
                                    <li data-value="year"  class="px-4 py-2 dark:text-dark hover:bg-gray-100 dark:hover:bg-slate-700 cursor-pointer">This Year</li>
                                </ul>
                            </div>
                        </div>
                        <div class="h-64 relative">
                            <canvas id="expenseChart"></canvas>
                        </div>
                    </div>

                    <!-- Recent Transactions Section -->
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700">
                        <h3 class="text-lg font-bold mb-4">Transaksi Terakhir</h3>
                        <div class="space-y-4">
                            <?php foreach($transaksi_terakhir as $trx): ?>
                            <div class="flex items-center justify-between border-b border-gray-50 pb-2">
                                <div>
                                    <p class="font-semibold text-sm"><?= $trx['kategori'] ?></p>
                                    <p class="text-xs text-slate-400"><i class="fa-regular fa-calendar mr-1"></i><?= date('d M Y', strtotime($trx['tanggal'])) ?></p>
                                </div>
                                <span class="font-bold text-sm <?= $trx['tipe'] == 'masuk' ? 'text-emerald-600' : 'text-rose-600' ?>">
                                    <?= formatRupiah($trx['nominal']) ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <a href="./transactions.php" class="w-full mt-4 py-2 border rounded-lg text-sm text-slate-600 hover:bg-gray-50 dark:hover:bg-slate-700 dark:text-white dark:border-slate-700 block text-center">Lihat Semua</a>
                    </div>
                </div>

                <!-- Quick Add Button -->
                <div>
                    <button id="openModal" class="fixed bottom-6 right-6 bg-emerald-600 text-white w-14 h-14 rounded-full shadow-lg text-3xl hover:bg-emerald-700 transition">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Transaction Modal -->
    <div id="transactionModal" class="min-h-screen fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center px-4 z-40 hidden">
        <div class="bg-white dark:bg-slate-800 rounded-xl w-full max-w-md p-6">
            <h2 class="text-2xl font-bold mb-4 text-emerald-800 dark:text-emerald-400">Add Transaction</h2>

            <form action="../src/php/transactions/store.php" method="POST" class="space-y-4">
                <!-- Input Nominal -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2">Nominal</label>
                    <input
                        type="text"
                        id="nominalInput"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-slate-700 dark:bg-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 text-lg font-semibold"
                        placeholder="Rp 0"
                        autocomplete="off"
                        required
                    >
                    <input type="hidden" name="nominal" id="nominalHidden">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2">Type</label>
                    <div class="flex text-center gap-4 justify-between items-center text-sm font-medium text-gray-700 mb-2 grid-cols-2">
                        <label class="w-full flex items-center">
                            <input type="radio" name="tipe" value="masuk" class="mr-2 hidden peer" required>
                            <span class="w-full px-3 py-2 bg-emerald-100 text-emerald-700 rounded-lg cursor-pointer hover:bg-emerald-200 peer-checked:bg-emerald-500 peer-checked:text-white ">Income</span>
                        </label>
                        <label class="w-full flex items-center">
                            <input type="radio" name="tipe" value="keluar" class="mr-2 hidden peer" required>
                            <span class="w-full px-3 py-2 bg-rose-100 text-rose-700 rounded-lg cursor-pointer hover:bg-rose-200 peer-checked:bg-rose-500 peer-checked:text-white">Expense</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2">Date & Time</label>
                    <input type="datetime-local" name="tanggal" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-700 dark:bg-slate-700 rounded-lg focus:ring-2 focus:ring-emerald-500" value="<?= date('Y-m-d\TH:i') ?>">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2">Category</label>
                    <input type="text" name="kategori" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-700 dark:bg-slate-700 rounded-lg focus:ring-2 focus:ring-emerald-500" placeholder="e.g. Food, Salary" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2">Asset</label>
                    <input type="text" name="aset" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-700 dark:bg-slate-700 rounded-lg focus:ring-2 focus:ring-emerald-500" placeholder="e.g. Cash, Bank" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2">Description <span class="text-gray-400 font-normal">(Optional)</span></label>
                    <input
                        type="text"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-slate-700 dark:bg-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        name="ket"
                        placeholder="Description"
                    >
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" id="closeModal" class="px-4 py-2 border rounded-lg text-slate-600 hover:bg-gray-50 dark:text-white dark:border-slate-700 dark:hover:bg-slate-700">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">Add</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
