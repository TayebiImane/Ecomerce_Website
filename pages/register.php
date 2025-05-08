<?php include '../includes/db1.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-bottom: 40px;
        }
        .registration-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            background-color: white;
        }
        .registration-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }
        .form-section {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 4px solid #0d6efd;
        }
        .form-section h3 {
            color: #0d6efd;
            font-size: 1.5rem;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .bi {
            margin-right: 5px;
        }
        .btn-register {
            padding: 10px 30px;
            font-size: 1.1rem;
        }
        .alert {
            margin-top: 20px;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="registration-container">
            <div class="registration-header">
                <h2><i class="bi bi-person-plus"></i> Inscription</h2>
                <p class="text-muted">Créez votre compte pour accéder à nos services</p>
            </div>

            <form method="POST" enctype="multipart/form-data">
                <!-- Section Informations personnelles -->
                <div class="form-section">
                    <h3><i class="bi bi-person"></i> Informations personnelles</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="prenom" class="form-label">Prénom</label>
                                <input type="text" name="prenom" id="prenom" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nom" class="form-label">Nom</label>
                                <input type="text" name="nom" id="nom" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="genre" class="form-label">Genre</label>
                                <select name="genre" id="genre" class="form-select" required>
                                    <option value="" disabled selected>Sélectionner un genre</option>
                                    <?php
                                    $sql = "SELECT * FROM genre";
                                    $stmt = $conn->query($sql);
                                    while ($row = $stmt->fetch()) {
                                        echo "<option value='" . $row['ID_GENRE'] . "'>" . htmlspecialchars($row['LIBELLE_GENRE']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="role" class="form-label">Rôle</label>
                                <select name="role" id="role" class="form-select" required>
                                    <option value="" disabled selected>Sélectionner un rôle</option>
                                    <?php
                                    $sql = "SELECT * FROM role";
                                    $stmt = $conn->query($sql);
                                    while ($row = $stmt->fetch()) {
                                        echo "<option value='" . $row['ID_ROLE'] . "'>" . htmlspecialchars($row['LIBELLE_ROLE']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date_naissance" class="form-label">Date de Naissance</label>
                                <input type="date" name="date_naissance" id="date_naissance" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="telephone" class="form-label">Téléphone</label>
                                <input type="text" name="telephone" id="telephone" class="form-control" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Compte -->
                <div class="form-section">
                    <h3><i class="bi bi-lock"></i> Informations de compte</h3>
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="password" class="form-label">Mot de Passe</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>
                </div>

                <!-- Section Adresse -->
                <div class="form-section">
                    <h3><i class="bi bi-geo-alt"></i> Adresse</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ville" class="form-label">Ville</label>
                                <select name="ville" id="ville" class="form-select" required>
                                    <option value="" disabled selected>Sélectionner une ville</option>
                                    <?php
                                    $sql = "SELECT * FROM ville";
                                    $stmt = $conn->query($sql);
                                    while ($row = $stmt->fetch()) {
                                        echo "<option value='" . $row['ID_VILLE'] . "'>" . htmlspecialchars($row['LINTITULLE_VILLE']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pays" class="form-label">Pays</label>
                                <select name="pays" id="pays" class="form-select" required>
                                    <option value="" disabled selected>Sélectionner un pays</option>
                                    <?php
                                    $sql = "SELECT * FROM pays";
                                    $stmt = $conn->query($sql);
                                    while ($row = $stmt->fetch()) {
                                        echo "<option value='" . $row['ID_PAYS'] . "'>" . htmlspecialchars($row['INTITULE_PAYS']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="rue" class="form-label">Rue</label>
                                <input type="text" name="rue" id="rue" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="code_postal" class="form-label">Code Postal</label>
                                <input type="text" name="code_postal" id="code_postal" class="form-control" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 col-md-6 mx-auto">
                    <button type="submit" name="submit" class="btn btn-primary btn-register">
                        <i class="bi bi-check-circle"></i> inscrire
                    </button>
                </div>
            </form>

            <div class="login-link">
                <p>Vous avez déjà un compte ? <a href="login.php" class="text-decoration-none">Se connecter</a></p>
            </div>

            <?php
            if (isset($_POST['submit'])) {
                $prenom = trim($_POST['prenom']);
                $nom = trim($_POST['nom']);
                $email = trim($_POST['email']);
                $telephone = trim($_POST['telephone']);
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $date_naissance = $_POST['date_naissance'];
                $genre = $_POST['genre'];
                $role = $_POST['role'];
                $date_inscription = date('Y-m-d H:i:s');

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    echo "<div class='alert alert-danger mt-3'><i class='bi bi-exclamation-triangle'></i> Erreur : Email invalide.</div>";
                    exit;
                }

                $stmtCheckEmail = $conn->prepare("SELECT COUNT(*) FROM utilisateur WHERE EMAIL_USER = ?");
                $stmtCheckEmail->execute([$email]);
                if ($stmtCheckEmail->fetchColumn() > 0) {
                    echo "<div class='alert alert-danger mt-3'><i class='bi bi-exclamation-triangle'></i> Erreur : Cet email est déjà utilisé.</div>";
                    exit;
                }

                if (!is_numeric($genre)) {
                    echo "<div class='alert alert-danger mt-3'><i class='bi bi-exclamation-triangle'></i> Erreur : sélection de genre invalide.</div>";
                    exit;
                }
                $genre = (int)$genre;

                $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM genre WHERE ID_GENRE = ?");
                $stmtCheck->execute([$genre]);
                if ($stmtCheck->fetchColumn() == 0) {
                    echo "<div class='alert alert-danger mt-3'><i class='bi bi-exclamation-triangle'></i> Erreur : genre sélectionné non valide.</div>";
                    exit;
                }

                $sql = "INSERT INTO utilisateur (ID_GENRE, ID_ROLE, PRENOM_USER, NOM_USER, EMAIL_USER, TELEPHONE_USER, PASSWORD_USER, DATE_INSCRIPTION, DATE_NAISSANCE) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);

                try {
                    $stmt->execute([$genre, $role, $prenom, $nom, $email, $telephone, $password, $date_inscription, $date_naissance]);
                    echo "<div class='alert alert-success mt-3'><i class='bi bi-check-circle'></i> Utilisateur inscrit avec succès !</div>";
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger mt-3'><i class='bi bi-exclamation-triangle'></i> Erreur : " . htmlspecialchars($e->getMessage()) . "</div>";
                }

                $id_user = $conn->lastInsertId();
                //Creation d'un nouveau panier qui correspont à l'utilisateur
                $sqlPanier = "INSERT INTO panier (DATE_CREATION, ID_USER) VALUES (?, ?)";
                $stmtPanier = $conn->prepare($sqlPanier);

                try {        
                    $stmtPanier->execute([date('Y-m-d H:i:s'), $id_user]);
                    echo "<div class='alert alert-success mt-3'><i class='bi bi-check-circle'></i> Panier créé avec succès !</div>";
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger mt-3'><i class='bi bi-exclamation-triangle'></i> Erreur lors de la création du panier : " . htmlspecialchars($e->getMessage()) . "</div>";
                }

                //Insertion de l'adresse
                $ville = $_POST['ville'];
                $pays = $_POST['pays'];
                $rue = trim($_POST['rue']);
                $code_postal = (int)$_POST['code_postal'];

                $stmtIdAdresse = $conn->query("SELECT MAX(ID_ADRESSE) + 1 AS next_id FROM adresse");
                $nextIdAdresse = $stmtIdAdresse->fetch()['next_id'] ?? 1;

                $sqlAdresse = "INSERT INTO adresse (ID_ADRESSE, ID_USER, ID_VILLE, ID_PAYS, RUE, CODE_POSTAL)
                          VALUES (?, ?, ?, ?, ?, ?)";
                $stmtAdresse = $conn->prepare($sqlAdresse);

                try {
                    $stmtAdresse->execute([$nextIdAdresse, $id_user, $ville, $pays, $rue, $code_postal]);
                    echo "<div class='alert alert-success mt-3'><i class='bi bi-check-circle'></i> Adresse enregistrée avec succès !</div>";
                    
                    // Redirection après inscription réussie
                    echo "<script>
                        setTimeout(function() {
                            window.location.href = 'login.php';
                        }, 3000);
                    </script>";
                    echo "<div class='alert alert-info'><i class='bi bi-info-circle'></i> Vous allez être redirigé vers la page de connexion dans quelques secondes...</div>";
                    
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger mt-3'><i class='bi bi-exclamation-triangle'></i> Erreur lors de l'enregistrement de l'adresse : " . htmlspecialchars($e->getMessage()) . "</div>";
                }
            }
            ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>