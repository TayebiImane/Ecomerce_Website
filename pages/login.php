<?php
include '../includes/header.php';
include '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (login($email, $password)) {
        header('Location: ../index.php');
        exit();
    } else {
        echo "<div class='alert alert-danger'>Email ou mot de passe incorrect</div>";
    }
}
?>

<form method="POST">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Mot de passe" required>
    <button type="submit">Se connecter</button>
</form>
<?php include '../includes/footer.php'; ?>