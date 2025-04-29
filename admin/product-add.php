<?php require_once('header.php'); ?>
<?php
$error_message = '';
$success_message = '';
include '../includes/db.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (isset($_POST['form1'])) {
    $valid = 1;

    // Validation des champs
    if (empty($_POST['tcat_id'])) {
        $valid = 0;
        $error_message .= "Vous devez sélectionner une catégorie.<br>";
    }

    if (empty($_POST['p_name'])) {
        $valid = 0;
        $error_message .= "Le nom du produit ne peut pas être vide.<br>";
    }

    if (empty($_POST['p_description'])) {
        $valid = 0;
        $error_message .= "La description ne peut pas être vide.<br>";
    }

    if (empty($_POST['p_current_price'])) {
        $valid = 0;
        $error_message .= "Le prix ne peut pas être vide.<br>";
    }

    if (empty($_POST['p_qty'])) {
        $valid = 0;
        $error_message .= "La quantité ne peut pas être vide.<br>";
    }

    if (empty($_POST['code_produit'])) {
        $valid = 0;
        $error_message .= "Le code produit ne peut pas être vide.<br>";
    }

    if (empty($_POST['marque'])) {
        $valid = 0;
        $error_message .= "La marque ne peut pas être vide.<br>";
    }

    if (empty($_POST['seuil_alerte'])) {
        $valid = 0;
        $error_message .= "Le seuil d'alerte est requis.<br>";
    }

    if (empty($_POST['statu_produit'])) {
        $valid = 0;
        $error_message .= "Statut du produit requis.<br>";
    }

    if (empty($_POST['devise'])) {
        $valid = 0;
        $error_message .= "Devise requise.<br>";
    }

    $path = $_FILES['p_featured_photo']['name'] ?? '';
    $path_tmp = $_FILES['p_featured_photo']['tmp_name'] ?? '';

    if ($path != '') {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        if (!in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif'])) {
            $valid = 0;
            $error_message .= "L'image doit être JPG, JPEG, PNG ou GIF.<br>";
        }
    } else {
        $valid = 0;
        $error_message .= "Vous devez sélectionner une image principale.<br>";
    }

    if ($valid == 1) {
        // 1. Récupérer l'ID auto-incrémenté du produit
        $statement = $pdo->prepare("SHOW TABLE STATUS LIKE 'PRODUIT'");
        $statement->execute();
        $result = $statement->fetch();
        $ai_id = $result['Auto_increment'];
    
        // 2. Insérer le produit dans la table PRODUIT
        $statement = $pdo->prepare("INSERT INTO PRODUIT (
            ID_CATEGORIE,
            NOM_PRODUIT,
            DESCRIPTION_PRODUIT,
            PRIX,
            QUANTITE_STOCK,
            CODE_PRODUIT,
            MARQUE,
            SEUIL_ALERTE,
            ID_STATU_PRODUIT,
            ID_DEVISE
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
        $statement->execute([
            $_POST['tcat_id'],
            $_POST['p_name'],
            $_POST['p_description'],
            $_POST['p_current_price'],
            $_POST['p_qty'],
            $_POST['code_produit'],
            $_POST['marque'],
            $_POST['seuil_alerte'],
            $_POST['statu_produit'],
            $_POST['devise']
        ]);
    
        // 3. Préparer le nom du fichier image
        $path = $_FILES['p_featured_photo']['name'] ?? '';
        $path_tmp = $_FILES['p_featured_photo']['tmp_name'] ?? '';
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $final_name = 'product-featured-' . $ai_id . '.' . $ext;
        $destination = '../assets/uploads/' . $final_name;
        move_uploaded_file($path_tmp, $destination);
        $file_binary = file_get_contents($destination);
    
        // 4. Insérer l'image dans la table IMAGE
        $statement = $pdo->prepare("INSERT INTO IMAGE (ID_PRODUIT, TYPE, FILE_NAME, FILE_PATH, FILE) VALUES (?, ?, ?, ?, ?)");
        $statement->execute([
            $ai_id,  // L'ID du produit que nous venons d'insérer
            $ext,
            $final_name,
            $destination,
            $file_binary
        ]);
    
        // 5. Message de succès
        $success_message = 'Le produit a été ajouté avec succès.';
    }
}
?>    

<!-- HTML Formulaire -->
<section class="content-header">
    <div class="content-header-left">
        <h1>Ajouter un produit</h1>
    </div>
    <div class="content-header-right">
        <a href="product.php" class="btn btn-primary btn-sm">Voir tous les produits</a>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">

            <?php if ($error_message): ?>
                <div class="callout callout-danger"><p><?= $error_message ?></p></div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="callout callout-success"><p><?= $success_message ?></p></div>
            <?php endif; ?>

            <form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
                <div class="box box-info">
                    <div class="box-body">

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Catégorie <span>*</span></label>
                            <div class="col-sm-4">
                                <select name="tcat_id" class="form-control">
                                    <option value="">Choisir une catégorie</option>
                                    <?php
                                    $statement = $pdo->prepare("SELECT * FROM CATEGORIE ORDER BY NOM_CATEGORIE ASC");
                                    $statement->execute();
                                    $categories = $statement->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($categories as $cat) {
                                        echo '<option value="' . $cat['ID_CATEGORIE'] . '">' . $cat['NOM_CATEGORIE'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <!-- Nom produit -->
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Nom du produit <span>*</span></label>
                            <div class="col-sm-4"><input type="text" name="p_name" class="form-control"></div>
                        </div>

                        <!-- Prix -->
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Prix <span>*</span></label>
                            <div class="col-sm-4"><input type="text" name="p_current_price" class="form-control"></div>
                        </div>

                        <!-- Quantité -->
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Quantité en stock <span>*</span></label>
                            <div class="col-sm-4"><input type="text" name="p_qty" class="form-control"></div>
                        </div>

                        <!-- Code -->
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Code produit <span>*</span></label>
                            <div class="col-sm-4"><input type="text" name="code_produit" class="form-control"></div>
                        </div>

                        <!-- Marque -->
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Marque <span>*</span></label>
                            <div class="col-sm-4"><input type="text" name="marque" class="form-control"></div>
                        </div>

                        <!-- Seuil alerte -->
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Seuil d'alerte <span>*</span></label>
                            <div class="col-sm-4"><input type="number" name="seuil_alerte" class="form-control"></div>
                        </div>

                        <!-- Statut -->
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Statut du produit <span>*</span></label>
                            <div class="col-sm-4">
                                <select name="statu_produit" class="form-control">
                                    <option value="">Choisir le statut</option>
                                    <?php
                                    $statement = $pdo->prepare("SELECT * FROM STATUT_PRODUIT ORDER BY LIBELLE_STAT_PROD ASC");
                                    $statement->execute();
                                    $statuts = $statement->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($statuts as $statu) {
                                        echo '<option value="' . $statu['ID_STATU_PRODUIT'] . '">' . $statu['LIBELLE_STAT_PROD'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <!-- Devise -->
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Devise <span>*</span></label>
                            <div class="col-sm-4">
                                <select name="devise" class="form-control">
                                    <option value="">Choisir une devise</option>
                                    <?php
                                    $statement = $pdo->prepare("SELECT * FROM DEVISE ORDER BY NOM_DEVISE ASC");
                                    $statement->execute();
                                    $devises = $statement->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($devises as $devise) {
                                        echo '<option value="' . $devise['ID_DEVISE'] . '">' . $devise['NOM_DEVISE'] . ' (' . $devise['SYMBOLE'] . ')</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <!-- Image -->
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Image principale <span>*</span></label>
                            <div class="col-sm-4"><input type="file" name="p_featured_photo" required></div>
                        </div>

                        <!-- Description -->
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Description</label>
                            <div class="col-sm-8"><textarea name="p_description" class="form-control" rows="5"></textarea></div>
                        </div>

                        <!-- Bouton -->
                        <div class="form-group">
                            <label class="col-sm-3 control-label"></label>
                            <div class="col-sm-6">
                                <button type="submit" class="btn btn-success" name="form1">Ajouter le produit</button>
                            </div>
                        </div>

                    </div>
                </div>
            </form>

        </div>
    </div>
</section>

<?php require_once('footer.php'); ?>
