<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit;
}
include '../includes/db.php';

// Récupérer produits
$sql = "SELECT * FROM PRODUIT";
$stmt = $conn->query($sql);
$products = $stmt->fetchAll();
?>

<h1>Dashboard Admin</h1>
<a href="add_product.php">Ajouter un produit</a>

<table>
    <thead>
        <tr><th>ID</th><th>Nom</th><th>Prix</th><th>Actions</th></tr>
    </thead>
    <tbody>
        <?php foreach($products as $p): ?>
        <tr>
            <td><?= $p['ID_PRODUIT'] ?></td>
            <td><?= htmlspecialchars($p['NOM_PRODUIT']) ?></td>
            <td><?= $p['PRIX'] ?> €</td>
            <td>
                <a href="edit_product.php?id=<?= $p['ID_PRODUIT'] ?>">Modifier</a> | 
                <a href="delete_product.php?id=<?= $p['ID_PRODUIT'] ?>" onclick="return confirm('Confirmer la suppression ?')">Supprimer</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
