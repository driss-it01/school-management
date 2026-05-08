<?php
$page_title = "Ajouter un professeur";
require_once '../../includes/auth_check.php';
requireRole('idara');
require_once '../../includes/header.php';
$subjectObj = new Subject();
$subjects = $subjectObj->getAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $subject_id = $_POST['subject_id'] ?? null;

    if (empty($full_name) || empty($email) || empty($password) || !$subject_id) {
        $error = 'Tous les champs sont obligatoires.';
    } else {
        $userObj = new User();
        $userObj->addProf([
            'full_name' => $full_name,
            'email' => $email,
            'password' => $password,
            'subject_id' => $subject_id,
        ]);
        redirect('index.php');
    }
}
?>
<h2>Nouveau professeur</h2>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<form method="post">
    <div class="mb-3">
        <label>Nom complet</label>
        <input type="text" name="full_name" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" placeholder="prenom@school.ma" required>
    </div>
    <div class="mb-3">
        <label>Mot de passe</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Matière</label>
        <select name="subject_id" class="form-select" required>
            <option value="">Choisir...</option>
            <?php foreach ($subjects as $s): ?>
                <option value="<?= $s['id'] ?>"><?= $s['name'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-success">Enregistrer</button>
    <a href="index.php" class="btn btn-secondary">Annuler</a>
</form>
<?php require_once '../../includes/footer.php'; ?>