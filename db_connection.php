<?php
$servername = "localhost";
$username = "root";  // Remplacez par votre nom d'utilisateur
$password = "";      // Remplacez par votre mot de passe
$dbname = "abs";

// Créer une connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
