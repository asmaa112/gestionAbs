<?php
// Inclusion du fichier de connexion à la base de données
include 'db_connection.php';

// Vérification des identifiants
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input_username = $_POST['username'];
    $input_password = $_POST['password'];

    // Requête SQL pour vérifier les identifiants dans la table users
    $sql = "SELECT * FROM users WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $input_username, $input_password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        // Démarrage de la session
        session_start();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role']; // Optionnel : Récupérer le rôle de l'utilisateur
        $_SESSION['permissions'] = $user['permissions']; // Optionnel : Récupérer les permissions de l'utilisateur

        // Redirection vers le tableau de bord
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Nom d'utilisateur ou mot de passe incorrect";
    }

    $stmt->close();
}

// Fermer la connexion à la base de données
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Connexion</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <div class="login-container">
        <h2>Connexion</h2>
        <form action="login.php" method="POST" class="login-form">
            <div class="form-group">
                <label for="username">Nom d'utilisateur :</label>
                <input type="text" id="username" name="username" required class="input-text">
            </div>
            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required class="input-text">
            </div>
            <button type="submit" class="btn-primary">Se connecter</button>
        </form>
    </div>
</body>
</html>
