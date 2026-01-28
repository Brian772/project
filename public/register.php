<?php
session_start();
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
    <title>FinTrack | Register</title>
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

    <div class="min-h-screen fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center px-4 z-40">
        <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-8">
            <h2 class="text-2xl font-bold text-center text-emerald-800 mb-2"> Sign Up</h2>
            <p class="text-center text-sm text-gray-400 mb-6">Create New Account</p>

            <form action="../src/php/auth/register_process.php" method="POST" class="space-y-4">

                <!-- Email -->
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

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-600 mb-1">
                        Password :
                    </label>
                    <div class="flex items-center gap-2">
                        <input 
                            type="password"
                            id="password" 
                            name="password" 
                            required
                            class="flex-1 px-3 py-2 border rounded-full focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            placeholder="password"
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

                <!-- Confirm Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Confirm Password :</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        required
                        class="w-full px-3 py-2 border rounded-full focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        placeholder="confirm password"
                    >
                </div>

                <!-- Error Message -->
                <?php
                if (isset($_SESSION['error'])): ?>
                    <p class="text-sm text-red-500 mt-3 text-center">
                        <?= $_SESSION['error']; ?>
                    </p>
                    <?php unset($_SESSION['error']); endif; ?>

                <!-- Submit Button -->
                <button
                type="submit"
                class="w-full bg-emerald-500 hover:bg-emerald-700 text-white font-bold py-2 rounded-full transition"
                >
                    Sign Up
                </button>

                <p class="text-sm text-center text-gray-500 mt-4">
                    Have an account? 
                    <a href="index.php?from=register" class="text-emerald-500 hover:underline">
                        Login
                    </a>
                </p>
            </form>
        </div>

    </div>
    
</body>
<script src="./js/main.js"></script>
</html>