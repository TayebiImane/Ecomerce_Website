<?php
session_start();

// Fonction de connexion
function login($email, $password) {
    include 'includes/db.php';
    $sql = "SELECT * FROM utilisateurs WHERE EMAIL_USER = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['PASSWORD_USER'])) {
        $_SESSION['user'] = $user;
        return true;
    }
    return false;
}

function logout() {
    session_start();
    session_destroy();
    header("Location: /index.php");
    exit();
}
?>
