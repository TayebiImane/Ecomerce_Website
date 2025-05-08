<?php
// Inclure la connexion à la base de données
include 'includes/db1.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo 'Aucun utilisateur connecté. Vous serez redirigé vers la page de connexion.';
    header("Location: login.php"); // Rediriger vers la page de connexion
    exit();
}

$id_utilisateur = $_SESSION['user_id']; // Récupérer l'ID de l'utilisateur depuis la session

// Récupérer l'ID du panier de l'utilisateur
$sqlPanier = "SELECT ID_PANIER FROM panier WHERE ID_USER = ?";
$stmtPanier = $conn->prepare($sqlPanier);
$stmtPanier->execute([$id_utilisateur]);

if ($stmtPanier->rowCount() > 0) {
    $panier = $stmtPanier->fetch();
    $id_panier = $panier['ID_PANIER'];
} else {
    // Rediriger si aucun panier n'est trouvé
    header("Location: show_panier.php?message=no_cart");
    exit();
}

// 1. Action : Retirer un produit du panier
if (isset($_POST['action']) && $_POST['action'] == 'retirer') {
    // Vérifier si l'ID du produit est fourni
    if (isset($_POST['product_id']) && !empty($_POST['product_id'])) {
        $product_id = $_POST['product_id'];
        
        // Supprimer le produit du panier
        $sqlRetirerProduit = "DELETE FROM panier_produit WHERE ID_PANIER = ? AND ID_PRODUIT = ?";
        $stmtRetirerProduit = $conn->prepare($sqlRetirerProduit);
        $stmtRetirerProduit->execute([$id_panier, $product_id]);
        
        // Rediriger l'utilisateur vers la page du panier
        header("Location: show_panier.php?message=product_removed");
        exit();
    } else {
        // Si l'ID du produit n'est pas fourni
        header("Location: show_panier.php?message=product_not_found");
        exit();
    }
}
// 2. Action : Vider le panier
else if (isset($_POST['action']) && $_POST['action'] == 'vider') {
    // Supprimer tous les produits du panier
    $sqlViderPanier = "DELETE FROM panier_produit WHERE ID_PANIER = ?";
    $stmtViderPanier = $conn->prepare($sqlViderPanier);
    $stmtViderPanier->execute([$id_panier]);
    
    // Rediriger l'utilisateur vers la page du panier
    header("Location: show_panier.php?message=cart_cleared");
    exit();
}
// 3. Action : Mettre à jour les quantités
else if (isset($_POST['action']) && $_POST['action'] == 'mettre_a_jour') {
    if (isset($_POST['quantite']) && is_array($_POST['quantite'])) {
        $stockInvalide = false;
        
        foreach ($_POST['quantite'] as $id_produit => $quantite) {
            // S'assurer que la quantité est un nombre positif
            $quantite = max(1, intval($quantite));
            
            // Vérifier la quantité en stock avant la mise à jour
            $sqlStock = "SELECT QUANTITE_STOCK FROM produit WHERE ID_PRODUIT = ?";
            $stmtStock = $conn->prepare($sqlStock);
            $stmtStock->execute([$id_produit]);
            $produit = $stmtStock->fetch();
            
            // Si le stock est insuffisant, ajuster la quantité au maximum disponible
            if ($quantite > $produit['QUANTITE_STOCK']) {
                $quantite = $produit['QUANTITE_STOCK'];
                $stockInvalide = true;
            }
            
            // Mettre à jour la quantité du produit dans le panier
            $sqlUpdateQuantite = "UPDATE panier_produit SET QUANTITE_PROUIT = ? WHERE ID_PANIER = ? AND ID_PRODUIT = ?";
            $stmtUpdateQuantite = $conn->prepare($sqlUpdateQuantite);
            $stmtUpdateQuantite->execute([$quantite, $id_panier, $id_produit]);
        }
        
        // Rediriger l'utilisateur vers la page du panier avec un message approprié
        if ($stockInvalide) {
            header("Location: show_panier.php?message=quantities_adjusted");
        } else {
            header("Location: show_panier.php?message=quantities_updated");
        }
        exit();
    }
}
// Si aucune action valide n'est reçue, rediriger vers le panier
header("Location: show_panier.php");
exit();
?>