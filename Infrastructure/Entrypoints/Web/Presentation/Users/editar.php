<?php
declare(strict_types=1);

$error = null;
$id = (string) ($_GET['id'] ?? $_POST['id'] ?? '');
$user = null;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $updated = $userController->update($_POST);
        header('Location: ?action=users.list&msg=' . urlencode('Usuario actualizado: ' . $updated->id));
        exit;
    }
    $user = $userController->getById($id);
} catch (Throwable $e) {
    $error = $e->getMessage();
    if ($id !== '') {
        try {
            $user = $userController->getById($id);
        } catch (Throwable) {
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario — Hexagonal DDD</title>
    <link rel="stylesheet" href="?asset=css">
</head>
<body>
<header class="topbar">
    <h1>Actualizar Usuario</h1>
    <nav>
        <a href="?action=users.list">Volver a Usuarios</a>
    </nav>
</header>
<main class="container">
    <section class="card">
        <h2>Editar Usuario <?= htmlspecialchars($id) ?></h2>
        <?php if ($error !== null): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($user !== null): ?>
        <form method="post" action="?action=users.edit">
            <input type="hidden" name="id" value="<?= htmlspecialchars($user->id) ?>">
            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Nombre Completo</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($user->name) ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Correo</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user->email) ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Nueva Clave (dejar en blanco para conservar actual)</label>
                    <input type="password" id="password" name="password">
                </div>
                <div class="form-group">
                    <label for="role">Rol</label>
                    <select id="role" name="role">
                        <?php foreach (array('ADMIN', 'MEMBER', 'REVIEWER') as $roleOpt): ?>
                            <option value="<?= $roleOpt ?>" <?= $user->role === $roleOpt ? 'selected' : '' ?>><?= $roleOpt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status">Estado</label>
                    <select id="status" name="status">
                        <?php foreach (array('ACTIVE', 'INACTIVE', 'PENDING', 'BLOCKED') as $stOpt): ?>
                            <option value="<?= $stOpt ?>" <?= $user->status === $stOpt ? 'selected' : '' ?>><?= $stOpt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="actions">
                <button type="submit" class="btn">Actualizar</button>
                <a class="btn btn-secondary" href="?action=users.list">Cancelar</a>
            </div>
        </form>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
