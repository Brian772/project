<?php
session_start();
    if (!isset($_SESSION['login'])) {
        header("Location: ../src/php/index.php");
        exit;
    }

    include '../src/php/config/conection.php';
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
    <title>Document</title>
    <link rel="stylesheet" href="./css/output.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="font-['Inter']">
    <header class=" fixed w-full h-16 bg-white shadow-md flex items-center px-6 justify-between">
        <div class="flex items-center gap-2">
            <span class="p-2 bg-emerald-500 rounded-md text-center text-white font-extrabold text-balance">KU</span>
            <h1 class="text-xl font-extrabold text-emerald-600">KeuanganKu</h1>
        </div>
        <button class="flex items-center gap-2"> <!-- dummy Profile Button -->
            <span class="hidden sm:block text-sm"><?= $_SESSION['username']; ?></span>
            <img src="" alt="Profile" class="w-9 h-9 sm:w-10 sm:h-10 rounded-full object-cover bg-gray-300">
        </button>
    </header>

    <!-- Side Bar -->
    <div>
        <?php include 'sidebar.php'; ?>
    </div>
    
</body>
<script src="./js/main.js"></script>
</html>