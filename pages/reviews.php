<?php
session_start();
include '../includes/header.php';
include '../includes/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: /pages/login.php');
    exit();
}

if (!isset($_GET['product_id'])) {
    echo "<p>Produit introuvable.</p>";
    exit();
}

$productId = $_GET['product_id'];


$sql = "SELECT * FROM avis WHERE ID_PRODUIT = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$productId]);
$reviews = $stmt->fetchAll();

echo "<h1>Avis sur le produit</h1>";


if (count($reviews) > 0) {
    foreach ($reviews as $review) {
        echo "<div class='review'>";
        echo "<p><strong>" . $review['username'] . "</strong></p>";
        echo "<p>" . $review['commentaire'] . "</p>";
        echo "<p>Note: " . $review['note'] . "/5</p>";
        echo "</div>";
    }
} else {
    echo "<p>Aucun avis pour ce produit.</p>";
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $comment = $_POST['commentaire'];
    $rating = $_POST['note'];

    
    $sql = "INSERT INTO avis (ID_USER, ID_PRODUIT, commentaire, note) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_SESSION['user']['ID_USER'], $productId, $comment, $rating]);

    echo "<p>Votre avis a été ajouté.</p>";
}
?>

<form method="POST">
    <textarea name="commentaire" placeholder="Votre commentaire" required></textarea>
    <input type="number" name="note" min="1" max="5" placeholder="Note (1-5)" required>
    <button type="submit">Soumettre</button>
</form>

<?php include '../includes/footer.php'; ?>
