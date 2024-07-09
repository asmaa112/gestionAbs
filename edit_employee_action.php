<?php
session_start();

// Vérification de la session pour s'assurer que l'utilisateur est connecté
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Inclure le fichier de connexion à la base de données
include 'db_connection.php';

// Initialiser les variables pour les messages d'erreur et de succès
$error_message = "";
$success_message = "";

// Vérifier si l'ID de l'employé est passé en paramètre via GET
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $error_message = "ID d'employé non valide.";
} else {
    $employee_id = $_GET['id'];

    // Requête SQL pour récupérer le nom actuel de l'employé
    $sql_select = "SELECT name FROM employees WHERE employee_id = ?";
    $stmt_select = $conn->prepare($sql_select);
    $stmt_select->bind_param("i", $employee_id);
    $stmt_select->execute();
    $result_select = $stmt_select->get_result();

    if ($result_select->num_rows > 0) {
        $row = $result_select->fetch_assoc();
        $current_name = $row['name'];
    } else {
        $error_message = "Aucun employé trouvé avec cet ID.";
    }

    $stmt_select->close();
}

// Traitement du formulaire de modification
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les nouvelles données depuis le formulaire
    $new_name = trim($_POST['name']);

    // Validation simple du nom (vérifiez que le nom n'est pas vide)
    if (empty($new_name)) {
        $error_message = "Veuillez saisir un nouveau nom.";
    } else {
        // Requête SQL pour mettre à jour le nom de l'employé
        $sql_update = "UPDATE employees SET name = ? WHERE employee_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $new_name, $employee_id);

        if ($stmt_update->execute()) {
            $success_message = "Nom de l'employé mis à jour avec succès.";
            $current_name = $new_name; // Mettre à jour le nom actuel pour l'afficher après la mise à jour
        } else {
            $error_message = "Erreur lors de la mise à jour du nom de l'employé : " . $conn->error;
        }

        $stmt_update->close();
    }
}

// Fermer la connexion
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Employé</title>
    <link rel="stylesheet" href="styles.css"> <!-- Assurez-vous d'ajuster le chemin selon votre structure de fichiers -->
</head>
<body>
    <div class="dashboard-container">
        <h2>Modifier Employé</h2>
        <a href="dashboard.php">Retour à dashboard</a>

        <?php
        // Affichage des messages d'erreur ou de succès
        if (!empty($error_message)) {
            echo "<p style='color:red;'>$error_message</p>";
        }
        if (!empty($success_message)) {
            echo "<p style='color:green;'>$success_message</p>";
        }
        ?>

        <?php if (isset($current_name)) : ?>
            <form method="post" action="">
                <label>Nouveau nom:</label> <input type="text" name="name" value="<?php echo htmlspecialchars($current_name); ?>" required><br>
                <input type="submit" value="Enregistrer">
            </form>
        <?php else: ?>
            <p>Aucun employé trouvé avec cet ID.</p>
        <?php endif; ?>
    </div>
</body>
</html>
