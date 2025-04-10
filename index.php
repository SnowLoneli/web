<?php
// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "carwash";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar si hay errores en la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Procesar el formulario para agregar registros
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tipo'])) {
    $tipo = $_POST['tipo'];
    $descripcion = $_POST['descripcion'];
    $monto = $_POST['monto'];
    $fecha = $_POST['fecha'];

    if ($tipo == "servicio") {
        $sql = "INSERT INTO servicios (nombre_servicio, precio, fecha) VALUES ('$descripcion', '$monto', '$fecha')";
    } elseif ($tipo == "ingreso") {
        $sql = "INSERT INTO ingresos (descripcion, monto, fecha) VALUES ('$descripcion', '$monto', '$fecha')";
    } elseif ($tipo == "gasto") {
        $sql = "INSERT INTO gastos (descripcion, monto, fecha) VALUES ('$descripcion', '$monto', '$fecha')";
    }

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('¡Registro exitoso!');</script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Procesar la limpieza de registros
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['limpiar_registros'])) {
    $sql_limpiar_servicios = "TRUNCATE TABLE servicios";
    $sql_limpiar_ingresos = "TRUNCATE TABLE ingresos";
    $sql_limpiar_gastos = "TRUNCATE TABLE gastos";

    if (
        $conn->query($sql_limpiar_servicios) === TRUE &&
        $conn->query($sql_limpiar_ingresos) === TRUE &&
        $conn->query($sql_limpiar_gastos) === TRUE
    ) {
        echo "<script>alert('¡Todos los registros han sido eliminados!');</script>";
    } else {
        echo "Error al limpiar registros: " . $conn->error;
    }
}

// Consultar los datos de las tablas
$servicios = $conn->query("SELECT * FROM servicios ORDER BY fecha DESC");
$ingresos = $conn->query("SELECT * FROM ingresos ORDER BY fecha DESC");
$gastos = $conn->query("SELECT * FROM gastos ORDER BY fecha DESC");

// Calcular totales
$total_ingresos = $conn->query("SELECT SUM(monto) AS total FROM ingresos")->fetch_assoc()['total'] ?? 0;
$total_gastos = $conn->query("SELECT SUM(monto) AS total FROM gastos")->fetch_assoc()['total'] ?? 0;
$margen_ganancia = $total_ingresos - $total_gastos;
?>

<!DOCTYPE html>
<html lang="es">
<head>
<link rel="stylesheet" href="styles.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carwash - Administración</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        form {
            background-color: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 300px;
            margin-bottom: 20px;
        }
        input, select, button {
            width: 100%;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .totales {
            font-size: 1.2em;
            font-weight: bold;
        }
        .ganancia {
            color: green;
        }
        .perdida {
            color: red;
        }
    </style>
</head>
<body>
    <h1>Profesional Services Carwash SJF</h1>

    <!-- Formulario para ingresar datos -->
    <form method="POST" action="">
        <label for="tipo">Tipo:</label>
        <select name="tipo" id="tipo" required>
            <option value="servicio">Servicio</option>
            <option value="ingreso">Ingreso</option>
            <option value="gasto">Gasto</option>
        </select><br><br>

        <label for="descripcion">Descripción:</label>
        <input type="text" name="descripcion" id="descripcion" required><br><br>

        <label for="monto">Monto:</label>
        <input type="number" step="0.01" name="monto" id="monto" required><br><br>

        <label for="fecha">Fecha:</label>
        <input type="date" name="fecha" id="fecha" required><br><br>

        <button type="submit">Registrar</button>
    </form>

    <!-- Botón para limpiar registros -->
    <form method="POST" action="">
        <button type="submit" name="limpiar_registros" style="background-color: red; color: white;">Limpiar todos los registros</button>
    </form>

    <!-- Mostrar totales -->
    <h2>Totales</h2>
    <table class="totales">
        <tr>
            <th>Total Ingresos</th>
            <td>$<?php echo number_format($total_ingresos, 2); ?></td>
        </tr>
        <tr>
            <th>Total Gastos</th>
            <td>-$<?php echo number_format($total_gastos, 2); ?></td>
        </tr>
        <tr>
            <th>Margen de Ganancia</th>
            <td class="<?php echo $margen_ganancia >= 0 ? 'ganancia' : 'perdida'; ?>">
                $<?php echo number_format($margen_ganancia, 2); ?>
            </td>
        </tr>
    </table>

    <!-- Mostrar servicios registrados -->
    <h2>Servicios</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Descripción</th>
            <th>Precio</th>
            <th>Fecha</th>
        </tr>
        <?php while ($row = $servicios->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['nombre_servicio']; ?></td>
            <td><?php echo $row['precio']; ?></td>
            <td><?php echo $row['fecha']; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <!-- Mostrar ingresos registrados -->
    <h2>Ingresos</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Descripción</th>
            <th>Monto</th>
            <th>Fecha</th>
        </tr>
        <?php while ($row = $ingresos->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['descripcion']; ?></td>
            <td><?php echo $row['monto']; ?></td>
            <td><?php echo $row['fecha']; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <!-- Mostrar gastos registrados -->
    <h2>Gastos</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Descripción</th>
            <th>Monto</th>
            <th>Fecha</th>
        </tr>
        <?php while ($row = $gastos->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['descripcion']; ?></td>
            <td><?php echo $row['monto']; ?></td>
            <td><?php echo $row['fecha']; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>