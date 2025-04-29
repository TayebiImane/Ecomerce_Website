<?php
include '../includes/header.php';
include '../includes/db.php';

// Récupérer les produits de la base de données
$sql = "SELECT * FROM produit";
$stmt = $conn->prepare($sql);
$stmt->execute();
$products = $stmt->fetchAll();

// Traitement de l'ajout d'un produit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $nom = $_POST['nom'];
    $description = $_POST['description'];
    $prix = $_POST['prix'];
    $image = $_FILES['image']['name'];

    // Déplacement de l'image dans le dossier approprié
    $target = "../assets/images/" . basename($image);
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        // Insertion du produit dans la base de données
        $sql = "INSERT INTO produit (nom, description, prix, image) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$nom, $description, $prix, $image]);
        
        echo "<div class='alert alert-success mt-3'>Produit ajouté avec succès!</div>";
    } else {
        echo "<div class='alert alert-danger mt-3'>Erreur lors de l'ajout de l'image!</div>";
    }
}
?>

<h1>Nos produits</h1>
<div class="product-list">
    <?php foreach ($products as $product) : ?>
        <div class="product">
            <?php
            // Vérifier si l'image existe
            $imagePath = '/assets/images/' . $product['image'];
            if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $imagePath)) {
                $imagePath = '/assets/images/default.png'; // Image par défaut si l'image n'existe pas
            }
            ?>
            <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($product['nom']); ?>">
            <h3><?php echo htmlspecialchars($product['nom']); ?></h3>
            <p><?php echo htmlspecialchars($product['description']); ?></p>
            <p>Prix: <?php echo number_format($product['prix'], 2, ',', ' '); ?> €</p>
            <a href="/pages/cart.php?add=<?php echo $product['id']; ?>">Ajouter au panier</a>
        </div>
    <?php endforeach; ?>
</div>

<h2>Ajouter un nouveau produit</h2>
<form method="POST" enctype="multipart/form-data">
    <div class="form-group">
        <label>Nom du produit</label>
        <input type="text" name="nom" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Description</label>
        <textarea name="description" class="form-control" required></textarea>
    </div>
    <div class="form-group">
        <label>Prix</label>
        <input type="number" name="prix" class="form-control" step="0.01" required>
    </div>
    <div class="form-group">
        <label>Image du produit</label>
        <input type="file" name="image" class="form-control" required>
    </div>
    <button type="submit" name="add_product" class="btn btn-success mt-3">Ajouter le produit</button>
</form>

<?php include '../includes/footer.php'; ?>
