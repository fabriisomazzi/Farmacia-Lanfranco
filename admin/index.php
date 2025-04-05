<?php
// Autenticación básica
$usuarios = [
    'admin' => password_hash('contraseñaSegura123', PASSWORD_DEFAULT)
];

session_start();

// Verificar login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];
    
    if (isset($usuarios[$usuario]) && password_verify($password, $usuarios[$usuario])) {
        $_SESSION['loggedin'] = true;
        $_SESSION['usuario'] = $usuario;
    } else {
        $error = "Usuario o contraseña incorrectos";
    }
}

// Cerrar sesión
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Procesar guardado de datos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar']) && isset($_SESSION['loggedin'])) {
    $datos = [
        'ofertas' => json_decode($_POST['ofertas'], true),
        'novedades' => json_decode($_POST['novedades'], true)
    ];
    
    file_put_contents('datos.json', json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $exito = "Datos guardados correctamente";
}

// Cargar datos existentes
$datos = file_exists('datos.json') ? json_decode(file_get_contents('datos.json'), true) : ['ofertas' => [], 'novedades' => []];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Farmacia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card-oferta { margin-bottom: 20px; border-left: 4px solid #28a745; }
        .card-novedad { margin-bottom: 20px; border-left: 4px solid #17a2b8; }
        .preview-img { max-height: 100px; }
    </style>
</head>
<body>
    <div class="container py-4">
        <?php if (!isset($_SESSION['loggedin'])): ?>
            <!-- Formulario de Login -->
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0">Acceso Administrador</h4>
                        </div>
                        <div class="card-body">
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger"><?= $error ?></div>
                            <?php endif; ?>
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="usuario" class="form-label">Usuario</label>
                                    <input type="text" class="form-control" id="usuario" name="usuario" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Contraseña</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <button type="submit" name="login" class="btn btn-primary">Ingresar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Panel de Administración -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Panel de Administración</h2>
                <a href="?logout" class="btn btn-danger">Cerrar Sesión</a>
            </div>
            
            <?php if (isset($exito)): ?>
                <div class="alert alert-success"><?= $exito ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">Ofertas</h4>
                    </div>
                    <div class="card-body">
                        <div id="ofertas-container">
                            <?php foreach ($datos['ofertas'] as $index => $oferta): ?>
                                <div class="card card-oferta mb-3">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Imagen (URL)</label>
                                                    <input type="text" class="form-control img-url" 
                                                           value="<?= htmlspecialchars($oferta['imagen'] ?? '') ?>">
                                                    <?php if (!empty($oferta['imagen'])): ?>
                                                        <img src="<?= htmlspecialchars($oferta['imagen']) ?>" 
                                                             class="preview-img mt-2 img-thumbnail">
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="mb-3">
                                                    <label class="form-label">Título</label>
                                                    <input type="text" class="form-control titulo" 
                                                           value="<?= htmlspecialchars($oferta['titulo']) ?>">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Descripción</label>
                                                    <textarea class="form-control descripcion" rows="2"><?= htmlspecialchars($oferta['descripcion']) ?></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Etiqueta (ej: "30% OFF")</label>
                                                    <input type="text" class="form-control etiqueta" 
                                                           value="<?= htmlspecialchars($oferta['etiqueta'] ?? '') ?>">
                                                </div>
                                                <button type="button" class="btn btn-sm btn-danger btn-eliminar">Eliminar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" id="agregar-oferta" class="btn btn-success mt-3">
                            + Agregar Oferta
                        </button>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0">Novedades</h4>
                    </div>
                    <div class="card-body">
                        <div id="novedades-container">
                            <?php foreach ($datos['novedades'] as $index => $novedad): ?>
                                <div class="card card-novedad mb-3">
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">Título</label>
                                            <input type="text" class="form-control titulo" 
                                                   value="<?= htmlspecialchars($novedad['titulo']) ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Descripción</label>
                                            <textarea class="form-control descripcion" rows="2"><?= htmlspecialchars($novedad['descripcion']) ?></textarea>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-danger btn-eliminar">Eliminar</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" id="agregar-novedad" class="btn btn-info mt-3">
                            + Agregar Novedad
                        </button>
                    </div>
                </div>
                
                <input type="hidden" id="ofertas-json" name="ofertas">
                <input type="hidden" id="novedades-json" name="novedades">
                <button type="submit" name="guardar" class="btn btn-primary btn-lg">Guardar Cambios</button>
            </form>
            
            <script>
                // Funcionalidad del panel
                document.getElementById('agregar-oferta').addEventListener('click', function() {
                    const container = document.getElementById('ofertas-container');
                    const newCard = document.createElement('div');
                    newCard.className = 'card card-oferta mb-3';
                    newCard.innerHTML = `
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Imagen (URL)</label>
                                        <input type="text" class="form-control img-url">
                                        <small class="text-muted">Ej: img/ofertas/nombre.jpg</small>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label">Título</label>
                                        <input type="text" class="form-control titulo">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Descripción</label>
                                        <textarea class="form-control descripcion" rows="2"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Etiqueta (ej: "30% OFF")</label>
                                        <input type="text" class="form-control etiqueta">
                                    </div>
                                    <button type="button" class="btn btn-sm btn-danger btn-eliminar">Eliminar</button>
                                </div>
                            </div>
                        </div>
                    `;
                    container.appendChild(newCard);
                });
                
                document.getElementById('agregar-novedad').addEventListener('click', function() {
                    const container = document.getElementById('novedades-container');
                    const newCard = document.createElement('div');
                    newCard.className = 'card card-novedad mb-3';
                    newCard.innerHTML = `
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Título</label>
                                <input type="text" class="form-control titulo">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea class="form-control descripcion" rows="2"></textarea>
                            </div>
                            <button type="button" class="btn btn-sm btn-danger btn-eliminar">Eliminar</button>
                        </div>
                    `;
                    container.appendChild(newCard);
                });
                
                // Eliminar elementos
                document.addEventListener('click', function(e) {
                    if (e.target.classList.contains('btn-eliminar')) {
                        e.target.closest('.card').remove();
                    }
                });
                
                // Actualizar vista previa de imagen
                document.addEventListener('input', function(e) {
                    if (e.target.classList.contains('img-url')) {
                        const imgContainer = e.target.nextElementSibling;
                        if (e.target.value) {
                            if (!imgContainer || !imgContainer.classList.contains('preview-img')) {
                                const img = document.createElement('img');
                                img.src = e.target.value;
                                img.className = 'preview-img mt-2 img-thumbnail';
                                e.target.parentNode.appendChild(img);
                            } else {
                                imgContainer.src = e.target.value;
                            }
                        } else if (imgContainer && imgContainer.tagName === 'IMG') {
                            imgContainer.remove();
                        }
                    }
                });
                
                // Preparar datos para enviar
                document.querySelector('form').addEventListener('submit', function() {
                    const ofertas = [];
                    document.querySelectorAll('#ofertas-container .card-oferta').forEach(card => {
                        ofertas.push({
                            titulo: card.querySelector('.titulo').value,
                            descripcion: card.querySelector('.descripcion').value,
                            imagen: card.querySelector('.img-url')?.value || '',
                            etiqueta: card.querySelector('.etiqueta')?.value || ''
                        });
                    });
                    
                    const novedades = [];
                    document.querySelectorAll('#novedades-container .card-novedad').forEach(card => {
                        novedades.push({
                            titulo: card.querySelector('.titulo').value,
                            descripcion: card.querySelector('.descripcion').value
                        });
                    });
                    
                    document.getElementById('ofertas-json').value = JSON.stringify(ofertas);
                    document.getElementById('novedades-json').value = JSON.stringify(novedades);
                });
            </script>
        <?php endif; ?>
    </div>
</body>
</html>