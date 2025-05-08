<?php
session_start();
include '../includes/db1.php'; // $conn = new PDO(...);
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $stmt = $conn->prepare("SELECT ID_USER, PRENOM_USER, NOM_USER, PASSWORD_USER FROM utilisateur WHERE EMAIL_USER = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['PASSWORD_USER'])) {
        $_SESSION['user_id'] = $user['ID_USER'];
        $_SESSION['email'] = $email;
        $_SESSION['nom'] = $user['NOM_USER'];
        $_SESSION['prenom'] = $user['PRENOM_USER'];
        header("Location: ../index.php");
        exit;
    } else {
        $error = "Email ou mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 450px;
            margin: 100px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            background-color: white;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-footer {
            text-align: center;
            margin-top: 20px;
        }
        .form-floating {
            margin-bottom: 20px;
        }
        .bi {
            margin-right: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <h2><i class="bi bi-lock"></i> Connexion</h2>
                <p class="text-muted">Veuillez vous connecter pour continuer</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-floating">
                    <input type="email" class="form-control" id="email" name="email" placeholder="nom@exemple.com" required>
                    <label for="email"><i class="bi bi-envelope"></i> Adresse email</label>
                </div>
                
                <div class="form-floating">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Mot de passe" required>
                    <label for="password"><i class="bi bi-key"></i> Mot de passe</label>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-box-arrow-in-right"></i> Se connecter
                    </button>
                </div>
            </form>
            
            <div class="login-footer">
                <p>Vous n'avez pas de compte ? <a href="register.php" class="text-decoration-none">S'inscrire</a></p>
                <p><a href="forgot-password.php" class="text-decoration-none">Mot de passe oublié ?</a></p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php include '../includes/footer.php'; ?>