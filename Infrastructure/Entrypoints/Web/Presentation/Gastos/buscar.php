<?php
declare(strict_types=1);

$q = trim((string) ($_GET['q'] ?? ''));
$usuario = trim((string) ($_GET['usuario'] ?? ''));
$results = ($q !== '' || $usuario !== '')
    ? $gastoController->list(array('q' => $q, 'usuario' => $usuario))
    : array();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Buscar Gastos — CQRS Query</title>
    <link rel="stylesheet" href="?asset=css">
</head>
<body>
<header class="topbar">
    <h1>Consulta CQRS de Gastos</h1>
    <nav>
        <a href="?action=gastos.list">Listado de Gastos</a>
        <a href="?action=users.list">Usuarios</a>
    </nav>
</header>
<main class="container">
    <section class="card">
        <form method="get" action="">
            <input type="hidden" name="action" value="gastos.search">
            <div class="form-grid">
                <div class="form-group">
                    <label for="q">Palabra clave (ID, lugar, descripcion o fecha)</label>
                    <input type="text" id="q" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Ej: Cartagena, GAS-001, 2026-09">
                </div>
                <div class="form-group">
                    <label for="usuario">Filtrar por Nombre de Usuario exacto</label>
                    <input type="text" id="usuario" name="usuario" value="<?= htmlspecialchars($usuario) ?>" placeholder="Ej: Rosary Carmona">
                </div>
            </div>
            <button type="submit" class="btn">Consultar Gastos</button>
        </form>
        <?php if ($q !== '' || $usuario !== ''): ?>
            <h3 style="margin: 1.25rem 0 0.75rem;">Coincidencias encontradas: <?= count($results) ?></h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Lugar</th>
                        <th>Sin IVA</th>
                        <th>IVA</th>
                        <th>Con IVA</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($results as $g): ?>
                    <tr>
                        <td><?= htmlspecialchars($g->id) ?></td>
                        <td><?= htmlspecialchars($g->fecha) ?></td>
                        <td><?= htmlspecialchars($g->nombreUsuario) ?></td>
                        <td><?= htmlspecialchars($g->lugar) ?></td>
                        <td>$<?= number_format($g->valorTotalSinIVA, 2) ?></td>
                        <td>$<?= number_format($g->ivaTotal, 2) ?></td>
                        <td>$<?= number_format($g->valorTotalConIVA, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
