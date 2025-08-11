<?php
require_once 'includes/auth.php';
include 'includes/menu.php';
if (!verificarSesion() || $_SESSION['rol_id'] != 1) {
    header("Location: login.php");
    exit;
}
require_once 'includes/db.php';

$listaUsuarios = $conn->query("
    SELECT u.*, r.nombre AS rol_desc
    FROM usuarios u
    INNER JOIN roles r ON u.rol_id = r.id
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Administrar Usuarios</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #eef2f7;
            margin: 0;
            padding: 0px;
            color: #1f2937;
        }
        .wrapper {
            max-width: 960px;
            margin: 0 auto;
        }
        h1 {
            margin-bottom: 24px;
            font-weight: 700;
            color: #334155;
        }
        .btn {
            padding: 10px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: background-color 0.3s ease;
            display: inline-block;
            margin-bottom: 16px;
            user-select: none;
            cursor: pointer;
        }
        .btn-agregar {
            background-color: #2563eb;
            color: white;
        }
        .btn-agregar:hover {
            background-color: #1e40af;
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 12px;
        }
        th, td {
            text-align: left;
            padding: 14px 18px;
        }
        th {
            background-color: transparent;
            color: #64748b;
            font-weight: 600;
            font-size: 15px;
        }
        tbody tr {
            background-color: white;
            box-shadow: 0 3px 6px rgb(100 116 139 / 0.1);
            border-radius: 10px;
        }
        tbody tr td {
            border: none;
        }
        tbody tr td.actions {
            display: flex;
            gap: 10px;
        }
        .btn-editar {
            background-color: #3b82f6;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
        }
        .btn-editar:hover {
            background-color: #2563eb;
        }
        .btn-eliminar {
            background-color: #ef4444;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
        }
        .btn-eliminar:hover {
            background-color: #b91c1c;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <h1>Usuarios Registrados</h1>
        <a href="usuario_form.php" class="btn btn-agregar">+ Añadir Nuevo Usuario</a>
        <table>
            <thead>
                <tr>
                    <th>Nombre Completo</th>
                    <th>Correo Electrónico</th>
                    <th>Rol</th>
                    <th>Opciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($usuario = $listaUsuarios->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                    <td><?= htmlspecialchars($usuario['correo']) ?></td>
                    <td><?= htmlspecialchars($usuario['rol_desc']) ?></td>
                    <td class="actions">
                        <a href="usuario_form.php?id=<?= $usuario['id'] ?>" class="btn-editar">Modificar</a>
                        <a href="usuario_eliminar.php?id=<?= $usuario['id'] ?>" class="btn-eliminar" onclick="return confirm('¿Estás seguro que deseas eliminar este usuario?');">Borrar</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
