<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.html");
    exit;
}
$conexion = new mysqli("192.168.222.4", "martin", "Martin27", "soportetecnico");
if ($conexion->connect_error) die("Error de conexión: " . $conexion->connect_error);

$id = $_GET['id'] ?? null;
if (!$id) die("ID no válido.");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $tipoCaso = $_POST['tipoCaso'];
    $descripcion = $_POST['descripcion'];
    $resuelto = isset($_POST['resuelto']) ? 1 : 0;
    $stmt = $conexion->prepare("UPDATE tickets SET nombre=?, correo=?, tipoCaso=?, descripcion=?, resuelto=? WHERE id=?");
    $stmt->bind_param("ssssii", $nombre, $correo, $tipoCaso, $descripcion, $resuelto, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_panel.php");
    exit;
}

$stmt = $conexion->prepare("SELECT * FROM tickets WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$ticket = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Ticket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">
    <h1>Editar Ticket</h1>
    <form method="post">
        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($ticket['nombre']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Correo</label>
            <input type="email" name="correo" class="form-control" value="<?= htmlspecialchars($ticket['correo']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Tipo de caso</label>
            <input type="text" name="tipoCaso" class="form-control" value="<?= htmlspecialchars($ticket['tipoCaso']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control" required><?= htmlspecialchars($ticket['descripcion']) ?></textarea>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" name="resuelto" class="form-check-input" id="resuelto" value="1" <?= $ticket['resuelto'] ? 'checked' : '' ?>>
            <label class="form-check-label" for="resuelto">Resuelto</label>
        </div>
        <button type="submit" class="btn btn-success">Guardar cambios</button>
        <a href="admin_panel.php" class="btn btn-secondary">Cancelar</a>
    </form>
</body>
</html>
<?php $conexion->close(); ?>