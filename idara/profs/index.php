<?php
$page_title = "Gestion des professeurs";
require_once '../../includes/auth_check.php';
requireRole('idara');
require_once '../../includes/header.php';
$userObj = new User();
$profs = $userObj->getAllProfs();
?>
<h2>Professeurs</h2>
<a href="add.php" class="btn btn-primary mb-3">Ajouter un professeur</a>
<table class="table table-bordered">
    <thead class="table-dark">
        <tr><th>Nom</th><th>Email</th><th>Matière</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php foreach ($profs as $p): ?>
        <tr>
            <td><?= htmlspecialchars($p['full_name']) ?></td>
            <td><?= htmlspecialchars($p['email']) ?></td>
            <td><?= htmlspecialchars($p['subject_name'] ?? '—') ?></td>
            <td>
                <a href="delete.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce professeur ?')">Supprimer</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require_once '../../includes/footer.php'; ?>