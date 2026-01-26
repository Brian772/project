<?php
session_start();

if (isset($_SESSION['login'])) {
    header("Location: ../public/dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/output.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title>Login | KeuanganKu</title>
</head>
<body class="font-['Inter']">
    <header class="w-full h-16 bg-white shadow-md flex items-center px-6 z-10">
        <div class="flex items-center gap-2">
            <span class="p-2 bg-emerald-500 rounded-md text-center text-white font-extrabold text-balance">KU</span>
            <h1 class="text-xl font-extrabold text-emerald-600">KeuanganKu</h1>
        </div>
    </header><br>

    <!-- Login Form -->
    <div class="fixed inset-0 bg-black/40 flex item-center justify-center z-20">
        <div class="w-full max-w-md max-h-min bg-white rounded-xl shadow-xl p-8 relative">
            <h2 class=" text-2xl font-extrabold text-center mb-2 text-green-800">
                Login
            </h2>
            <p class="text-xs text-gray-400 text-center mb-6">Masuk untuk mengakses dashboard</p>

            <form action="login.php" method="POST" class="flex flex-col gap-4">
                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-600 mb-1">Email :</label>
                    <input 
                        type="email" 
                        id="email" 
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
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        class="w-full px-3 py-2 border rounded-full focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        placeholder="password"
                    >
                </div>

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
</html>