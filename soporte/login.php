<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="estiloLogin.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <link rel="icon" type="img/png" href="imgr/apoyo2.webp">
        <title>Inicio de sesión</title>
    </head>
    <body>
        <main class="container d-flex flex-column justify-content-center align-items-center" style="max-height: 95vh;">
            <div class="text-center">
                <a href="index.html"><img src="imgr/cruzTech2.webp" alt="Icono soporte" id="img1" style="max-width: 33vh; max-height: 45vw;"></a>
                <h1 class="text-center mb-3 fs-1">Soporte Técnico</h1>
            </div>
            <div class="card p-5 shadow text-center">
                <h2 class="text-center mb-4 fs-3">Acceso Administrador</h2>
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger text-center"><?= htmlspecialchars($_GET['error']) ?></div>
                <?php endif; ?>
                <form method="post" action="Adminlogin.php" class="mb-3" autocomplete="off">    
                    <div class="mb-4">
                        <label for="usuario" class="form-label fs-5">Usuario</label>
                        <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Escribe tu usuario" required>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label fs-5">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Escribe tu contraseña" required>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary w-100">Iniciar Sesión</button>
                    </div>
                </form>
            </div>
        </main>
    </body>
</html>