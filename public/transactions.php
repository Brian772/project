<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ./index.php");
    exit;
}

require '../src/php/config/connection.php';
require '../src/php/functions/finance.php';
require '../src/php/functions/transactions.php';

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FinTrack | Transactions</title>
    <link rel="stylesheet" href="./css/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="font-['Inter'] bg-slate-50 text-slate-800">
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
                <a href="./transactions.php" class="block px-4 py-2 rounded-lg bg-emerald-100 text-emerald-900 font-medium">
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
                <h2 class="text-2xl font-bold text-slate-800">Transactions</h2>
                <button class="flex items-center gap-2">
                    <span class="hidden sm:block text-sm">Hello, Brian</span>
                    <img src="" alt="Profile" class="w-9 h-9 sm:w-10 sm:h-10 rounded-full object-cover bg-gray-300">
                </button>
            </header>

            <div class="p-6 max-w-7xl mx-auto space-y-6">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                        <p class="text-sm text-slate-500">Total Income</p>
                        <h3 class="text-2xl font-bold text-emerald-600"><?= formatRupiah($stats['income']) ?></h3>
                    </div>
                    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                        <p class="text-sm text-slate-500">Total Expense</p>
                        <h3 class="text-2xl font-bold text-rose-600"><?= formatRupiah($stats['expense']) ?></h3>
                    </div>
                    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                        <p class="text-sm text-slate-500">Net Balance</p>
                        <h3 class="text-2xl font-bold text-slate-800"><?= formatRupiah($stats['balance']) ?></h3>
                    </div>
                </div>

                <!-- Filters & Search -->
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                    <form method="GET" class="space-y-4">
                        <!-- Search Bar -->
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input 
                                type="text" 
                                name="search" 
                                value="<?= htmlspecialchars($filters['search']) ?>"
                                placeholder="Search transactions..." 
                                class="w-full pl-11 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            >
                        </div>

                        <!-- Filter Buttons -->
                        <div class="flex flex-wrap gap-3">
                            <a href="?type=" class="px-4 py-2 rounded-lg border-2 <?= empty($filters['type']) ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-gray-200 text-gray-600 hover:border-gray-300' ?> font-medium transition-all">
                                All
                            </a>
                            <a href="?type=masuk<?= !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : '' ?>" class="px-4 py-2 rounded-lg border-2 <?= $filters['type'] === 'masuk' ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-gray-200 text-gray-600 hover:border-gray-300' ?> font-medium transition-all">
                                <i class="fa-solid fa-arrow-down text-emerald-600"></i> Income
                            </a>
                            <a href="?type=keluar<?= !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : '' ?>" class="px-4 py-2 rounded-lg border-2 <?= $filters['type'] === 'keluar' ? 'border-rose-500 bg-rose-50 text-rose-700' : 'border-gray-200 text-gray-600 hover:border-gray-300' ?> font-medium transition-all">
                                <i class="fa-solid fa-arrow-up text-rose-600"></i> Expense
                            </a>
                            
                            <button type="submit" class="ml-auto px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-medium">
                                <i class="fa-solid fa-filter mr-2"></i>Apply Filters
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Transactions List -->
                <div class="space-y-3">
                    <?php if (empty($transactions)): ?>
                        <div class="bg-white p-12 rounded-xl shadow-sm border border-gray-100 text-center">
                            <i class="fa-solid fa-inbox text-6xl text-gray-300 mb-4"></i>
                            <h3 class="text-xl font-semibold text-gray-600 mb-2">No Transactions Found</h3>
                            <p class="text-gray-500">Start by adding your first transaction!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($transactions as $trx): ?>
                            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <!-- Icon -->
                                        <div class="w-12 h-12 rounded-full flex items-center justify-center <?= $trx['tipe'] === 'masuk' ? 'bg-emerald-100' : 'bg-rose-100' ?>">
                                            <i class="fa-solid <?= $trx['tipe'] === 'masuk' ? 'fa-arrow-down text-emerald-600' : 'fa-arrow-up text-rose-600' ?> text-xl"></i>
                                        </div>
                                        
                                        <!-- Info -->
                                        <div>
                                            <h4 class="font-semibold text-slate-800"><?= htmlspecialchars($trx['ket']) ?></h4>
                                            <div class="flex items-center gap-3 text-sm text-slate-500 mt-1">
                                                <span><i class="fa-regular fa-calendar mr-1"></i><?= date('d M Y, H:i', strtotime($trx['tanggal'])) ?></span>
                                                <?php if (!empty($trx['kategori'])): ?>
                                                    <span><i class="fa-solid fa-tag mr-1"></i><?= htmlspecialchars($trx['kategori']) ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($trx['aset'])): ?>
                                                    <span><i class="fa-solid fa-wallet mr-1"></i><?= htmlspecialchars($trx['aset']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Amount & Actions -->
                                    <div class="flex items-center gap-4">
                                        <span class="text-xl font-bold <?= $trx['tipe'] === 'masuk' ? 'text-emerald-600' : 'text-rose-600' ?>">
                                            <?= $trx['tipe'] === 'masuk' ? '+' : '-' ?><?= formatRupiah($trx['nominal']) ?>
                                        </span>
                                        
                                        <!-- Action Buttons -->
                                        <div class="flex gap-2">
                                            <button onclick="editTransaction(<?= $trx['id'] ?>)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <button onclick="deleteTransaction(<?= $trx['id'] ?>)" class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Delete">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
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
                    <select name="tipe" id="editTipe" class="w-full border rounded-lg p-2" required>
                        <option value="masuk">Income</option>
                        <option value="keluar">Expense</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date & Time</label>
                    <input type="datetime-local" name="tanggal" id="editTanggal" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <input type="text" name="kategori" id="editKategori" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500" placeholder="Optional">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Asset</label>
                    <input type="text" name="aset" id="editAset" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500" placeholder="Optional">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <input type="text" name="ket" id="editKet" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500" placeholder="Description" required>
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
        <div class="bg-white rounded-xl w-96 p-6">
            <h2 class="text-2xl font-bold mb-4 text-emerald-800">Add Transaction</h2>
            <form action="../src/php/transactions/store.php" method="POST" class="space-y-4">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nominal</label>
                    <input type="text" id="nominalInput" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 text-lg font-semibold" placeholder="Rp 0" autocomplete="off" required>
                    <input type="hidden" name="nominal" id="nominalHidden">
                </div>

                <select name="tipe" class="w-full border rounded-lg mb-4 p-2" required>
                    <option value="" disabled selected>Select Type</option>
                    <option value="masuk">Income</option>
                    <option value="keluar">Expense</option>
                </select>
                
                <input type="text" class="w-full mb-4 px-3 py-4 border rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" name="ket" placeholder="Description" required>
                
                <div class="flex justify-end gap-2">
                    <button type="button" id="closeModal" class="px-4 py-2 border rounded-lg text-slate-600 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">Add</button>
                </div>
            </form>
        </div>
    </div>

    <script src="./js/transactions.js"></script>
</body>
</html>
