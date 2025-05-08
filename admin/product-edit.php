<?php
require_once('header.php');
include '../includes/db.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérifiez si un ID de produit a été passé via l'URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = $_GET['id'];

    // Préparer et exécuter la requête pour récupérer les informations du produit
    $statement = $pdo->prepare("
        SELECT 
            p.ID_PRODUIT,
            p.NOM_PRODUIT,
            p.DESCRIPTION_PRODUIT,
            p.PRIX,
            p.QUANTITE_STOCK,
            p.CODE_PRODUIT,
            p.MARQUE,
            p.SEUIL_ALERTE,
            c.NOM_CATEGORIE AS category_name,
            s.LIBELLE_STAT_PROD AS statut_name,
            d.NOM_DEVISE AS devise_name,
            I.FILE 
        FROM produit p
        JOIN categorie c ON p.ID_CATEGORIE = c.ID_CATEGORIE
        JOIN statut_produit s ON p.ID_STATU_PRODUIT = s.ID_STATU_PRODUIT
        JOIN devise d ON p.ID_DEVISE = d.ID_DEVISE
        JOIN image I ON p.ID_PRODUIT = I.ID_PRODUIT
        WHERE p.ID_PRODUIT = :id
    ");
    $statement->execute([':id' => $product_id]);
    $product = $statement->fetch(PDO::FETCH_ASSOC);

    // Vérifiez si le produit existe
    if (!$product) {
        echo "Product not found!";
        exit;
    }
} else {
    echo "Invalid product ID!";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupération des valeurs du formulaire
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $code = $_POST['code'];
    $brand = $_POST['brand'];
    $alert_threshold = $_POST['alert_threshold'];
    $category_id = $_POST['category_id'];
    $status_id = $_POST['status_id'];
    $currency_id = $_POST['currency_id'];

	if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
		$image_tmp = $_FILES['image']['tmp_name'];  // Le chemin du fichier temporaire
		$image_name = basename($_FILES['image']['name']);  // Le nom de l'image
		$image_path = '../assets/uploads/' . $image_name;  // Le chemin définitif du fichier
	
		// Déplacer l'image téléchargée vers le répertoire final
		if (move_uploaded_file($image_tmp, $image_path)) {
			// Lire le contenu du fichier image pour le stocker en binaire
			$image_file = file_get_contents($image_path);  // Contenu de l'image en binaire
	
			// Préparer la requête pour mettre à jour tous les champs de l'image dans la table IMAGE
			$update_image_stmt = $pdo->prepare("
				UPDATE image 
				SET 
					FILE_NAME = :file_name,
					FILE_PATH = :file_path,
					FILE = :file
				WHERE ID_PRODUIT = :id
			");
			
			// Exécuter la requête pour mettre à jour les champs de l'image
			$update_image_stmt->execute([
				':file_name' => $image_name,
				':file_path' => $image_path,
				':file' => $image_file,  // Le fichier binaire de l'image
				':id' => $product_id  // ID du produit pour mettre à jour l'image correspondante
			]);
		} else {
			echo "Error in moving the uploaded file.";
			exit;
		}
	}
	
    

    // Mise à jour du produit dans la base de données
    $update_statement = $pdo->prepare("
        UPDATE produit
        SET 
            NOM_PRODUIT = :name,
            DESCRIPTION_PRODUIT = :description,
            PRIX = :price,
            QUANTITE_STOCK = :quantity,
            CODE_PRODUIT = :code,
            MARQUE = :brand,
            SEUIL_ALERTE = :alert_threshold,
            ID_CATEGORIE = :category_id,
            ID_STATU_PRODUIT = :status_id,
            ID_DEVISE = :currency_id
        WHERE ID_PRODUIT = :id
    ");
    
    $update_statement->execute([
        ':name' => $name,
        ':description' => $description,
        ':price' => $price,
        ':quantity' => $quantity,
        ':code' => $code,
        ':brand' => $brand,
        ':alert_threshold' => $alert_threshold,
        ':category_id' => $category_id,
        ':status_id' => $status_id,
        ':currency_id' => $currency_id,
        ':id' => $product_id
    ]);
    
    echo "Product updated successfully!";
    exit;
}

?>


<!-- Formulaire d'édition -->
<section class="content-header">
    <div class="content-header-left">
        <h1>Edit Product</h1>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-body">
				<form action="product-edit.php?id=<?php echo $product['ID_PRODUIT']; ?>" method="POST" enctype="multipart/form-data">

                        <div class="form-group">
                            <label for="name">Product Name</label>
                            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($product['NOM_PRODUIT']); ?>" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control"><?php echo htmlspecialchars($product['DESCRIPTION_PRODUIT']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="price">Price</label>
                            <input type="number" name="price" id="price" value="<?php echo htmlspecialchars($product['PRIX']); ?>" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="quantity">Quantity</label>
                            <input type="number" name="quantity" id="quantity" value="<?php echo htmlspecialchars($product['QUANTITE_STOCK']); ?>" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="code">Product Code</label>
                            <input type="text" name="code" id="code" value="<?php echo htmlspecialchars($product['CODE_PRODUIT']); ?>" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="brand">Brand</label>
                            <input type="text" name="brand" id="brand" value="<?php echo htmlspecialchars($product['MARQUE']); ?>" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="alert_threshold">Alert Threshold</label>
                            <input type="number" name="alert_threshold" id="alert_threshold" value="<?php echo htmlspecialchars($product['SEUIL_ALERTE']); ?>" class="form-control">
                        </div>

                        <!-- Catégorie -->
                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select name="category_id" id="category_id" class="form-control">
                                <?php
                                // Afficher les catégories disponibles
                                $category_statement = $pdo->prepare("SELECT * FROM categorie");
                                $category_statement->execute();
                                $categories = $category_statement->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($categories as $category) {
                                    $selected = ($category['ID_CATEGORIE'] == $product['ID_CATEGORIE']) ? 'selected' : '';
                                    echo "<option value='" . $category['ID_CATEGORIE'] . "' $selected>" . htmlspecialchars($category['NOM_CATEGORIE']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Statut -->
                        <div class="form-group">
                            <label for="status_id">Status</label>
                            <select name="status_id" id="status_id" class="form-control">
                                <?php
                                // Afficher les statuts disponibles
                                $status_statement = $pdo->prepare("SELECT * FROM statut_produit");
                                $status_statement->execute();
                                $statuses = $status_statement->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($statuses as $status) {
                                    $selected = ($status['ID_STATU_PRODUIT'] == $product['ID_STATU_PRODUIT']) ? 'selected' : '';
                                    echo "<option value='" . $status['ID_STATU_PRODUIT'] . "' $selected>" . htmlspecialchars($status['LIBELLE_STAT_PROD']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Devise -->
                        <div class="form-group">
                            <label for="currency_id">Devise</label>
                            <select name="currency_id" id="currency_id" class="form-control">
                                <?php
                                // Afficher les devises disponibles
                                $currency_statement = $pdo->prepare("SELECT * FROM devise");
                                $currency_statement->execute();
                                $currencies = $currency_statement->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($currencies as $currency) {
                                    $selected = ($currency['ID_DEVISE'] == $product['ID_DEVISE']) ? 'selected' : '';
                                    echo "<option value='" . $currency['ID_DEVISE'] . "' $selected>" . htmlspecialchars($currency['DEVISE_NAME']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
						 <!-- Image Upload -->
						 <div class="form-group">
                            <label for="image">Product Image</label>
                            <input type="file" name="image" id="image" class="form-control">
                            <small>Current Image:</small><br>
                            <!-- Afficher l'image actuelle si elle existe -->
                            <?php if (!empty($product['FILE_PATH']) && file_exists($product['FILE_PATH'])): ?>
                                <img src="<?php echo htmlspecialchars($product['FILE_PATH']); ?>" width="150" alt="Product Image">
                            <?php else: ?>
                                <p>No image uploaded yet.</p>
                            <?php endif; ?>
                        </div>

                        </div>

                        <button type="submit" class="btn btn-success">Update Product</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once('footer.php'); ?>
