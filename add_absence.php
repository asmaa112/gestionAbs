<?php
// Inclure le fichier de connexion à la base de données
include 'db_connection.php';

// Initialiser les variables pour les messages d'erreur et de succès
$error_message = "";
$success_message = "";

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $employee_id = isset($_POST['employee_id']) ? $_POST['employee_id'] : '';
    $absence_date = isset($_POST['absence_date']) ? $_POST['absence_date'] : '';
    $duration_minutes = isset($_POST['duration_minutes']) ? $_POST['duration_minutes'] : '';
    $justification = isset($_POST['justification']) ? $_POST['justification'] : '';
    $is_justified = isset($_POST['is_justified']) ? $_POST['is_justified'] : 0;

    // Vérifier que tous les champs sont remplis
    if (empty($employee_id) || empty($absence_date) || empty($duration_minutes)) {
        $error_message = "Veuillez remplir tous les champs obligatoires.";
    } else {
        // Requête SQL pour insérer une nouvelle absence
        $sql = "
            INSERT INTO absences (employee_id, absence_date, duration_minutes, justification, is_justified)
            VALUES (?, ?, ?, ?, ?)
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isisi", $employee_id, $absence_date, $duration_minutes, $justification, $is_justified);

        if ($stmt->execute()) {
            $success_message = "Absence ajoutée avec succès.";
        } else {
            $error_message = "Erreur lors de l'ajout de l'absence.";
        }

        $stmt->close();
    }
}

// Récupérer la liste des employés
$employees = [];
$sql_employees = "SELECT employee_id, name FROM employees";
$result_employees = $conn->query($sql_employees);

if ($result_employees->num_rows > 0) {
    while ($row = $result_employees->fetch_assoc()) {
        $employees[] = $row;
    }
}

// Fermer la connexion
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ajouter une Absence</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

</head>
<body>
<?php include 'sidebar.php'; ?> <!-- Inclusion de la barre latérale -->

<div class="content">
<h2 class="heading">Ajouter une Absence</h2>
<p><a href="dashboard.php" class="btn-link"><i class="fas fa-arrow-left"></i> Retour au dashboard</a></p>

<form method="post" action="" class="form">
<?php
// Affichage des messages d'erreur ou de succès
if (!empty($error_message)) {
    echo "<p style='color:red;text-align:center;'>$error_message</p>";
}
if (!empty($success_message)) {
    echo "<p style='color:green;text-align:center;'>$success_message</p>";
}
?>
    <div class="form-group">
        <label for="employee_id" class="label">Employé:</label><br>
        <select id="employee_id" name="employee_id" class="input-text">
            <?php
            foreach ($employees as $employee) {
                echo "<option value=\"" . $employee['employee_id'] . "\">" . $employee['name'] . "</option>";
            }
            ?>
        </select>
    </div>
    <div class="form-group">
        <label for="absence_date" class="label">Date de l'absence:</label><br>
        <input type="date" id="absence_date" name="absence_date" class="input-text">
    </div>
    <div class="form-group">
        <label for="duration_minutes" class="label">Durée (minutes):</label><br>
        <input type="number" id="duration_minutes" name="duration_minutes" class="input-text">
    </div>
    <div class="form-group">
        <label for="justification" class="label">Justification:</label><br>
        <input type="text" id="justification" name="justification" class="input-text">
    </div>
    <div class="form-group">
        <label for="is_justified" class="label">Justifiée:</label><br>
        <input type="checkbox" id="is_justified" name="is_justified" value="1">
    </div>
    <input type="submit" value="Ajouter" class="btn-primary">
</form>


</div>


</body>
</html>
