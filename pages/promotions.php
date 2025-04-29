<?php
include '../includes/header.php';
include '../includes/db.php';


$sql = "SELECT * FROM promotion WHERE date_debut <= NOW() AND date_fin >= NOW()";
$stmt = $conn->query($sql);
$promotions = $stmt->fetchAll();

echo "<h1>Promotions Actuelles</h1>";

if (count($promotions) > 0) {
    foreach ($promotions as $promotion) {
        echo "<div class='promotion'>";
        echo "<p><strong>" . $promotion['titre'] . "</strong></p>";
        echo "<p>" . $promotion['description'] . "</p>";
        echo "<p>Réduction : " . $promotion['reduction'] . "%</p>";
        echo "<p>Valable jusqu'au : " . $promotion['date_fin'] . "</p>";
        echo "</div>";
    }
} else {
    echo "<p>Aucune promotion en cours.</p>";
}

include '../includes/footer.php';
?>
