<?php
session_start();

require_once '../app/config/database.php';
require_once '../app/helpers/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $selectedStructure = $_POST['structure_id'] ?? null;

    $stmt = $pdo->prepare("
        SELECT *
        FROM users
        WHERE email = ?
        AND is_active = 1
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        $error = "Email ou mot de passe incorrect.";
    } else {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['firstname'] = $user['firstname'];
        $_SESSION['lastname'] = $user['lastname'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['global_role'] = $user['global_role'];

        if ($user['global_role'] === 'super_admin') {
            header('Location: ../admin/dashboard.php');
            exit;
        }

        $stmt = $pdo->prepare("
            SELECT us.structure_id, us.role, s.name AS structure_name
            FROM user_structures us
            JOIN structures s ON s.id = us.structure_id
            WHERE us.user_id = ?
            AND s.is_active = 1
        ");
        $stmt->execute([$user['id']]);
        $structures = $stmt->fetchAll();

        if (count($structures) === 0) {
            $error = "Aucune structure associée à ce compte.";
            session_unset();
        } elseif (count($structures) === 1) {
            $_SESSION['structure_id'] = $structures[0]['structure_id'];
            $_SESSION['structure_name'] = $structures[0]['structure_name'];
            $_SESSION['role'] = $structures[0]['role'];

            header('Location: index.php');
            exit;
        } else {
            $_SESSION['available_structures'] = $structures;
            header('Location: select_structure.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion ENT</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <h1>ENT Scolaire</h1>
        <p>Connexion à votre espace</p>

        <?php if ($error): ?>
            <div class="alert">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label>Email</label>
            <input type="email" name="email" required>

            <label>Mot de passe</label>
            <input type="password" name="password" required>

            <button type="submit">Se connecter</button>
        </form>

        <div class="help">
            Super admin par défaut :
            <br>
            <strong>admin@ent.test</strong> / <strong>admin123</strong>
        </div>
    </div>
</div>

</body>
</html>
