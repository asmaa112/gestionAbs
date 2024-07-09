<?php
// Inclure le fichier de connexion à la base de données
include 'db_connection.php';

// Initialiser les variables pour les messages d'erreur et de succès
$error_message = "";
$success_message = "";

// Vérifier si le formulaire a été soumis pour supprimer un employé
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer le nom de l'employé à supprimer depuis le formulaire
    $employee_name = isset($_POST['employee_name']) ? $_POST['employee_name'] : '';

    // Vérifier que le nom de l'employé est valide
    if (!empty($employee_name)) {
        // Requête SQL pour supprimer l'employé par son nom
        $sql = "DELETE FROM employees WHERE name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $employee_name);

        if ($stmt->execute()) {
            $success_message = "Employé supprimé avec succès.";
        } else {
            $error_message = "Erreur lors de la suppression de l'employé.";
        }

        $stmt->close();
    } else {
        $error_message = "Veuillez entrer un nom d'employé valide.";
    }
}

// Récupérer la liste des employés pour afficher la liste déroulante (ou la liste de résultats de recherche)
$employees = [];
$sql_employees = "SELECT employee_id, name FROM employees";
$result_employees = $conn->query($sql_employees);

if ($result_employees->num_rows > 0) {
    while ($row = $result_employees->fetch_assoc()) {
        $employees[] = $row;
    }
}

// Fermer la connexion à la base de données
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer Employé</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <link rel="stylesheet" href="styles.css"> <!-- Assurez-vous d'ajuster le chemin selon votre structure de fichiers -->
</head>
<body>
<?php include 'sidebar.php'; ?> 
    <div class="content">
        <h2 class="heading">Supprimer Employé</h2>
        <a href="dashboard.php" class="btn-link"><i class="fas fa-arrow-left"></i> Retour à dashboard</a>
        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <p class="success-message"><?php echo $success_message; ?></p>
        <?php endif; ?>
        <form method="post" action="" class="form">
            <div class="form-group">
                <label for="employee_name">Nom de l'employé à supprimer :</label>
                <input type="text" id="employee_name" name="employee_name" required class="input-text">
            </div>
            <input type="submit" value="Supprimer" class="btn-primary">
        </form>

       

        <!-- Afficher la liste des employés pour référence -->
        <?php if (!empty($employees)): ?>
            <h3>Liste des employés</h3>
            <ul class="employee-list">
                <?php foreach ($employees as $employee): ?>
                    <li><?php echo htmlspecialchars($employee['name']); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</body>
</html>
