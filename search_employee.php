<!DOCTYPE html>
<html>
<head>
    <title>Recherche d'Employé</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="body">
<?php include 'sidebar.php'; ?> <!-- Inclusion de la barre latérale -->

    <div class="content">
        <h2 class="heading">Recherche d'Employé</h2>
        <p><a href="dashboard.php" class="btn-link"><i class="fas fa-arrow-left"></i> Retour au dashboard</a></p>

        <form method="post" action="" class="form" id="search-form">
            <div class="form-group">
                <label for="employee_name" class="label">Nom de l'employé:</label><br>
                <input type="text" id="employee_name" name="employee_name" class="input-text">
            </div>
            <div class="form-group">
                <label for="month" class="label">Mois:</label><br>
                <input type="text" id="month" name="month" value="<?php echo date('m'); ?>" class="input-text">
            </div>
            <div class="form-group">
                <label for="year" class="label">Année:</label><br>
                <input type="text" id="year" name="year" value="<?php echo date('Y'); ?>" class="input-text">
            </div>
            <input type="submit" value="Rechercher" class="btn-primary">
        </form>

        <div id="results"></div>

        <?php
        // Inclure le fichier de connexion à la base de données
        include 'db_connection.php';

        // Initialiser les variables pour les messages d'erreur et les résultats
        $error_message = "";
        $result_message = "";
        $absence_details = [];

        // Vérifier si le formulaire a été soumis
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Récupérer le nom de l'employé depuis le formulaire
            $employee_name = isset($_POST['employee_name']) ? $_POST['employee_name'] : '';

            // Récupérer le mois et l'année depuis le formulaire
            $month = isset($_POST['month']) ? $_POST['month'] : date('m');
            $year = isset($_POST['year']) ? $_POST['year'] : date('Y');

            // Calculer les dates de début et de fin pour le mois donné
            $start_date = $year . '-' . $month . '-01';
            $end_date = date("Y-m-t", strtotime($start_date));

            // Requête SQL pour vérifier si l'employé existe
            $sql_check_employee = "SELECT COUNT(*) AS employee_count FROM employees WHERE name = ?";
            $stmt_check_employee = $conn->prepare($sql_check_employee);
            $stmt_check_employee->bind_param("s", $employee_name);
            $stmt_check_employee->execute();
            $result_check_employee = $stmt_check_employee->get_result();
            $row_employee_count = $result_check_employee->fetch_assoc()['employee_count'];

            if ($row_employee_count == 0) {
                $error_message = "Aucun employé trouvé avec le nom '$employee_name'.";
            } else {
                // Requête SQL pour obtenir le total des minutes d'absence pour l'employé donné
                $sql = "
                    SELECT e.name, SUM(a.duration_minutes) AS total_absence_minutes
                    FROM employees e
                    LEFT JOIN absences a ON e.employee_id = a.employee_id
                    WHERE e.name = ? AND a.absence_date BETWEEN ? AND ?
                    GROUP BY e.employee_id, e.name
                ";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sss", $employee_name, $start_date, $end_date);
                $stmt->execute();
                $result = $stmt->get_result();

                // Affichage des résultats
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $result_message = "Nom: " . $row["name"] ."<br>" . "  Total des minutes d'absence: " . $row["total_absence_minutes"]." min". "<br>";
                    }

                    // Requête SQL pour obtenir les détails des absences pour l'employé donné
                    $sql_details = "
                        SELECT a.absence_id, a.absence_date, a.duration_minutes, a.justification, a.is_justified
                        FROM employees e
                        JOIN absences a ON e.employee_id = a.employee_id
                        WHERE e.name = ? AND a.absence_date BETWEEN ? AND ?
                    ";

                    $stmt_details = $conn->prepare($sql_details);
                    $stmt_details->bind_param("sss", $employee_name, $start_date, $end_date);
                    $stmt_details->execute();
                    $result_details = $stmt_details->get_result();

                    while ($row = $result_details->fetch_assoc()) {
                        $absence_details[] = $row;
                    }

                    $stmt_details->close();
                } else {
                    $error_message = "Aucune absence trouvée pour l'employé '$employee_name' dans le mois donné.";
                }

                $stmt->close();
            }

            $stmt_check_employee->close();
        }

        // Fermer la connexion
        $conn->close();
        ?>

        <?php
        // Affichage des messages d'erreur ou des résultats
        if (!empty($error_message)) {
            echo "<div class='error-message'>$error_message</div>";
        }
        if (!empty($result_message)) {
            echo "<div class='result-message'>$result_message</div>";

            // Afficher le tableau des détails des absences
            if (!empty($absence_details)) {
                echo "<table class='table'>
                        <tr>
                            <th>Date d'absence</th>
                            <th>Durée (minutes)</th>
                            <th>Justification</th>
                            <th>Justifiée</th>
                            <th>Action</th>
                        </tr>";
                foreach ($absence_details as $detail) {
                    echo "<tr id='row_" . $detail['absence_id'] . "'>
                            <td>" . $detail['absence_date'] . "</td>
                            <td><span id='duration_minutes_" . $detail['absence_id'] . "'>" . $detail['duration_minutes'] . "</span></td>
                            <td><span id='justification_" . $detail['absence_id'] . "'>" . $detail['justification'] . "</span></td>
                            <td>" . ($detail['is_justified'] ? 'Oui' : 'Non') . "</td>
                            <td id='action_cell_" . $detail['absence_id'] . "'>
                                <button onclick=\"editAbsence(" . $detail['absence_id'] . ")\">Modifier</button> |
                                <button onclick=\"deleteAbsence(" . $detail['absence_id'] . ")\">Supprimer</button>
                            </td>
                          </tr>";
                }
                echo "</table>";
            }
        }
        ?>

    </div>

    <script>
        function editAbsence(absenceId) {
            // Récupérer les valeurs actuelles
            var duration = document.getElementById('duration_minutes_' + absenceId).innerHTML;
            var justification = document.getElementById('justification_' + absenceId).innerHTML;

            // Créer des inputs pour éditer
            document.getElementById('duration_minutes_' + absenceId).innerHTML = "<input type='text' id='edit_duration_" + absenceId + "' value='" + duration + "'>";
            document.getElementById('justification_' + absenceId).innerHTML = "<input type='text' id='edit_justification_" + absenceId + "' value='" + justification + "'>";

            // Modifier le bouton pour Enregistrer
            var actionCell = document.getElementById('action_cell_' + absenceId);
            actionCell.innerHTML = "<button onclick=\"saveAbsence(" + absenceId + ")\">Enregistrer</button>";
        }

        function saveAbsence(absenceId) {
            // Récupérer les nouvelles valeurs
            var newDuration = document.getElementById('edit_duration_' + absenceId).value;
            var newJustification = document.getElementById('edit_justification_' + absenceId).value;

            // Envoyer la requête AJAX pour enregistrer
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'search_employee.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    alert('Absence modifiée avec succès.');
                    // Mettre à jour l'affichage
                    document.getElementById('duration_minutes_' + absenceId).innerHTML = newDuration;
                    document.getElementById('justification_' + absenceId).innerHTML = newJustification;
                    document.getElementById('action_cell_' + absenceId).innerHTML = "<button onclick=\"editAbsence(" + absenceId + ")\">Modifier</button> | <button onclick=\"deleteAbsence(" + absenceId + ")\">Supprimer</button>";
                }
            };
            xhr.send('action=update&absence_id=' + absenceId + '&duration_minutes=' + newDuration + '&justification=' + newJustification);
        }

        function deleteAbsence(absenceId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette absence ?')) {
                // Envoyer la requête AJAX pour supprimer
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'search_employee.php', true);
                xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        alert('Absence supprimée avec succès.');
                        // Supprimer la ligne du tableau
                        var row = document.getElementById('row_' + absenceId);
                        row.parentNode.removeChild(row);
                    }
                };
                xhr.send('action=delete&absence_id=' + absenceId);
            }
        }
    </script>
</body>
</html>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    include 'db_connection.php';

    $action = $_POST['action'];

    if ($action == 'update') {
        $absenceId = $_POST['absence_id'];
        $newDuration = $_POST['duration_minutes'];
        $newJustification = $_POST['justification'];

        $sql_update_absence = "UPDATE absences SET duration_minutes = ?, justification = ? WHERE absence_id = ?";
        $stmt_update_absence = $conn->prepare($sql_update_absence);
        $stmt_update_absence->bind_param("isi", $newDuration, $newJustification, $absenceId);

        if ($stmt_update_absence->execute()) {
            http_response_code(200);
            echo "Modification réussie.";
        } else {
            http_response_code(500);
            echo "Erreur lors de la modification de l'absence.";
        }

        $stmt_update_absence->close();
    } elseif ($action == 'delete') {
        $absenceId = $_POST['absence_id'];

        $sql_delete_absence = "DELETE FROM absences WHERE absence_id = ?";
        $stmt_delete_absence = $conn->prepare($sql_delete_absence);
        $stmt_delete_absence->bind_param("i", $absenceId);

        if ($stmt_delete_absence->execute()) {
            http_response_code(200);
            echo "Suppression réussie.";
        } else {
            http_response_code(500);
            echo "Erreur lors de la suppression de l'absence.";
        }

        $stmt_delete_absence->close();
    }

    $conn->close();
    exit;
}
?>
