<?php
function getUserSettings($conn, $user_id) {
    $q = $conn->prepare("SELECT name, email, profile_picture, theme, language, currency FROM users WHERE id = ?");
    $q->bind_param("i", $user_id);
    $q->execute();
    $result = $q->get_result();
    return $result->fetch_assoc();
}

function updateUserProfile($conn, $user_id, $name, $email, $profile_picture = null) {
    if ($profile_picture) {
        $q = $conn->prepare("UPDATE users SET name = ?, email = ?, profile_picture = ? WHERE id = ?");
        $q->bind_param("sssi", $name, $email, $profile_picture, $user_id);
    } else {
        $q = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $q->bind_param("ssi", $name, $email, $user_id);
    }
    return $q->execute();
}

function updateUserPassword($conn, $user_id, $new_password) {
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $q = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $q->bind_param("si", $hashed_password, $user_id);
    return $q->execute();
}

function updateUserPreferences($conn, $user_id, $theme, $language, $currency) {
    $q = $conn->prepare("UPDATE users SET theme = ?, language = ?, currency = ? WHERE id = ?");
    $q->bind_param("sssi", $theme, $language, $currency, $user_id);
    return $q->execute();
}

function getUserBudget($conn, $user_id) {
    $q = $conn->prepare("SELECT monthly_budget, category_budgets, alert_threshold FROM budgets WHERE user_id = ?");
    $q->bind_param("i", $user_id);
    $q->execute();
    $result = $q->get_result();
    $budget = $result->fetch_assoc();

    if (!$budget) {
        // Create default budget entry if not exists
        $q = $conn->prepare("INSERT INTO budgets (user_id) VALUES (?)");
        $q->bind_param("i", $user_id);
        $q->execute();
        return ['monthly_budget' => 0, 'category_budgets' => '{}', 'alert_threshold' => 80];
    }

    return $budget;
}

function updateUserBudget($conn, $user_id, $monthly_budget, $category_budgets, $alert_threshold) {
    $q = $conn->prepare("
        INSERT INTO budgets (user_id, monthly_budget, category_budgets, alert_threshold)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        monthly_budget = VALUES(monthly_budget),
        category_budgets = VALUES(category_budgets),
        alert_threshold = VALUES(alert_threshold)
    ");
    $q->bind_param("isds", $user_id, $monthly_budget, $category_budgets, $alert_threshold);
    return $q->execute();
}

function exportUserData($conn, $user_id) {
    // Get user info
    $q = $conn->prepare("SELECT name, email, created_at FROM users WHERE id = ?");
    $q->bind_param("i", $user_id);
    $q->execute();
    $user = $q->get_result()->fetch_assoc();

    // Get all transactions
    $q = $conn->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY tanggal DESC");
    $q->bind_param("i", $user_id);
    $q->execute();
    $transactions = $q->get_result()->fetch_all(MYSQLI_ASSOC);

    // Get budget settings
    $budget = getUserBudget($conn, $user_id);

    return [
        'user' => $user,
        'transactions' => $transactions,
        'budget_settings' => $budget,
        'export_date' => date('Y-m-d H:i:s')
    ];
}

function resetUserTransactions($conn, $user_id) {
    $q = $conn->prepare("DELETE FROM transactions WHERE user_id = ?");
    $q->bind_param("i", $user_id);
    return $q->execute();
}

function deleteUserAccount($conn, $user_id) {
    // Delete budget settings
    $q = $conn->prepare("DELETE FROM budgets WHERE user_id = ?");
    $q->bind_param("i", $user_id);
    $q->execute();

    // Delete transactions
    $q = $conn->prepare("DELETE FROM transactions WHERE user_id = ?");
    $q->bind_param("i", $user_id);
    $q->execute();

    // Delete user account
    $q = $conn->prepare("DELETE FROM users WHERE id = ?");
    $q->bind_param("i", $user_id);
    return $q->execute();
}
?>
