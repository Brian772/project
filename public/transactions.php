<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ./index.php");
    exit;
}

require '../src/php/config/connection.php';
require '../src/php/functions/finance.php';
require '../src/php/functions/transactions.php';
require '../src/php/functions/settings.php';

// Get filters from query params
$filters = [
    'type' => $_GET['type'] ?? '',
    'search' => $_GET['search'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
];

// Get transactions and stats
$transactions = getAllTransactions($conn, $_SESSION['user_id'], $filters);
$stats = getTransactionStats($conn, $_SESSION['user_id'], $filters);

// Group transactions by date
$groupedTransactions = [];
foreach ($transactions as $trx) {
    $date = date('Y-m-d', strtotime($trx['tanggal']));
    if (!isset($groupedTransactions[$date])) {
        $groupedTransactions[$date] = [
            'income' => 0,
            'expense' => 0,
            'items' => []
        ];
    }
    $groupedTransactions[$date]['items'][] = $trx;
    if ($trx['tipe'] === 'masuk') {
        $groupedTransactions[$date]['income'] += $trx['nominal'];
    } else {
        $groupedTransactions[$date]['expense'] += $trx['nominal'];
    }
}

$userSettings = getUserSettings($conn, $_SESSION['user_id']);
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
    <title>FinTrack | Transactions</title>
    <link rel="stylesheet" href="./css/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="font-['Inter'] bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100">
    <!-- Session Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div id="successModal" class="fixed flex top-6 right-6 items-center justify-end z-50 pointer-events-none animate-slide-in">
            <div class="bg-white dark:bg-slate-800 border border-emerald-100 dark:border-emerald-900 rounded-xl shadow-lg p-4 flex items-center gap-4 pointer-events-auto min-w-[300px]">
                <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-check text-emerald-600 dark:text-emerald-400 text-lg"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800 dark:text-slate-100 text-sm">Success</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400"><?= $_SESSION['success']; ?></p>
                </div>
            </div>
            <?php unset($_SESSION['success']); ?>
            <script>
                setTimeout(() => {
                    const modal = document.getElementById('successModal');
                    if(modal) {
                        modal.classList.add('opacity-0', 'translate-x-full', 'transition-all', 'duration-300');
                        setTimeout(() => modal.remove(), 300);
                    }
                }, 3000);
            </script>
        </div>
    <?php endif; ?>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center px-4 z-50 hidden opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-2xl w-full max-w-sm p-6 transform scale-95 transition-transform duration-300" id="deleteModalContent">
            <div class="text-center">
                <div class="w-16 h-16 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-trash-can text-rose-500 text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-2">Hapus Transaksi?</h3>
                <p class="text-sm text-slate-500 mb-6">Apakah Anda yakin ingin menghapus transaksi ini? Tindakan ini tidak dapat dibatalkan.</p>
                
                <div class="flex gap-3 justify-center">
                    <button id="cancelDelete" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-medium hover:bg-slate-50 transition-colors w-full">
                        Batal
                    </button>
                    <button id="confirmDelete" class="px-5 py-2.5 rounded-xl bg-rose-600 text-white font-medium hover:bg-rose-700 transition-colors w-full shadow-lg shadow-rose-200">
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div id="errorModal" class="fixed flex top-20 right-1 -translate-x-1 items-center justify-end z-50 mr-4 pointer-events-none">
            <div class="w-full max-w-md bg-white border rounded-2xl shadow-xl p-5 text-center pointer-events-auto relative">
                <div class="text-rose-500 text-xl mb-3">
                    <i class="fa-solid fa-circle-xmark fa-3x"></i>
                    <h2 class="text-lg font-bold mb-2">Error</h2>
                    <p class="text-sm text-gray-400 mb-5">
                        <?= $_SESSION['error']; ?>
                    </p>
                </div>
            </div>
            <?php unset($_SESSION['error']); ?>
            <script>
                setTimeout(() => {
                    document.getElementById('errorModal').remove();
                }, 3000);
            </script>
        </div>
    <?php endif; ?>

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
                <a href="./dashboard.php" class="block px-4 py-2 rounded-lg text-gray-700 dark:text-slate-300 hover:bg-emerald-100 dark:hover:bg-slate-700 hover:text-emerald-900 dark:hover:text-emerald-400 font-medium">
                    <i class="fa-solid fa-chart-line mr-2"></i> Dashboard
                </a>
                <a href="./transactions.php" class="block px-4 py-2 rounded-lg bg-emerald-100 dark:bg-slate-700 text-emerald-900 dark:text-emerald-400 font-medium">
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
                <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Transactions</h2>
                <button class="flex items-center gap-2">
                    <span class="hidden sm:block text-sm">Hello, <?= htmlspecialchars($userSettings['name'] ?? 'User') ?></span>
                    <?php if (!empty($userSettings['profile_picture'])): ?>
                        <img src="../src/uploads/<?= $userSettings['profile_picture'] ?>" alt="Profile" class="w-9 h-9 sm:w-10 sm:h-10 rounded-full object-cover bg-gray-300">
                    <?php else: ?>
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-emerald-600 flex items-center justify-center text-white font-semibold">
                            <?= strtoupper(substr($userSettings['name'] ?? 'U', 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </button>
            </header>

            <div class="p-4 max-w-5xl mx-auto space-y-6">
                <!-- Stats Summary (Compact) -->
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 relative overflow-hidden">
                    <div class="flex justify-between items-center text-center divide-x divide-slate-500 dark:divide-slate-600">
                        <div class="flex-1 px-2">
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Income</p>
                            <h3 class="text-lg font-bold text-emerald-600 dark:text-emerald-400"><?= formatRupiah($stats['income']) ?></h3>
                        </div>
                        <div class="flex-1 px-2">
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Expense</p>
                            <h3 class="text-lg font-bold text-rose-600 dark:text-rose-400"><?= formatRupiah($stats['expense']) ?></h3>
                        </div>
                        <div class="flex-1 px-2">
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Balance</p>
                            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100"><?= formatRupiah($stats['balance']) ?></h3>
                        </div>
                    </div>
                </div>

                <!-- Filters & Search (Simplified) -->
                <div class="flex gap-2 overflow-x-auto pb-1 no-scrollbar">
                    <a href="?type=" class="px-4 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition-colors <?= empty($filters['type']) ? 'bg-slate-800 dark:bg-slate-700 text-white' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700' ?>">
                        Harian
                    </a>
                    <a href="?type=masuk" class="px-4 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition-colors <?= $filters['type'] === 'masuk' ? 'bg-emerald-600 text-white' : 'bg-white dark:bg-slate-800 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900 hover:bg-emerald-50 dark:hover:bg-emerald-900' ?>">
                        Pemasukan
                    </a>
                    <a href="?type=keluar" class="px-4 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition-colors <?= $filters['type'] === 'keluar' ? 'bg-rose-600 text-white' : 'bg-white dark:bg-slate-800 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900 hover:bg-rose-50 dark:hover:bg-rose-900' ?>">
                        Pengeluaran
                    </a>
                </div>

                <!-- Grouped Transactions List -->
                <div class="space-y-6 pb-20">
                    <?php if (empty($groupedTransactions)): ?>
                        <div class="text-center py-12">
                            <i class="fa-solid fa-file-invoice text-4xl text-slate-300 mb-3"></i>
                            <p class="text-slate-500">Belum ada transaksi</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($groupedTransactions as $date => $group): ?>
                            <div class="transaction-group">
                                <!-- Date Header -->
                                <div class="flex justify-between items-end mb-3 px-2">
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-2xl font-bold text-slate-800"><?= date('d', strtotime($date)) ?></span>
                                        <span class="text-xs font-bold text-slate-500 bg-slate-200 px-1.5 py-0.5 rounded"><?= date('D', strtotime($date)) ?></span>
                                        <span class="text-xs text-slate-400"><?= date('M . Y', strtotime($date)) ?></span>
                                    </div>
                                    <div class="text-right flex gap-3 text-xs font-semibold">
                                        <?php if($group['income'] > 0): ?>
                                            <span class="text-emerald-500 mr-8">+ Rp <?= number_format($group['income'], 0, ',', '.') ?></span>
                                        <?php endif; ?>
                                        <?php if($group['expense'] > 0): ?>
                                            <span class="text-rose-500  mr-8">- Rp <?= number_format($group['expense'], 0, ',', '.') ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Transaction Cards -->
                                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
                                    <?php foreach($group['items'] as $trx): ?>
                                        <div class="p-4 border-b border-slate-50 last:border-0 hover:bg-slate-50 transition-colors cursor-pointer relative">
                                            
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="flex items-start gap-3 flex-1 overflow-hidden">
                                                    <!-- Icon -->
                                                    <div class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center text-xl
                                                        <?= $trx['tipe'] === 'masuk' ? 'bg-emerald-50 text-emerald-500' : 'bg-yellow-50 text-yellow-500' ?>">
                                                        <?php if($trx['tipe'] === 'masuk'): ?>
                                                            <i class="fa-solid fa-money-bill-wave"></i>
                                                        <?php else: ?>
                                                            <i class="fa-solid fa-bag-shopping"></i> 
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <!-- Details -->
                                                    <div class="flex-1 min-w-0">
                                                        <h4 class="font-medium text-slate-700 truncate pr-2">
                                                            <?= !empty($trx['kategori']) ? htmlspecialchars($trx['kategori']) : 'Umum' ?>
                                                        </h4>
                                                        <div class="text-xs text-slate-400 mt-0.5 flex flex-col sm:flex-row gap-1">
                                                            <span class="truncate"><?= htmlspecialchars($trx['ket']) ?></span>
                                                            <?php if (!empty($trx['aset'])): ?>
                                                                <span class="hidden sm:inline">•</span>
                                                                <span class="text-slate-500"><?= htmlspecialchars($trx['aset']) ?></span>
                                                            <?php endif; ?>
                                                            <span class="hidden sm:inline">•</span>
                                                            <span class="text-slate-500"><?= date('H:i', strtotime($trx['tanggal'])) ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Amount & Actions -->
                                                <div class="flex flex-col items-end gap-1 flex-shrink-0">
                                                    <span class="block font-bold <?= $trx['tipe'] === 'masuk' ? 'text-emerald-500' : 'text-rose-500' ?>">
                                                        <?= $trx ['tipe'] === 'masuk' ? '+ Rp ' . number_format($trx['nominal'], 0, ',', '.') : '- Rp ' . number_format($trx['nominal'], 0, ',', '.') ?>
                                                    </span>
                                                    
                                                    <!-- Action Buttons -->
                                                    <div class="flex gap-1 mt-1">
                                                        <button onclick="editTransaction(<?= $trx['id'] ?>)" class="text-xs text-blue-600 hover:bg-blue-50 px-2 py-1 rounded transition-colors" title="Edit">
                                                            Edit
                                                        </button>
                                                        <button onclick="deleteTransaction(<?= $trx['id'] ?>)" class="text-xs text-rose-600 hover:bg-rose-50 px-2 py-1 rounded transition-colors" title="Delete">
                                                            Hapus
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Add Button -->
                <button id="openModal" class="fixed bottom-6 right-6 bg-emerald-600 text-white w-14 h-14 rounded-full shadow-lg text-3xl hover:bg-emerald-700 transition">
                    <i class="fa-solid fa-plus"></i>
                </button>
            </div>
        </main>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="min-h-screen fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center px-4 z-40 hidden">
        <div class="bg-white rounded-xl w-full max-w-md p-6">
            <h2 class="text-2xl font-bold mb-4 text-emerald-800">Edit Transaction</h2>
            <form id="editForm" action="../src/php/transactions/update.php" method="POST" class="space-y-4">
                <input type="hidden" name="id" id="editId">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nominal</label>
                    <input type="text" id="editNominalInput" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 text-lg font-semibold" placeholder="Rp 0" autocomplete="off" required>
                    <input type="hidden" name="nominal" id="editNominalHidden">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                    <div class="flex text-center gap-4 justify-between items-center text-sm font-medium text-gray-700 mb-2 grid-cols-2">
                        <label class="w-full flex items-center">
                            <input type="radio" name="tipe" id="editTipeMasuk" value="masuk" class="mr-2 hidden peer" required>
                            <span class="w-full px-3 py-2 bg-emerald-100 text-emerald-700 rounded-lg cursor-pointer hover:bg-emerald-200 peer-checked:bg-emerald-500 peer-checked:text-white ">Income</span>
                        </label>
                        <label class="w-full flex items-center">
                            <input type="radio" name="tipe" id="editTipeKeluar" value="keluar" class="mr-2 hidden peer" required>
                            <span class="w-full px-3 py-2 bg-rose-100 text-rose-700 rounded-lg cursor-pointer hover:bg-rose-200 peer-checked:bg-rose-500 peer-checked:text-white">Expense</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date & Time</label>
                    <input type="datetime-local" name="tanggal" id="editTanggal" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <input type="text" name="kategori" id="editKategori" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500" placeholder="e.g. Food, Salary" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Asset</label>
                    <input type="text" name="aset" id="editAset" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500" placeholder="e.g. Cash, Bank" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <input type="text" name="ket" id="editKet" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500" placeholder="Description">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" id="closeEditModal" class="px-4 py-2 border rounded-lg text-slate-600 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">Update</button>
                </div>
            </form>
        </div>
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

    <script src="./js/transactions.js"></script>
</body>
</html>
