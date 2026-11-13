<?php
declare(strict_types=1);

$error = null;
$usuariosDisponibles = $userController->list();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $created = $gastoController->create($_POST);
        header('Location: ?action=gastos.list&msg=' . urlencode('Gasto registrado exitosamente: ' . $created->id));
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
    <title>Registrar Gasto (#6) — Hexagonal DDD</title>
    <link rel="stylesheet" href="?asset=css">
    <script src="?asset=js" defer></script>
</head>
<body>
<header class="topbar">
    <h1>Registrar Nuevo Gasto — Ejercicio #6</h1>
    <nav>
        <a href="?action=gastos.list">Volver a Gastos</a>
        <a href="?action=users.list">Usuarios</a>
    </nav>
</header>
<main class="container">
    <section class="card">
        <div class="card-header">
            <h2>Formulario de Registro de Gasto</h2>
            <button type="button" id="btnCalcularIva19" class="btn btn-secondary">Calcular IVA 19%</button>
        </div>
        <?php if ($error !== null): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="?action=gastos.create">
            <div class="form-grid">
                <div class="form-group">
                    <label for="id">ID del Gasto (opcional)</label>
                    <input type="text" id="id" name="id" placeholder="GAS-003">
                </div>
                <div class="form-group">
                    <label for="fecha">Fecha (YYYY-MM-DD)</label>
                    <input type="date" id="fecha" name="fecha" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label for="nombreUsuario">Nombre de Usuario</label>
                    <input type="text" id="nombreUsuario" name="nombreUsuario" list="listaUsuarios" required placeholder="Ej: Rosary Carmona">
                    <datalist id="listaUsuarios">
                        <?php foreach ($usuariosDisponibles as $usr): ?>
                            <option value="<?= htmlspecialchars($usr->name) ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div class="form-group">
                    <label for="lugar">Lugar o Establecimiento</label>
                    <input type="text" id="lugar" name="lugar" required minlength="2" maxlength="150">
                </div>
                <div class="form-group">
                    <label for="valorTotalSinIVA">Valor Total Sin IVA</label>
                    <input type="number" step="0.01" min="0" id="valorTotalSinIVA" name="valorTotalSinIVA" required>
                </div>
                <div class="form-group">
                    <label for="ivaTotal">IVA Total</label>
                    <input type="number" step="0.01" min="0" id="ivaTotal" name="ivaTotal" required>
                </div>
                <div class="form-group">
                    <label for="valorTotalConIVA">Valor Total Con IVA (Invariante SinIVA + IVA)</label>
                    <input type="number" step="0.01" min="0" id="valorTotalConIVA" name="valorTotalConIVA" required>
                </div>
            </div>
            <div class="form-group">
                <label for="descripcion">Descripcion Detallada del Gasto</label>
                <textarea id="descripcion" name="descripcion" rows="3" required minlength="3" maxlength="500"></textarea>
            </div>
            <div class="actions">
                <button type="submit" class="btn">Guardar Gasto</button>
                <a class="btn btn-secondary" href="?action=gastos.list">Cancelar</a>
            </div>
        </form>
    </section>
</main>
</body>
</html>
