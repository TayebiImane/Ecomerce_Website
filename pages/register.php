<?php include '../includes/db.php'; ?>
<?php include '../includes/header.php'; ?>

<div class="container mt-5">
    <h2>Inscription</h2>
    <form method="POST" enctype="multipart/form-data">
        
        <div class="form-group">
            <label>Prénom</label>
            <input type="text" name="prenom" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label>Nom</label>
            <input type="text" name="nom" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Genre</label>
            <select name="genre" class="form-control" required>
                <option value="" disabled selected>Sélectionner un genre</option>
                <?php
                $sql = "SELECT * FROM GENRE";
                $stmt = $conn->query($sql);
                while ($row = $stmt->fetch()) {
                    echo "<option value='" . $row['ID_GENRE'] . "'>" . htmlspecialchars($row['LIBELLE_GENRE']) . "</option>";
                }
                ?>
            </select>
        </div>
        
        <div class="form-group">
            <label>Date de Naissance</label>
            <input type="date" name="date_naissance" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Téléphone</label>
            <input type="text" name="telephone" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Mot de Passe</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <button type="submit" name="submit" class="btn btn-primary mt-3">S'inscrire</button>
    </form>
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
    $date_inscription = date('Y-m-d H:i:s');

    // Validation du genre : doit être un entier
    if (!is_numeric($genre)) {
        echo "<div class='alert alert-danger mt-3'>Erreur : sélection de genre invalide.</div>";
        exit;
    }
    $genre = (int)$genre;

    // Vérification que le genre existe en base
    $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM GENRE WHERE ID_GENRE = ?");
    $stmtCheck->execute([$genre]);
    if ($stmtCheck->fetchColumn() == 0) {
        echo "<div class='alert alert-danger mt-3'>Erreur : genre sélectionné non valide.</div>";
        exit;
    }

    // Insertion utilisateur
    $sql = "INSERT INTO UTILISATEUR (ID_GENRE, PRENOM_USER, NOM_USER, EMAIL_USER, TELEPHONE_USER, PASSWORD_USER, DATE_INSCRIPTION, DATE_NAISSANCE) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    try {
        $stmt->execute([$genre, $prenom, $nom, $email, $telephone, $password, $date_inscription, $date_naissance]);
        echo "<div class='alert alert-success mt-3'>Utilisateur inscrit avec succès !</div>";
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger mt-3'>Erreur : " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
?>

<?php include '../includes/footer.php'; ?>
