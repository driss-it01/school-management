<?php
$page_title = "Gestion des étudiants";
require_once '../../includes/auth_check.php';
requireRole('idara');
require_once '../../includes/header.php';

$studentObj = new Student();
$class_id = $_GET['class'] ?? null;
$students = $studentObj->getAll($class_id);
$classes = (new Subject())->getAll(); // On récupère plutôt les classes via une requête rapide
$db = Database::getInstance()->getConnection();
$classesList = $db->query("SELECT * FROM classes ORDER BY name")->fetchAll();
?>
<h2>Étudiants</h2>
<div class="mb-3">
    <a href="add.php" class="btn btn-primary">Ajouter un étudiant</a>
    <form method="get" class="d-inline ms-3">
        <select name="class" class="form-select d-inline w-auto" onchange="this.form.submit()">
            <option value="">Toutes les classes</option>
            <?php foreach ($classesList as $cl): ?>
                <option value="<?= $cl['id'] ?>" <?= $class_id == $cl['id'] ? 'selected' : '' ?>> <?= $cl['name'] ?> </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>
<table class="table table-bordered table-hover">
    <thead class="table-dark">
        <tr>
            <th>Nom</th>
            <th>Classe</th>
            <th>Date de naissance</th>
            <th>Photo</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($students as $st): ?>
        <tr>
            <td><?= htmlspecialchars($st['full_name']) ?></td>
            <td><?= htmlspecialchars($st['class_name']) ?></td>
            <td><?= htmlspecialchars($st['date_naissance']) ?></td>
            <td><img src="<?= BASE_URL ?>uploads/students/<?= htmlspecialchars($st['photo']) ?>" width="40" class="img-thumbnail"></td>
            <td>
                <a href="profile.php?id=<?= $st['id'] ?>" class="btn btn-sm btn-info">Profil</a>
                <a href="edit.php?id=<?= $st['id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
                <a href="delete.php?id=<?= $st['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cet étudiant ?')">Supprimer</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require_once '../../includes/footer.php'; ?>