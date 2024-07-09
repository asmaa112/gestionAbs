<?php
// Inclure le fichier de connexion à la base de données
include 'db_connection.php';

// Initialiser les variables pour les messages d'erreur et les résultats
$error_message = "";
$absence_details = [];

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer le type de vue (jour ou mois) depuis le formulaire
    $view_type = isset($_POST['view_type']) ? $_POST['view_type'] : 'day';
    
    // Récupérer la date ou le mois/année depuis le formulaire
    if ($view_type == 'day') {
        $date = isset($_POST['date']) ? $_POST['date'] : date('Y-m-d');
        
        // Requête SQL pour obtenir les absences du jour donné
        $sql = "
            SELECT e.name, a.absence_date, a.duration_minutes, a.justification, a.is_justified
            FROM employees e
            JOIN absences a ON e.employee_id = a.employee_id
            WHERE a.absence_date = ?
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $date);
    } else {
        $month = isset($_POST['month']) ? $_POST['month'] : date('m');
        $year = isset($_POST['year']) ? $_POST['year'] : date('Y');

        // Calculer les dates de début et de fin pour le mois donné
        $start_date = $year . '-' . $month . '-01';
        $end_date = date("Y-m-t", strtotime($start_date));

        // Requête SQL pour obtenir les absences du mois donné
        $sql = "
            SELECT e.name, a.absence_date, a.duration_minutes, a.justification, a.is_justified
            FROM employees e
            JOIN absences a ON e.employee_id = a.employee_id
            WHERE a.absence_date BETWEEN ? AND ?
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $start_date, $end_date);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    // Récupérer les résultats
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $absence_details[] = $row;
        }
    } else {
        $error_message = "Aucune absence trouvée pour la période donnée.";
    }

    $stmt->close();
}

// Fermer la connexion
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Voir les Absences</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

</head>
<body>


<div class="content">
    <h2 class="heading">Voir les Absences</h2>
    <p><a href="dashboard.php" class="btn-link"><i class="fas fa-arrow-left"></i> Retour au dashboard</a></p>
    <?php include 'sidebar.php'; ?> <!-- Inclusion de la barre latérale -->
    <form method="post" action="" class="form">
        <div class="form-group">
            <label for="view_type">Afficher par :</label>
        <select id="view_type" name="view_type" onchange="toggleViewType()">
            <option value="day" <?php echo (isset($view_type) && $view_type == 'day') ? 'selected' : ''; ?>>Jour</option>
            <option value="month" <?php echo (isset($view_type) && $view_type == 'month') ? 'selected' : ''; ?>>Mois</option>
        </select><br>
        </div>

      <div class="form-group">
      <div id="day_view" style="display: <?php echo (isset($view_type) && $view_type == 'day') ? 'block' : 'none'; ?>;">
            Date : <input type="date" name="date" value="<?php echo isset($date) ? $date : date('Y-m-d'); ?>"><br>
        </div>
      </div>

        <div class="form-group">
        <div id="month_view" style="display: <?php echo (isset($view_type) && $view_type == 'month') ? 'block' : 'none'; ?>;">
            Mois : <input type="text" name="month" value="<?php echo isset($month) ? $month : date('m'); ?>"><br>
            Année : <input type="text" name="year" value="<?php echo isset($year) ? $year : date('Y'); ?>"><br>
        </div>
        </div>

        <input type="submit" value="Afficher" class="btn-primary">
    </form>

    <?php
    // Affichage des messages d'erreur
    if (!empty($error_message)) {
        echo "<p style='color:red;'>$error_message</p>";
    }

    // Affichage des détails des absences
    if (!empty($absence_details)) {
        echo "<table border='1' class='table'>
                <tr>
                    <th>Nom de l'employé</th>
                    <th>Date d'absence</th>
                    <th>Durée (minutes)</th>
                    <th>Justification</th>
                    <th>Justifiée</th>
                </tr>";
        foreach ($absence_details as $detail) {
            echo "<tr>
                    <td>" . $detail['name'] . "</td>
                    <td>" . $detail['absence_date'] . "</td>
                    <td>" . $detail['duration_minutes'] . "</td>
                    <td>" . $detail['justification'] . "</td>
                    <td>" . ($detail['is_justified'] ? 'Oui' : 'Non') . "</td>
                  </tr>";
        }
        echo "</table>";
    }
    ?>

</div>

<script>
function toggleViewType() {
    var viewType = document.getElementById('view_type').value;
    if (viewType === 'day') {
        document.getElementById('day_view').style.display = 'block';
        document.getElementById('month_view').style.display = 'none';
    } else {
        document.getElementById('day_view').style.display = 'none';
        document.getElementById('month_view').style.display = 'block';
    }
}
</script>

</body>
</html>
