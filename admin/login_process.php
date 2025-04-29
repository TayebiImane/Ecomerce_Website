<?php
// Afficher les erreurs pour debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("
      SELECT u.*, a.ID_ROLE
FROM UTILISATEUR u
LEFT JOIN AVOIR a ON u.ID_USER = a.ID_USER
WHERE u.EMAIL_USER = ?

    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if (password_verify($password, $user['PASSWORD_USER'])) {
            $_SESSION['user_id'] = $user['ID_USER'];
            $_SESSION['role'] = $user['ID_ROLE'] ?? null; // Prend null si pas trouvé

            echo "<p style='color:green;'>Mot de passe valide ! Redirection...</p>";
            header("Location: /Ecomerce_Website-main/pages/Admin/dashboard.php");
exit();

            exit();
        } else {
            echo "<p style='color:red;'>Mot de passe incorrect.</p>";
        }
    } else {
        echo "<p style='color:red;'>Email incorrect ou utilisateur non trouvé.</p>";
    }
} else {
    echo "<p>Merci d'utiliser le formulaire de connexion.</p>";
}
?>
