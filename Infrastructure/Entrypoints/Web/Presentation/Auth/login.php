<?php
declare(strict_types=1);

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $logged = $authController->login(
            (string) ($_POST['email'] ?? ''),
            (string) ($_POST['password'] ?? '')
        );
        $_SESSION['auth_user'] = $logged->toArray();
        header('Location: ?action=gastos.list');
        exit;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso al Sistema — Usuarios y Gastos</title>
    <link rel="stylesheet" href="?asset=css">
</head>
<body>
<header class="topbar">
    <h1>Control Hexagonal DDD + CQRS — Usuarios &amp; Gastos (#6)</h1>
    <nav>
        <a href="?action=gastos.list">Gastos</a>
        <a href="?action=users.list">Usuarios</a>
        <a href="/swagger" target="_blank">Swagger API</a>
    </nav>
</header>
<main class="container" style="max-width: 480px;">
    <section class="card">
        <div class="card-header">
            <h2>Iniciar Sesion</h2>
            <span class="badge">DDD Auth</span>
        </div>
        <?php if ($error !== null): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="?action=login">
            <div class="form-group">
                <label for="email">Correo Electronico</label>
                <input type="email" id="email" name="email" value="admin@crud.com" required>
            </div>
            <div class="form-group">
                <label for="password">Clave</label>
                <input type="password" id="password" name="password" value="Developer123" required>
            </div>
            <button type="submit" class="btn">Ingresar</button>
        </form>
    </section>
</main>
</body>
</html>
