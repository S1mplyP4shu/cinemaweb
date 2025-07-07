<?php
include('../includes/db.php');

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$q = mb_strtolower($q);

$resultados = [];

$peliculas = $conn->query("SELECT * FROM peliculas WHERE id <> 4");

while ($peli = $peliculas->fetch_assoc()) {
    $titulo = mb_strtolower($peli['titulo']);
    $genero = mb_strtolower($peli['genero']);
    $clasificacion = mb_strtolower($peli['clasificacion'] ?? '');

    $esExacto = ($titulo === $q || $genero === $q || $clasificacion === $q);
    $esParcial = (strpos($titulo, $q) !== false || strpos($genero, $q) !== false || strpos($clasificacion, $q) !== false);

    if ($q === '' || $esExacto || $esParcial) {
        $peli['relevancia'] = $esExacto ? 2 : ($esParcial ? 1 : 0);
        $resultados[] = $peli;
    }
}

// Ordena: exactos primero, luego parciales
usort($resultados, function($a, $b) {
    return $b['relevancia'] <=> $a['relevancia'];
});

foreach ($resultados as $peli) {
    echo '<div class="movie-card">';
    echo '  <div class="movie-poster">';
    echo "    <img src='../images/{$peli['imagen_url']}' alt='" . htmlspecialchars($peli['titulo']) . "' style='width: auto; height: 100%; object-fit: cover;'>";
    echo '  </div>';
    echo '  <div class="movie-info">';
    echo '    <h3 class="movie-title">' . htmlspecialchars($peli['titulo']) . '</h3>';
    echo '    <p class="movie-genre">' . htmlspecialchars($peli['genero']) . ' • ' . htmlspecialchars($peli['duracion_min']) . ' min</p>';
    echo '    <div class="movie-times">';
    $id_peli = (int)$peli['id'];
    $funciones = $conn->query("
    SELECT hora 
    FROM funciones 
    WHERE pelicula_id = $id_peli 
    AND fecha IS NOT NULL 
    AND fecha > '1000-01-01' 
    AND hora != '00:00:00'
    ORDER BY hora ASC
");

    if ($funciones && $funciones->num_rows > 0) {
        while ($f = $funciones->fetch_assoc()) {
            echo '<span class="time-slot">' . htmlspecialchars(substr($f['hora'], 0, 5)) . '</span>';
        }
    } else {
        echo '<span class="time-slot">Próximamente</span>';
    }
    echo '    </div>';
    echo '<a href="catalogos_usuario/detalles_peliculas/detalles_pelicula.php?id=' . $peli['id'] . '" class="btn btn-primary" style="margin-top: 1rem;">Ver detalles</a>';
    echo '  </div>';
    echo '</div>';
}

if (empty($resultados)) {
    echo '<p style="text-align:center;">No hay películas disponibles por el momento.</p>';
}

$conn->close();
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const buscador = document.getElementById('buscador');
    const btnBuscar = document.getElementById('btn-buscar');
    const btnLimpiar = document.getElementById('btn-limpiar');
    const cartelera = document.getElementById('cartelera');
    const resultados = document.getElementById('resultados');

    btnBuscar.addEventListener('click', function(e) {
        e.preventDefault();
        const q = buscador.value.trim();
        if (q === "") return;
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'buscar_peliculas.php?q=' + encodeURIComponent(q), true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                resultados.innerHTML = xhr.responseText;
                resultados.style.display = "";
                cartelera.style.display = "none";
                btnLimpiar.style.display = "";
            }
        };
        xhr.send();
    });

    btnLimpiar.addEventListener('click', function(e) {
        e.preventDefault();
        buscador.value = "";
        resultados.innerHTML = "";
        resultados.style.display = "none";
        cartelera.style.display = "";
        btnLimpiar.style.display = "none";
    });
});
</script>