<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    // Configuración LDAP FreeIPA
    $ldap_host = "ldaps://192.168.222.2";
    $ldap_port = 636;
    $ldap_base = "cn=users,cn=accounts,dc=martinrebo,dc=mro";

    $ldapconn = ldap_connect($ldap_host, $ldap_port);
    if ($ldapconn) {
        ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);

        // Buscar DN del usuario
        $filtro = "(uid=$usuario)";
        $search = @ldap_search($ldapconn, $ldap_base, $filtro, ["dn"]);
        $info = ldap_get_entries($ldapconn, $search);

        if ($info["count"] == 1) {
            $user_dn = $info[0]["dn"];
            // Autenticación con contraseña
            if (@ldap_bind($ldapconn, $user_dn, $password)) {
                // Comprobar pertenencia al grupo webadmins
                $filtroGrupo = "(&(objectClass=posixGroup)(cn=webadmins)(memberUid=$usuario))";
                $searchGrupo = @ldap_search($ldapconn, "cn=groups,cn=accounts,dc=martinrebo,dc=mro", $filtroGrupo, ["cn"]);
                $infoGrupo = ldap_get_entries($ldapconn, $searchGrupo);

                if ($infoGrupo["count"] > 0) {
                    $_SESSION['admin'] = true;
                    $_SESSION['usuario'] = $usuario;
                    ldap_close($ldapconn);
                    header("Location: admin_panel.php");
                    exit;
                } else {
                    $error = "No tienes permisos de administrador.";
                }
            } else {
                $error = "Usuario o contraseña incorrectos.";
            }
        } else {
            $error = "Usuario no encontrado en el dominio.";
        }
        ldap_close($ldapconn);
    } else {
        $error = "No se pudo conectar al servidor LDAP.";
    }

    // Redirige de vuelta al login con el error
    header("Location: login.php?error=" . urlencode($error));
    exit;
} else {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">
    <h1 class="mb-4">Acceso Administrador</h1>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" class="mb-3" autocomplete="off">
        <div class="mb-3">
            <label for="usuario" class="form-label">Usuario</label>
            <input type="text" name="usuario" id="usuario" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Entrar</button>
    </form>
</body>
</html>