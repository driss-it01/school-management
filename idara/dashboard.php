<?php
$page_title = "Tableau de bord";
require_once __DIR__ . '/../includes/auth_check.php';
requireRole('idara');
require_once __DIR__ . '/../includes/header.php';

$db = Database::getInstance()->getConnection();
$totalStudents = $db->query("SELECT COUNT(*) FROM students")->fetchColumn();
$totalProfs = $db->query("SELECT COUNT(*) FROM users WHERE role='prof'")->fetchColumn();
$totalClasses = 4;
$todayAbsences = $db->query("SELECT COUNT(*) FROM absences WHERE date = CURDATE()")->fetchColumn();
$lastStudents = $db->query("SELECT s.*, c.name as class_name FROM students s JOIN classes c ON s.class_id = c.id ORDER BY s.id DESC LIMIT 5")->fetchAll();
?>
<h2>Tableau de bord</h2>
<div class="row g-4 mt-2">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5>Étudiants</h5>
                <p class="display-6"><?= $totalStudents ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5>Professeurs</h5>
                <p class="display-6"><?= $totalProfs ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5>Classes</h5>
                <p class="display-6"><?= $totalClasses ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5>Absences aujourd'hui</h5>
                <p class="display-6"><?= $todayAbsences ?></p>
            </div>
        </div>
    </div>
</div>
<hr>
<h4>Derniers étudiants ajoutés</h4>
<table class="table table-striped">
    <thead>
        <tr><th>Nom</th><th>Classe</th><th>Date de naissance</th></tr>
    </thead>
    <tbody>
    <?php foreach ($lastStudents as $s): ?>
        <tr>
            <td><?= htmlspecialchars($s['full_name']) ?></td>
            <td><?= htmlspecialchars($s['class_name']) ?></td>
            <td><?= htmlspecialchars($s['date_naissance']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>