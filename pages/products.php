<?php
include '../includes/header.php';
include '../includes/db.php';

// Récupérer tous les produits
$sql = "SELECT * FROM produit";
$stmt = $conn->query($sql);
$products = $stmt->fetchAll();
?>

<h1>Nos produits</h1>
<div class="product-list">
    <?php foreach ($products as $product) : ?>
        <div class="product">
            <h3><?php echo htmlspecialchars($product['nom']); ?></h3>
            <p><?php echo htmlspecialchars($product['description']); ?></p>
            <p>Prix: <?php echo htmlspecialchars($product['prix']); ?> €</p>

            <?php
            // Récupérer la première image liée au produit
            $sqlImg = "SELECT nom_fichier FROM  IMAGE WHERE id_produit = ? LIMIT 1";
            $stmtImg = $conn->prepare($sqlImg);
            $stmtImg->execute([$product['ID_PRODUIT']]);
            $image = $stmtImg->fetchColumn();

            if ($image) {
                echo '<img src="/assets/images/' . htmlspecialchars($image) . '" alt="' . htmlspecialchars($product['nom']) . '">';
            } else {
                echo '<img src="/assets/images/default.jpg" alt="Image par défaut">';
            }
            ?>

            <a href="/pages/cart.php?add=<?php echo $product['ID_PRODUIT']; ?>">Ajouter au panier</a>
        </div>
    <?php endforeach; ?>
</div>

<a href="/pages/add_product.php">Ajouter un produit</a>

<?php include '../includes/footer.php'; ?>
