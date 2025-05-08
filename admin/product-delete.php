<?php
require_once('header.php');
include '../includes/db.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérification de l'ID
if (!isset($_GET['id'])) {
    header('Location: product-view.php');
    exit;
}

$id = $_GET['id'];

try {
    // Vérifier si le produit existe
    $statement = $pdo->prepare("SELECT * FROM produit WHERE ID_PRODUIT = ?");
    $statement->execute([$id]);
    $product = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo "<div class='alert alert-danger'>Produit non trouvé.</div>";
        exit;
    }

    // Supprimer les images liées au produit
    $delete_images = $pdo->prepare("DELETE FROM image WHERE ID_PRODUIT = ?");
    $delete_images->execute([$id]);
	// Supprimer les avis liés au produit
$delete_avis = $pdo->prepare("DELETE FROM avis WHERE ID_PRODUIT = ?");
$delete_avis->execute([$id]);

$delete_panier_produit = $pdo->prepare("DELETE FROM panier_produit WHERE ID_PRODUIT = ?");
    $delete_panier_produit->execute([$id]);

// Supprimer les lignes de commande liées
$delete_commandes = $pdo->prepare("DELETE FROM commande_produit WHERE ID_PRODUIT = ?");
$delete_commandes->execute([$id]);

    // Supprimer le produit
    $delete_product = $pdo->prepare("DELETE FROM produit WHERE ID_PRODUIT = ?");
    $delete_product->execute([$id]);

    // Rediriger avec succès
    header("Location: product.php");
    exit;

} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Erreur : " . $e->getMessage() . "</div>";
}

require_once('footer.php');
?>
