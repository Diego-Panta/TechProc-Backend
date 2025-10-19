<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generación de Reportes - DataAnalyst</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }

        h2 {
            color: #007bff;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }

        input[type="text"],
        input[type="date"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        input[type="checkbox"] {
            margin-right: 8px;
        }

        button {
            background-color: #007bff;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }

        button:hover {
            background-color: #0056b3;
        }

        button:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }

        #dynamicFilters {
            margin-top: 20px;
        }

        .error {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <h1>Generación de Reportes</h1>

    <div class="container">
        <!-- Formulario de Generación de Reportes -->
        <div class="report-form">
            <h2>Generar Nuevo Reporte</h2>
            <form id="exportForm">
                @csrf

                <div class="form-group">
                    <label for="report_type">Tipo de Reporte *</label>
                    <select name="report_type" id="report_type" required>
                        <option value="">Seleccionar tipo de reporte</option>
                        <option value="students">Estudiantes</option>
                        <option value="courses">Cursos</option>
                        <option value="attendance">Asistencia</option>
                        <option value="grades">Calificaciones</option>
                        <option value="financial">Financiero</option>
                        <option value="tickets">Tickets de Soporte</option>
                        <option value="security">Seguridad</option>
                        <option value="dashboard">Dashboard General</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="format">Formato *</label>
                    <select name="format" id="format" required>
                        <option value="excel">Excel</option>
                        <option value="pdf">PDF</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="report_title">Título del Reporte</label>
                    <input type="text" name="report_title" id="report_title"
                        placeholder="Título personalizado del reporte">
                </div>

                <div class="form-group">
                    <label for="start_date">Fecha Inicio</label>
                    <input type="date" name="start_date" id="start_date">
                </div>

                <div class="form-group">
                    <label for="end_date">Fecha Fin</label>
                    <input type="date" name="end_date" id="end_date">
                </div>

                <!-- Filtros dinámicos -->
                <div id="dynamicFilters">
                    <!-- Los filtros específicos se cargarán aquí -->
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="include_charts" id="include_charts">
                        Incluir gráficos (PDF)
                    </label>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="include_raw_data" id="include_raw_data">
                        Incluir datos crudos
                    </label>
                </div>

                <button type="submit" id="generateBtn">Generar y Descargar Reporte</button>
            </form>
        </div>
    </div>

    <script>
        // Cargar filtros dinámicos cuando cambie el tipo de reporte
        document.getElementById('report_type').addEventListener('change', function() {
            loadFilterOptions(this.value);
        });

        // Manejar envío del formulario
        document.getElementById('exportForm').addEventListener('submit', function(e) {
            e.preventDefault();
            generateReport();
        });

        function loadFilterOptions(reportType) {
            if (!reportType) return;

            fetch(`/data-analyst/export/filter-options/${reportType}`)
                .then(response => response.json())
                .then(options => {
                    const container = document.getElementById('dynamicFilters');
                    container.innerHTML = '';

                    console.log('Opciones de filtro cargadas:', options);

                    // SOLO MOSTRAR FILTRO DE ESTADO para estudiantes y tickets
                    if (options.statuses && options.statuses.length > 0) {
                        const statusSelect = createSelectFromArray('filters[status]', 'Estado', options.statuses);
                        container.appendChild(statusSelect);

                        // Agregar mensaje informativo
                        const info = document.createElement('div');
                        info.style.fontSize = '12px';
                        info.style.color = '#666';
                        info.style.marginTop = '5px';
                        info.textContent = 'Filtro de estado disponible';
                        container.appendChild(info);
                    } else {
                        console.log('No hay opciones de estado para este reporte');

                        // Mostrar mensaje cuando no hay filtros
                        const noFilters = document.createElement('div');
                        noFilters.style.fontSize = '12px';
                        noFilters.style.color = '#999';
                        noFilters.style.fontStyle = 'italic';
                        noFilters.textContent = 'No hay filtros adicionales para este tipo de reporte';
                        container.appendChild(noFilters);
                    }

                })
                .catch(error => {
                    console.error('Error cargando opciones de filtro:', error);
                });
        }

        function createSelect(name, label, options) {
            const group = document.createElement('div');
            group.className = 'form-group';

            const labelElement = document.createElement('label');
            labelElement.textContent = label;

            const select = document.createElement('select');
            select.name = name;

            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = `Seleccionar ${label.toLowerCase()}`;
            select.appendChild(defaultOption);

            options.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option.id;
                optionElement.textContent = option.name;
                select.appendChild(optionElement);
            });

            group.appendChild(labelElement);
            group.appendChild(select);
            return group;
        }

        function createSelectFromArray(name, label, optionsArray) {
            const group = document.createElement('div');
            group.className = 'form-group';

            const labelElement = document.createElement('label');
            labelElement.textContent = label;

            const select = document.createElement('select');
            select.name = name;

            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = `Seleccionar ${label.toLowerCase()}`;
            select.appendChild(defaultOption);

            optionsArray.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option;
                optionElement.textContent = option;
                select.appendChild(optionElement);
            });

            group.appendChild(labelElement);
            group.appendChild(select);
            return group;
        }

        function generateReport() {
            const form = document.getElementById('exportForm');
            const formData = new FormData(form);

            // Crear objeto data base
            const data = {
                report_type: formData.get('report_type'),
                format: formData.get('format'),
                report_title: formData.get('report_title') || null,
                include_charts: formData.has('include_charts'),
                include_raw_data: formData.has('include_raw_data'),
                filters: {} // Inicializar filters como objeto
            };

            // Validar campos requeridos
            if (!data.report_type) {
                alert('Por favor selecciona un tipo de reporte');
                return;
            }

            if (!data.format) {
                alert('Por favor selecciona un formato');
                return;
            }

            // PROCESAR FILTROS - VERSIÓN MEJORADA
            // Fechas directamente en filters
            const startDate = formData.get('start_date');
            const endDate = formData.get('end_date');
            
            if (startDate && startDate.trim() !== '') {
                data.filters.start_date = startDate;
            }
            if (endDate && endDate.trim() !== '') {
                data.filters.end_date = endDate;
            }

            // Procesar filtro de estado usando querySelector directo
            const statusSelect = document.querySelector('select[name="filters[status]"]');
            if (statusSelect && statusSelect.value && statusSelect.value.trim() !== '') {
                data.filters.status = statusSelect.value;
            }

            // DEBUG: Verificar estructura de filtros antes de enviar
            console.log('=== DEBUG FILTROS ===');
            console.log('start_date del form:', startDate);
            console.log('end_date del form:', endDate);
            console.log('status del select:', statusSelect ? statusSelect.value : 'No encontrado');
            console.log('Filtros procesados:', data.filters);
            console.log('Claves de filtros:', Object.keys(data.filters));
            console.log('Total de filtros:', Object.keys(data.filters).length);

            console.log('Datos a enviar (CORREGIDOS):', data);

            const generateBtn = document.getElementById('generateBtn');
            generateBtn.disabled = true;
            generateBtn.textContent = 'Generando...';

            // Usar fetch para manejar la descarga
            fetch('/data-analyst/export/generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/octet-stream'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(errorData => {
                            throw new Error(errorData.message || 'Error en la respuesta del servidor');
                        });
                    }

                    // Obtener el nombre del archivo del header Content-Disposition
                    const contentDisposition = response.headers.get('Content-Disposition');
                    let fileName = 'reporte_descargado';

                    if (contentDisposition) {
                        const fileNameMatch = contentDisposition.match(/filename="(.+)"/);
                        if (fileNameMatch) {
                            fileName = fileNameMatch[1];
                        }
                    }

                    return response.blob().then(blob => {
                        return {
                            blob,
                            fileName
                        };
                    });
                })
                .then(({
                    blob,
                    fileName
                }) => {
                    // Crear URL para descargar el blob
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    a.download = fileName;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);

                    alert('Reporte generado y descargado exitosamente');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al generar el reporte: ' + error.message);
                })
                .finally(() => {
                    generateBtn.disabled = false;
                    generateBtn.textContent = 'Generar y Descargar Reporte';
                });
        }
    </script>
</body>

</html>
