<?php
// Parámetros de conexión
$host = 'localhost';
$dbname = 'cine';
$username = 'root';
$password = 'root';

try {
    // Crear una nueva instancia de PDO para una conexión orientada a objetos
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Configurar el modo de error para lanzar excepciones
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Si ocurre un error, mostrar el mensaje y detener la ejecución
    die("Error de conexión: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CinemaWeb - Cartelera</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <header>
        <nav>
            <img style="width: 70px; height: auto; border-radius: 20%;" src="images/CinemaWeb.png" alt="Logo">
            <div class="logo">Cinema<span class="yellow">Web</span></div>
            <div class="nav-links">
                <a href="#cartelera">Cartelera</a>
                <a href="#promociones">Promociones</a>
                <a href="#contacto">Contacto</a>
            </div>
            <div class="auth-buttons">
                <a href="login.php" class="btn btn-secondary">Iniciar Sesión</a>
                <a href="registro.php" class="btn btn-primary">Registrarse</a>
            </div>
        </nav>
    </header>

    <main>
        <section class="hero">
            <h1>Bienvenido a CinemaWeb</h1>
            <p>Disfruta de las mejores películas en la mejor calidad</p>
        </section>

        <section id="cartelera">
            <h2 style="text-align: center; margin-bottom: 2rem; font-size: 2.2rem; color: #FFC857;">Cartelera Actual</h2>
            <div class="movies-grid">
                <?php
                // Consulta segura con PDO
                $query = "SELECT * FROM peliculas";
                $stmt = $pdo->prepare($query);
                $stmt->execute();
                $peliculas = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($peliculas) > 0) {
                    foreach ($peliculas as $pelicula) {
                        $id = (int)$pelicula['id'];
                        $titulo = htmlspecialchars($pelicula['titulo'] ?? 'Sin título');
                        $genero = htmlspecialchars($pelicula['genero'] ?? 'Sin género');
                        $duracion = isset($pelicula['duracion_min']) && $pelicula['duracion_min'] !== null ? htmlspecialchars($pelicula['duracion_min']) . ' min' : 'N/A';
                        $imagen = isset($pelicula['imagen_url']) && $pelicula['imagen_url'] !== '' ? htmlspecialchars($pelicula['imagen_url']) : 'placeholder.png';

                        echo '<div class="movie-card">';
                        echo '  <div class="movie-poster">';
                        echo '    <img src="images/' . $imagen . '" alt="' . $titulo . '" style="width: auto; height: 100%; object-fit: cover;">';
                        echo '  </div>';
                        echo '  <div class="movie-info">';
                        echo '    <h3 class="movie-title">' . $titulo . '</h3>';
                        echo '    <p class="movie-genre">' . $genero . ' • ' . $duracion . '</p>';

                        // Funciones seguras con PDO
                        $funciones_query = "SELECT hora FROM funciones WHERE pelicula_id = ? ORDER BY hora ASC";
                        $func_stmt = $pdo->prepare($funciones_query);
                        $func_stmt->execute([$id]);
                        $funciones = $func_stmt->fetchAll(PDO::FETCH_ASSOC);

                        echo '<div class="movie-times">';
                        foreach ($funciones as $funcion) {
                            $hora = htmlspecialchars(substr($funcion['hora'], 0, 5));
                            echo '<span class="time-slot">' . $hora . '</span>';
                        }
                        echo '</div>'; // cierre movie-times
                        echo '  </div>'; // cierre movie-info
                        echo '</div>'; // cierre movie-card
                    }
                } else {
                    echo "<p style='text-align: center;'>No hay películas en cartelera por el momento.</p>";
                }
                ?>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; <?= date('Y') ?> CinemaWeb. Todos los derechos reservados.</p>
        <p>Dirección: Av. Principal 123, Ciudad • Teléfono: (555) 123-4567</p>
    </footer>
</body>
</html>