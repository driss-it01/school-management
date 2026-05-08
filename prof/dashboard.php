<?php
$page_title = "Tableau de bord professeur";
require_once '../includes/auth_check.php';
requireRole('prof');
require_once '../includes/header.php';

$subject_id = $_SESSION['user_subject'];
$subjectObj = new Subject();
$subject = $subjectObj->getById($subject_id);
$userObj = new User();
$prof = $userObj->getCurrentUser();

$db = Database::getInstance()->getConnection();
$classes = $db->query("SELECT * FROM classes ORDER BY name")->fetchAll();
$gradeObj = new Grade();
?>
<h2>Bonjour, <?= htmlspecialchars($prof['full_name']) ?> — Matière : <?= htmlspecialchars($subject['name'] ?? 'Inconnue') ?></h2>
<div class="row mt-4">
<?php foreach ($classes as $cl): 
    $nbStudents = $db->prepare("SELECT COUNT(*) FROM students WHERE class_id = :cid");
    $nbStudents->execute(['cid' => $cl['id']]);
    $countStudents = $nbStudents->fetchColumn();
    
    $nbNotes = $db->prepare("SELECT COUNT(*) FROM grades g JOIN students s ON g.student_id = s.id WHERE s.class_id = :cid AND g.subject_id = :sid");
    $nbNotes->execute(['cid' => $cl['id'], 'sid' => $subject_id]);
    $countNotes = $nbNotes->fetchColumn();
    
    $nbAbsences = $db->prepare("SELECT COUNT(*) FROM absences a JOIN students s ON a.student_id = s.id WHERE s.class_id = :cid AND a.subject_id = :sid");
    $nbAbsences->execute(['cid' => $cl['id'], 'sid' => $subject_id]);
    $countAbsences = $nbAbsences->fetchColumn();
?>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($cl['name']) ?></h5>
                <p>Étudiants : <?= $countStudents ?></p>
                <p>Notes saisies : <?= $countNotes ?></p>
                <p>Absences : <?= $countAbsences ?></p>
                <a href="grades/enter.php?class_id=<?= $cl['id'] ?>" class="btn btn-sm btn-outline-primary">Saisir notes</a>
                <a href="attendance/enter.php?class_id=<?= $cl['id'] ?>" class="btn btn-sm btn-outline-warning">Saisir absences</a>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>
<?php require_once '../includes/footer.php'; ?>