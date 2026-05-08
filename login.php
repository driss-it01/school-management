<?php
require_once 'config.php';
$user = new User();
if ($user->isLoggedIn()) redirect('index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } elseif ($user->login($email, $password)) {
        redirect('index.php');
    } else {
        $error = 'Email ou mot de passe incorrect.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion – Lycée Technique Maroc</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body{background:#f0f4f8;}</style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">
<div class="card shadow-lg p-4" style="min-width: 380px; max-width: 420px;">
    <div class="text-center mb-4">
        <h4 class="fw-bold text-primary">Lycée Technique – Maroc</h4>
        <p class="text-muted">Espace de gestion scolaire</p>
    </div>
    <h5 class="text-center mb-3">Connexion</h5>
    <?php if ($error): ?>
        <div class="alert alert-danger py-2 text-center"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label class="form-label">Adresse email</label>
            <input type="email" name="email" class="form-control" placeholder="exemple@domaine.ma" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label">Mot de passe</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Se connecter</button>
    </form>
</div>
</body>
</html>