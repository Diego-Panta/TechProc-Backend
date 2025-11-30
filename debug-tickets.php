<?php

// Cargar Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Tickets - Permisos</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
        h1 { color: #4ec9b0; }
        h2 { color: #569cd6; margin-top: 30px; }
        .ticket { background: #2d2d30; padding: 15px; margin: 10px 0; border-left: 4px solid #007acc; }
        .user { background: #2d2d30; padding: 15px; margin: 10px 0; border-left: 4px solid #4ec9b0; }
        .error { background: #3f1f1f; padding: 15px; margin: 10px 0; border-left: 4px solid #f48771; }
        .success { background: #1f3f1f; padding: 15px; margin: 10px 0; border-left: 4px solid #6a9955; }
        .warning { background: #3f3f1f; padding: 15px; margin: 10px 0; border-left: 4px solid #ce9178; }
        pre { background: #1e1e1e; padding: 10px; overflow-x: auto; }
        .label { color: #9cdcfe; font-weight: bold; }
        .value { color: #ce9178; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border: 1px solid #404040; }
        th { background: #2d2d30; color: #569cd6; }
        tr:nth-child(even) { background: #252526; }
    </style>
</head>
<body>

<h1>🔍 Debug de Tickets y Permisos</h1>

<?php

try {
    // Listar todos los tickets
    echo "<h2>📋 Todos los Tickets en el Sistema</h2>";

    $tickets = \IncadevUns\CoreDomain\Models\Ticket::with('user')
        ->orderBy('id', 'desc')
        ->get();

    if ($tickets->isEmpty()) {
        echo "<div class='warning'>⚠️ No hay tickets en el sistema</div>";
    } else {
        echo "<table>";
        echo "<tr>
                <th>ID</th>
                <th>Título</th>
                <th>User ID</th>
                <th>Usuario (Nombre)</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Prioridad</th>
              </tr>";

        foreach ($tickets as $ticket) {
            $user = $ticket->user;
            $roles = $user ? $user->getRoleNames()->implode(', ') : 'N/A';

            echo "<tr>";
            echo "<td><strong>#{$ticket->id}</strong></td>";
            echo "<td>" . htmlspecialchars($ticket->title) . "</td>";
            echo "<td>{$ticket->user_id}</td>";
            echo "<td>" . ($user ? htmlspecialchars($user->name) : 'N/A') . "</td>";
            echo "<td>" . ($user ? htmlspecialchars($user->email) : 'N/A') . "</td>";
            echo "<td>{$roles}</td>";
            echo "<td>{$ticket->status->value}</td>";
            echo "<td>{$ticket->priority->value}</td>";
            echo "</tr>";
        }

        echo "</table>";
    }

    // Listar todos los usuarios
    echo "<h2>👥 Todos los Usuarios en el Sistema</h2>";

    $users = \App\Models\User::with('roles')->get();

    echo "<table>";
    echo "<tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Roles</th>
            <th>Tickets Creados</th>
          </tr>";

    foreach ($users as $user) {
        $roles = $user->getRoleNames()->implode(', ');
        $ticketCount = \IncadevUns\CoreDomain\Models\Ticket::where('user_id', $user->id)->count();

        echo "<tr>";
        echo "<td><strong>{$user->id}</strong></td>";
        echo "<td>" . htmlspecialchars($user->name) . "</td>";
        echo "<td>" . htmlspecialchars($user->email) . "</td>";
        echo "<td>{$roles}</td>";
        echo "<td>{$ticketCount}</td>";
        echo "</tr>";
    }

    echo "</table>";

    // Test de Políticas
    echo "<h2>🔐 Test de Políticas (Policy Test)</h2>";

    echo "<div class='warning'>";
    echo "<p><strong>⚠️ Instrucciones:</strong></p>";
    echo "<p>Agrega <code>?user_id=X&ticket_id=Y</code> a la URL para probar permisos específicos.</p>";
    echo "<p>Ejemplo: <code>debug-tickets.php?user_id=13&ticket_id=11</code></p>";
    echo "</div>";

    if (isset($_GET['user_id']) && isset($_GET['ticket_id'])) {
        $userId = (int)$_GET['user_id'];
        $ticketId = (int)$_GET['ticket_id'];

        $user = \App\Models\User::find($userId);
        $ticket = \IncadevUns\CoreDomain\Models\Ticket::find($ticketId);

        if (!$user) {
            echo "<div class='error'>❌ Usuario #{$userId} no encontrado</div>";
        } elseif (!$ticket) {
            echo "<div class='error'>❌ Ticket #{$ticketId} no encontrado</div>";
        } else {
            echo "<div class='ticket'>";
            echo "<h3>Usuario que intenta acceder:</h3>";
            echo "<p><span class='label'>ID:</span> <span class='value'>{$user->id}</span> (" . gettype($user->id) . ")</p>";
            echo "<p><span class='label'>Nombre:</span> <span class='value'>" . htmlspecialchars($user->name) . "</span></p>";
            echo "<p><span class='label'>Email:</span> <span class='value'>" . htmlspecialchars($user->email) . "</span></p>";
            echo "<p><span class='label'>Roles:</span> <span class='value'>" . $user->getRoleNames()->implode(', ') . "</span></p>";
            echo "</div>";

            echo "<div class='ticket'>";
            echo "<h3>Ticket:</h3>";
            echo "<p><span class='label'>ID:</span> <span class='value'>{$ticket->id}</span></p>";
            echo "<p><span class='label'>Título:</span> <span class='value'>" . htmlspecialchars($ticket->title) . "</span></p>";
            echo "<p><span class='label'>User ID (dueño):</span> <span class='value'>{$ticket->user_id}</span> (" . gettype($ticket->user_id) . ")</p>";

            $owner = \App\Models\User::find($ticket->user_id);
            if ($owner) {
                echo "<p><span class='label'>Dueño:</span> <span class='value'>" . htmlspecialchars($owner->name) . " ({$owner->email})</span></p>";
            }
            echo "</div>";

            echo "<div class='ticket'>";
            echo "<h3>Verificaciones de la Policy:</h3>";

            $isOwner = $ticket->user_id === $user->id;
            $hasPermissionViewAny = $user->hasPermissionTo('tickets.view-any');
            $hasRoleSupportAdmin = $user->hasRole(['support', 'admin']);

            echo "<p><span class='label'>✓ Es el dueño ($ticket->user_id === $user->id):</span> ";
            echo $isOwner ? "<span style='color:#6a9955'>✅ SÍ</span>" : "<span style='color:#f48771'>❌ NO</span>";
            echo "</p>";

            echo "<p><span class='label'>✓ Tiene permiso 'tickets.view-any':</span> ";
            echo $hasPermissionViewAny ? "<span style='color:#6a9955'>✅ SÍ</span>" : "<span style='color:#f48771'>❌ NO</span>";
            echo "</p>";

            echo "<p><span class='label'>✓ Tiene rol support/admin:</span> ";
            echo $hasRoleSupportAdmin ? "<span style='color:#6a9955'>✅ SÍ</span>" : "<span style='color:#f48771'>❌ NO</span>";
            echo "</p>";

            $canView = $isOwner || $hasPermissionViewAny || $hasRoleSupportAdmin;

            echo "</div>";

            if ($canView) {
                echo "<div class='success'>";
                echo "<h3>✅ RESULTADO: El usuario PUEDE ver este ticket</h3>";
                echo "<p>Al menos una de las condiciones es verdadera.</p>";
                echo "</div>";
            } else {
                echo "<div class='error'>";
                echo "<h3>❌ RESULTADO: El usuario NO PUEDE ver este ticket</h3>";
                echo "<p>Ninguna de las condiciones es verdadera. La policy bloqueará el acceso (403).</p>";
                echo "</div>";
            }

            // Test actual de la policy
            try {
                $gate = app(\Illuminate\Contracts\Auth\Access\Gate::class);
                $gate->forUser($user);

                if ($gate->allows('view', $ticket)) {
                    echo "<div class='success'>";
                    echo "<h3>✅ GATE TEST: Policy permite el acceso</h3>";
                    echo "</div>";
                } else {
                    echo "<div class='error'>";
                    echo "<h3>❌ GATE TEST: Policy DENIEGA el acceso</h3>";
                    echo "</div>";
                }
            } catch (\Exception $e) {
                echo "<div class='error'>";
                echo "<h3>❌ ERROR en Gate Test:</h3>";
                echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
                echo "</div>";
            }
        }
    }

} catch (\Exception $e) {
    echo "<div class='error'>";
    echo "<h3>❌ ERROR:</h3>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}

?>

<div style="margin-top: 50px; padding: 20px; background: #2d2d30; border-top: 2px solid #007acc;">
    <p><strong>💡 Cómo usar este script:</strong></p>
    <ol>
        <li>Primero, revisa la lista completa de tickets y usuarios arriba</li>
        <li>Identifica el ID del usuario y el ID del ticket que quieres probar</li>
        <li>Agrega a la URL: <code>?user_id=X&ticket_id=Y</code></li>
        <li>El script te mostrará si el usuario puede o no ver ese ticket según la policy</li>
    </ol>
    <p><strong>Ejemplo:</strong> <code><?php echo $_SERVER['PHP_SELF']; ?>?user_id=13&ticket_id=11</code></p>
</div>

</body>
</html>
