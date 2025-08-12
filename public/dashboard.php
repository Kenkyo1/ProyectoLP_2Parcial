<?php
require '../config/auth.php';
require_login();
require '../config/conexion.php';

$usuario_id = $_SESSION['usuario_id'];
$usuario_nombre = $_SESSION['usuario_nombre'];
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Dashboard - Denuncias</title>
</head>

<body>
    <h1>Bienvenido, <?php echo htmlspecialchars($usuario_nombre); ?></h1>
    <p><a href="logout.php">Cerrar sesión</a></p>

    <h2>Crear nueva denuncia</h2>
    <form id="formDenuncia" action="registrar_denuncia.php" method="POST" enctype="multipart/form-data">
        <label>Tipo<br>
            <select name="tipo" required>
                <option value="">Seleccione...</option>
                <option value="Incendio">Incendio</option>
                <option value="Mineria Ilegal">Minería ilegal</option>
                <option value="Contaminacion">Contaminación</option>
            </select>
        </label><br><br>
        <label>Descripción<br><textarea id="descripcion"
        name="descripcion" 
        placeholder="Ejemplo: Reporto un incendio en el bosque cercano al río, con mucho humo visible desde la carretera."
        required></textarea></label><br><br>
        <label>Ubicación<br><input type="text" name="ubicacion" required></label><br><br>
        <label>Imagen (opcional)<br><input type="file" name="imagen" accept="image/*"></label><br><br>
        <button type="submit">Registrar denuncia</button>
    </form>

    <script>
     document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('formDenuncia');
            form.addEventListener('submit', function (e) {
                const descripcion = document.getElementById('descripcion').value.trim();
                const regex = /^[A-Za-z0-9\s.,;:¡!¿?áéíóúÁÉÍÓÚñÑ()\-]{10,}$/;

                if (!regex.test(descripcion)) {
                    alert('La descripción debe tener al menos 10 caracteres y solo puede contener letras, números y signos básicos.');
                    e.preventDefault(); // Detener envío
                }
            });
        });
    </script>

    <h2>Mis denuncias</h2>
    <?php
    $stmt = $conn->prepare("SELECT * FROM denuncias WHERE usuario_id = ? ORDER BY fecha DESC");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        echo "<p>No tienes denuncias registradas.</p>";
    } else {
        echo "<ul>";
        while ($row = $res->fetch_assoc()) {
            echo "<li>";
            echo "<strong>" . htmlspecialchars($row['tipo']) . "</strong> - " . htmlspecialchars($row['descripcion']);
            if ($row['imagen']) {
                echo "<br><img src='uploads/" . htmlspecialchars($row['imagen']) . "' width='180'>";
            }
            echo "<br><small>" . htmlspecialchars($row['ubicacion']) . " - " . $row['fecha'] . "</small><br>";
            echo "<a href='editar_denuncia.php?id=" . $row['id'] . "'>Editar</a> | ";
            echo "<a href='eliminar_denuncia.php?id=" . $row['id'] . "' onclick='return confirm(\"¿Eliminar esta denuncia?\")'>Eliminar</a>";
            echo "</li><br>";
        }
        echo "</ul>";
    }
    ?>

    <h2>Todas las denuncias (públicas)</h2>
    <?php
    $res2 = $conn->query("SELECT d.*, u.nombre as autor FROM denuncias d JOIN usuarios u ON d.usuario_id = u.id ORDER BY d.fecha DESC");
    if ($res2->num_rows === 0) {
        echo "<p>No hay denuncias registradas.</p>";
    } else {
        echo "<ul>";
        while ($r = $res2->fetch_assoc()) {
            echo "<li><strong>" . htmlspecialchars($r['tipo']) . "</strong> por " . htmlspecialchars($r['autor']) . " - " . htmlspecialchars($r['descripcion']);
            if ($r['imagen']) {
                echo "<br><img src='uploads/" . htmlspecialchars($r['imagen']) . "' width='180'>";
            }
            echo "<br><small>" . htmlspecialchars($r['ubicacion']) . " - " . $r['fecha'] . "</small></li><br>";
        }
        echo "</ul>";
    }
    ?>
</body>

</html>