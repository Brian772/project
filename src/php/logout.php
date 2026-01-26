<?php
session_start();
include 'config/conection.php';

if(isset($_SESSION['user_id'])) {
    mysqli_query(
        $conn,
        "UPDATE users SET remember_token=NULL WHERE id=".$_SESSION['user_id']
    );
}

setccookie("remember_token", "", time() - 3600, "/");
session_destroy();
header("Location: ./index.php");
