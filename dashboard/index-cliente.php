<?php
include('../includes/session.php');
verificarRol('cliente');
include('../includes/db.php');



// Obtener el nombre e inicial
$nombre = $_SESSION['nombre'];
$inicial = strtoupper(substr($nombre, 0, 1));
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CinemaWeb - Mi Cartelera</title>
    <link rel="stylesheet" href="styles/cliente_styles.css">
</head>

<body>
    <header>
        <nav>
            <div class="logo">Cinema<span class="gold-accent">Web</span></div>
            <div class="nav-links">
                <a href="#cartelera">Cartelera</a>
                <a href="catalogos_usuario/reservas/reservas.html">Reservar</a>
            </div>
            <div class="user-menu">
                <div class="user-info">
                    <span>¡Hola, <?= htmlspecialchars($nombre) ?>!</span>
                </div>
                <div class="dropdown">
                    <div class="user-avatar"><?= $inicial ?></div>
                    <div class="dropdown-content">
                        <a href="catalogos_usuario/perfil/perfil.php">Mi Perfil</a>
                        <a href="logout.php">Cerrar Sesión</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <section class="welcome-section">
            <h1>¡Bienvenido de vuelta, <span class="gold-accent"><?= htmlspecialchars($nombre) ?></span>!</h1>
            <p>Disfruta de las mejores películas en la mejor calidad</p>
        </section>

        <section id="cartelera">
            <h2 class="section-title">Cartelera <span class="gold-accent">Actual</span></h2>
            <div style="margin-bottom: 2rem; text-align: center;">
                <input type="text" id="buscador" placeholder="Buscar por título, género o clasificación...">
                <button id="btn-limpiar" class="btn logout-btn" style="display:none;">Limpiar</button>
            </div>
            <div class="movies-grid" id="resultados">
                <!-- Aquí AJAX pondrá los resultados -->
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 <span class="gold-accent">CinemaWeb</span>. Todos los derechos reservados.</p>
        <p>Dirección: Av. Principal 123, Ciudad • Teléfono: (555) 123-4567</p>
    </footer>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    const buscador = document.getElementById('buscador');
    const resultados = document.getElementById('resultados');
    const btnLimpiar = document.getElementById('btn-limpiar');

    function buscarPeliculas(q = '') {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'buscar_peliculas.php?q=' + encodeURIComponent(q), true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                resultados.innerHTML = xhr.responseText;
            }
        };
        xhr.send();
    }

    // Cargar todo el catálogo al inicio
    buscarPeliculas();

    // Filtrar automáticamente al escribir
    buscador.addEventListener('input', function() {
        buscarPeliculas(this.value);
        btnLimpiar.style.display = this.value.trim() !== '' ? 'inline-block' : 'none';
    });

    btnLimpiar.addEventListener('click', function() {
        buscador.value = '';
        buscarPeliculas('');
        btnLimpiar.style.display = 'none';
    });
});
</script>
</body>

</html>
<?php
// ... después de insertar la reserva ...
//$reserva_id = $conn->insert_id;
//echo json_encode([
    //'status' => 'success',
    //'message' => 'Reserva realizada correctamente.',
    //'reserva_id' => $reserva_id
//]);
exit;
?>
<script>
    // Suponiendo que el código para manejar la reserva está aquí
    // ... código de reserva ...
    //    if (data.status === 'success') {
    //        window.location.href = '../reservas/pago.php?reserva_id=' + encodeURIComponent(data.reserva_id);
    //  }
   // })
</script>
