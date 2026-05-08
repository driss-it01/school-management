<?php
$page_title = "Profil étudiant";
require_once '../../includes/auth_check.php';
requireRole('idara');
require_once '../../includes/header.php';

$id = $_GET['id'] ?? null;
if (!$id) redirect('index.php');

$studentObj = new Student();
$student = $studentObj->getById($id);
if (!$student) redirect('index.php');

$gradeObj = new Grade();
$grades = $gradeObj->getByStudent($id);
$moyenne = $gradeObj->calculateAverage($id);
$gradeLetter = Grade::getGradeLetter($moyenne);
$status = $moyenne >= 10 ? 'Admis' : 'Non Admis';
$absenceObj = new Absence();
$absences = $absenceObj->getByStudent($id);
$counts = $absenceObj->countByStudent($id);
?>
<div class="row">
    <div class="col-md-3 text-center">
        <img src="<?= BASE_URL ?>uploads/students/<?= htmlspecialchars($student['photo']) ?>" class="img-fluid rounded-circle border" width="150">
        <h4 class="mt-2"><?= htmlspecialchars($student['full_name']) ?></h4>
        <p>Classe : <?= htmlspecialchars($student['class_name']) ?></p>
        <p>Né(e) le : <?= htmlspecialchars($student['date_naissance']) ?></p>
    </div>
    <div class="col-md-9">
        <h4>Notes</h4>
        <table class="table table-bordered">
            <thead>
                <tr><th>Matière</th><th>Coefficient</th><th>Note /20</th><th>Mention</th></tr>
            </thead>
            <tbody>
            <?php foreach ($grades as $g): ?>
                <tr>
                    <td><?= htmlspecialchars($g['subject_name']) ?></td>
                    <td><?= $g['coefficient'] ?></td>
                    <td><?= number_format($g['note'], 2) ?></td>
                    <td><?= Grade::getGradeLetter($g['note']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot class="table-secondary">
                <tr>
                    <td colspan="2"><strong>Moyenne générale</strong></td>
                    <td><strong><?= number_format($moyenne, 2) ?>/20</strong></td>
                    <td><strong><?= $gradeLetter ?></strong></td>
                </tr>
            </tfoot>
        </table>
        <div class="alert <?= $status === 'Admis' ? 'alert-success' : 'alert-danger' ?>">
            Résultat : <strong><?= $status ?></strong>
        </div>

        <h4 class="mt-4">Absences</h4>
        <p>Total : <?= $counts['total'] ?> (Justifiées : <?= $counts['justified'] ?>, Non justifiées : <?= $counts['non_justified'] ?>)</p>
        <table class="table table-sm">
            <thead>
                <tr><th>Date</th><th>Matière</th><th>Justifiée</th></tr>
            </thead>
            <tbody>
            <?php foreach ($absences as $a): ?>
                <tr>
                    <td><?= htmlspecialchars($a['date']) ?></td>
                    <td><?= htmlspecialchars($a['subject_name']) ?></td>
                    <td><?= $a['justified'] === 'oui' ? 'Oui' : 'Non' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>