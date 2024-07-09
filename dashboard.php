<?php
session_start();

// Vérification de la session pour s'assurer que l'utilisateur est connecté
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'db_connection.php';

// Calculer le pourcentage d'absence pour le mois actuel
$currentMonth = date('m');
$currentYear = date('Y');
$currentDay = date('Y-m-d');

// Total des employés
$sqlTotalEmployees = "SELECT COUNT(*) as total FROM employees";
$resultTotalEmployees = $conn->query($sqlTotalEmployees);
$totalEmployees = $resultTotalEmployees->fetch_assoc()['total'];

// Absences mensuelles
$sqlMonthlyAbsences = "SELECT SUM(duration_minutes) as total_absence_minutes FROM absences WHERE MONTH(absence_date) = ? AND YEAR(absence_date) = ?";
$stmtMonthlyAbsences = $conn->prepare($sqlMonthlyAbsences);
$stmtMonthlyAbsences->bind_param("ii", $currentMonth, $currentYear);
$stmtMonthlyAbsences->execute();
$resultMonthlyAbsences = $stmtMonthlyAbsences->get_result();
$totalMonthlyAbsenceMinutes = $resultMonthlyAbsences->fetch_assoc()['total_absence_minutes'] ?? 0;

// Absences quotidiennes
$sqlDailyAbsences = "SELECT SUM(duration_minutes) as total_absence_minutes FROM absences WHERE absence_date = ?";
$stmtDailyAbsences = $conn->prepare($sqlDailyAbsences);
$stmtDailyAbsences->bind_param("s", $currentDay);
$stmtDailyAbsences->execute();
$resultDailyAbsences = $stmtDailyAbsences->get_result();
$totalDailyAbsenceMinutes = $resultDailyAbsences->fetch_assoc()['total_absence_minutes'] ?? 0;

// Pourcentage d'absence
$monthlyPercentage = ($totalMonthlyAbsenceMinutes / ($totalEmployees * 8 * 60 * 30)) * 100;
$dailyPercentage = ($totalDailyAbsenceMinutes / ($totalEmployees * 8 * 60)) * 100;

// Récupérer les informations sur la présence des employés
$sqlEmployees = "SELECT e.name, IF(a.duration_minutes IS NULL, 'Présent', 'Absent') as status
                 FROM employees e
                 LEFT JOIN absences a ON e.employee_id = a.employee_id AND a.absence_date = ?";
$stmtEmployees = $conn->prepare($sqlEmployees);
$stmtEmployees->bind_param("s", $currentDay);
$stmtEmployees->execute();
$resultEmployees = $stmtEmployees->get_result();

$stmtMonthlyAbsences->close();
$stmtDailyAbsences->close();
$stmtEmployees->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="styles.css">
    <style>
   
    </style>
</head>

<body>

    <?php include 'sidebar.php'; ?> <!-- Inclusion de la barre latérale -->

    <div class="content">
        <div class="dashboard-container">
            <h1>Bienvenue sur le Tableau de Bord</h1>
            <p>Bonjour, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
            <div class="logout-link">
                <p><a href="logout.php">Déconnexion</a></p>
            </div>
            <!-- Contenu principal ici -->
            <div class="box">
               
                <div class="chart-container">
                    <h4>Pourcentage d'absence du jour actuel</h4>
                    <canvas id="dailyAbsenceChart" width="200" height="200"></canvas>
                    <div class="percentage-label">
                        <p>Présent: <?php echo round(100 - $dailyPercentage, 2); ?>%</p>
                        <p>Absent: <?php echo round($dailyPercentage, 2); ?>%</p>
                    </div>
                </div>
                <div class="chart-container">
                    <h4>Pourcentage d'absence par mois</h4>
                    <canvas id="monthlyAbsenceChart" width="200" height="200"></canvas>
                    <div class="percentage-label">
                        <p>Présent: <?php echo round(100 - $monthlyPercentage, 2); ?>%</p>
                        <p>Absent: <?php echo round($monthlyPercentage, 2); ?>%</p>
                    </div>
                </div>

            </div>

            <div class="table-container">
                <h4>Statut de Présence des Employés</h4>
                <table>
                    <tr>
                        <th>Nom de l'employé</th>
                        <th>Statut</th>
                    </tr>
                    <?php while ($row = $resultEmployees->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td class="<?php echo $row['status'] == 'Présent' ? 'status-present' : 'status-absent'; ?>">
                                <?php echo $row['status']; ?>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>

            <!-- Déconnexion -->
            
        </div>
    </div>

    <script>
        // Initialiser les graphiques après le chargement de la page
        window.onload = function () {
            // Pourcentage d'absence par mois
            const ctxMonthly = document.getElementById('monthlyAbsenceChart').getContext('2d');
            const monthlyChart = new Chart(ctxMonthly, {
                type: 'doughnut',
                data: {
                    labels: ['Présent', 'Absent'],
                    datasets: [{
                        data: [100 - <?php echo round($monthlyPercentage, 2); ?>, <?php echo round($monthlyPercentage, 2); ?>],
                        backgroundColor: ['#36A2EB', '#FF6384']
                    }]
                },
                options: {
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return context.label + ': ' + context.raw + '%';
                                }
                            }
                        }
                    }
                }
            });

            // Pourcentage d'absence du jour actuel
            const ctxDaily = document.getElementById('dailyAbsenceChart').getContext('2d');
            const dailyChart = new Chart(ctxDaily, {
                type: 'doughnut',
                data: {
                    labels: ['Présent', 'Absent'],
                    datasets: [{
                        data: [100 - <?php echo round($dailyPercentage, 2); ?>, <?php echo round($dailyPercentage, 2); ?>],
                        backgroundColor: ['#36A2EB', '#FF6384']
                    }]
                },
                options: {
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return context.label + ': ' + context.raw + '%';
                                }
                            }
                        }
                    }
                }
            });
        };
    </script>

</body>

</html>
