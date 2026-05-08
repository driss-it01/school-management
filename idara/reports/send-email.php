<?php
$page_title = "Envoyer bulletin par email";
require_once '../../includes/auth_check.php';
requireRole('idara');
require '../../vendor/autoload.php';

$db         = Database::getInstance()->getConnection();
$studentObj = new Student();
$success    = [];
$error      = '';

// ✅ FIX — AJAX 9bl header.php bach ma ykhletsh HTML m3a JSON
if (isset($_GET['get_students'])) {
    $cid  = (int) $_GET['class_id'];
    $stmt = $db->prepare("
        SELECT s.id, s.full_name, s.photo,
               ROUND(COALESCE(
                   (SELECT SUM(g.note * sub.coefficient) / SUM(sub.coefficient)
                    FROM grades g
                    JOIN subjects sub ON g.subject_id = sub.id
                    WHERE g.student_id = s.id), 0
               ), 2) as moyenne
        FROM students s
        WHERE s.class_id = :cid
        ORDER BY s.full_name
    ");
    $stmt->execute(['cid' => $cid]);
    $rows = $stmt->fetchAll();

    foreach ($rows as &$row) {
        // Auto-generate email mn smiyat student
        $parts = explode(' ', strtolower(trim($row['full_name'])));
        $row['email_suggest'] = implode('.', $parts) . '@gmail.com';
    }

    header('Content-Type: application/json');
    echo json_encode($rows);
    exit;
}

// ✅ header.php hna — ba3d AJAX check
require_once '../../includes/header.php';

// Classes
$classesList = $db->query("SELECT * FROM classes ORDER BY name")->fetchAll();

// Build PDF HTML
function buildBulletinHtml(array $student, array $grades, float $moyenne, string $gradeLetter, string $status, array $counts): string
{
    $statusColor = $status === 'Admis' ? '#15803d' : '#dc2626';
    $html = '<style>
        body{font-family:Arial,sans-serif;font-size:12px;}
        h2,h3{text-align:center;margin:4px 0;}
        table{width:100%;border-collapse:collapse;margin-top:10px;}
        th,td{border:1px solid #333;padding:6px 8px;text-align:center;}
        th{background:#1e40af;color:white;}
        .fr{background:#f1f5f9;font-weight:bold;}
        hr{border:1px solid #ccc;margin:12px 0;}
    </style>
    <div style="text-align:center;border-bottom:2px solid #1e40af;padding-bottom:10px;margin-bottom:14px;">
        <h2>Lycée Technique – Maroc</h2>
        <h3>Bulletin Scolaire — Année 2024/2025</h3>
    </div>
    <p><strong>Nom :</strong> ' . htmlspecialchars($student['full_name']) . '</p>
    <p><strong>Classe :</strong> ' . htmlspecialchars($student['class_name']) . '</p>
    <p><strong>Date de naissance :</strong> ' . htmlspecialchars($student['date_naissance']) . '</p>
    <hr>
    <table>
        <thead><tr><th>Matière</th><th>Coefficient</th><th>Note /20</th><th>Mention</th></tr></thead>
        <tbody>';
    foreach ($grades as $g) {
        $html .= '<tr>
            <td>' . htmlspecialchars($g['subject_name']) . '</td>
            <td>' . $g['coefficient'] . '</td>
            <td>' . number_format($g['note'], 2) . '</td>
            <td>' . Grade::getGradeLetter((float)$g['note']) . '</td>
        </tr>';
    }
    $html .= '</tbody>
        <tfoot>
            <tr class="fr">
                <td colspan="2">Moyenne Générale</td>
                <td>' . number_format($moyenne, 2) . '/20</td>
                <td>' . $gradeLetter . '</td>
            </tr>
        </tfoot>
    </table>
    <p><strong>Résultat :</strong> <span style="color:' . $statusColor . ';font-weight:bold;">' . $status . '</span></p>
    <hr>
    <h4>Absences</h4>
    <table>
        <thead><tr><th>Total</th><th>Justifiées</th><th>Non justifiées</th></tr></thead>
        <tbody><tr>
            <td>' . $counts['total'] . '</td>
            <td>' . $counts['justified'] . '</td>
            <td>' . $counts['non_justified'] . '</td>
        </tr></tbody>
    </table>';
    return $html;
}

// Envoyer un seul bulletin
function sendBulletinEmail(int $student_id, string $email, $studentObj): array
{
    $student = $studentObj->getById($student_id);
    if (!$student) return ['error' => 'Étudiant introuvable.'];

    $gradeObj    = new Grade();
    $grades      = $gradeObj->getByStudent($student_id);
    $moyenne     = $gradeObj->calculateAverage($student_id);
    $gradeLetter = Grade::getGradeLetter($moyenne);
    $status      = $moyenne >= 10 ? 'Admis' : 'Non Admis';
    $absenceObj  = new Absence();
    $counts      = $absenceObj->countByStudent($student_id);

    $html   = buildBulletinHtml($student, $grades, $moyenne, $gradeLetter, $status, $counts);
    $domPdf = new \Dompdf\Dompdf();
    $domPdf->loadHtml($html);
    $domPdf->setPaper('A4', 'portrait');
    $domPdf->render();

    $tempPath = __DIR__ . '/../../uploads/temp_' . $student_id . '_' . time() . '.pdf';
    file_put_contents($tempPath, $domPdf->output());

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'zeroualdriss15@gmail.com'; // ← bdl hna
        $mail->Password   = 'xpqt eyuf weab cupt';    // ← bdl hna
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        $mail->setFrom('zeroualdriss15@gmail.com', 'Lycée Technique Maroc');
        $mail->addAddress($email);
        $mail->Subject = 'Bulletin Scolaire — ' . $student['full_name'];
        $mail->isHTML(true);
        $mail->Body    = '<p>Bonjour,</p><p>Veuillez trouver ci-joint le bulletin scolaire de <strong>'
            . htmlspecialchars($student['full_name']) . '</strong>.</p>'
            . '<p>Cordialement,<br><strong>Administration — Lycée Technique Maroc</strong></p>';
        $mail->addAttachment($tempPath, 'bulletin_' . $student['full_name'] . '.pdf');
        $mail->send();
        if (file_exists($tempPath)) unlink($tempPath);
        return ['success' => true, 'name' => $student['full_name'], 'email' => $email];
    } catch (\Exception $e) {
        if (file_exists($tempPath)) unlink($tempPath);
        return ['error' => $mail->ErrorInfo];
    }
}

// Traitement POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emails  = $_POST['emails'] ?? [];
    $sendIds = $_POST['send']   ?? [];

    if (empty($sendIds)) {
        $error = 'Veuillez sélectionner au moins un étudiant.';
    } else {
        foreach ($sendIds as $sid => $val) {
            $email = trim($emails[$sid] ?? '');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error .= "Email invalide pour étudiant #$sid. ";
                continue;
            }
            $result = sendBulletinEmail((int)$sid, $email, $studentObj);
            if (isset($result['success'])) {
                $success[] = $result['name'] . ' → ' . $result['email'];
            } else {
                $error .= "Erreur (#$sid) : " . $result['error'] . " ";
            }
        }
    }
}

$selectedClass = $_POST['class_id'] ?? $_GET['class_id'] ?? '';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>📧 Envoyer bulletins par email</h2>
    <a href="index.php" class="btn btn-secondary">← Retour</a>
</div>

<?php if (!empty($success)): ?>
<div class="alert alert-success">
    ✅ <strong>Bulletins envoyés avec succès :</strong><br>
    <?php foreach ($success as $s): ?>
        <span class="badge bg-success me-1 mt-1"><?= htmlspecialchars($s) ?></span>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">

        <!-- Étape 1 — Classe -->
        <div class="mb-4">
            <label class="form-label fw-bold">① Choisir la classe</label>
            <select id="selectClasse" class="form-select w-auto" onchange="loadStudents(this.value)">
                <option value="">— Sélectionner une classe —</option>
                <?php foreach ($classesList as $cl): ?>
                    <option value="<?= $cl['id'] ?>"
                        <?= $selectedClass == $cl['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cl['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Loading -->
        <div id="loadingDiv" style="display:none" class="text-center py-3">
            <div class="spinner-border text-primary"></div>
            <p class="mt-2 text-muted">Chargement des étudiants...</p>
        </div>

        <!-- Étape 2 — Table students -->
        <div id="studentSection" style="display:none">
            <form method="post" id="emailForm">
                <input type="hidden" name="class_id" id="hiddenClassId" value="<?= htmlspecialchars($selectedClass) ?>">

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label fw-bold mb-0">② Étudiants — cochez et vérifiez l'email</label>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll(true)">✅ Tout sélectionner</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectAll(false)">❌ Tout désélectionner</button>
                    </div>
                </div>

                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="checkAll" onchange="selectAll(this.checked)">
                            </th>
                            <th>Nom complet</th>
                            <th width="120">Moyenne</th>
                            <th>Email destinataire</th>
                            <th width="80">PDF</th>
                        </tr>
                    </thead>
                    <tbody id="studentTableBody">
                        <tr><td colspan="5" class="text-center text-muted">Sélectionnez une classe.</td></tr>
                    </tbody>
                </table>

                <button type="submit" class="btn btn-primary btn-lg">
                    📨 Envoyer les bulletins sélectionnés
                </button>
            </form>
        </div>

    </div>
</div>

<script>
function loadStudents(classId) {
    const section = document.getElementById('studentSection');
    const loading = document.getElementById('loadingDiv');
    const tbody   = document.getElementById('studentTableBody');

    section.style.display = 'none';
    loading.style.display = 'block';
    document.getElementById('hiddenClassId').value = classId;

    if (!classId) {
        loading.style.display = 'none';
        return;
    }

    fetch(`send-email.php?get_students=1&class_id=${classId}`)
        .then(r => r.json())
        .then(students => {
            loading.style.display = 'none';

            if (!students.length) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Aucun étudiant.</td></tr>';
                section.style.display = 'block';
                return;
            }

            tbody.innerHTML = '';
            students.forEach(s => {
                const moy   = parseFloat(s.moyenne);
                const badge = moy >= 10
                    ? `<span class="badge bg-success fs-6">${moy.toFixed(2)}/20</span>`
                    : `<span class="badge bg-danger fs-6">${moy.toFixed(2)}/20</span>`;

                tbody.innerHTML += `
                <tr>
                    <td class="text-center">
                        <input type="checkbox" name="send[${s.id}]" value="1"
                               class="student-check" checked>
                    </td>
                    <td><strong>${esc(s.full_name)}</strong></td>
                    <td class="text-center">${badge}</td>
                    <td>
                        <input type="email"
                               name="emails[${s.id}]"
                               class="form-control form-control-sm"
                               value="${esc(s.email_suggest)}"
                               placeholder="email@gmail.com">
                    </td>
                    <td class="text-center">
                        <a href="../reports/generate.php?id=${s.id}"
                           target="_blank"
                           class="btn btn-sm btn-outline-danger" title="Voir PDF">
                           📄
                        </a>
                    </td>
                </tr>`;
            });

            section.style.display = 'block';
        })
        .catch(() => {
            loading.style.display = 'none';
            tbody.innerHTML = '<tr><td colspan="5" class="text-danger text-center">Erreur de chargement.</td></tr>';
            section.style.display = 'block';
        });
}

function selectAll(checked) {
    document.querySelectorAll('.student-check').forEach(cb => cb.checked = checked);
    const ca = document.getElementById('checkAll');
    if (ca) ca.checked = checked;
}

function esc(str) {
    return String(str)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Auto-load si classe déjà sélectionnée
window.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('selectClasse');
    if (sel.value) loadStudents(sel.value);
});
</script>

<?php require_once '../../includes/footer.php'; ?>