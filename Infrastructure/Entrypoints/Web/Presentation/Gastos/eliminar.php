<?php
declare(strict_types=1);

$id = (string) ($_GET['id'] ?? $_POST['id'] ?? '');
$error = null;
$gasto = null;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id !== '') {
        $gastoController->delete($id);
        header('Location: ?action=gastos.list&msg=' . urlencode('Gasto eliminado: ' . $id));
        exit;
    }
    if ($id !== '') {
        $gasto = $gastoController->getById($id);
    }
} catch (Throwable $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Eliminar Gasto — Hexagonal DDD</title>
    <link rel="stylesheet" href="?asset=css">
</head>
<body>
<header class="topbar">
    <h1>Confirmar Eliminacion de Gasto</h1>
    <nav><a href="?action=gastos.list">Volver a Gastos</a></nav>
</header>
<main class="container" style="max-width: 640px;">
    <section class="card">
        <?php if ($error !== null): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($gasto !== null): ?>
            <h2>¿Eliminar el gasto <?= htmlspecialchars($gasto->id) ?>?</h2>
            <p style="margin: 0.75rem 0;">
                Lugar: <strong><?= htmlspecialchars($gasto->lugar) ?></strong><br>
                Usuario: <strong><?= htmlspecialchars($gasto->nombreUsuario) ?></strong><br>
                Total con IVA: <strong>$<?= number_format($gasto->valorTotalConIVA, 2) ?></strong>
            </p>
            <form method="post" action="?action=gastos.delete">
                <input type="hidden" name="id" value="<?= htmlspecialchars($gasto->id) ?>">
                <div class="actions">
                    <button type="submit" class="btn btn-danger">Confirmar Eliminacion</button>
                    <a class="btn btn-secondary" href="?action=gastos.list">Cancelar</a>
                </div>
            </form>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
