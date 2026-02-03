<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

require '../src/php/config/connection.php';
require '../src/php/functions/settings.php';
require '../src/php/functions/finance.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $profile_picture = null;

        // Handle profile picture upload
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../src/uploads/';
            $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $file_name = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_extension;
            $target_file = $upload_dir . $file_name;

            // Check if file is an image
            $check = getimagesize($_FILES['profile_picture']['tmp_name']);
            if ($check !== false) {
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
                    $profile_picture = $file_name;
                } else {
                    $_SESSION['error'] = 'Failed to upload profile picture.';
                    header("Location: settings.php");
                    exit;
                }
            } else {
                $_SESSION['error'] = 'File is not an image.';
                header("Location: settings.php");
                exit;
            }
        }

        if (updateUserProfile($conn, $_SESSION['user_id'], $name, $email, $profile_picture)) {
            $_SESSION['success'] = 'Profile updated successfully!';
        } else {
            $_SESSION['error'] = 'Failed to update profile.';
        }
        header("Location: settings.php");
        exit;
    }

    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Verify current password
        $q = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $q->bind_param("i", $_SESSION['user_id']);
        $q->execute();
        $user = $q->get_result()->fetch_assoc();

        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                if (updateUserPassword($conn, $_SESSION['user_id'], $new_password)) {
                    $_SESSION['success'] = 'Password changed successfully!';
                } else {
                    $_SESSION['error'] = 'Failed to change password.';
                }
            } else {
                $_SESSION['error'] = 'New passwords do not match.';
            }
        } else {
            $_SESSION['error'] = 'Current password is incorrect.';
        }
        header("Location: settings.php");
        exit;
    }

    if (isset($_POST['update_preferences'])) {
        $theme = $_POST['theme'];
        $language = $_POST['language'];
        $currency = $_POST['currency'];

        if (updateUserPreferences($conn, $_SESSION['user_id'], $theme, $language, $currency)) {
            $_SESSION['success'] = 'Preferences updated successfully!';
        } else {
            $_SESSION['error'] = 'Failed to update preferences.';
        }
        header("Location: settings.php");
        exit;
    }

    if (isset($_POST['update_budget'])) {
        $monthly_budget = (float) $_POST['monthly_budget'];
        $category_budgets = json_encode($_POST['category_budgets'] ?? []);
        $alert_threshold = (float) $_POST['alert_threshold'];

        if (updateUserBudget($conn, $_SESSION['user_id'], $monthly_budget, $category_budgets, $alert_threshold)) {
            $_SESSION['success'] = 'Budget settings updated successfully!';
        } else {
            $_SESSION['error'] = 'Failed to update budget settings.';
        }
        header("Location: settings.php");
        exit;
    }

    if (isset($_POST['export_data'])) {
        $data = exportUserData($conn, $_SESSION['user_id']);
        $filename = 'fintrack_data_' . date('Y-m-d') . '.json';

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }

    if (isset($_POST['reset_transactions'])) {
        if (resetUserTransactions($conn, $_SESSION['user_id'])) {
            $_SESSION['success'] = 'All transactions have been reset!';
        } else {
            $_SESSION['error'] = 'Failed to reset transactions.';
        }
        header("Location: settings.php");
        exit;
    }

    if (isset($_POST['delete_account'])) {
        if (deleteUserAccount($conn, $_SESSION['user_id'])) {
            session_destroy();
            header("Location: index.php");
            exit;
        } else {
            $_SESSION['error'] = 'Failed to delete account.';
            header("Location: settings.php");
            exit;
        }
    }
}

// Get current settings
$userSettings = getUserSettings($conn, $_SESSION['user_id']);
$userBudget = getUserBudget($conn, $_SESSION['user_id']);
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
    <title>FinTrack | Settings</title>
    <link rel="stylesheet" href="./css/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="font-['Inter'] bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100">
    <!-- Session Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div id="successModal" class="fixed flex top-6 right-6 items-center justify-end z-50 pointer-events-none animate-slide-in">
            <div class="bg-white border border-emerald-100 rounded-xl shadow-lg p-4 flex items-center gap-4 pointer-events-auto min-w-[300px]">
                <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-check text-emerald-600 text-lg"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800 text-sm">Success</h4>
                    <p class="text-xs text-slate-500"><?= $_SESSION['success']; ?></p>
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
                    <img src="./../src/img/logo.png" alt="Logo" class="w-10 h-10 object-contain rounded-md">
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
                <a href="./transactions.php" class="block px-4 py-2 rounded-lg text-gray-700 dark:text-slate-300 hover:bg-emerald-100 dark:hover:bg-slate-700 hover:text-emerald-900 dark:hover:text-emerald-400 font-medium">
                    <i class="fa-solid fa-wallet mr-2"></i> Transactions
                </a>
                <a href="#" class="block px-4 py-2 rounded-lg bg-emerald-100 dark:bg-slate-700 text-emerald-900 dark:text-emerald-400 font-medium">
                    <i class="fa-solid fa-cog mr-2"></i> Settings
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 h-screen overflow-y-auto bg-slate-50 dark:bg-slate-900">
            <!-- Header -->
            <header class="bg-white dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700 px-6 py-4 flex items-center justify-between sticky top-0 z-10">
                <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Settings</h2>
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

            <div class="p-6 max-w-4xl mx-auto space-y-8">
                <!-- Profile Section -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 mb-6 overflow-hidden">
                    <div class="section-header flex items-center justify-between p-6 cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-700 dark:hover:bg-slate-700" onclick="toggleSection(this)">
                        <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-user text-emerald-600 dark:text-emerald-400"></i> Profile
                        </h3>
                        <i class="fa-solid fa-chevron-right text-gray-400 dark:text-slate-500 transition-transform duration-300 rotate-90"></i>
                    </div>
                    <div class="section-content px-6 pb-6">
                    <form method="POST" enctype="multipart/form-data" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Full Name</label>
                                <input type="text" name="name" value="<?= htmlspecialchars($userSettings['name'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Email</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($userSettings['email']) ?>" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                            </div>
                        </div>
<div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Profile Picture</label>
                            <div class="flex items-center gap-3">
                                <label for="profile_picture" class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 dark:bg-slate-700 border border-emerald-200 dark:border-slate-600 rounded-lg text-emerald-700 dark:text-emerald-400 font-medium hover:bg-emerald-100 dark:hover:bg-slate-600 transition-colors">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                    <span>Choose File</span>
                                </label>
                                <span id="fileName" class="text-sm text-slate-500 dark:text-slate-400 truncate max-w-[200px]">No file chosen</span>
                            </div>
                            <input type="file" id="profile_picture" name="profile_picture" accept="image/*" class="hidden" onchange="document.getElementById('fileName').textContent = this.files[0] ? this.files[0].name : 'No file chosen'">
                        </div>
                        <button type="submit" name="update_profile" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">Update Profile</button>
                    </form>
                    </div>
                </div>

                <!-- Security Section -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 mb-6 overflow-hidden">
                    <div class="section-header flex items-center justify-between p-6 cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-700" onclick="toggleSection(this)">
                        <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-shield-alt text-emerald-600"></i> Security
                        </h3>
                        <i class="fa-solid fa-chevron-right text-gray-400 dark:text-slate-500 transition-transform duration-300"></i>
                    </div>
                    <div class="section-content px-6 pb-6 hidden">
                    <div class="space-y-6">
                        <!-- Change Password Collapsible -->
                        <div class="border border-gray-200 dark:border-slate-600 rounded-lg overflow-hidden">
                            <div class="flex items-center justify-between p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-700 bg-gray-50 dark:bg-slate-700" onclick="toggleSubSection(this)">
                                <h4 class="text-lg font-semibold text-slate-700 dark:text-slate-200">Change Password</h4>
                                <i class="fa-solid fa-chevron-down text-gray-400 dark:text-slate-500 transition-transform duration-300"></i>
                            </div>
                            <div class="subsection-content hidden p-4 bg-white dark:bg-slate-800">
                                <form method="POST" class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Current Password</label>
                                        <input type="password" name="current_password" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">New Password</label>
                                            <input type="password" name="new_password" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Confirm New Password</label>
                                            <input type="password" name="confirm_password" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                                        </div>
                                    </div>
                                    <button type="submit" name="change_password" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">Change Password</button>
                                </form>
                            </div>
                        </div>

                        <!-- Email Verification -->
                        <div>
                            <h4 class="text-lg font-semibold text-slate-700 dark:text-slate-200 mb-3">Email Verification</h4>
                            <?php 
                            $email_verified = true; // Replace with actual verification check from database
                            if ($email_verified): 
                            ?>
                                <p class="text-sm text-slate-600 dark:text-slate-400">Your email is <span class="text-emerald-600 dark:text-emerald-400 font-medium">Verified</span></p>
                            <?php else: ?>
                                <p class="text-sm text-slate-600 dark:text-slate-400 mb-3">Your email is not verified.</p>
                                <button class="px-4 py-2 border border-gray-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700">Resend Verification Email</button>
                            <?php endif; ?>
                        </div>

                        <!-- Logout -->
                        <div class="border-t border-gray-200 dark:border-slate-600 pt-4">
                            <h4 class="text-lg font-semibold text-slate-700 dark:text-slate-200 mb-3">Account Actions</h4>
                            <a href="../src/php/auth/logout.php" class="inline-block px-4 py-2 bg-rose-600 text-white rounded-lg hover:bg-rose-700">Logout</a>
                        </div>
                    </div>
                    </div>
                </div>

                <!-- Preferences Section -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 mb-6 overflow-hidden">
                    <div class="section-header flex items-center justify-between p-6 cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-700" onclick="toggleSection(this)">
                        <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-palette text-emerald-600"></i> Preferences
                        </h3>
                        <i class="fa-solid fa-chevron-right text-gray-400 dark:text-slate-500 transition-transform duration-300"></i>
                    </div>
                    <div class="section-content px-6 pb-6 hidden">
                        <!-- Dark Mode Toggle -->
                        <div class="mb-6 pb-6 border-b border-gray-200 dark:border-slate-600">
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-3">Dark Mode</label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="darkModeToggle" class="sr-only peer" onchange="toggleDarkMode()">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-300 dark:peer-focus:ring-emerald-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-emerald-600"></div>
                                <span class="ms-3 text-sm font-medium text-gray-700 dark:text-gray-300">Enable Dark Mode</span>
                            </label>
                        </div>
                        
                    <form method="POST" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Language</label>
                                <select name="language" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                    <option value="id" <?= ($userSettings['language'] ?? 'id') === 'id' ? 'selected' : '' ?>>Indonesian</option>
                                    <option value="en" <?= ($userSettings['language'] ?? 'id') === 'en' ? 'selected' : '' ?>>English</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Currency Format</label>
                                <select name="currency" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                    <option value="IDR" <?= ($userSettings['currency'] ?? 'IDR') === 'IDR' ? 'selected' : '' ?>>IDR (Rp)</option>
                                    <option value="USD" <?= ($userSettings['currency'] ?? 'IDR') === 'USD' ? 'selected' : '' ?>>USD ($)</option>
                                    <option value="EUR" <?= ($userSettings['currency'] ?? 'IDR') === 'EUR' ? 'selected' : '' ?>>EUR (€)</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" name="update_preferences" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">Update Preferences</button>
                    </form>
                    </div>
                </div>

                <!-- Budget Settings Section -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 mb-6 overflow-hidden">
                    <div class="section-header flex items-center justify-between p-6 cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-700" onclick="toggleSection(this)">
                        <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-chart-pie text-emerald-600"></i> Budget Settings
                        </h3>
                        <i class="fa-solid fa-chevron-right text-gray-400 dark:text-slate-500 transition-transform duration-300"></i>
                    </div>
                    <div class="section-content px-6 pb-6 hidden">
                    <form method="POST" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Monthly Budget</label>
                                <input type="number" name="monthly_budget" value="<?= $userBudget['monthly_budget'] ?? 0 ?>" step="0.01" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Alert Threshold (%)</label>
                                <input type="number" name="alert_threshold" value="<?= $userBudget['alert_threshold'] ?? 80 ?>" min="1" max="100" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Category Budgets (JSON format)</label>
                            <textarea name="category_budgets" rows="4" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder='{"Food": 500000, "Transport": 300000}'><?= $userBudget['category_budgets'] ?? '{}' ?></textarea>
                        </div>
                        <button type="submit" name="update_budget" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">Update Budget Settings</button>
                    </form>
                    </div>
                </div>

                <!-- Data & Account Section -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 mb-6 overflow-hidden">
                    <div class="section-header flex items-center justify-between p-6 cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-700" onclick="toggleSection(this)">
                        <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-database text-emerald-600"></i> Data & Account
                        </h3>
                        <i class="fa-solid fa-chevron-right text-gray-400 dark:text-slate-500 transition-transform duration-300"></i>
                    </div>
                    <div class="section-content px-6 pb-6 hidden">
                    <div class="space-y-6">
                        <!-- Export Data -->
                        <div>
                            <h4 class="text-lg font-semibold text-slate-700 dark:text-slate-200 mb-3">Export Data</h4>
                            <p class="text-sm text-slate-600 mb-3">Download all your financial data in JSON format.</p>
                            <form method="POST" class="inline">
                                <button type="submit" name="export_data" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    <i class="fa-solid fa-download mr-2"></i>Export as JSON
                                </button>
                            </form>
                        </div>

                        <!-- Reset Transactions -->
                        <div class="border-t border-gray-200 dark:border-slate-600 pt-6">
                            <h4 class="text-lg font-semibold text-slate-700 dark:text-slate-200 mb-3">Reset All Transactions</h4>
                            <p class="text-sm text-slate-600 mb-3">This will permanently delete all your transaction data. This action cannot be undone.</p>
                            <button onclick="confirmReset()" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                                <i class="fa-solid fa-refresh mr-2"></i>Reset All Transactions
                            </button>
                        </div>

                        <!-- Delete Account (Danger Zone) -->
                        <div class="border-t border-gray-200 dark:border-slate-600 pt-6">
                            <h4 class="text-lg font-semibold text-rose-700 dark:text-rose-400 mb-3 flex items-center gap-2">
                                <i class="fa-solid fa-triangle-exclamation"></i> Danger Zone
                            </h4>
                            <p class="text-sm text-slate-600 mb-3">Once you delete your account, there is no going back. Please be certain.</p>
                            <button onclick="confirmDelete()" class="px-4 py-2 bg-rose-600 text-white rounded-lg hover:bg-rose-700">
                                <i class="fa-solid fa-trash-can mr-2"></i>Delete Account
                            </button>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Reset Confirmation Modal -->
    <div id="resetModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center px-4 z-50 hidden">
        <div class="bg-white rounded-2xl w-full max-w-sm p-6">
            <div class="text-center">
                <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-triangle-exclamation text-yellow-500 text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-2">Reset All Transactions?</h3>
                <p class="text-sm text-slate-500 mb-6">This will permanently delete all your transaction data. This action cannot be undone.</p>

                <div class="flex gap-3 justify-center">
                    <button id="cancelReset" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-medium hover:bg-slate-50 transition-colors w-full">
                        Cancel
                    </button>
                    <form method="POST" class="w-full">
                        <button type="submit" name="reset_transactions" class="w-full px-5 py-2.5 rounded-xl bg-yellow-600 text-white font-medium hover:bg-yellow-700 transition-colors">
                            Reset All
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Account Confirmation Modal -->
    <div id="deleteAccountModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center px-4 z-50 hidden">
        <div class="bg-white rounded-2xl w-full max-w-sm p-6">
            <div class="text-center">
                <div class="w-16 h-16 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-trash-can text-rose-500 text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-2">Delete Account?</h3>
                <p class="text-sm text-slate-500 mb-6">This will permanently delete your account and all associated data. This action cannot be undone.</p>

                <div class="flex gap-3 justify-center">
                    <button id="cancelDeleteAccount" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-medium hover:bg-slate-50 transition-colors w-full">
                        Cancel
                    </button>
                    <form method="POST" class="w-full">
                        <button type="submit" name="delete_account" class="w-full px-5 py-2.5 rounded-xl bg-rose-600 text-white font-medium hover:bg-rose-700 transition-colors">
                            Delete Account
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmReset() {
            document.getElementById('resetModal').classList.remove('hidden');
        }

        function confirmDelete() {
            document.getElementById('deleteAccountModal').classList.remove('hidden');
        }

        document.getElementById('cancelReset').addEventListener('click', () => {
            document.getElementById('resetModal').classList.add('hidden');
        });

        document.getElementById('cancelDeleteAccount').addEventListener('click', () => {
            document.getElementById('deleteAccountModal').classList.add('hidden');
        });
        
        // Toggle Section Function
        function toggleSection(header) {
            const content = header.nextElementSibling;
            const arrow = header.querySelector('.fa-chevron-right');
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                arrow.classList.add('rotate-90');
            } else {
                content.classList.add('hidden');
                arrow.classList.remove('rotate-90');
            }
        }
        
        // Dark Mode Toggle Function
        function toggleDarkMode() {
            const htmlElement = document.documentElement;
            const toggle = document.getElementById('darkModeToggle');
            
            if (htmlElement.classList.contains('dark')) {
                htmlElement.classList.remove('dark');
                localStorage.setItem('darkMode', 'disabled');
            } else {
                htmlElement.classList.add('dark');
                localStorage.setItem('darkMode', 'enabled');
            }
        }
        
        // Set initial checkbox state based on localStorage
        document.addEventListener('DOMContentLoaded', function() {
            const darkModeToggle = document.getElementById('darkModeToggle');
            const isDarkMode = localStorage.getItem('darkMode') === 'enabled';
            if (darkModeToggle) {
                darkModeToggle.checked = isDarkMode;
            }
            // Also ensure HTML has dark class if needed
            if (isDarkMode) {
                document.documentElement.classList.add('dark');
            }
        });
        
        // Toggle SubSection Function (for nested collapsible like Change Password)
        function toggleSubSection(header) {
            const content = header.nextElementSibling;
            const arrow = header.querySelector('.fa-chevron-down');
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                arrow.classList.add('rotate-180');
            } else {
                content.classList.add('hidden');
                arrow.classList.remove('rotate-180');
            }
        }
    </script>
</body>
</html>
