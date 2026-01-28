<?php
session_start();
    if (!isset($_SESSION['login'])) {
        header("Location: ./index.php");
        exit;
    }

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
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
            <?php unset($_SESSION['success']); endif?>
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
                <a href="#" class="block px-4 py-2 rounded-lg text-gray-700 hover:bg-emerald-100 hover:text-emerald-900 font-medium">
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
                    <span class="hidden sm:block text-sm">Hello, John Doe</span>
                    <img src="" alt="Profile" class="w-9 h-9 sm:w-10 sm:h-10 rounded-full object-cover bg-gray-300">
                </button>
            </header>

            <!-- Dashboard Content -->
            <div class="p-6 max-w-7xl mx-auto space-y-6">
                <!-- Cards Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold mb-4">Content</h3>
                        <p class="text-slate-600">The Main Content Will Appear Here</p>
                    </div>
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold mb-4">Content</h3>
                        <p class="text-slate-600">The Main Content Will Appear Here</p>
                    </div>
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold mb-4">Content</h3>
                        <p class="text-slate-600">The Main Content Will Appear Here</p>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold mb-4">Chart</h3>
                        <p class="text-slate-600">Chart Will Appear Here</p>
                    </div>
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold mb-4">Chart</h3>
                        <p class="text-slate-600">Chart Will Appear Here</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
<script src="./js/main.js"></script>
</html>