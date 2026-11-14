<?php
declare(strict_types=1);

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $created = $userController->create($_POST);
        header('Location: ?action=users.list&msg=' . urlencode('Usuario creado: ' . $created->id));
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
    <title>Registrar Usuario — Hexagonal DDD</title>
    <link rel="stylesheet" href="?asset=css">
</head>
<body>
<header class="topbar">
    <h1>Registrar Nuevo Usuario</h1>
    <nav>
        <a href="?action=users.list">Volver a Usuarios</a>
        <a href="?action=gastos.list">Ir a Gastos</a>
    </nav>
</header>
<main class="container">
    <section class="card">
        <h2>Datos del Usuario</h2>
        <?php if ($error !== null): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="?action=users.create">
            <div class="form-grid">
                <div class="form-group">
                    <label for="id">ID (opcional, autogenerado si se omite)</label>
                    <input type="text" id="id" name="id" placeholder="USR-003">
                </div>
                <div class="form-group">
                    <label for="name">Nombre Completo</label>
                    <input type="text" id="name" name="name" required minlength="3">
                </div>
                <div class="form-group">
                    <label for="email">Correo Institucional / Personal</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Clave (min. 8 caracteres)</label>
                    <input type="password" id="password" name="password" required minlength="8">
                </div>
                <div class="form-group">
                    <label for="role">Rol</label>
                    <select id="role" name="role">
                        <option value="MEMBER">MEMBER</option>
                        <option value="ADMIN">ADMIN</option>
                        <option value="REVIEWER">REVIEWER</option>
                    </select>
                </div>
            </div>
            <div class="actions">
                <button type="submit" class="btn">Guardar Usuario</button>
                <a class="btn btn-secondary" href="?action=users.list">Cancelar</a>
            </div>
        </form>
    </section>
</main>
</body>
</html>
