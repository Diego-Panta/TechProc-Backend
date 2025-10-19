<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Data Analyst</title>
</head>
<body>
    <header>
        <h1>Dashboard de Analítica</h1>
        <div id="filters">
            <form id="dashboardFilters">
                <label for="start_date">Fecha Inicio:</label>
                <input type="date" id="start_date" name="start_date">
                
                <label for="end_date">Fecha Fin:</label>
                <input type="date" id="end_date" name="end_date">
                
                <button type="submit">Aplicar Filtros</button>
            </form>
        </div>
    </header>

    <main>
        <div id="loading">Cargando datos del dashboard...</div>
        
        <div id="dashboard" style="display: none;">
            <!-- Métricas de Estudiantes -->
            <section id="students-metrics">
                <h2>Estudiantes</h2>
                <div class="metric-card">
                    <h3>Total Estudiantes</h3>
                    <div class="value" id="total-students">0</div>
                    <div class="sub-value">
                        <span id="active-students">0</span> activos
                        <span class="growth" id="student-growth">+0%</span>
                    </div>
                </div>
            </section>

            <!-- Métricas de Cursos -->
            <section id="courses-metrics">
                <h2>Cursos</h2>
                <div class="metric-card">
                    <h3>Total Cursos</h3>
                    <div class="value" id="total-courses">0</div>
                    <div class="sub-value">
                        <span id="active-courses">0</span> activos
                        <span id="total-enrollments">0</span> matrículas
                    </div>
                </div>
            </section>

            <!-- Métricas de Asistencia -->
            <section id="attendance-metrics">
                <h2>Asistencia</h2>
                <div class="metric-card">
                    <h3>Tasa de Asistencia</h3>
                    <div class="value" id="attendance-rate">0%</div>
                    <div class="sub-value">
                        Tendencia: <span id="attendance-trend">estable</span>
                    </div>
                </div>
            </section>

            <!-- Métricas de Rendimiento -->
            <section id="performance-metrics">
                <h2>Rendimiento</h2>
                <div class="metric-card">
                    <h3>Nota Promedio</h3>
                    <div class="value" id="average-grade">0.0</div>
                    <div class="sub-value">
                        Tasa de aprobación: <span id="passing-rate">0%</span>
                    </div>
                </div>
            </section>

            <!-- Métricas Financieras -->
            <section id="revenue-metrics">
                <h2>Ingresos</h2>
                <div class="metric-card">
                    <h3>Ingresos Totales</h3>
                    <div class="value" id="total-revenue">$0.00</div>
                    <div class="sub-value">
                        Crecimiento: <span id="revenue-growth">+0%</span>
                    </div>
                </div>
            </section>

            <!-- Métricas de Soporte -->
            <section id="support-metrics">
                <h2>Soporte</h2>
                <div class="metric-card">
                    <h3>Tickets Abiertos</h3>
                    <div class="value" id="open-tickets">0</div>
                    <div class="sub-value">
                        Tiempo promedio: <span id="resolution-time">0h</span>
                    </div>
                </div>
            </section>

            <!-- Métricas de Seguridad -->
            <section id="security-metrics">
                <h2>Seguridad</h2>
                <div class="metric-card">
                    <h3>Alertas Activas</h3>
                    <div class="value" id="active-alerts">0</div>
                    <div class="sub-value">
                        IPs bloqueadas: <span id="blocked-ips">0</span>
                    </div>
                </div>
            </section>

            <!-- Actividades Recientes -->
            <section id="recent-activities">
                <h2>Actividades Recientes</h2>
                <div id="activities-list">
                    <!-- Las actividades se cargarán aquí -->
                </div>
            </section>
        </div>
    </main>

    <script>
        // Función para cargar los datos del dashboard
        async function loadDashboardData(filters = {}) {
            try {
                document.getElementById('loading').style.display = 'block';
                document.getElementById('dashboard').style.display = 'none';
                
                const queryParams = new URLSearchParams(filters).toString();
                const response = await fetch(`/data-analyst/dashboard/data?${queryParams}`);
                const data = await response.json();
                
                // Actualizar la interfaz con los datos
                updateDashboardUI(data);
                
                document.getElementById('loading').style.display = 'none';
                document.getElementById('dashboard').style.display = 'block';
            } catch (error) {
                console.error('Error loading dashboard data:', error);
                document.getElementById('loading').innerHTML = 'Error cargando datos';
            }
        }

        // Función para actualizar la interfaz
        function updateDashboardUI(data) {
            // Estudiantes
            document.getElementById('total-students').textContent = data.students.total;
            document.getElementById('active-students').textContent = data.students.active;
            document.getElementById('student-growth').textContent = `+${data.students.growth_rate}%`;
            
            // Cursos
            document.getElementById('total-courses').textContent = data.courses.total;
            document.getElementById('active-courses').textContent = data.courses.active;
            document.getElementById('total-enrollments').textContent = data.courses.total_enrollments;
            
            // Asistencia
            document.getElementById('attendance-rate').textContent = `${data.attendance.average_rate}%`;
            document.getElementById('attendance-trend').textContent = data.attendance.trend === 'up' ? '↑' : '↓';
            
            // Rendimiento
            document.getElementById('average-grade').textContent = data.performance.average_grade;
            document.getElementById('passing-rate').textContent = `${data.performance.passing_rate}%`;
            
            // Ingresos
            document.getElementById('total-revenue').textContent = `$${data.revenue.total.toLocaleString()}`;
            document.getElementById('revenue-growth').textContent = `+${data.revenue.growth_rate}%`;
            
            // Soporte
            document.getElementById('open-tickets').textContent = data.support.open_tickets;
            document.getElementById('resolution-time').textContent = `${data.support.average_resolution_time_hours}h`;
            
            // Seguridad
            document.getElementById('active-alerts').textContent = data.security.active_alerts;
            document.getElementById('blocked-ips').textContent = data.security.blocked_ips;
            
            // Actividades recientes
            const activitiesList = document.getElementById('activities-list');
            activitiesList.innerHTML = data.recent_activities.map(activity => `
                <div class="activity-item">
                    <strong>${activity.type}</strong>: ${activity.description}
                    <br><small>${new Date(activity.timestamp).toLocaleString()}</small>
                </div>
            `).join('');
        }

        // Manejar el envío del formulario de filtros
        document.getElementById('dashboardFilters').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const filters = Object.fromEntries(formData.entries());
            loadDashboardData(filters);
        });

        // Cargar datos iniciales al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardData();
        });
    </script>
</body>
</html>