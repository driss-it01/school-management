<?php
$subjectObj = new Subject();
$subject = $subjectObj->getById($_SESSION['user_subject']);
?>
<div class="bg-dark text-white p-3" style="min-width: 250px; min-height: 100vh;">
    <h5 class="text-center mb-4">Espace Professeur</h5>
    <div class="text-center mb-3">
        <span class="badge bg-primary"><?= htmlspecialchars($subject['name'] ?? 'Matière inconnue') ?></span>
    </div>
    <ul class="nav flex-column">
        <li class="nav-item"><a href="<?= BASE_URL ?>prof/dashboard.php" class="nav-link text-white">Tableau de bord</a></li>
        <li class="nav-item"><a href="<?= BASE_URL ?>prof/grades/index.php" class="nav-link text-white">Saisir notes</a></li>
        <li class="nav-item"><a href="<?= BASE_URL ?>prof/attendance/index.php" class="nav-link text-white">Saisir absences</a></li>
    </ul>
</div>