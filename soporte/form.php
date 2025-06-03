<?php
// Inicializar variables y errores
$errores = [];
$nombre = $correo = $tipoCaso = $descripcion = "";
$archivoGuardado = false;
$archivoDestino = null; // Inicializa para evitar undefined variable

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validar nombre
    $nombre = trim($_POST["nombre"] ?? "");
    if (empty($nombre)) {
        $errores["nombre"] = "El nombre es obligatorio.";
    }

    // Validar correo
    $correo = strtolower(trim($_POST["correo"] ?? ""));
    if (empty($correo)) {
        $errores["correo"] = "El correo es obligatorio.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores["correo"] = "Formato de correo inválido.";
    } else {
        // Validar que el correo existe en FreeIPA (LDAPs)
        $ldap_host = "ldaps://192.168.222.2";
        $ldap_port = 636;
        $ldapconn = ldap_connect($ldap_host, $ldap_port);
        if ($ldapconn) {
            ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
            $ldap_base = "cn=users,cn=accounts,dc=martinrebo,dc=mro";
            $filtro = "(mail=$correo)";
            $search = @ldap_search($ldapconn, $ldap_base, $filtro, ["mail"]);
            if ($search) {
                $info = ldap_get_entries($ldapconn, $search);
                if ($info["count"] == 0) {
                    $errores["correo"] = "El correo no existe en FreeIPA.";
                }
            } else {
                $errores["correo"] = "Error al buscar el correo en FreeIPA.";
            }
            ldap_close($ldapconn);
        } else {
            $errores["correo"] = "No se pudo conectar al servidor de usuarios (LDAPS).";
        }
    }

    // Validar tipo de caso
    $tipoCaso = $_POST["tipoCaso"] ?? "";
    if (empty($tipoCaso)) {
        $errores["tipoCaso"] = "Selecciona un tipo de caso.";
    }

    // Validar descripción
    $descripcion = trim($_POST["descripcion"] ?? "");
    if (empty($descripcion)) {
        $errores["descripcion"] = "La descripción es obligatoria.";
    }

    // Procesar archivo si se sube
    if (isset($_FILES["archivo"]) && $_FILES["archivo"]["error"] === UPLOAD_ERR_OK) {
        $tamanoMaximo = 5 * 1024 * 1024; // 5 MB
        $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'txt', 'log', 'pdf'];
        $archivoTmp = $_FILES["archivo"]["tmp_name"];
        $archivoNombre = basename($_FILES["archivo"]["name"]);
        $extension = strtolower(pathinfo($archivoNombre, PATHINFO_EXTENSION));

        if ($_FILES["archivo"]["size"] > $tamanoMaximo) {
            $errores["archivo"] = "El archivo supera el tamaño máximo permitido (5MB).";
        } elseif (!in_array($extension, $extensiones_permitidas)) {
            $errores["archivo"] = "Tipo de archivo no permitido. Solo imágenes, .txt, .log y .pdf.";
        } else {
            // Asegura que la carpeta uploads existe
            if (!is_dir("uploads")) {
                mkdir("uploads", 0777, true);
            }
            $archivoDestino = "uploads/" . uniqid() . "_" . $archivoNombre;
            if (move_uploaded_file($archivoTmp, $archivoDestino)) {
                $archivoGuardado = true;
            } else {
                $errores["archivo"] = "No se pudo guardar el archivo.";
            }
        }
    }

    // Si no hay errores, guardar en base de datos
    if (empty($errores)) {
        $conexion = new mysqli("192.168.222.4", "martin", "Martin27", "soportetecnico");
        if ($conexion->connect_error) {
            die("Error de conexión: " . $conexion->connect_error);
        }

        $stmt = $conexion->prepare("INSERT INTO tickets (nombre, correo, tipoCaso, descripcion, archivo) VALUES (?, ?, ?, ?, ?)");
        $archivoNombreBD = $archivoGuardado ? $archivoDestino : null;
        $stmt->bind_param("sssss", $nombre, $correo, $tipoCaso, $descripcion, $archivoNombreBD);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success text-center'>Ticket enviado correctamente.</div>";
            // Resetear valores tras éxito
            $nombre = $correo = $tipoCaso = $descripcion = "";
        } else {
            echo "<div class='alert alert-danger text-center'>Error al guardar en la base de datos.</div>";
        }
        $stmt->close();
        $conexion->close();
    }
}
?>

<!-- HTML -->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="estiloForm.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="icon" type="img/png" href="imgr/apoyo2.webp">
    <title>Formulario Soporte Técnico</title>
</head>
<body>
    <header>
        <a href="index.html"><img src="imgr/cruzTech2.webp" alt="Logo web home" id="logo"></a>
        <nav>
            <a href="index.html" id="head1">Inicio</a>
            <a href="index.html"><img src="imgr/casa.webp" alt="Icono casa" id="head11"></a>
            <a href="index.html#preguntas" id="head2">Preguntas Frecuentes</a>
            <a href="index.html#preguntas"><img src="imgr/informacion.webp" alt="Icono pregunta" id="head22"></a>
            <a href="index.html#contacto" id="head3">Contacto</a>
            <a href="index.html#contacto"><img src="imgr/ubicacion.webp" alt="Icono contacto" id="head33"></a>
        </nav>
        <a href="login.html"><img src="imgr/ajustes.webp" alt="Logo web home" id="login"></a>
    </header>

    <div class="container mt-4">
        <div id="main" class="text-center mb-1">
            <img src="imgr/SopTec.webp" alt="Logo CruzTech" id="logoForm">
            <h1 id="h11">Soporte</h1>
            <h1 id="h12">Técnico</h1>
            <h2 id="h21">Rellena este ticket</h2>
        </div>

        <form action="form.php" method="POST" enctype="multipart/form-data" class="fs-5">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre completo</label>
                <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($nombre) ?>" required>
                <?php if (!empty($errores["nombre"])): ?>
                    <div class="text-danger"><?= $errores["nombre"] ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="correo" class="form-label">Correo electrónico</label>
                <input type="email" class="form-control" id="correo" name="correo" value="<?= htmlspecialchars($correo) ?>" required>
                <?php if (!empty($errores["correo"])): ?>
                    <div class="text-danger"><?= $errores["correo"] ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="tipoCaso" class="form-label">Tipo de caso</label>
                <select class="form-select" id="tipoCaso" name="tipoCaso" required>
                    <option value="" disabled <?= $tipoCaso === "" ? "selected" : "" ?>>Selecciona una opción</option>
                    <option value="incidencia" <?= $tipoCaso === "incidencia" ? "selected" : "" ?>>Incidencia</option>
                    <option value="peticion" <?= $tipoCaso === "peticion" ? "selected" : "" ?>>Petición</option>
                    <option value="otros" <?= $tipoCaso === "otros" ? "selected" : "" ?>>Otros</option>
                </select>
                <?php if (!empty($errores["tipoCaso"])): ?>
                    <div class="text-danger"><?= $errores["tipoCaso"] ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción del problema</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="5" required><?= htmlspecialchars($descripcion) ?></textarea>
                <?php if (!empty($errores["descripcion"])): ?>
                    <div class="text-danger"><?= $errores["descripcion"] ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="archivo" class="form-label">Adjuntar archivo (opcional)</label>
                <input type="file" class="form-control" id="archivo" name="archivo">
                <?php if (!empty($errores["archivo"])): ?>
                    <div class="text-danger"><?= $errores["archivo"] ?></div>
                <?php endif; ?>
            </div>

            <div class="text-center mb-4">
                <button type="submit" class="btn btn-primary">Enviar</button>
                <button type="reset" class="btn">Borrar</button>
            </div>
        </form>
    </div>
</body>
</html>
