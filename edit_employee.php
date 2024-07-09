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
$employee_name = "";
$current_name = "";

// Traitement du formulaire de recherche
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer le nom de l'employé depuis le formulaire
    $employee_name = trim($_POST['employee_name']);

    // Vérifier si le nom de l'employé est vide
    if (empty($employee_name)) {
        $error_message = "Veuillez saisir un nom d'employé.";
    } else {
        // Requête SQL pour récupérer le nom actuel de l'employé
        $sql = "SELECT employee_id, name FROM employees WHERE name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $employee_name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $current_name = $row['name'];
            $employee_id = $row['employee_id'];
        } else {
            $error_message = "Aucun employé trouvé avec ce nom.";
        }

        $stmt->close();
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

</head>
<body>
    
<?php include 'sidebar.php'; ?> <!-- Inclusion de la barre latérale -->
    <div class="content">
        <h2 class="heading">Modifier Employé</h2>
        <p><a href="dashboard.php" class="btn-link"><i class="fas fa-arrow-left"></i> Retour au dashboard</a></p>


      

        <form method="post" action="" class="form">
        <?php
        // Affichage des messages d'erreur
        if (!empty($error_message)) {
            echo "<p class='error-message'>$error_message</p>";
        }
        ?>
            <div class="form-group">
                <label for="employee_name">Nom de l'employé :</label><br>
                <input type="text" id="employee_name" name="employee_name" value="<?php echo htmlspecialchars($employee_name); ?>" required class="input-text">
            </div>
            <input type="submit" value="Rechercher" class="btn-primary">
        </form>

        <?php if (!empty($current_name)) : ?>
            <div class="employee-found">
                <h3>Employé trouvé : <?php echo htmlspecialchars($current_name); ?></h3>
                <p><a href="edit_employee_action.php?id=<?php echo $employee_id; ?>" class="btn-primary">Modifier cet employé</a></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
