<?php
function getAllTransactions($conn, $user_id, $filters = []) {
    $where = ["user_id = ?"];
    $params = [$user_id];
    $types = "i";
    
    // Filter by type
    if (!empty($filters['type']) && in_array($filters['type'], ['masuk', 'keluar'])) {
        $where[] = "tipe = ?";
        $params[] = $filters['type'];
        $types .= "s";
    }
    
    // Filter by search
    if (!empty($filters['search'])) {
        $where[] = "ket LIKE ?";
        $params[] = "%" . $filters['search'] . "%";
        $types .= "s";
    }
    
    // Filter by date range
    if (!empty($filters['date_from'])) {
        $where[] = "DATE(tanggal) >= ?";
        $params[] = $filters['date_from'];
        $types .= "s";
    }
    
    if (!empty($filters['date_to'])) {
        $where[] = "DATE(tanggal) <= ?";
        $params[] = $filters['date_to'];
        $types .= "s";
    }
    
    $whereSql = implode(" AND ", $where);
    $orderBy = $filters['order_by'] ?? 'tanggal DESC';
    
    $sql = "SELECT id, ket, tanggal, nominal, tipe
            FROM transactions 
            WHERE $whereSql 
            ORDER BY $orderBy";
    
    $q = $conn->prepare($sql);
    
    // Bind parameters using reference array
    if (!empty($params)) {
        $bindParams = [$types];
        foreach ($params as $key => $value) {
            $bindParams[] = &$params[$key];
        }
        call_user_func_array([$q, 'bind_param'], $bindParams);
    }
    
    $q->execute();
    $result = $q->get_result();
    
    $transactions = [];
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
    
    return $transactions;
}

function getTransactionStats($conn, $user_id, $filters = []) {
    $where = ["user_id = ?"];
    $params = [$user_id];
    $types = "i";
    
    // Apply same filters as getAllTransactions
    if (!empty($filters['type']) && in_array($filters['type'], ['masuk', 'keluar'])) {
        $where[] = "tipe = ?";
        $params[] = $filters['type'];
        $types .= "s";
    }
    
    if (!empty($filters['search'])) {
        $where[] = "ket LIKE ?";
        $params[] = "%" . $filters['search'] . "%";
        $types .= "s";
    }
    
    if (!empty($filters['date_from'])) {
        $where[] = "DATE(tanggal) >= ?";
        $params[] = $filters['date_from'];
        $types .= "s";
    }
    
    if (!empty($filters['date_to'])) {
        $where[] = "DATE(tanggal) <= ?";
        $params[] = $filters['date_to'];
        $types .= "s";
    }
    
    $whereSql = implode(" AND ", $where);
    
    // Prepare bind params array
    $bindParams = [$types];
    foreach ($params as $key => $value) {
        $bindParams[] = &$params[$key];
    }
    
    // Get income
    $sql = "SELECT COALESCE(SUM(nominal), 0) as total 
            FROM transactions 
            WHERE $whereSql AND tipe = 'masuk'";
    $q = $conn->prepare($sql);
    if (!empty($params)) {
        call_user_func_array([$q, 'bind_param'], $bindParams);
    }
    $q->execute();
    $income = $q->get_result()->fetch_assoc()['total'];
    
    // Reset bind params for expense query
    $bindParams = [$types];
    foreach ($params as $key => $value) {
        $bindParams[] = &$params[$key];
    }
    
    // Get expense
    $sql = "SELECT COALESCE(SUM(nominal), 0) as total 
            FROM transactions 
            WHERE $whereSql AND tipe = 'keluar'";
    $q = $conn->prepare($sql);
    if (!empty($params)) {
        call_user_func_array([$q, 'bind_param'], $bindParams);
    }
    $q->execute();
    $expense = $q->get_result()->fetch_assoc()['total'];
    
    return [
        'income' => $income,
        'expense' => $expense,
        'balance' => $income - $expense
    ];
}

function getTransactionById($conn, $transaction_id, $user_id) {
    $q = $conn->prepare("SELECT * FROM transactions WHERE id = ? AND user_id = ?");
    $q->bind_param("ii", $transaction_id, $user_id);
    $q->execute();
    return $q->get_result()->fetch_assoc();
}

function deleteTransaction($conn, $transaction_id, $user_id) {
    $q = $conn->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
    $q->bind_param("ii", $transaction_id, $user_id);
    return $q->execute();
}

function updateTransaction($conn, $transaction_id, $user_id, $data) {
    $q = $conn->prepare("
        UPDATE transactions 
        SET nominal = ?, tipe = ?, ket = ?, tanggal = ?
        WHERE id = ? AND user_id = ?
    ");
    
    $q->bind_param(
        "isssii",
        $data['nominal'],
        $data['tipe'],
        $data['ket'],
        $data['tanggal'],
        $transaction_id,
        $user_id
    );
    
    return $q->execute();
}
?>
