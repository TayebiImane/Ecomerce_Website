<?php
session_start();
include '../includes/header.php';
include '../includes/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: /Ecomerce/pages/login.php');
    exit();
}

$userId = $_SESSION['user']['ID_USER'];


$sql = "SELECT * FROM commandes WHERE ID_USER = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();

echo "<h1>Vos commandes</h1>";

if (count($orders) > 0) {
    foreach ($orders as $order) {
        echo "<div class='order'>";
        echo "<p>Commande n°: " . $order['ID_COMMANDE'] . "</p>";
        echo "<p>Date: " . $order['DATE_COMMANDE'] . "</p>";
        echo "<p>Status: " . $order['STATUS'] . "</p>";
        echo "<a href='/pages/payment.php?order_id=" . $order['ID_COMMANDE'] . "'>Voir la commande</a>";
        echo "</div>";
    }
} else {
    echo "<p>Vous n'avez pas encore passé de commande.</p>";
}

include '../includes/footer.php';
?>
