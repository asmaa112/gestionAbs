<div class="sidebar">
    <ul class="sidebar-menu">
    <li class="app-title">
    <a href="#" class="sidebar-link" onclick="redirectToDashboard()">
        <i class="fas fa-cogs"></i>
        Gestion des Absences Corio
    </a>
</li>
        <li class="title"><i class="fas fa-calendar-alt"></i> Partie Absences :</li>
        <li><a href="search_employee.php"><i class="fas fa-search"></i> Rechercher un Employé</a></li>
        <li><a href="add_absence.php"><i class="fas fa-plus"></i> Ajouter une Absence</a></li>
        <li><a href="view_absences.php"><i class="fas fa-list"></i> Voir les Absences</a></li>
        <li class="title"><i class="fas fa-users"></i> Partie Employés :</li>
        <li><a href="add_employee.php"><i class="fas fa-user-plus"></i> Ajouter un Employé</a></li>
        <li><a href="edit_employee.php"><i class="fas fa-user-edit"></i> Modifier un Employé</a></li>
        <li><a href="delete_employee.php"><i class="fas fa-user-minus"></i> Supprimer un Employé</a></li>
    </ul>
</div>
<script>
function redirectToDashboard() {
    window.location.href = "dashboard.php";
}
</script>
