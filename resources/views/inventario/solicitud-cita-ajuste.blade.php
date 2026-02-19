<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar cita de ajuste de inventario</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            max-width: 700px;
            width: 100%;
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        .header {
            background: #0f172a;
            color: #f8fafc;
            padding: 22px 26px;
        }
        .header h1 {
            font-size: 22px;
            margin-bottom: 4px;
        }
        .header p {
            font-size: 13px;
            color: #cbd5e1;
        }
        .body {
            padding: 24px 26px 26px;
        }
        .alert {
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 14px;
            margin-bottom: 14px;
            border: 1px solid;
        }
        .alert-success {
            background: #dcfce7;
            border-color: #86efac;
            color: #166534;
        }
        .alert-error {
            background: #fee2e2;
            border-color: #fca5a5;
            color: #991b1b;
        }
        .box {
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 14px;
            margin-bottom: 16px;
            line-height: 1.65;
            color: #0f172a;
        }
        .note {
            border: 1px solid #fdba74;
            background: #fff7ed;
            color: #9a3412;
        }
        .group {
            margin-bottom: 14px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
            font-weight: 600;
            color: #334155;
        }
        input, select, textarea {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 14px;
            font-family: inherit;
        }
        textarea {
            resize: vertical;
            min-height: 130px;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }
        .checkbox {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 13px;
            color: #334155;
            line-height: 1.5;
        }
        .checkbox input {
            width: 18px;
            margin-top: 2px;
            flex-shrink: 0;
        }
        .error {
            color: #b91c1c;
            font-size: 12px;
            margin-top: 4px;
        }
        .btn {
            width: 100%;
            border: none;
            border-radius: 10px;
            padding: 13px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 4px;
        }
        .readonly-input {
            background: #f1f5f9;
            color: #334155;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>Solicitar cita de ajuste</h1>
            <p>{{ config('institucion.nombre', 'Institución Educativa') }}</p>
        </div>
        <div class="body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-error">{{ $errors->first() }}</div>
            @endif

            <div class="box">
                <div><strong>Código envío:</strong> {{ $envio->codigo_envio }}</div>
                <div><strong>Responsable:</strong> {{ $responsable->nombre_completo }}</div>
                @if($ubicacion)
                    <div><strong>Ubicación:</strong> {{ $ubicacion->codigo }} - {{ $ubicacion->nombre }}</div>
                @endif
                <div><strong>Tipo reporte:</strong> {{ $envio->tipo === 'por_ubicacion' ? 'Inventario por ubicación' : 'Inventario por responsable' }}</div>
            </div>

            <div class="box note">
                Esta solicitud es para coordinar una cita de ajuste con el administrador de inventario. No reemplaza los cambios directos en sistema.
            </div>

            <form action="{{ route('inventario.cita-ajuste.guardar', ['token' => $envio->token]) }}" method="POST">
                @csrf

                <div class="group">
                    <label for="solicitante_nombre">Nombre de quien solicita</label>
                    <input id="solicitante_nombre" name="solicitante_nombre" type="text" value="{{ old('solicitante_nombre', $responsable->nombre_completo) }}">
                    @error('solicitante_nombre')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="group">
                    <label for="tipo_solicitud">Tipo de ajuste</label>
                    <select id="tipo_solicitud" name="tipo_solicitud">
                        <option value="ajuste_general" @selected(old('tipo_solicitud') === 'ajuste_general')>Ajuste general</option>
                        <option value="entrada_items" @selected(old('tipo_solicitud') === 'entrada_items')>Entrada de ítems</option>
                        <option value="salida_items" @selected(old('tipo_solicitud') === 'salida_items')>Salida de ítems</option>
                        <option value="baja_items" @selected(old('tipo_solicitud') === 'baja_items')>Baja de ítems</option>
                        <option value="mantenimiento" @selected(old('tipo_solicitud') === 'mantenimiento')>Mantenimiento</option>
                        <option value="otro" @selected(old('tipo_solicitud') === 'otro')>Otro</option>
                    </select>
                    @error('tipo_solicitud')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="group">
                    <label for="medio_contacto">Medio de contacto</label>
                    <select id="medio_contacto" name="medio_contacto">
                        <option value="">Seleccione...</option>
                        <option value="whatsapp" @selected(old('medio_contacto') === 'whatsapp')>WhatsApp (registrado)</option>
                        <option value="correo" @selected(old('medio_contacto') === 'correo')>Correo (registrado)</option>
                    </select>
                    @error('medio_contacto')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="group" id="grupo_contacto_auto" style="display:none;">
                    <label for="contacto_auto_preview">Contacto usado automáticamente</label>
                    <input id="contacto_auto_preview" type="text" class="readonly-input" readonly>
                </div>

                <div class="group" id="grupo_whatsapp_manual" style="display:none;">
                    <label for="whatsapp_manual">WhatsApp de contacto</label>
                    <input id="whatsapp_manual" name="whatsapp_manual" type="text" value="{{ old('whatsapp_manual') }}" placeholder="Ejemplo: 3001234567">
                    @error('whatsapp_manual')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="group">
                    <label for="franja_horaria">Franja horaria sugerida (opcional)</label>
                    <input id="franja_horaria" name="franja_horaria" type="text" value="{{ old('franja_horaria') }}" placeholder="Ejemplo: Martes 9:00 - 10:00 am">
                    @error('franja_horaria')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="group">
                    <label for="detalle">Detalle de la solicitud</label>
                    <textarea id="detalle" name="detalle" placeholder="Describa qué ajustes necesita revisar con el administrador...">{{ old('detalle') }}</textarea>
                    @error('detalle')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="group">
                    <label class="checkbox">
                        <input type="checkbox" name="confirmado_coordinacion" value="1" @checked(old('confirmado_coordinacion'))>
                        <span>Confirmo que esta solicitud corresponde a ajustes ya verificados en sitio y acordados con coordinación de inventario.</span>
                    </label>
                    @error('confirmado_coordinacion')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn">Enviar solicitud de cita</button>
            </form>
        </div>
    </div>
    <script>
        (function () {
            const select = document.getElementById('medio_contacto');
            if (!select) {
                return;
            }

            const grupoAuto = document.getElementById('grupo_contacto_auto');
            const grupoWhatsappManual = document.getElementById('grupo_whatsapp_manual');
            const inputAuto = document.getElementById('contacto_auto_preview');

            const responsableWhatsapp = @json((string) ($responsable->telefono ?? ''));
            const responsableCorreo = @json((string) ($responsable->email ?? ''));

            function updateContactoUi() {
                const value = select.value;

                grupoAuto.style.display = 'none';
                grupoWhatsappManual.style.display = 'none';
                inputAuto.value = '';

                if (value === 'whatsapp') {
                    if (responsableWhatsapp !== '') {
                        grupoAuto.style.display = 'block';
                        inputAuto.value = responsableWhatsapp;
                    } else {
                        grupoWhatsappManual.style.display = 'block';
                    }
                }

                if (value === 'correo') {
                    grupoAuto.style.display = 'block';
                    inputAuto.value = responsableCorreo !== '' ? responsableCorreo : 'No hay correo registrado. Seleccione WhatsApp.';
                }
            }

            select.addEventListener('change', updateContactoUi);
            updateContactoUi();
        })();
    </script>
</body>
</html>
