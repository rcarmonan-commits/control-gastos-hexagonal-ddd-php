<?php
declare(strict_types=1);

$error = null;
$id = (string) ($_GET['id'] ?? $_POST['id'] ?? '');
$gasto = null;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $updated = $gastoController->update($_POST);
        header('Location: ?action=gastos.list&msg=' . urlencode('Gasto actualizado: ' . $updated->id));
        exit;
    }
    $gasto = $gastoController->getById($id);
} catch (Throwable $e) {
    $error = $e->getMessage();
    if ($id !== '') {
        try {
            $gasto = $gastoController->getById($id);
        } catch (Throwable) {
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Gasto — Hexagonal DDD</title>
    <link rel="stylesheet" href="?asset=css">
    <script src="?asset=js" defer></script>
</head>
<body>
<header class="topbar">
    <h1>Actualizar Gasto <?= htmlspecialchars($id) ?></h1>
    <nav><a href="?action=gastos.list">Volver a Gastos</a></nav>
</header>
<main class="container">
    <section class="card">
        <div class="card-header">
            <h2>Editar Datos del Gasto</h2>
            <button type="button" id="btnCalcularIva19" class="btn btn-secondary">Recalcular IVA 19%</button>
        </div>
        <?php if ($error !== null): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($gasto !== null): ?>
        <form method="post" action="?action=gastos.edit">
            <input type="hidden" name="id" value="<?= htmlspecialchars($gasto->id) ?>">
            <div class="form-grid">
                <div class="form-group">
                    <label for="fecha">Fecha</label>
                    <input type="date" id="fecha" name="fecha" value="<?= htmlspecialchars($gasto->fecha) ?>" required>
                </div>
                <div class="form-group">
                    <label for="nombreUsuario">Nombre de Usuario</label>
                    <input type="text" id="nombreUsuario" name="nombreUsuario" value="<?= htmlspecialchars($gasto->nombreUsuario) ?>" required>
                </div>
                <div class="form-group">
                    <label for="lugar">Lugar</label>
                    <input type="text" id="lugar" name="lugar" value="<?= htmlspecialchars($gasto->lugar) ?>" required>
                </div>
                <div class="form-group">
                    <label for="valorTotalSinIVA">Valor Total Sin IVA</label>
                    <input type="number" step="0.01" min="0" id="valorTotalSinIVA" name="valorTotalSinIVA" value="<?= number_format($gasto->valorTotalSinIVA, 2, '.', '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="ivaTotal">IVA Total</label>
                    <input type="number" step="0.01" min="0" id="ivaTotal" name="ivaTotal" value="<?= number_format($gasto->ivaTotal, 2, '.', '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="valorTotalConIVA">Valor Total Con IVA</label>
                    <input type="number" step="0.01" min="0" id="valorTotalConIVA" name="valorTotalConIVA" value="<?= number_format($gasto->valorTotalConIVA, 2, '.', '') ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label for="descripcion">Descripcion</label>
                <textarea id="descripcion" name="descripcion" rows="3" required><?= htmlspecialchars($gasto->descripcion) ?></textarea>
            </div>
            <div class="actions">
                <button type="submit" class="btn">Guardar Cambios</button>
                <a class="btn btn-secondary" href="?action=gastos.list">Cancelar</a>
            </div>
        </form>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
