# SPA_ROUTING_SYSTEM.md

# W4 SPA Bridge

## Sistema de Routing SPA

---

# 1. Introducción

El sistema de routing de `W4 SPA Bridge` es una capa híbrida diseñada para integrar:

```txt id="r7dwz0"
- Routing backend
- Routing SPA
- Navigation runtime
- SSR routing
- API routing
- Streaming routing
```

sobre una arquitectura desacoplada y extensible.

---

# 2. Filosofía del Routing

El routing NO pertenece exclusivamente al frontend.

El routing NO pertenece exclusivamente al backend.

El routing es:

```txt id="0r9l41"
Un sistema compartido.
```

---

# Objetivos

```txt id="0wnzgo"
- Navegación unificada
- Compatibilidad SSR futura
- Shared metadata
- Navigation lifecycle
- Partial reloads
- Runtime synchronization
```

---

# 3. Arquitectura General

```txt id="t1mj6m"
┌──────────────────────────┐
│      Frontend Runtime    │
│ React / Vue / Svelte     │
└────────────┬─────────────┘
             │
             │ navigate()
             │
┌────────────▼─────────────┐
│      SPA Runtime Layer   │
│ Navigation Orchestrator  │
└────────────┬─────────────┘
             │
             │ request
             │
┌────────────▼─────────────┐
│      VoltStack Router    │
│ Route Resolution         │
└────────────┬─────────────┘
             │
             │
┌────────────▼─────────────┐
│    Controllers/Actions   │
└────────────┬─────────────┘
             │
             │ SpaPayload
             │
┌────────────▼─────────────┐
│      Frontend Adapter    │
└──────────────────────────┘
```

---

# 4. Tipos de Rutas

El sistema soporta múltiples tipos de rutas.

---

# 4.1 SPA Routes

Rutas SPA tradicionales.

---

## Ejemplo

```php id="h91d7f"
Route::spa('/dashboard', DashboardController::class);
```

---

## Respuesta

```json id="b7qklk"
{
  "type": "spa.page",
  "component": "Dashboard/Home"
}
```

---

# Uso

```txt id="2fpfuu"
- Aplicaciones SPA
- Dashboards
- Paneles
```

---

# 4.2 Hybrid Routes

Rutas híbridas backend/frontend.

---

## Objetivo

Permitir:

```txt id="9c5sg0"
HTML tradicional + navegación SPA
```

---

## Ejemplo

```php id="j63tlw"
Route::hybrid('/products', ProductController::class);
```

---

# Uso

```txt id="e80qkq"
- SEO
- E-commerce
- Landing pages
```

---

# 4.3 API Routes

Rutas API tradicionales.

---

## Ejemplo

```php id="0bgg9p"
Route::api('/users', UserApiController::class);
```

---

# Uso

```txt id="jv08lz"
- Mobile apps
- Third-party integrations
```

---

# 4.4 SSR Routes

Rutas Server Side Rendering futuras.

---

## Ejemplo conceptual

```php id="u5xd0q"
Route::ssr('/blog', BlogController::class);
```

---

# Uso

```txt id="7ofx95"
- SEO
- Performance
- Initial render optimization
```

---

# 4.5 Streaming Routes

Rutas streaming progresivas.

---

## Ejemplo conceptual

```php id="3by5g5"
Route::stream('/metrics', MetricsController::class);
```

---

# Uso

```txt id="1u1jq5"
- Live dashboards
- Progressive rendering
```

---

# 5. SPA Route Lifecycle

Toda navegación SPA sigue este ciclo:

```txt id="mjlwmz"
navigate()
    ↓
Runtime Request
    ↓
Backend Router
    ↓
Route Resolution
    ↓
Controller
    ↓
SpaPayload
    ↓
Adapter Resolution
    ↓
Hydration
    ↓
UI Render
```

---

# 6. Route Registration System

---

# Objetivo

Registrar rutas SPA desacopladas.

---

# Ejemplo

```php id="vjz42u"
Route::spa('/users', UserController::class);
```

---

# Internamente

```txt id="06vtwv"
SpaRoute
SpaRouteCollection
SpaRouteRegistrar
SpaRouteResolver
```

---

# 7. Route Metadata

Las rutas deben soportar metadata.

---

# Ejemplo

```php id="1vgz6m"
Route::spa('/dashboard', DashboardController::class)
    ->name('dashboard')
    ->layout('main')
    ->middleware(['auth'])
    ->meta([
        'title' => 'Dashboard',
    ]);
```

---

# Uso

```txt id="xn76m6"
- Layouts
- SEO
- Runtime behavior
- Navigation hints
```

---

# 8. Route Manifest System

El frontend debe conocer las rutas disponibles.

---

# Manifest

```json id="rrt0fi"
{
  "dashboard": "/dashboard",
  "users.index": "/users"
}
```

---

# Objetivos

```txt id="y1pjc5"
- Client navigation
- Typed routes
- Prefetching
```

---

# 9. Catch-All Routing

El sistema puede manejar rutas SPA automáticamente.

---

# Ejemplo

```php id="bwjlwm"
Route::spaFallback();
```

---

# Objetivo

Delegar resolución al runtime SPA.

---

# Uso

```txt id="yduhll"
- SPA completas
- Client-side routing
```

---

# 10. Navigation System

La navegación SPA utiliza un runtime interno.

---

# Ejemplo frontend

```ts id="pcknwm"
spa.navigate('/dashboard');
```

---

# Flujo

```txt id="0mjlwm"
Frontend Runtime
    ↓
Backend Request
    ↓
SpaPayload
    ↓
Hydration
```

---

# 11. Navigation Modes

---

# 11.1 Full Navigation

Carga completa de página.

---

# 11.2 Partial Navigation

Actualización parcial.

---

# 11.3 Background Navigation

Precarga silenciosa.

---

# 11.4 Replace Navigation

Reemplazo de historial.

---

# 11.5 Streaming Navigation

Render progresivo.

---

# 12. Partial Reload System

El runtime debe soportar partial reloads.

---

# Ejemplo

```ts id="o3ndf5"
spa.reload({
    only: ['stats']
});
```

---

# Objetivos

```txt id="b2a7pi"
- Menor tráfico
- Mejor performance
- UI reactiva
```

---

# 13. Route Guards

Las rutas deben soportar guards.

---

# Ejemplo

```php id="fwo3or"
Route::spa('/admin')
    ->guard('admin');
```

---

# Tipos

```txt id="1yoym9"
- auth
- guest
- role
- permission
- tenant
- feature
```

---

# 14. Middleware Integration

Las rutas SPA usan middleware del framework.

---

# Ejemplo

```php id="gvb9d1"
Route::spa('/dashboard')
    ->middleware([
        'auth',
        'tenant',
        'verified',
    ]);
```

---

# 15. Route Context

Cada navegación debe transportar contexto.

---

# Ejemplo

```json id="lpw1jl"
{
  "context": {
    "locale": "es_MX",
    "tenant": "acme"
  }
}
```

---

# Objetivos

```txt id="2g7r0p"
- Runtime sync
- Shared state
```

---

# 16. Route State Preservation

La navegación puede preservar estado.

---

# Ejemplo

```ts id="xzc1iy"
spa.navigate('/users', {
    preserveState: true,
    preserveScroll: true
});
```

---

# Objetivos

```txt id="r8t2nn"
- UX moderna
- Navegación fluida
```

---

# 17. Prefetch System

El runtime puede precargar rutas.

---

# Ejemplo

```ts id="b3g0v4"
spa.prefetch('/dashboard');
```

---

# Beneficios

```txt id="nqk7if"
- Navegación instantánea
- Mejor UX
```

---

# 18. Error Route Handling

Las rutas deben soportar errores SPA.

---

# Ejemplo

```json id="g1vcga"
{
  "type": "spa.error",
  "status": 404
}
```

---

# Uso

```txt id="yzgzk9"
- 404
- 403
- 500
```

---

# 19. Route Transition System

Las rutas pueden definir transiciones.

---

# Ejemplo

```php id="mx9gl0"
Route::spa('/dashboard')
    ->transition('fade');
```

---

# Uso

```txt id="we0qz4"
- Animaciones
- UX
```

---

# 20. Layout Routing System

Las rutas pueden definir layouts.

---

# Ejemplo

```php id="szlcb9"
Route::spa('/dashboard')
    ->layout('admin');
```

---

# Objetivos

```txt id="h2rzzd"
- Layout reuse
- Nested UI
```

---

# 21. Nested Route System

El sistema debe soportar rutas anidadas.

---

# Ejemplo

```txt id="8dxd56"
/dashboard
/dashboard/users
/dashboard/users/create
```

---

# Objetivos

```txt id="gmf7ta"
- Modular navigation
- Nested layouts
```

---

# 22. Runtime Synchronization

El frontend y backend deben sincronizar navegación.

---

# Objetivos

```txt id="e8j22o"
- URL sync
- State sync
- History sync
```

---

# 23. SSR Compatibility

El routing fue diseñado para soportar SSR.

---

# Compatibilidad futura

```txt id="7fc9rt"
- Initial hydration
- Edge rendering
- Streaming SSR
```

---

# 24. Microfrontend Compatibility

El sistema debe soportar:

```txt id="uk8gxm"
- Remote modules
- Federated routing
- Independent apps
```

---

# 25. Route Security

Las rutas deben soportar:

```txt id="mbdwq5"
- Signed routes
- Tenant isolation
- Origin validation
- CSRF
```

---

# 26. Offline Compatibility

El runtime podrá soportar navegación offline futura.

---

# Objetivos

```txt id="s8p3y9"
- Cached routes
- Offline navigation
- Local hydration
```

---

# 27. Filosofía del Sistema

El routing no es:

```txt id="2crlzs"
Solo URLs.
```

El routing es:

```txt id="lzjlwm"
Un sistema completo de navegación runtime.
```

---

# 28. Objetivo Final

El objetivo final del routing SPA es permitir:

```txt id="l7rwzr"
- Navegación moderna
- Render desacoplado
- SSR futuro
- Streaming futuro
- Runtime synchronization
```

sobre una arquitectura universal para frontend modernos.
