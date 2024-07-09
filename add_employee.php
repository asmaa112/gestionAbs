<?php
// Inclure le fichier de connexion à la base de données
include 'db_connection.php';

// Initialiser les variables pour les messages d'erreur et de succès
$error_message = "";
$success_message = "";

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer le nom de l'employé depuis le formulaire
    $name = $_POST['name'];

    // Requête SQL pour ajouter un nouvel employé
    $sql = "INSERT INTO employees (name) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $name);

    if ($stmt->execute()) {
        $success_message = "Employé ajouté avec succès.";
    } else {
        $error_message = "Erreur lors de l'ajout de l'employé: " . $conn->error;
    }

    $stmt->close();
}

// Fermer la connexion
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Employé</title>
    <link rel="stylesheet" href="styles.css">
    <!-- Assurez-vous d'ajuster le chemin selon votre structure de fichiers -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

</head>

<body>

    <?php include 'sidebar.php'; ?> <!-- Inclusion de la barre latérale -->
    <div class="content">
        <h2 class="heading">Ajouter un Employé</h2>
        <p><a href="dashboard.php" class="btn-link"><i class="fas fa-arrow-left"></i> Retour au dashboard</a></p>


        <?php
        // Affichage des messages d'erreur ou de succès
        if (!empty($error_message)) {
            echo "<p style='color:red;'>$error_message</p>";
        }
        if (!empty($success_message)) {
            echo "<p style='color:green;'>$success_message</p>";
        }
        ?>

        <form method="post" action="" class="form">
            <div class="form-group">
                <label>Nom:</label><br> <input type="text" name="name" required class="input-text"><br>
                <input type="submit" value="Ajouter " class="btn-primary">
            </div>
        </form>
    </div>
</body>

</html>