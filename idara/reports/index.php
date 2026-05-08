<?php
$page_title = "Bulletins scolaires";
require_once '../../includes/auth_check.php';
requireRole('idara');
require_once '../../includes/header.php';

$studentObj = new Student();
$students = $studentObj->getAll();
?>
<h2>Générer un bulletin</h2>
<table class="table table-striped">
    <thead>
        <tr><th>Nom</th><th>Classe</th><th>Action</th></tr>
    </thead>
    <tbody>
    <?php foreach ($students as $s): ?>
        <tr>
            <td><?= htmlspecialchars($s['full_name']) ?></td>
            <td><?= htmlspecialchars($s['class_name']) ?></td>
            <td><a href="generate.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary">Générer PDF</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require_once '../../includes/footer.php'; ?>