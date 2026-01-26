<?php
session_Start() ;
    include 'config/conection.php';

    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = mysqli_query(
        $conn,
        "SELECT * FROM users WHERE email='$email'"
    );

    $user = mysqli_fetch_assoc($query);

    if ($user && password_verify($password, $user['password'])) {

        $_SESSION['login'] =  true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];

        if (isset($_POST['remember'])) {
            $token = bin2hex(random_bytes(32));

            mysqli_query(
                $conn,
                "UPDATE users SET remember_token='$token' WHERE id=".$user['id']
            );

            setcookie(
                "remember_token",
                $token,
                time() + (86400 * 30), // 30 days
                "/"
            );

            header("Location: ../public/dashboard.php");
            exit;
        }

        header("Location: ../public/dashboard.php");
        exit;

    } else {
        echo "Email atau password salah.";
    }