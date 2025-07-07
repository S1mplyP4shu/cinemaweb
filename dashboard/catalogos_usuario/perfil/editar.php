<?php
include('../../../includes/session.php');
verificarRol('cliente');
include('../../../includes/db.php');

$nombre_actual = $_SESSION['nombre'];
$email = isset($_SESSION['correo']) ? $_SESSION['correo'] : 'No disponible';
$mensaje = "";
$tipo_mensaje = "";

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevo_nombre = trim($_POST['nombre']);
    $nueva_contraseña = $_POST['password'];
    $id_usuario = $_SESSION['id'];
    
    if (!empty($nuevo_nombre)) {
        $stmt = $conn->prepare("UPDATE usuarios SET nombre = ? WHERE id = ?");
        $stmt->bind_param("si", $nuevo_nombre, $id_usuario);
        if ($stmt->execute()) {
            $_SESSION['nombre'] = $nuevo_nombre;
            $mensaje .= "Nombre actualizado correctamente. ";
            $tipo_mensaje = "exito";
        }
        $stmt->close();
    }
    
    if (!empty($nueva_contraseña)) {
        if (strlen($nueva_contraseña) >= 6) {
            $hash = password_hash($nueva_contraseña, PASSWORD_DEFAULT);
            // Cambia 'password' por 'contraseña'
            $stmt = $conn->prepare("UPDATE usuarios SET contraseña = ? WHERE id = ?");
            $stmt->bind_param("si", $hash, $id_usuario);
            if ($stmt->execute()) {
                $mensaje .= "Contraseña actualizada correctamente.";
                $tipo_mensaje = "exito";
            }
            $stmt->close();
        } else {
            $mensaje = "La contraseña debe tener al menos 6 caracteres.";
            $tipo_mensaje = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil | CinemaWeb</title>
    <link rel="stylesheet" href="styles/styles_editar.css">
</head>
<body>
<header>
    <nav>
        <div class="logo">Cinema<span class="gold-accent">Web</span></div>
        <div class="nav-links">
            <a href="../../index-cliente.php">Inicio</a>
            <a href="perfil.php" class="active">Perfil</a>
        </div>
        <div class="user-menu">
            <div class="user-info">¡Hola, <?= htmlspecialchars($_SESSION['nombre']) ?>!</div>
            <div class="dropdown">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['nombre'], 0, 1)) ?></div>
                <div class="dropdown-content">
                    <a href="perfil.php">Mi Perfil</a>
                    <a href="../../../logout.php">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </nav>
</header>

<main>
    <section class="form-container">
        <h2>✏️ Editar Perfil</h2>
        
        <?php if ($mensaje): ?>
            <div class="mensaje <?= $tipo_mensaje === 'error' ? 'mensaje-error' : '' ?>">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="form-perfil">
            <div class="form-group">
                <label for="nombre">Nuevo Nombre:</label>
                <input type="text" 
                        id="nombre" 
                        name="nombre" 
                        value="<?= htmlspecialchars($nombre_actual) ?>" 
                        required 
                        minlength="2"
                        maxlength="50">
            </div>
            
            <div class="form-group">
                <label for="password">Nueva Contraseña (opcional):</label>
                <input type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Deja en blanco si no deseas cambiarla"
                        minlength="6">
            </div>
            
            <button type="submit" class="btn-primary" id="btn-guardar">
                Guardar Cambios
            </button>
        </form>
        
        <div style="margin-top: 2rem; text-align: center;">
            <a href="perfil.php" style="color: #448aff; text-decoration: none; font-weight: bold;">
                ← Volver al perfil
            </a>
        </div>
    </section>
</main>

<footer>
    <p>&copy; 2025 <span class="gold-accent">CinemaWeb</span>. Todos los derechos reservados.</p>
</footer>

<script>
    // JavaScript básico para mejorar UX
    const form = document.getElementById('form-perfil');
    const btnGuardar = document.getElementById('btn-guardar');
    const inputNombre = document.getElementById('nombre');
    const inputPassword = document.getElementById('password');

    // Validación en tiempo real
    inputNombre.addEventListener('input', function() {
        if (this.value.length < 2) {
            this.style.borderColor = '#ff6b6b';
        } else {
            this.style.borderColor = '#39b050';
        }
    });

    inputPassword.addEventListener('input', function() {
        if (this.value.length > 0 && this.value.length < 6) {
            this.style.borderColor = '#ff6b6b';
        } else if (this.value.length >= 6) {
            this.style.borderColor = '#39b050';
        } else {
            this.style.borderColor = '#35506b';
        }
    });

    // Confirmación antes de enviar
    form.addEventListener('submit', function(e) {
        const nombre = inputNombre.value.trim();
        const password = inputPassword.value;
        
        if (nombre.length < 2) {
            e.preventDefault();
            alert('❌ El nombre debe tener al menos 2 caracteres');
            return;
        }
        
        if (password.length > 0 && password.length < 6) {
            e.preventDefault();
            alert('❌ La contraseña debe tener al menos 6 caracteres');
            return;
        }
        
        // Mostrar indicador de carga
        btnGuardar.innerHTML = '⏳ Guardando...';
        btnGuardar.disabled = true;
        
        // Confirmación
        const cambios = [];
        if (nombre !== '<?= htmlspecialchars($nombre_actual) ?>') {
            cambios.push('nombre');
        }
        if (password.length > 0) {
            cambios.push('contraseña');
        }
        
        if (cambios.length > 0) {
            const confirmacion = confirm(`¿Confirmar cambios en: ${cambios.join(' y ')}?`);
            if (!confirmacion) {
                e.preventDefault();
                btnGuardar.innerHTML = '💾 Guardar Cambios';
                btnGuardar.disabled = false;
            }
        }
    });

    // Auto-ocultar mensajes después de 5 segundos
    const mensaje = document.querySelector('.mensaje');
    if (mensaje) {
        setTimeout(() => {
            mensaje.style.opacity = '0';
            setTimeout(() => {
                mensaje.style.display = 'none';
            }, 300);
        }, 5000);
    }

    console.log('✅ Sistema de edición de perfil cargado');
</script>

<style>
    .mensaje-error {
        background-color: #ff6b6b !important;
        color: white;
    }
    
    .mensaje {
        transition: opacity 0.3s ease;
    }
</style>
</body>
</html>
