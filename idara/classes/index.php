<?php
$page_title = "Classes";
require_once '../../includes/auth_check.php';
requireRole('idara');
require_once '../../includes/header.php';
$db = Database::getInstance()->getConnection();
$classes = $db->query("
    SELECT c.*, COUNT(s.id) as nb_students 
    FROM classes c 
    LEFT JOIN students s ON s.class_id = c.id 
    GROUP BY c.id
")->fetchAll();
?>
<h2>Classes</h2>
<div class="row">
<?php foreach ($classes as $cl): ?>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <h5><?= htmlspecialchars($cl['name']) ?></h5>
                <p><?= $cl['nb_students'] ?> étudiants</p>
                <a href="../students/index.php?class=<?= $cl['id'] ?>" class="btn btn-sm btn-primary">Voir étudiants</a>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>
<?php require_once '../../includes/footer.php'; ?>