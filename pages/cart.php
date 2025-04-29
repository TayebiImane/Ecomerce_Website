<?php
session_start();
include '../includes/header.php';

if (isset($_GET['add'])) {
    $productId = $_GET['add'];
    $_SESSION['cart'][] = $productId;
}

echo "<h2>Votre panier</h2>";
if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
    foreach ($_SESSION['cart'] as $productId) {
        echo "<p>Produit ID: $productId</p>";
    }
} else {
    echo "<p>Votre panier est vide.</p>";
}
?>
<a href="../index.php">Continuer vos achats</a>
<a href="checkout.php">Passer à la caisse</a>
<?php include '../includes/footer.php'; ?>
<?php
