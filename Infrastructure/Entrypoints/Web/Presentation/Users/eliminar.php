<?php
declare(strict_types=1);

$id = (string) ($_GET['id'] ?? $_POST['id'] ?? '');
$error = null;
$user = null;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id !== '') {
        $userController->delete($id);
        header('Location: ?action=users.list&msg=' . urlencode('Usuario eliminado: ' . $id));
        exit;
    }
    if ($id !== '') {
        $user = $userController->getById($id);
    }
} catch (Throwable $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Eliminar Usuario — Hexagonal DDD</title>
    <link rel="stylesheet" href="?asset=css">
</head>
<body>
<header class="topbar">
    <h1>Confirmar Eliminacion de Usuario</h1>
    <nav><a href="?action=users.list">Volver a Usuarios</a></nav>
</header>
<main class="container" style="max-width: 640px;">
    <section class="card">
        <?php if ($error !== null): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($user !== null): ?>
            <h2>¿Deseas eliminar al usuario <?= htmlspecialchars($user->name) ?> (<?= htmlspecialchars($user->id) ?>)?</h2>
            <p style="margin: 1rem 0;">Correo: <strong><?= htmlspecialchars($user->email) ?></strong></p>
            <form method="post" action="?action=users.delete">
                <input type="hidden" name="id" value="<?= htmlspecialchars($user->id) ?>">
                <div class="actions">
                    <button type="submit" class="btn btn-danger">Confirmar Eliminacion</button>
                    <a class="btn btn-secondary" href="?action=users.list">Cancelar</a>
                </div>
            </form>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
