<?php
session_start();

$errores = [];
$form_data = [
    'nombre' => trim($_POST['nombre'] ?? ''),
    'usuario' => trim($_POST['usuario'] ?? ''),
    'tipoCaso' => $_POST['tipoCaso'] ?? '',
    'descripcion' => trim($_POST['descripcion'] ?? '')
];

// Validación básica
if (empty($form_data['nombre'])) {
    $errores['nombre'] = 'El nombre es obligatorio.';
}

if (empty($form_data['usuario'])) {
    $errores['usuario'] = 'El usuario es obligatorio.';
}

if (empty($form_data['tipoCaso'])) {
    $errores['tipoCaso'] = 'Debes seleccionar un tipo de caso.';
}

if (empty($form_data['descripcion'])) {
    $errores['descripcion'] = 'La descripción es obligatoria.';
}

// Procesamiento del archivo
$archivo_nombre = null;
$archivo_datos = null;
$archivo_tipo = null;

if (!empty($_FILES['archivo']['name'])) {
    if ($_FILES['archivo']['error'] === 0) {
        $archivo_nombre = $_FILES['archivo']['name'];
        $archivo_tipo = $_FILES['archivo']['type'];
        $archivo_datos = file_get_contents($_FILES['archivo']['tmp_name']);

        // Limitamos el tipo de archivo a imágenes y .txt
        $permitidos = ['image/jpeg', 'image/png', 'text/plain'];
        if (!in_array($archivo_tipo, $permitidos)) {
            $errores['archivo'] = 'Formato de archivo no permitido (solo JPG, PNG o TXT).';
        }
    } else {
        $errores['archivo'] = 'Error al subir el archivo.';
    }
}

if (!empty($errores)) {
    $_SESSION['errores'] = $errores;
    $_SESSION['form_data'] = $form_data;
    header('Location: form.php');
    exit;
}

// Guardar en la base de datos
$mysqli = new mysqli("192.168.222.4", "usuario", "clave", "basedatos");
if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}

$stmt = $mysqli->prepare("INSERT INTO tickets (nombre, usuario, tipo_caso, descripcion, archivo_nombre, archivo_tipo, archivo_datos) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param(
    "sssssss",
    $form_data['nombre'],
    $form_data['usuario'],
    $form_data['tipoCaso'],
    $form_data['descripcion'],
    $archivo_nombre,
    $archivo_tipo,
    $archivo_datos
);

if ($stmt->execute()) {
    unset($_SESSION['form_data']);
    echo "<script>alert('Formulario enviado correctamente.'); window.location.href='form.php';</script>";
} else {
    echo "Error al guardar en la base de datos.";
}

$stmt->close();
$mysqli->close();
