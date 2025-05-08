<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();
session_start();
include("inc/CSRF_Protect.php");
include("../includes/db.php");
$csrf = new CSRF_Protect();
$error_message='';
if (isset($_POST['form1'])) {
    if (empty($_POST['email']) || empty($_POST['password'])) {
        $error_message = 'Email and/or Password cannot be empty<br>';
    } else {
        $email = strip_tags($_POST['email']);
        $password = strip_tags($_POST['password']);
        // Requête adaptée pour ta table UTILISATEUR
        $statement = $pdo->prepare("SELECT * FROM utilisateur WHERE EMAIL_USER = ?");
        $statement->execute([$email]);
        $total = $statement->rowCount();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        if ($total == 0) {
            $error_message .= 'Email Address does not match<br>';
        } else {
            foreach ($result as $row) {
                $row_password = $row['PASSWORD_USER'];
            }
            if (!password_verify($password, $row_password)) {
                $error_message .= 'Password does not match<br>';
            } else {
                // Enregistrer les informations utilisateur en session
                $_SESSION['user'] = $row;
                header("Location: product.php");
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Connexion Admin</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Chargement des CSS originaux -->
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/ionicons.min.css">
    <link rel="stylesheet" href="css/datepicker3.css">
    <link rel="stylesheet" href="css/all.css">
    <link rel="stylesheet" href="css/select2.min.css">
    <link rel="stylesheet" href="css/dataTables.bootstrap.css">
    <link rel="stylesheet" href="css/AdminLTE.min.css">
    <link rel="stylesheet" href="css/_all-skins.min.css">
    
    <style>
        :root {
            --primary-blue: #1a73e8;
            --light-blue: #e8f0fe;
            --dark-blue: #0d47a1;
            --accent-blue: #4285f4;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        
        .login-page {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-box {
            max-width: 450px;
            width: 100%;
            padding: 0;
        }
        
        .login-logo {
            text-align: center;
            margin-bottom: 20px;
            color: var(--primary-blue);
            font-size: 24px;
        }
        
        .login-box-body {
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            background-color: white;
        }
        
        .login-box-msg {
            text-align: center;
            font-size: 18px;
            margin-bottom: 25px;
            color: #555;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .form-control {
            height: 50px;
            padding-left: 40px;
            border: 1px solid #ddd;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 0.25rem rgba(26, 115, 232, 0.25);
        }
        
        .form-group .bi {
            position: absolute;
            left: 15px;
            top: 15px;
            color: #aaa;
        }
        
        .login-button {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
            padding: 12px;
            font-size: 16px;
            font-weight: 500;
            border-radius: 8px;
            width: 100%;
            margin-top: 10px;
            transition: all 0.3s;
        }
        
        .login-button:hover {
            background-color: var(--dark-blue);
            border-color: var(--dark-blue);
        }
        
        .error {
            background-color: #ffeaea;
            border-left: 4px solid #f44336;
            color: #d32f2f;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 25px;
            color: #666;
        }
        
        .login-footer a {
            color: var(--primary-blue);
            text-decoration: none;
        }
        
        .login-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body class="hold-transition login-page">
    <div class="login-box">
        <div class="login-logo">
            <b><i class="bi bi-shield-lock"></i> Admin Panel</b>
        </div>
        <div class="login-box-body">
            <p class="login-box-msg">Connexion à votre espace administrateur</p>
            
            <?php
            if((isset($error_message)) && ($error_message!='')):
                echo '<div class="error"><i class="bi bi-exclamation-triangle"></i> '.$error_message.'</div>';
            endif;
            ?>
            
            <form action="" method="post">
                <?php $csrf->echoInputField(); ?>
                <div class="form-group">
                    <i class="bi bi-envelope"></i>
                    <input class="form-control" placeholder="Adresse email" name="email" type="email" autocomplete="off" autofocus>
                </div>
                <div class="form-group">
                    <i class="bi bi-key"></i>
                    <input class="form-control" placeholder="Mot de passe" name="password" type="password" autocomplete="off" value="">
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary login-button" name="form1">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                        </button>
                    </div>
                </div>
            </form>
            
            <div class="login-footer">
                <p>Vous avez oublié votre mot de passe? <a href="reset-password.php">Réinitialiser</a></p>
            </div>
        </div>
    </div>

    <!-- Scripts JavaScript -->
    <script src="js/jquery-2.2.3.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.dataTables.min.js"></script>
    <script src="js/dataTables.bootstrap.min.js"></script>
    <script src="js/select2.full.min.js"></script>
    <script src="js/jquery.inputmask.js"></script>
    <script src="js/jquery.inputmask.date.extensions.js"></script>
    <script src="js/jquery.inputmask.extensions.js"></script>
    <script src="js/moment.min.js"></script>
    <script src="js/bootstrap-datepicker.js"></script>
    <script src="js/icheck.min.js"></script>
    <script src="js/fastclick.js"></script>
    <script src="js/jquery.sparkline.min.js"></script>
    <script src="js/jquery.slimscroll.min.js"></script>
    <script src="js/app.min.js"></script>
    <script src="js/demo.js"></script>
</body>
</html>