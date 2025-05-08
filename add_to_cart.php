<?php
include 'includes/db1.php';
session_start();
header('Content-Type: application/json');

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit;
}

$id_utilisateur = $_SESSION['user_id']; // ID de l'utilisateur connecté

// Récupérer les données JSON
$data = json_decode(file_get_contents('php://input'), true);
$id_produit = $data['productId'] ?? null;

if (!$id_produit) {
    echo json_encode(['success' => false, 'message' => 'ID produit manquant']);
    exit;
}

// Vérifie ou crée le panier
$sql = "SELECT ID_PANIER FROM panier WHERE ID_USER = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id_utilisateur]);

if ($stmt->rowCount() > 0) {
    $id_panier = $stmt->fetchColumn();
} else {
    // Créer un panier si l'utilisateur n'en a pas
    $stmtInsert = $conn->prepare("INSERT INTO panier (ID_USER) VALUES (?)");
    $stmtInsert->execute([$id_utilisateur]);
    $id_panier = $conn->lastInsertId();
}

// Vérifie le stock disponible du produit
$sqlStock = "SELECT QUANTITE_STOCK FROM produit WHERE ID_PRODUIT = ?";
$stmtStock = $conn->prepare($sqlStock);
$stmtStock->execute([$id_produit]);
$stock = $stmtStock->fetchColumn();

// Vérifie si le stock est suffisant
if ($stock <= 0) {
    echo json_encode(['success' => false, 'message' => 'Produit en rupture de stock']);
    exit;
}

// Vérifie si le produit est déjà dans le panier
$sqlCheck = "SELECT QUANTITE_PROUIT FROM panier_produit WHERE ID_PANIER = ? AND ID_PRODUIT = ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->execute([$id_panier, $id_produit]);

if ($stmtCheck->rowCount() > 0) {
    $quantite_panier = $stmtCheck->fetchColumn();
    
    // Empêche d'ajouter plus que le stock disponible
    if ($quantite_panier >= $stock) {
        echo json_encode(['success' => false, 'message' => 'Quantité maximale atteinte pour ce produit']);
        exit;
    }
    
    // Incrémente la quantité si possible
    $conn->prepare("UPDATE panier_produit SET QUANTITE_PROUIT = QUANTITE_PROUIT + 1 WHERE ID_PANIER = ? AND ID_PRODUIT = ?")
        ->execute([$id_panier, $id_produit]);
} else {
    // Ajouter seulement si stock ≥ 1
    $conn->prepare("INSERT INTO panier_produit (ID_PANIER, ID_PRODUIT, QUANTITE_PROUIT) VALUES (?, ?, 1)")
        ->execute([$id_panier, $id_produit]);
}

// Recompte le nombre total de produits dans le panier
$sqlCount = "SELECT SUM(QUANTITE_PROUIT) AS total FROM panier_produit WHERE ID_PANIER = ?";
$stmtCount = $conn->prepare($sqlCount);
$stmtCount->execute([$id_panier]);
$totalProducts = $stmtCount->fetchColumn();

// S'assurer que totalProducts n'est pas null
$totalProducts = $totalProducts ? (int)$totalProducts : 0;

echo json_encode(['success' => true, 'cartCount' => $totalProducts]);
?>