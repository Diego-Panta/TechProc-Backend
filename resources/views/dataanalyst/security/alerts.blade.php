<!DOCTYPE html>
<html>
<head>
    <title>Alertas de Seguridad - DataAnalyst</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .critical { background-color: #ffcccc; }
        .high { background-color: #ffe6cc; }
        .medium { background-color: #ffffcc; }
        .low { background-color: #e6ffe6; }
    </style>
</head>
<body>
    <h1>Alertas de Seguridad</h1>
    
    <div id="alertsContent">
        <!-- Las alertas se cargarán aquí -->
    </div>

    <script>
        function loadSecurityAlerts() {
            const params = new URLSearchParams(window.location.search);
            
            fetch(`/data-analyst/security/alerts?${params}`)
                .then(response => response.json())
                .then(data => {
                    displaySecurityAlerts(data);
                });
        }

        function displaySecurityAlerts(data) {
            let html = '';

            if (data.data && data.data.length > 0) {
                html += `
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo Amenaza</th>
                            <th>Severidad</th>
                            <th>Estado</th>
                            <th>IP Bloqueada</th>
                            <th>Fecha Detección</th>
                        </tr>
                    </thead>
                    <tbody>
                `;

                data.data.forEach(alert => {
                    const severityClass = alert.severity ? alert.severity.toLowerCase() : '';
                    html += `
                    <tr class="${severityClass}">
                        <td>${alert.id_security_alert || alert.id}</td>
                        <td>${alert.threat_type}</td>
                        <td>${alert.severity}</td>
                        <td>${alert.status}</td>
                        <td>${alert.blocked_ip ? alert.blocked_ip.ip_address : 'N/A'}</td>
                        <td>${new Date(alert.detection_date).toLocaleString()}</td>
                    </tr>
                    `;
                });

                html += `
                    </tbody>
                </table>
                `;
            } else {
                html = '<p>No se encontraron alertas de seguridad en el período seleccionado.</p>';
            }

            document.getElementById('alertsContent').innerHTML = html;
        }

        // Cargar datos al iniciar
        document.addEventListener('DOMContentLoaded', loadSecurityAlerts);
    </script>
</body>
</html>