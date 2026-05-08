<?php
$page_title = "Saisir les notes";
require_once '../../includes/auth_check.php';
requireRole('prof');
require_once '../../includes/header.php';

$db = Database::getInstance()->getConnection();
$classes = $db->query("SELECT * FROM classes ORDER BY name")->fetchAll();
?>
<h2>Choisir une classe</h2>
<div class="row">
<?php foreach ($classes as $cl): ?>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <h5><?= htmlspecialchars($cl['name']) ?></h5>
                <a href="enter.php?class_id=<?= $cl['id'] ?>" class="btn btn-primary">Saisir les notes</a>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>
<?php require_once '../../includes/footer.php'; ?>