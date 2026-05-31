# SPA_ARCHITECTURE.md

# W4 SPA Bridge

## Arquitectura Interna del Sistema

---

# 1. Introducción

`W4 SPA Bridge` es una capa arquitectónica diseñada para conectar el núcleo backend de `VoltStack` con cualquier cliente SPA moderno mediante contratos desacoplados.

La arquitectura fue diseñada bajo estos principios:

```txt
- Desacoplamiento total
- Comunicación orientada a contratos
- Extensibilidad modular
- Frontend agnóstico
- Backend agnóstico
- Escalabilidad horizontal
- Compatibilidad SSR futura
- Compatibilidad Streaming futura
```

---

# 2. Arquitectura General

La arquitectura completa del sistema se divide en:

```txt
1. Application Layer
2. VoltStack Core
3. SPA Bridge Core
4. Runtime Protocol Layer
5. Frontend Adapter Layer
6. Frontend Runtime
```

---

# 3. Diagrama General

```txt
┌──────────────────────────────────────┐
│          Frontend Runtime            │
│ React / Vue / Svelte / Solid         │
└──────────────────┬───────────────────┘
                   │
                   │ SPA Protocol
                   │
┌──────────────────▼───────────────────┐
│        Frontend Adapter Layer        │
│ React Adapter / Vue Adapter          │
└──────────────────┬───────────────────┘
                   │
                   │
┌──────────────────▼───────────────────┐
│         Runtime Protocol Layer       │
│ Hydration / Navigation / Sync        │
└──────────────────┬───────────────────┘
                   │
                   │
┌──────────────────▼───────────────────┐
│            SPA Bridge Core           │
│ Payloads / Actions / Context         │
└──────────────────┬───────────────────┘
                   │
                   │
┌──────────────────▼───────────────────┐
│            VoltStack Core            │
│ Router / Kernel / Container          │
└──────────────────┬───────────────────┘
                   │
                   │
┌──────────────────▼───────────────────┐
│          Application Layer           │
│ Controllers / Actions / Services     │
└──────────────────────────────────────┘
```

---

# 4. Capas del Sistema

---

# 4.1 Application Layer

Representa la aplicación desarrollada por el usuario.

Incluye:

```txt
- Controllers
- Actions
- Services
- Modules
- Domain Logic
- Policies
- Repositories
```

Esta capa NO conoce internamente:

```txt
- React
- Vue
- Svelte
```

Solo trabaja con:

```txt
SpaPage
SpaPayload
SpaActionResult
```

---

# 4.2 VoltStack Core

El núcleo del framework.

Responsabilidades:

```txt
- Routing
- Middleware
- Dependency Injection
- HTTP Lifecycle
- Events
- Exception Handling
- Kernel Execution
```

VoltStack Core NO conoce el frontend.

---

# 4.3 SPA Bridge Core

El corazón del paquete.

Responsabilidades:

```txt
- Crear payloads SPA
- Resolver páginas SPA
- Compartir contexto
- Manejar acciones
- Resolver adapters
- Normalizar respuestas
- Generar metadata
```

---

# 4.4 Runtime Protocol Layer

La capa más importante conceptualmente.

Responsabilidades:

```txt
- Navigation
- Hydration
- State synchronization
- Redirects
- Partial reloads
- Streaming
- Patch updates
- Event transport
```

Esta capa define:

```txt
Cómo frontend y backend se comunican.
```

---

# 4.5 Frontend Adapter Layer

Capa que conecta:

```txt
SPA Protocol ↔ Framework frontend
```

Ejemplo:

```txt
React Adapter
Vue Adapter
Svelte Adapter
```

Responsabilidades:

```txt
- Resolver componentes
- Navegar
- Hidratar props
- Manejar layouts
- Renderizar páginas
- Resolver errores
```

---

# 4.6 Frontend Runtime

Es la aplicación SPA real.

Ejemplos:

```txt
- React App
- Vue App
- Svelte App
```

Responsabilidades:

```txt
- Render UI
- Local state
- Effects
- Client routing
- Event handling
```

---

# 5. Arquitectura Interna del SPA Bridge

El núcleo SPA se divide en:

```txt
1. Contracts
2. Core
3. Actions
4. Routing
5. Context
6. Responses
7. Adapters
8. Security
9. Runtime
```

---

# 6. Contracts Layer

Contiene interfaces oficiales.

Objetivo:

```txt
Definir contratos estables.
```

---

## Interfaces principales

```txt
SpaBridgeInterface
SpaPageResolverInterface
SpaResponseFactoryInterface
SpaManifestInterface
SpaAdapterInterface
SpaActionDispatcherInterface
SpaRuntimeInterface
```

---

# 7. Core Layer

Contiene las implementaciones principales.

---

## Clases principales

```txt
SpaBridge
SpaPage
SpaPayload
SpaContext
SpaManifest
SpaMetadata
SpaState
```

---

## Responsabilidades

```txt
- Construcción de payloads
- Shared context
- Metadata generation
- Serialization
- Payload normalization
```

---

# 8. Payload Architecture

Todo el sistema gira alrededor de payloads normalizados.

---

# Estructura base

```json
{
  "type": "spa.page",
  "component": "Users/Index",
  "props": {},
  "meta": {},
  "state": {},
  "errors": {},
  "status": 200
}
```

---

# Objetivos

```txt
- Comunicación consistente
- Serialización universal
- SSR compatibility
- Streaming compatibility
- Adapter interoperability
```

---

# 9. Action System Architecture

El sistema de acciones permite:

```txt
Frontend → Backend execution
```

sin necesidad de APIs tradicionales manuales.

---

# Flujo

```txt
SPA Runtime
    ↓
SPA Action Request
    ↓
Action Dispatcher
    ↓
Application Action
    ↓
SpaActionResult
    ↓
SPA Runtime
```

---

# Componentes

```txt
SpaAction
SpaActionDispatcher
SpaActionRequest
SpaActionResult
SpaActionValidator
```

---

# 10. Routing Architecture

El sistema SPA Routing se integra al router del framework.

---

# Tipos de rutas

```txt
- SPA Routes
- Hybrid Routes
- SSR Routes
- API Routes
- Streaming Routes
```

---

# Flujo

```txt
Request
    ↓
Router
    ↓
Route Resolver
    ↓
Controller
    ↓
SpaPage
    ↓
SpaResponse
```

---

# 11. Shared Context Architecture

El contexto compartido es global para toda la SPA.

---

# Datos compartidos

```txt
- Auth User
- Tenant
- Locale
- Permissions
- Features
- Theme
- Navigation
- Metadata
```

---

# Objetivos

```txt
Evitar duplicación frontend/backend.
```

---

# 12. Adapter Architecture

Cada frontend funciona mediante un adapter oficial.

---

# Arquitectura

```txt
SPA Protocol
      ↓
Frontend Adapter
      ↓
Frontend Runtime
```

---

# Responsabilidades del adapter

```txt
- Resolver páginas
- Resolver layouts
- Hydrate props
- Navigation lifecycle
- Error rendering
- State sync
```

---

# Ejemplo

```txt
@w4/spa-react
@w4/spa-vue
@w4/spa-svelte
```

---

# 13. Runtime Architecture

El runtime es el sistema vivo de comunicación.

---

# Responsabilidades

```txt
- Navigate
- Hydrate
- Reload
- Redirect
- Sync
- Stream
- Patch
```

---

# Conceptos importantes

## Hydration

Reconstrucción frontend desde payload backend.

---

## Patch Updates

Actualizaciones parciales.

---

## Partial Reloads

Actualizar solo partes necesarias.

---

## Streaming

Render progresivo futuro.

---

# 14. Response Architecture

Todas las respuestas SPA son normalizadas.

---

# Tipos principales

```txt
spa.page
spa.action
spa.error
spa.validation
spa.redirect
spa.stream
```

---

# Objetivos

```txt
- Predictability
- Serialization
- Extensibility
```

---

# 15. Error Handling Architecture

Los errores deben ser serializados.

---

# Ejemplo

```json
{
  "type": "spa.error",
  "message": "Unauthorized",
  "status": 401
}
```

---

# Beneficios

```txt
- Frontend consistente
- Manejo centralizado
- SSR compatibility
```

---

# 16. Security Architecture

El sistema debe incluir:

```txt
- CSRF protection
- SPA tokens
- Origin validation
- Payload sanitization
- Signed actions
- Tenant isolation
```

---

# 17. Serialization Architecture

Todos los payloads deben ser serializables.

---

# Compatibilidad requerida

```txt
- JSON
- Streaming JSON
- Binary payloads futuros
- SSR hydration
```

---

# 18. Extensibility Architecture

El sistema debe permitir:

```txt
- Custom adapters
- Custom payload types
- Custom actions
- Custom serializers
- Custom protocols
```

---

# 19. Future Architecture

La arquitectura fue diseñada para soportar:

---

## SSR

```txt
Server Side Rendering
```

---

## Edge Rendering

```txt
Cloudflare Workers
Deno
Bun
```

---

## Islands Architecture

---

## Streaming UI

---

## Real-Time Synchronization

---

## Server Components

---

# 20. Arquitectura Filosófica

W4 SPA Bridge NO es:

```txt
Un framework frontend.
```

W4 SPA Bridge es:

```txt
Una capa universal de integración SPA.
```

---

# 21. Objetivo Final

El objetivo final de la arquitectura es permitir:

```txt
Backend PHP moderno
+
Frontend moderno
+
Comunicación desacoplada
+
Escalabilidad empresarial
```

sin acoplar el framework a tecnologías frontend específicas.
