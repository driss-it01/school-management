<?php
$page_title = "Saisir notes";
require_once '../../includes/auth_check.php';
requireRole('prof');
require_once '../../includes/header.php';

$class_id = $_GET['class_id'] ?? null;
if (!$class_id) redirect('prof/grades/index.php');

$subject_id = (int) $_SESSION['user_subject'];
$prof_id    = (int) $_SESSION['user_id'];
$db         = Database::getInstance()->getConnection();
$gradeObj   = new Grade();

$classInfo = $db->prepare("SELECT name FROM classes WHERE id = :id");
$classInfo->execute(['id' => $class_id]);
$className = $classInfo->fetchColumn();

if (!$className) redirect('prof/grades/index.php');

$success = false;
$errors  = [];

// ✅ FIX — traitement POST s7i7
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notes = $_POST['notes'] ?? [];

    foreach ($notes as $student_id => $note) {
        $note = trim(str_replace(',', '.', $note));

        // ila khawi — skip (machi erreur)
        if ($note === '') continue;

        if (!is_numeric($note)) {
            $errors[] = "Note invalide pour étudiant #$student_id";
            continue;
        }

        $float = (float) $note;
        if ($float < 0 || $float > 20) {
            $errors[] = "Note hors plage (0-20) pour étudiant #$student_id";
            continue;
        }

        $saved = $gradeObj->saveOrUpdate(
            (int) $student_id,
            $subject_id,
            $prof_id,
            $float
        );

        if (!$saved) {
            $errors[] = "Erreur sauvegarde étudiant #$student_id";
        }
    }

    if (empty($errors)) {
        $success = true;
    }

    // ✅ FIX — reload les données après save
    $studentsData = $gradeObj->getByClassAndSubject((int)$class_id, $subject_id);

} else {
    $studentsData = $gradeObj->getByClassAndSubject((int)$class_id, $subject_id);
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Saisie des notes — <?= htmlspecialchars($className) ?></h2>
    <a href="index.php" class="btn btn-secondary">← Retour</a>
</div>

<?php if ($success): ?>
    <div class="alert alert-success">✅ Notes enregistrées avec succès !</div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $e): ?>
            <div>⚠️ <?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="post">
    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>Étudiant</th>
                <th>Note actuelle</th>
                <th>Nouvelle note (0–20)</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($studentsData as $sd): ?>
            <tr>
                <td><?= htmlspecialchars($sd['full_name']) ?></td>
                <td>
                    <?php if ($sd['note'] !== null): ?>
                        <span class="badge bg-<?= $sd['note'] >= 10 ? 'success' : 'danger' ?> fs-6">
                            <?= number_format($sd['note'], 2) ?>/20
                        </span>
                    <?php else: ?>
                        <span class="text-muted">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <input type="number"
                           step="0.25"
                           min="0"
                           max="20"
                           name="notes[<?= $sd['student_id'] ?>]"
                           value="<?= $sd['note'] !== null ? htmlspecialchars($sd['note']) : '' ?>"
                           class="form-control"
                           placeholder="Ex: 14.5">
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <button type="submit" class="btn btn-success btn-lg">
        💾 Enregistrer toutes les notes
    </button>
</form>

<?php require_once '../../includes/footer.php'; ?>