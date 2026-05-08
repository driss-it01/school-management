<?php
$page_title = "Saisir absences";
require_once '../../includes/auth_check.php';
requireRole('prof');
require_once '../../includes/header.php';

$class_id = $_GET['class_id'] ?? null;
if (!$class_id) redirect('prof/grades/index.php');

$subject_id = $_SESSION['user_subject'];
$db = Database::getInstance()->getConnection();

// ✅ FIX 1 — date vient de GET ou POST, pas les deux
$date = $_GET['date'] ?? $_POST['date'] ?? date('Y-m-d');

$classInfo = $db->prepare("SELECT name FROM classes WHERE id = :id");
$classInfo->execute(['id' => $class_id]);
$className = $classInfo->fetchColumn();

// Liste des étudiants de la classe
$stmtStudents = $db->prepare("SELECT id, full_name FROM students WHERE class_id = :cid ORDER BY full_name");
$stmtStudents->execute(['cid' => $class_id]);
$students = $stmtStudents->fetchAll();

// Absences existantes pour cette date + matière
$existingStmt = $db->prepare("
    SELECT student_id, justified FROM absences 
    WHERE date = :date AND subject_id = :sid
");
$existingStmt->execute(['date' => $date, 'sid' => $subject_id]);
$absMap = $existingStmt->fetchAll(PDO::FETCH_KEY_PAIR);

// ✅ FIX 2 — traitement POST complet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $absences = $_POST['absences'] ?? [];
    $justifies = $_POST['justified'] ?? [];

    foreach ($students as $st) {
        $isAbsent = isset($absences[$st['id']]) && $absences[$st['id']] === 'oui';
        $justified = $justifies[$st['id']] ?? 'non';

        if ($isAbsent) {
            // ✅ FIX 3 — upsert : ila kayna tbdlha, ila mkaynach zidha
            $checkStmt = $db->prepare("
                SELECT id FROM absences 
                WHERE student_id = :sid AND subject_id = :subid AND date = :date
            ");
            $checkStmt->execute(['sid' => $st['id'], 'subid' => $subject_id, 'date' => $date]);
            $existingId = $checkStmt->fetchColumn();

            if ($existingId) {
                $db->prepare("UPDATE absences SET justified = :j WHERE id = :id")
                   ->execute(['j' => $justified, 'id' => $existingId]);
            } else {
                $db->prepare("INSERT INTO absences (student_id, subject_id, date, justified) VALUES (?, ?, ?, ?)")
                   ->execute([$st['id'], $subject_id, $date, $justified]);
            }
        } else {
            // ✅ FIX 4 — ila student présent u 3ndo absence → supprime
            $db->prepare("
                DELETE FROM absences 
                WHERE student_id = :sid AND subject_id = :subid AND date = :date
            ")->execute(['sid' => $st['id'], 'subid' => $subject_id, 'date' => $date]);
        }
    }
    // ✅ FIX 5 — redirect m3a date s7i7
    redirect("prof/attendance/enter.php?class_id={$class_id}&date={$date}");
}
?>

<h2>Absences — <?= htmlspecialchars($className) ?></h2>

<!-- ✅ FIX 6 — form date séparée bach ma tkhletsh m3a form principale -->
<form method="get" class="mb-3 d-flex align-items-center gap-2">
    <input type="hidden" name="class_id" value="<?= htmlspecialchars($class_id) ?>">
    <label class="form-label mb-0 fw-bold">Date :</label>
    <input type="date" name="date" class="form-control w-auto"
           value="<?= htmlspecialchars($date) ?>"
           onchange="this.form.submit()">
</form>

<p class="text-muted">Matière : <strong><?= htmlspecialchars($_SESSION['user_subject'] ?? '') ?></strong> — <?= htmlspecialchars($date) ?></p>

<form method="post">
    <input type="hidden" name="date" value="<?= htmlspecialchars($date) ?>">
    <input type="hidden" name="submit" value="1">

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>Étudiant</th>
                <th>Statut</th>
                <th>Justifiée</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($students as $st):
            $isAbsent = array_key_exists($st['id'], $absMap);
            $just = $absMap[$st['id']] ?? 'non';
        ?>
            <tr class="<?= $isAbsent ? 'table-danger' : '' ?>">
                <td><?= htmlspecialchars($st['full_name']) ?></td>
                <td>
                    <select name="absences[<?= $st['id'] ?>]" class="form-select form-select-sm"
                            onchange="toggleJustified(this, 'just_<?= $st['id'] ?>')">
                        <option value="non" <?= !$isAbsent ? 'selected' : '' ?>>✅ Présent</option>
                        <option value="oui" <?= $isAbsent ? 'selected' : '' ?>>❌ Absent</option>
                    </select>
                </td>
                <td>
                    <select name="justified[<?= $st['id'] ?>]"
                            class="form-select form-select-sm"
                            id="just_<?= $st['id'] ?>"
                            <?= !$isAbsent ? 'disabled' : '' ?>>
                        <option value="non" <?= $just === 'non' ? 'selected' : '' ?>>Non justifiée</option>
                        <option value="oui" <?= $just === 'oui' ? 'selected' : '' ?>>Justifiée</option>
                    </select>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <button type="submit" class="btn btn-success">💾 Enregistrer les absences</button>
    <a href="index.php" class="btn btn-secondary">Retour</a>
</form>

<script>
// Enable/disable justified select selon présent ou absent
function toggleJustified(select, justId) {
    const justSelect = document.getElementById(justId);
    if (select.value === 'oui') {
        justSelect.disabled = false;
        select.closest('tr').classList.add('table-danger');
    } else {
        justSelect.disabled = true;
        justSelect.value = 'non';
        select.closest('tr').classList.remove('table-danger');
    }
}
</script>

<?php require_once '../../includes/footer.php'; ?>