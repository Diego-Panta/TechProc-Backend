<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Seguridad - DataAnalyst</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .section { margin: 20px 0; }
        .metric-card { 
            border: 1px solid #ccc; 
            padding: 15px; 
            margin: 10px 0; 
            border-radius: 5px; 
        }
    </style>
</head>
<body>
    <h1>Reporte de Seguridad - DataAnalyst</h1>
    
    <!-- Filtros -->
    <div class="section">
        <h2>Filtros</h2>
        <form id="securityFilters">
            <div>
                <label>Fecha Inicio:</label>
                <input type="date" name="start_date" id="start_date">
            </div>
            <div>
                <label>Fecha Fin:</label>
                <input type="date" name="end_date" id="end_date">
            </div>
            <div>
                <label>Tipo Evento:</label>
                <input type="text" name="event_type" id="event_type" placeholder="Ej: login_failed">
            </div>
            <button type="button" onclick="loadSecurityAnalysis()">Generar Reporte</button>
        </form>
    </div>

    <!-- Resultados -->
    <div id="securityResults">
        <!-- Análisis de Seguridad -->
        <div class="section" id="analysisSection">
            <h2>Análisis de Seguridad</h2>
            <div id="analysisContent">
                <!-- Los datos se cargarán aquí dinámicamente -->
            </div>
        </div>

        <!-- Eventos de Seguridad -->
        <div class="section" id="eventsSection">
            <h2>Eventos de Seguridad</h2>
            <div id="eventsContent">
                <!-- Los eventos se cargarán aquí -->
            </div>
        </div>

        <!-- Alertas de Seguridad -->
        <div class="section" id="alertsSection">
            <h2>Alertas de Seguridad</h2>
            <div id="alertsContent">
                <!-- Las alertas se cargarán aquí -->
            </div>
        </div>
    </div>

    <script>
        function loadSecurityAnalysis() {
            const formData = new FormData(document.getElementById('securityFilters'));
            const params = new URLSearchParams(formData);
            
            // Cargar análisis
            fetch(`/data-analyst/security/analysis?${params}`)
                .then(response => response.json())
                .then(data => {
                    displaySecurityAnalysis(data);
                })
                .catch(error => {
                    console.error('Error loading analysis:', error);
                });
            
            // Cargar eventos
            fetch(`/data-analyst/security/events?${params}`)
                .then(response => response.json())
                .then(data => {
                    displaySecurityEvents(data);
                })
                .catch(error => {
                    console.error('Error loading events:', error);
                });
        }

        function displaySecurityAnalysis(data) {
            let html = '';

            // Métricas principales
            html += `
            <div class="metric-card">
                <h3>Métricas Principales</h3>
                <table>
                    <tr>
                        <th>Total Eventos de Seguridad</th>
                        <td>${data.total_security_events}</td>
                    </tr>
                    <tr>
                        <th>IPs Bloqueadas (Total/Activas/Periodo)</th>
                        <td>${data.blocked_ips.total} / ${data.blocked_ips.active} / ${data.blocked_ips.this_period}</td>
                    </tr>
                    <tr>
                        <th>Alertas de Seguridad</th>
                        <td>${data.security_alerts.total}</td>
                    </tr>
                    <tr>
                        <th>Incidentes (Total/Resueltos/En Progreso)</th>
                        <td>${data.incidents.total} / ${data.incidents.resolved} / ${data.incidents.in_progress}</td>
                    </tr>
                    <tr>
                        <th>Tasa de Logins Fallidos</th>
                        <td>${data.failed_login_rate}%</td>
                    </tr>
                </table>
            </div>
            `;

            // Eventos por Tipo
            html += `
            <div class="metric-card">
                <h3>Eventos por Tipo</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Tipo de Evento</th>
                            <th>Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            for (const [eventType, count] of Object.entries(data.by_event_type)) {
                html += `
                    <tr>
                        <td>${eventType}</td>
                        <td>${count}</td>
                    </tr>
                `;
            }

            html += `
                    </tbody>
                </table>
            </div>
            `;

            // Alertas por Severidad
            html += `
            <div class="metric-card">
                <h3>Alertas por Severidad</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Severidad</th>
                            <th>Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            for (const [severity, count] of Object.entries(data.security_alerts.by_severity)) {
                html += `
                    <tr>
                        <td>${severity}</td>
                        <td>${count}</td>
                    </tr>
                `;
            }

            html += `
                    </tbody>
                </table>
            </div>
            `;

            // IPs con Mayor Amenaza
            if (data.top_threat_ips && data.top_threat_ips.length > 0) {
                html += `
                <div class="metric-card">
                    <h3>IPs con Mayor Amenaza</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Dirección IP</th>
                                <th>Intentos</th>
                                <th>Bloqueada</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                data.top_threat_ips.forEach(ip => {
                    html += `
                        <tr>
                            <td>${ip.ip_address}</td>
                            <td>${ip.attempt_count}</td>
                            <td>${ip.blocked ? 'SÍ' : 'NO'}</td>
                        </tr>
                    `;
                });

                html += `
                        </tbody>
                    </table>
                </div>
                `;
            } else {
                html += `
                <div class="metric-card">
                    <h3>IPs con Mayor Amenaza</h3>
                    <p>No se encontraron IPs con intentos sospechosos en el período seleccionado.</p>
                </div>
                `;
            }

            document.getElementById('analysisContent').innerHTML = html;
        }

        function displaySecurityEvents(data) {
            let html = '';

            if (data.data && data.data.length > 0) {
                html += `
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo Evento</th>
                            <th>Usuario</th>
                            <th>IP Origen</th>
                            <th>Descripción</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                `;

                data.data.forEach(event => {
                    html += `
                    <tr>
                        <td>${event.id_security_log || event.id}</td>
                        <td>${event.event_type}</td>
                        <td>${event.user ? event.user.name : 'N/A'}</td>
                        <td>${event.source_ip || 'N/A'}</td>
                        <td>${event.description || 'Sin descripción'}</td>
                        <td>${new Date(event.event_date).toLocaleString()}</td>
                    </tr>
                    `;
                });

                html += `
                    </tbody>
                </table>
                `;

                // Paginación
                if (data.links) {
                    html += `<div class="pagination">`;
                    if (data.links.prev) {
                        html += `<button onclick="loadPage('${data.links.prev}')">Anterior</button>`;
                    }
                    if (data.links.next) {
                        html += `<button onclick="loadPage('${data.links.next}')">Siguiente</button>`;
                    }
                    html += `</div>`;
                }
            } else {
                html = '<p>No se encontraron eventos de seguridad en el período seleccionado.</p>';
            }

            document.getElementById('eventsContent').innerHTML = html;
        }

        function loadPage(url) {
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    displaySecurityEvents(data);
                });
        }

        // Cargar datos al iniciar
        document.addEventListener('DOMContentLoaded', loadSecurityAnalysis);
    </script>
</body>
</html>