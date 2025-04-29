<?php
session_start();
include '../includes/header.php';
include '../includes/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: /pages/login.php');
    exit();
}

if (!isset($_GET['order_id'])) {
    header('Location: /pages/orders.php');
    exit();
}

$orderId = $_GET['order_id'];


$sql = "SELECT * FROM commandes WHERE ID_COMMANDE = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    echo "<p>Commande introuvable.</p>";
    exit();
}


echo "<h1>Paiement pour la commande n°" . $order['ID_COMMANDE'] . "</h1>";
echo "<p>Montant total : " . $order['MONTANT'] . " €</p>";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   
    $paymentStatus = "success"; 

    if ($paymentStatus == "success") {
       
        $sql = "UPDATE commandes SET STATUS = 'payé' WHERE ID_COMMANDE = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$orderId]);

        echo "<p>Paiement réussi ! Votre commande est maintenant payée.</p>";
    } else {
        echo "<p>Échec du paiement, essayez à nouveau.</p>";
    }
}

?>

<form method="POST">
    <button type="submit">Payer maintenant</button>
</form>

<?php include '../includes/footer.php'; ?>
