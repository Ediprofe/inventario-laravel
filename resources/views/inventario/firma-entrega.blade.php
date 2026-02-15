<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Capturar Firma de Entrega</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            margin: 0;
            background: #0f172a;
            color: #e2e8f0;
        }
        .container {
            max-width: 880px;
            margin: 0 auto;
            padding: 18px 14px 28px;
        }
        .card {
            background: #111827;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 14px;
        }
        h1 {
            margin: 0 0 6px 0;
            font-size: 1.28rem;
        }
        p {
            margin: 0 0 10px 0;
            color: #cbd5e1;
        }
        .meta {
            background: #0b1220;
            border: 1px solid #334155;
            border-radius: 10px;
            padding: 10px;
            margin-bottom: 12px;
        }
        .meta strong {
            color: #f8fafc;
        }
        .signature-box {
            background: #ffffff;
            border-radius: 10px;
            border: 1px dashed #94a3b8;
            padding: 8px;
        }
        #signature-pad {
            width: 100%;
            height: 220px;
            border-radius: 8px;
            touch-action: none;
            display: block;
        }
        .actions {
            margin-top: 12px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        button {
            border: none;
            border-radius: 8px;
            padding: 10px 14px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-primary {
            background: #f59e0b;
            color: #0f172a;
        }
        .btn-secondary {
            background: #334155;
            color: #f8fafc;
        }
        .alert {
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 12px;
            font-size: 0.95rem;
        }
        .alert-success {
            background: #14532d;
            border: 1px solid #22c55e;
            color: #dcfce7;
        }
        .alert-error {
            background: #7f1d1d;
            border: 1px solid #ef4444;
            color: #fee2e2;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>Firma de entrega/verificación</h1>
            <p>Capture aquí la firma en tablet o celular.</p>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-error">{{ $errors->first() }}</div>
            @endif

            <div class="meta">
                <div><strong>Responsable:</strong> {{ $responsable->nombre_completo }}</div>
                @if($responsable->cargo)
                    <div><strong>Cargo:</strong> {{ $responsable->cargo }}</div>
                @endif
            </div>

            <form method="POST" action="{{ $signedPostUrl }}" id="firma-form">
                @csrf
                <input type="hidden" name="firma_data" id="firma_data" value="{{ old('firma_data') }}">

                <div class="signature-box">
                    <canvas id="signature-pad"></canvas>
                </div>

                <div class="actions">
                    <button type="button" class="btn-secondary" id="clear-btn">Limpiar</button>
                    <button type="submit" class="btn-primary">Guardar firma</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const form = document.getElementById('firma-form');
            if (!form) {
                return;
            }

            const canvas = document.getElementById('signature-pad');
            const signatureInput = document.getElementById('firma_data');
            const clearButton = document.getElementById('clear-btn');
            const context = canvas.getContext('2d');

            let isDrawing = false;
            let hasStroke = false;

            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                const rect = canvas.getBoundingClientRect();
                canvas.width = rect.width * ratio;
                canvas.height = rect.height * ratio;
                context.setTransform(1, 0, 0, 1, 0, 0);
                context.scale(ratio, ratio);
                context.lineWidth = 2;
                context.lineCap = 'round';
                context.lineJoin = 'round';
                context.strokeStyle = '#0f172a';
                if (signatureInput.value) {
                    const image = new Image();
                    image.onload = () => {
                        context.drawImage(image, 0, 0, rect.width, rect.height);
                    };
                    image.src = signatureInput.value;
                    hasStroke = true;
                }
            }

            function getPoint(event) {
                const rect = canvas.getBoundingClientRect();
                const clientX = event.touches ? event.touches[0].clientX : event.clientX;
                const clientY = event.touches ? event.touches[0].clientY : event.clientY;
                return {
                    x: clientX - rect.left,
                    y: clientY - rect.top,
                };
            }

            function startDrawing(event) {
                event.preventDefault();
                isDrawing = true;
                const point = getPoint(event);
                context.beginPath();
                context.moveTo(point.x, point.y);
            }

            function draw(event) {
                if (!isDrawing) {
                    return;
                }
                event.preventDefault();
                const point = getPoint(event);
                context.lineTo(point.x, point.y);
                context.stroke();
                hasStroke = true;
            }

            function endDrawing(event) {
                if (!isDrawing) {
                    return;
                }
                event.preventDefault();
                isDrawing = false;
                signatureInput.value = canvas.toDataURL('image/png');
            }

            function clearSignature() {
                context.clearRect(0, 0, canvas.width, canvas.height);
                signatureInput.value = '';
                hasStroke = false;
            }

            canvas.addEventListener('mousedown', startDrawing);
            canvas.addEventListener('mousemove', draw);
            canvas.addEventListener('mouseup', endDrawing);
            canvas.addEventListener('mouseleave', endDrawing);

            canvas.addEventListener('touchstart', startDrawing, { passive: false });
            canvas.addEventListener('touchmove', draw, { passive: false });
            canvas.addEventListener('touchend', endDrawing, { passive: false });
            canvas.addEventListener('touchcancel', endDrawing, { passive: false });

            clearButton.addEventListener('click', clearSignature);

            form.addEventListener('submit', function (event) {
                if (!hasStroke && !signatureInput.value) {
                    event.preventDefault();
                    alert('Debe capturar la firma antes de guardar.');
                    return;
                }
                if (!signatureInput.value) {
                    signatureInput.value = canvas.toDataURL('image/png');
                }
            });

            window.addEventListener('resize', resizeCanvas);
            resizeCanvas();
        })();
    </script>
</body>
</html>
