<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tienda Deportiva')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

    {{-- Navbar --}}
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('home') }}">
                <i class="bi bi-shirt"></i> Tienda Deportiva
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('catalogo.index') }}">
                            <i class="bi bi-grid me-1"></i>Catálogo
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    @auth
                        {{-- Carrito --}}
                        <li class="nav-item me-2">
                            <a class="nav-link position-relative" href="{{ route('carrito.index') }}">
                                <i class="bi bi-cart3"></i>
                                @php $totalItems = collect(session('carrito', []))->sum('cantidad'); @endphp
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger carrito-badge"
                                      style="{{ $totalItems > 0 ? '' : 'display:none;' }}">
                                    {{ $totalItems ?: '' }}
                                </span>
                        </li>
                        {{-- Mis Pedidos --}}
                        <li class="nav-item me-1">
                            <a class="nav-link" href="{{ route('pedidos.historial') }}">
                                <i class="bi bi-bag-heart me-1"></i>Mis Pedidos
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i>
                                {{ Auth::user()->nombre }} {{ Auth::user()->apellido }}
                                <span class="badge bg-secondary ms-1">{{ Auth::user()->rol }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @if(Auth::user()->esAdmin())
                                    <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Panel Admin</a></li>
                                @endif
                                @if(Auth::user()->esAlmacen())
                                    <li><a class="dropdown-item" href="{{ route('almacen.dashboard') }}"><i class="bi bi-box-seam me-2"></i>Panel Almacén</a></li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right me-1"></i>Iniciar sesión</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('registro') }}"><i class="bi bi-person-plus me-1"></i>Registrarse</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    {{-- Alertas globales --}}
    <div class="container mt-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    {{-- Contenido principal --}}
    <main class="container my-4 flex-grow-1">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-dark text-white text-center py-3 mt-auto">
        <small>&copy; {{ date('Y') }} Tienda Deportiva. Todos los derechos reservados.</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')

    {{-- ══════════════════════════════════════════════════════════
         ASISTENTE IA — visible solo en páginas del catálogo
    ══════════════════════════════════════════════════════════ --}}
    @php
        $rutaActual = request()->route()?->getName() ?? '';
        $mostrarAsistente = str_starts_with($rutaActual, 'catalogo.');
    @endphp

    @if($mostrarAsistente)
    {{-- Botón flotante --}}
    <button id="btnAsistenteCatalogo" type="button"
            style="position:fixed;bottom:28px;right:28px;z-index:1055;
                   width:56px;height:56px;border-radius:50%;
                   background:linear-gradient(135deg,#0ea5e9,#6366f1);
                   border:none;box-shadow:0 4px 18px rgba(99,102,241,.45);
                   display:flex;align-items:center;justify-content:center;
                   cursor:pointer;transition:transform .15s;"
            title="Asistente de compras">
        <i class="bi bi-robot text-white" style="font-size:1.4rem;"></i>
    </button>

    {{-- Modal chat --}}
    <div id="modalAsistenteCatalogo"
         style="display:none;position:fixed;bottom:96px;right:28px;z-index:1054;
                width:360px;max-height:560px;
                background:#fff;border-radius:16px;
                box-shadow:0 8px 32px rgba(0,0,0,.18);
                flex-direction:column;overflow:hidden;">

        {{-- Header --}}
        <div style="background:linear-gradient(135deg,#0ea5e9,#6366f1);padding:14px 16px;
                    display:flex;align-items:center;justify-content:space-between;">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-robot text-white" style="font-size:1.1rem;"></i>
                <div>
                    <div class="text-white fw-semibold" style="font-size:.95rem;line-height:1.2;">Asistente de compras</div>
                    <div style="font-size:.72rem;color:rgba(255,255,255,.75);">Pregúntame por cualquier producto</div>
                </div>
            </div>
            <button id="btnCerrarAsistenteCatalogo" type="button"
                    style="background:none;border:none;color:rgba(255,255,255,.7);
                           font-size:1.2rem;line-height:1;cursor:pointer;padding:0;">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        {{-- Mensajes --}}
        <div id="chatMensajesCatalogo"
             style="flex:1;overflow-y:auto;padding:14px;display:flex;
                    flex-direction:column;gap:10px;max-height:380px;
                    background:#f8fafc;">
            <div style="align-self:flex-start;max-width:92%;">
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px 12px 12px 2px;
                            padding:10px 13px;font-size:.85rem;color:#374151;
                            box-shadow:0 1px 3px rgba(0,0,0,.06);">
                    ¡Hola! Dime qué producto buscas y te digo si hay stock, tallas disponibles y te muestro opciones similares. 🛍️
                </div>
            </div>
        </div>

        {{-- Input --}}
        <div style="padding:10px 12px;border-top:1px solid #e9ecef;background:#fff;display:flex;gap:8px;">
            <input id="chatInputCatalogo" type="text"
                   placeholder="Ej: camiseta del Real Madrid talla M..."
                   style="flex:1;border:1px solid #d1d5db;border-radius:8px;
                          padding:8px 12px;font-size:.85rem;outline:none;
                          transition:border-color .15s;"
                   maxlength="300" autocomplete="off">
            <button id="btnEnviarChatCatalogo" type="button"
                    style="background:linear-gradient(135deg,#0ea5e9,#6366f1);
                           border:none;border-radius:8px;width:38px;height:38px;
                           display:flex;align-items:center;justify-content:center;
                           cursor:pointer;flex-shrink:0;">
                <i class="bi bi-send-fill text-white" style="font-size:.85rem;"></i>
            </button>
        </div>
    </div>

    <script>
    (function () {
        const btnAbrir   = document.getElementById('btnAsistenteCatalogo');
        const btnCerrar  = document.getElementById('btnCerrarAsistenteCatalogo');
        const modal      = document.getElementById('modalAsistenteCatalogo');
        const mensajes   = document.getElementById('chatMensajesCatalogo');
        const input      = document.getElementById('chatInputCatalogo');
        const btnEnviar  = document.getElementById('btnEnviarChatCatalogo');
        const csrfToken  = document.querySelector('meta[name="csrf-token"]').content;
        const urlChat    = "{{ route('catalogo.asistente.chat') }}";

        // Abrir / cerrar
        btnAbrir.addEventListener('click', () => {
            const visible = modal.style.display === 'flex';
            modal.style.display = visible ? 'none' : 'flex';
            modal.style.flexDirection = 'column';
            if (!visible) setTimeout(() => input.focus(), 50);
        });
        btnCerrar.addEventListener('click', () => { modal.style.display = 'none'; });

        // Hover
        btnAbrir.addEventListener('mouseenter', () => btnAbrir.style.transform = 'scale(1.1)');
        btnAbrir.addEventListener('mouseleave', () => btnAbrir.style.transform = 'scale(1)');

        // Focus en input
        input.addEventListener('focus', () => input.style.borderColor = '#6366f1');
        input.addEventListener('blur',  () => input.style.borderColor = '#d1d5db');
        input.addEventListener('keydown', e => { if (e.key === 'Enter') enviar(); });
        btnEnviar.addEventListener('click', enviar);

        // ── Renderizar mensaje ────────────────────────────────────────────────
        function agregarMensaje(texto, tipo, accion) {
            const esBot = tipo === 'bot';
            const wrap  = document.createElement('div');
            wrap.style.cssText = esBot
                ? 'align-self:flex-start;max-width:94%;'
                : 'align-self:flex-end;max-width:94%;';

            const burbuja = document.createElement('div');
            burbuja.style.cssText = esBot
                ? 'background:#fff;border:1px solid #e2e8f0;border-radius:12px 12px 12px 2px;padding:10px 13px;font-size:.85rem;color:#374151;box-shadow:0 1px 3px rgba(0,0,0,.06);'
                : 'background:linear-gradient(135deg,#0ea5e9,#6366f1);border-radius:12px 12px 2px 12px;padding:10px 13px;font-size:.85rem;color:#fff;';

            burbuja.innerHTML = texto.replace(/\n/g, '<br>');
            wrap.appendChild(burbuja);

            if (accion && accion.accion === 'agregar_carrito') {
                // ── Botón "Agregar al carrito" que llama al endpoint JSON ────
                const btnAdd = document.createElement('button');
                btnAdd.innerHTML = '<i class="bi bi-cart-plus me-1"></i>Agregar al carrito';
                btnAdd.style.cssText = 'margin-top:6px;font-size:.78rem;padding:5px 12px;border-radius:6px;border:none;background:linear-gradient(135deg,#16a34a,#0ea5e9);color:#fff;cursor:pointer;display:inline-block;';
                btnAdd.addEventListener('click', async () => {
                    btnAdd.disabled = true;
                    btnAdd.textContent = 'Agregando...';
                    try {
                        const r = await fetch(`/carrito/${accion.id_producto}/asistente`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ id_talla: accion.id_talla, cantidad: accion.cantidad || 1 }),
                        });
                        const d = await r.json();
                        if (d.ok) {
                            btnAdd.textContent = '✓ Agregado';
                            btnAdd.style.background = '#15803d';
                            // Actualizar badge del carrito en navbar
                            const badge = document.querySelector('.carrito-badge');
                            if (badge && d.total_items !== undefined) {
                                badge.textContent = d.total_items;
                                badge.style.display = d.total_items > 0 ? "" : "none";
                            }
                            agregarMensaje(d.mensaje, 'bot');
                        } else {
                            btnAdd.disabled = false;
                            btnAdd.innerHTML = '<i class="bi bi-cart-plus me-1"></i>Agregar al carrito';
                            agregarMensaje('⚠️ ' + d.mensaje, 'bot');
                        }
                    } catch (err) {
                        btnAdd.disabled = false;
                        btnAdd.innerHTML = '<i class="bi bi-cart-plus me-1"></i>Agregar al carrito';
                        agregarMensaje('⚠️ Error de red al agregar. Intenta de nuevo.', 'bot');
                    }
                });
                wrap.appendChild(btnAdd);

                // Enlace "Ver producto" secundario
                if (accion.url) {
                    const btnVer = document.createElement('a');
                    btnVer.href = accion.url;
                    btnVer.innerHTML = '<i class="bi bi-eye me-1"></i>Ver producto';
                    btnVer.style.cssText = 'margin-top:4px;margin-left:6px;font-size:.75rem;padding:4px 10px;border-radius:6px;border:1px solid #6366f1;background:transparent;color:#6366f1;text-decoration:none;display:inline-block;';
                    wrap.appendChild(btnVer);
                }
            } else if (accion && accion.accion === 'ver_producto') {
                // Acción legacy: solo enlace al producto
                if (accion.url) {
                    const btnVer = document.createElement('a');
                    btnVer.href = accion.url;
                    btnVer.innerHTML = '<i class="bi bi-eye me-1"></i>Ver producto y comprar';
                    btnVer.style.cssText = 'margin-top:6px;font-size:.78rem;padding:5px 12px;border-radius:6px;border:none;background:linear-gradient(135deg,#0ea5e9,#6366f1);color:#fff;cursor:pointer;display:inline-block;text-decoration:none;';
                    wrap.appendChild(btnVer);
                }
            }

            // ── Productos similares (para ambas acciones) ─────────────────────
            if (accion && accion.similares && accion.similares.length > 0) {
                const label = document.createElement('div');
                label.textContent = 'También te puede interesar:';
                label.style.cssText = 'font-size:.72rem;color:#6b7280;margin-top:8px;';
                wrap.appendChild(label);

                const simWrap = document.createElement('div');
                simWrap.style.cssText = 'margin-top:4px;display:flex;flex-direction:column;gap:5px;';

                accion.similares.forEach(s => {
                    const chip = document.createElement('a');
                    chip.href = s.url || '#';
                    const stockBadge = s.stock > 0
                        ? `<span style="color:#16a34a;font-size:.7rem;">● ${s.stock} en stock</span>`
                        : `<span style="color:#dc2626;font-size:.7rem;">● Sin stock</span>`;
                    chip.innerHTML = `<i class="bi bi-bag me-1"></i><strong>${s.nombre}</strong> — $${parseFloat(s.precio).toFixed(2)} &nbsp;${stockBadge}`;
                    chip.style.cssText = 'font-size:.78rem;padding:5px 10px;border-radius:8px;border:1px solid #e0e7ff;background:#f0f5ff;color:#3730a3;text-decoration:none;display:block;transition:background .15s;';
                    chip.addEventListener('mouseenter', () => chip.style.background = '#e0e7ff');
                    chip.addEventListener('mouseleave', () => chip.style.background = '#f0f5ff');
                    simWrap.appendChild(chip);
                });
                wrap.appendChild(simWrap);
            }

            mensajes.appendChild(wrap);
            mensajes.scrollTop = mensajes.scrollHeight;
        }


        // ── Typing indicator ─────────────────────────────────────────────────
        function mostrarTyping() {
            const el = document.createElement('div');
            el.id = 'ai-typing';
            el.style.cssText = 'align-self:flex-start;';
            el.innerHTML = `<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px 12px 12px 2px;
                padding:8px 14px;font-size:.8rem;color:#9ca3af;box-shadow:0 1px 3px rgba(0,0,0,.06);">
                Buscando<span id="ai-dots">.</span>
            </div>`;
            mensajes.appendChild(el);
            mensajes.scrollTop = mensajes.scrollHeight;

            // Animación de puntos
            let n = 1;
            el._interval = setInterval(() => {
                const dots = document.getElementById('ai-dots');
                if (dots) dots.textContent = '.'.repeat((n++ % 3) + 1);
            }, 400);
        }

        function ocultarTyping() {
            const el = document.getElementById('ai-typing');
            if (el) { clearInterval(el._interval); el.remove(); }
        }

        // ── Enviar ────────────────────────────────────────────────────────────
        async function enviar() {
            const texto = input.value.trim();
            if (!texto) return;

            agregarMensaje(texto, 'user');
            input.value = '';
            btnEnviar.disabled = true;
            mostrarTyping();

            try {
                const res = await fetch(urlChat, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ mensaje: texto }),
                });

                ocultarTyping();
                const data = await res.json();

                if (data.error) {
                    agregarMensaje('⚠️ ' + data.mensaje, 'bot');
                } else {
                    agregarMensaje(data.mensaje, 'bot', data.accion);
                }
            } catch (e) {
                ocultarTyping();
                agregarMensaje('Error de red. Verifica tu conexión.', 'bot');
            } finally {
                btnEnviar.disabled = false;
                input.focus();
            }
        }
    })();
    </script>
    @endif
</body>
</html>
