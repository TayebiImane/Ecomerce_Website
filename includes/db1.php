<?php
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "ecomerce";
$port = 4000; 

try {
    $conn = new PDO("mysql:host=$servername;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connexion échouée: " . $e->getMessage();
}
?>