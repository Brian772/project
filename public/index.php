<?php
session_start();
if (isset($_GET['from']) && $_GET['from'] === 'register') {
    unset($_SESSION['login']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title>FinTrack | Login</title>
</head>
<body class="font-['Inter'] bg-gray-100">
    <header class="w-full h-16 bg-white border-b border-gray-200 flex items-center px-6 z-10">
        <div class="flex gap-2 flex-row">
            <img src="../src/img/logo.png" alt="Logo" class="w-10 h-10 object-contain rounded-md">
            <div class="flex flex-col items-center md:items-start">
                <h1 class="text-2xl font-bold tracking-tight text-emerald-950">FinTrack</h1>
                <span class="text-xs text-gray-400">Track Every Worth Precisely</span>
            </div>
        </div>
    </header><br>

    <!-- Session Success Message -->
    <?php if (isset($_SESSION['success'])): ?>
        <div id="successModal" class="fixed flex top-20 right-1 -translate-x-1 items-center justify-end z-50 mr-4 pointer-events-none">
            <div class="w-full max-w-md bg-white border rounded-2xl shadow-xl p-5 text-center pointer-events-auto relative">
                <div class="text-emerald-500 text-xl mb-3">
                    <i class="fa-solid fa-circle-check fa-3x"></i>
                    <h2 class="text-lg font-bold mb-2">Registration Success</h2>
                    <p class="text-sm text-gray-400 mb-5">
                        <?= $_SESSION['success']; ?>
                    </p>
                </div>
            </div>
            <?php unset($_SESSION['success']); endif?>
        </div>

    <!-- Login Form -->
    <div class="min-h-screen fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center px-4 z-40">
        <div class="w-full max-w-md max-h-min bg-white rounded-xl shadow-xl p-8 relative">
            <h2 class=" text-2xl font-extrabold text-center mb-2 text-emerald-800">
                Login
            </h2>
            <p class="text-xs text-gray-400 text-center mb-6">Login to your account</p>

            <form action="/project - copy/src/php/auth/login.php" method="POST" class="flex flex-col gap-4">
                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-600 mb-1">Email :</label>
                    <input 
                        type="email"
                        name="email" 
                        required
                        class="w-full px-3 py-2 border rounded-full focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        placeholder="example@email.com"
                    >
                </div>
                <!-- Password Input -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-600 mb-1">
                        Password :
                    </label>
                    <div class="flex items-center gap-2">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="flex-1 px-3 py-2 border rounded-full focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            placeholder="password"
                            required
                        >
                        <button
                            type="button"
                            onclick="togglePasswordVisibility(this)"
                            class="text-emerald-500 hover:text-emerald-700 flex items-center justify-center p-2"
                        >
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!--Session Error Message-->
                <?php
                if (isset($_SESSION['error'])): ?>
                    <p class="text-sm text-red-500 mt-3">
                        <?= $_SESSION['error']; ?>
                    </p>
                    <?php unset($_SESSION['error']); endif; ?>

                <!-- Remember Me Checkbox -->
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember" class="text-sm text-gray-600 ml-2">Remember me</label>
                </div>
                
                <!-- Submit Button -->
                <button 
                type="submit"
                class=" h-8 bg-emerald-500 rounded-full hover:bg-emerald-700 text-white font-bold transition">Login</button>

                <!-- Sing up Button -->
                <p class="text-sm text-center text-gray-500 mt-4">
                    Don`t have an account? 
                    <a href="register.php" class="text-emerald-500 hover:underline">
                        Sign up
                    </a>
                </p>
            </form>
        </div>
    </div>
</body>
<script src="./js/main.js"></script>
</html>