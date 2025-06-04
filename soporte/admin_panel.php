<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

$conexion = new mysqli("192.168.222.4", "martin", "Martin27", "soportetecnico");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Búsqueda
$busqueda = $_GET['buscar'] ?? '';
$sql = "SELECT * FROM tickets";
if ($busqueda) {
    $sql .= " WHERE nombre LIKE ? OR usuario LIKE ? OR tipoCaso LIKE ? OR descripcion LIKE ?";
    $stmt = $conexion->prepare($sql);
    $like = "%$busqueda%";
    $stmt->bind_param("ssss", $like, $like, $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conexion->query($sql);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">
    <h1 class="mb-4">Tickets registrados</h1>
    <form method="get" class="mb-3">
        <input type="text" name="buscar" placeholder="Buscar..." value="<?= htmlspecialchars($busqueda) ?>" class="form-control" style="max-width:300px;display:inline-block;">
        <button type="submit" class="btn btn-primary">Buscar</button>
        <a href="admin_panel.php" class="btn btn-secondary">Limpiar</a>
    </form>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Usuario</th>
                <th>Tipo de caso</th>
                <th>Descripción</th>
                <th>Archivo</th>
                <th>Resuelto</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['nombre']) ?></td>
                <td><?= htmlspecialchars($row['usuario']) ?></td>
                <td><?= htmlspecialchars($row['tipoCaso']) ?></td>
                <td><?= htmlspecialchars($row['descripcion']) ?></td>
                <td>
                    <?php if ($row['archivo']): ?>
                        <a href="<?= htmlspecialchars($row['archivo']) ?>" target="_blank" type="text/plain">Ver archivo</a>
                    <?php endif; ?>
                </td>
                <td>
                    <?= $row['resuelto'] ? 'Sí' : 'No' ?>
                </td>
                <td>
                    <a href="editar_ticket.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <a href="logout.php" class="btn btn-danger">Cerrar sesión</a>
</body>
</html>
<?php
if (isset($stmt)) $stmt->close();
$conexion->close();
?>