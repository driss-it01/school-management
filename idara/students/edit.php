<?php
$page_title = "Modifier un étudiant";
require_once '../../includes/auth_check.php';
requireRole('idara');
require_once '../../includes/header.php';

$studentObj = new Student();
$db = Database::getInstance()->getConnection();
$classes = $db->query("SELECT * FROM classes ORDER BY name")->fetchAll();

$id = $_GET['id'] ?? null;
if (!$id) redirect('idara/students/index.php');

$student = $studentObj->getById($id);
if (!$student) redirect('idara/students/index.php');

$error = '';

// ✅ FIX 1 — créer le dossier s'il n'existe pas
$uploadDir = __DIR__ . '/../../uploads/students/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name      = trim($_POST['full_name'] ?? '');
    $date_naissance = $_POST['date_naissance'] ?? '';
    $class_id       = $_POST['class_id'] ?? '';
    $photo_name     = $student['photo']; // ✅ garde l'ancienne par défaut

    if (empty($full_name) || empty($date_naissance) || empty($class_id)) {
        $error = 'Tous les champs sont obligatoires.';
    } else {

        // ✅ FIX 2 — validation complète upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $ext     = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $maxSize = 2 * 1024 * 1024; // 2MB

            if (!in_array($ext, $allowed)) {
                $error = 'Format invalide. Utilisez jpg, jpeg, png ou gif.';
            } elseif ($_FILES['photo']['size'] > $maxSize) {
                $error = 'Image trop grande. Maximum 2MB.';
            } else {
                // ✅ FIX 3 — supprimer ancienne photo si pas default
                if ($student['photo'] !== 'default.png') {
                    $oldPath = $uploadDir . $student['photo'];
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }

                // ✅ FIX 4 — nom unique
                $newName     = uniqid('stu_') . '_' . time() . '.' . $ext;
                $destination = $uploadDir . $newName;

                if (move_uploaded_file($_FILES['photo']['tmp_name'], $destination)) {
                    $photo_name = $newName;
                } else {
                    $error = 'Erreur upload. Vérifiez les permissions du dossier uploads/students/.';
                }
            }
        }

        if (!$error) {
            $studentObj->update($id, [
                'full_name'      => $full_name,
                'date_naissance' => $date_naissance,
                'photo'          => $photo_name,
                'class_id'       => $class_id,
            ]);
            redirect('idara/students/index.php');
        }
    }
}
?>

<h2>Modifier : <?= htmlspecialchars($student['full_name']) ?></h2>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
    <div class="mb-3">
        <label class="form-label">Nom complet</label>
        <input type="text" name="full_name" class="form-control"
               value="<?= htmlspecialchars($student['full_name']) ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Date de naissance</label>
        <input type="date" name="date_naissance" class="form-control"
               value="<?= htmlspecialchars($student['date_naissance']) ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Classe</label>
        <select name="class_id" class="form-select" required>
            <?php foreach ($classes as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $c['id'] == $student['class_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">
            Photo <small class="text-muted">(laisser vide pour conserver — jpg, png, max 2MB)</small>
        </label>

        <!-- ✅ FIX 5 — afficher photo actuelle toujours -->
        <div class="mb-2">
            <img src="<?= BASE_URL ?>uploads/students/<?= htmlspecialchars($student['photo']) ?>"
                 width="80" height="80"
                 style="object-fit:cover; border-radius:8px; border:1px solid #ddd;"
                 onerror="this.src='<?= BASE_URL ?>assets/images/default.png'">
            <small class="text-muted ms-2">Photo actuelle</small>
        </div>

        <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/gif">
    </div>

    <button type="submit" class="btn btn-warning">Mettre à jour</button>
    <a href="index.php" class="btn btn-secondary">Annuler</a>
</form>

<?php require_once '../../includes/footer.php'; ?>