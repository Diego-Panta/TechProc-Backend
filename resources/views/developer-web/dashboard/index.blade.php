<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DeveloperWeb Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8fafc; color: #334155; line-height: 1.6; }
        
        .container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        
        .header { 
            background: white; 
            padding: 30px; 
            border-radius: 12px; 
            margin-bottom: 24px; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-left: 4px solid #3b82f6;
        }
        
        .stats-overview { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 16px; 
            margin-bottom: 24px; 
        }
        
        .stat-card { 
            background: white; 
            padding: 20px; 
            border-radius: 8px; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            text-align: center;
            border-top: 4px solid #3b82f6;
        }
        
        .stat-number { 
            font-size: 2.5em; 
            font-weight: bold; 
            margin: 8px 0; 
            color: #1e293b;
        }
        
        .stat-label { 
            color: #64748b; 
            font-size: 0.9em;
            font-weight: 500;
        }
        
        .dashboard-grid { 
            display: grid; 
            grid-template-columns: 2fr 1fr; 
            gap: 24px; 
        }
        
        .main-content { display: flex; flex-direction: column; gap: 24px; }
        .sidebar { display: flex; flex-direction: column; gap: 24px; }
        
        .section { 
            background: white; 
            padding: 24px; 
            border-radius: 12px; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .section-header { 
            display: flex; 
            justify-content: between; 
            align-items: center; 
            margin-bottom: 20px; 
            padding-bottom: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .section-title { 
            font-size: 1.25em; 
            font-weight: 600; 
            color: #1e293b;
        }
        
        .contact-item, .news-item, .announcement-item { 
            padding: 16px; 
            border: 1px solid #e2e8f0; 
            border-radius: 8px; 
            margin-bottom: 12px;
            transition: all 0.2s ease;
        }
        
        .contact-item:hover, .news-item:hover, .announcement-item:hover { 
            border-color: #3b82f6;
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.1);
        }
        
        .contact-header { 
            display: flex; 
            justify-content: between; 
            align-items: start; 
            margin-bottom: 8px;
        }
        
        .contact-name { font-weight: 600; color: #1e293b; }
        .contact-priority { 
            padding: 4px 8px; 
            border-radius: 4px; 
            font-size: 0.8em; 
            font-weight: 500;
        }
        
        .priority-high { background: #fef2f2; color: #dc2626; }
        .priority-urgent { background: #fff7ed; color: #ea580c; }
        
        .contact-subject { 
            font-weight: 500; 
            margin-bottom: 4px;
            color: #374151;
        }
        
        .contact-meta { 
            color: #6b7280; 
            font-size: 0.85em;
        }
        
        .news-title { 
            font-weight: 500; 
            margin-bottom: 8px;
            color: #1e293b;
        }
        
        .news-meta { 
            display: flex; 
            justify-content: between; 
            color: #64748b; 
            font-size: 0.85em;
        }
        
        .news-category { 
            background: #f1f5f9; 
            padding: 2px 8px; 
            border-radius: 4px;
            font-size: 0.8em;
        }
        
        .news-status { 
            padding: 2px 8px; 
            border-radius: 4px; 
            font-size: 0.8em;
            font-weight: 500;
        }
        
        .status-published { background: #f0fdf4; color: #16a34a; }
        .status-draft { background: #fefce8; color: #ca8a04; }
        
        .announcement-stats { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 8px; 
            margin-top: 8px;
            font-size: 0.85em;
        }
        
        .stat-item { color: #64748b; }
        .stat-value { font-weight: 500; color: #1e293b; }
        
        .nav-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); 
            gap: 12px; 
            margin-top: 20px;
        }
        
        .nav-card { 
            background: white; 
            padding: 16px; 
            border-radius: 8px; 
            text-align: center; 
            text-decoration: none; 
            color: #334155; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: all 0.2s ease;
            border: 1px solid #e2e8f0;
        }
        
        .nav-card:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-color: #3b82f6;
            color: #3b82f6;
        }
        
        .loading { 
            text-align: center; 
            padding: 40px; 
            color: #64748b;
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75em;
            font-weight: 500;
        }
        
        .badge-success { background: #f0fdf4; color: #16a34a; }
        .badge-warning { background: #fff7ed; color: #ea580c; }
        .badge-info { background: #f0f9ff; color: #0ea5e9; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🚀 DeveloperWeb Dashboard</h1>
            <p>Panel de control del módulo DeveloperWeb - Gestión de contenido web</p>
        </div>

        <!-- Estadísticas Principales -->
        <div class="stats-overview" id="statsOverview">
            <div class="loading">📊 Cargando estadísticas...</div>
        </div>

        <!-- Contenido Principal -->
        <div class="dashboard-grid">
            <div class="main-content">
                <!-- Consultas Pendientes -->
                <div class="section">
                    <div class="section-header">
                        <h2 class="section-title">📧 Consultas Pendientes - Requieren Atención</h2>
                    </div>
                    <div id="pendingContacts">
                        @foreach($pendingContacts as $contact)
                        <div class="contact-item">
                            <div class="contact-header">
                                <span class="contact-name">{{ $contact->full_name }}</span>
                                <span class="contact-priority priority-{{ $contact->priority ?? 'high' }}">
                                    {{ $contact->priority ?? 'high' }}
                                </span>
                            </div>
                            <div class="contact-subject">{{ $contact->subject }}</div>
                            <div class="contact-meta">
                                {{ $contact->email }} • {{ $contact->submission_date->format('d M. Y, h:i a') }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Noticias Recientes -->
                <div class="section">
                    <div class="section-header">
                        <h2 class="section-title">📰 Noticias Recientes</h2>
                    </div>
                    <div id="recentNews">
                        @foreach($recentNews as $news)
                        <div class="news-item">
                            <div class="news-title">{{ $news->title }}</div>
                            <div class="news-meta">
                                <span class="news-category">{{ $news->category ?? 'General' }}</span>
                                <span>
                                    {{ $news->views }} vistas • 
                                    <span class="news-status status-{{ $news->status }}">
                                        {{ $news->status }}
                                    </span>
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="sidebar">
                <!-- Anuncios Activos -->
                <div class="section">
                    <div class="section-header">
                        <h2 class="section-title">📢 Anuncios Activos</h2>
                    </div>
                    <div id="activeAnnouncements">
                        @foreach($activeAnnouncements as $announcement)
                        <div class="announcement-item">
                            <div class="news-title">{{ $announcement['title'] }}</div>
                            <div class="announcement-stats">
                                <div class="stat-item">
                                    <span class="stat-value">{{ $announcement['views'] }}</span> vistas
                                </div>
                                <div class="stat-item">
                                    <span class="stat-value">{{ $announcement['clicks'] }}</span> clics
                                </div>
                                <div class="stat-item">
                                    CTR: <span class="stat-value">{{ $announcement['ctr'] }}%</span>
                                </div>
                                <div class="stat-item">
                                    <span class="badge badge-info">{{ $announcement['display_type'] }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Navegación Rápida -->
                <div class="section">
                    <div class="section-header">
                        <h2 class="section-title">⚡ Navegación Rápida</h2>
                    </div>
                    <div class="nav-grid">
                        <a href="/developer-web/news" class="nav-card">
                            <div>📰</div>
                            <div>Noticias</div>
                        </a>
                        <a href="/developer-web/announcements" class="nav-card">
                            <div>📢</div>
                            <div>Anuncios</div>
                        </a>
                        <a href="/developer-web/alerts" class="nav-card">
                            <div>⚠️</div>
                            <div>Alertas</div>
                        </a>
                        <a href="/developer-web/chatbot/faqs" class="nav-card">
                            <div>🤖</div>
                            <div>Chatbot FAQs</div>
                        </a>
                        <a href="/developer-web/contact-forms" class="nav-card">
                            <div>📧</div>
                            <div>Formularios</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadStatistics();
            setInterval(loadStatistics, 30000);
        });

        async function loadStatistics() {
            try {
                const response = await fetch('/developer-web/dashboard/statistics');
                const data = await response.json();

                if (data.success) {
                    displayStatistics(data.data);
                } else {
                    showError('Error al cargar estadísticas');
                }
            } catch (error) {
                console.error('Error:', error);
                showError('Error de conexión');
            }
        }

        function displayStatistics(stats) {
            const statsOverview = document.getElementById('statsOverview');
            
            statsOverview.innerHTML = `
                <div class="stat-card">
                    <div class="stat-label">Noticias Publicadas</div>
                    <div class="stat-number">${stats.news.published}</div>
                    <div style="color: #64748b; font-size: 0.85em;">Total: ${stats.news.total}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Alertas Activas</div>
                    <div class="stat-number">${stats.alerts.active}</div>
                    <div style="color: #64748b; font-size: 0.85em;">Total: ${stats.alerts.total}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Anuncios Activos</div>
                    <div class="stat-number">${stats.announcements.active}</div>
                    <div style="color: #64748b; font-size: 0.85em;">Total: ${stats.announcements.total}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Consultas Pendientes</div>
                    <div class="stat-number">${stats.contact_forms.pending}</div>
                    <div style="color: #64748b; font-size: 0.85em;">Total: ${stats.contact_forms.total}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">FAQs Chatbot</div>
                    <div class="stat-number">${stats.chatbot.active_faqs}</div>
                    <div style="color: #64748b; font-size: 0.85em;">Total: ${stats.chatbot.total_faqs}</div>
                </div>
            `;
        }

        function showError(message) {
            document.getElementById('statsOverview').innerHTML = 
                `<div class="stat-card" style="grid-column: 1 / -1; text-align: center; color: #dc2626;">
                    <div>❌ Error</div>
                    <div style="margin: 10px 0;">${message}</div>
                    <button onclick="loadStatistics()" style="padding: 8px 16px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">Reintentar</button>
                </div>`;
        }
    </script>
</body>
</html>