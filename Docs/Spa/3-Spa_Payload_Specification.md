# SPA_PAYLOAD_SPECIFICATION.md

# W4 SPA Bridge

## Especificación Oficial de Payloads

---

# 1. Introducción

El sistema `W4 SPA Bridge` utiliza un protocolo de payloads normalizados para toda comunicación entre backend y frontend.

Todo intercambio de información debe realizarse mediante estructuras serializables compatibles con:

```txt id="m79d3e"
- JSON
- Streaming JSON
- SSR Hydration
- Edge Rendering
- Partial Updates
- Real-Time Sync
```

---

# 2. Objetivos del Payload System

El sistema de payloads busca:

```txt id="l9b8l9"
- Estandarización
- Compatibilidad universal
- Desacoplamiento
- Serialización segura
- Predictibilidad
- Extensibilidad
```

---

# 3. Filosofía del Protocolo

El frontend NO consume HTML.

El frontend consume:

```txt id="x1om2x"
Payloads estructurados.
```

El backend NO renderiza componentes frontend.

El backend entrega:

```txt id="4k5h3u"
Estado + metadata + instrucciones.
```

---

# 4. Estructura Base Universal

Todo payload debe seguir esta estructura:

```json id="b08j7h"
{
  "type": "spa.page",
  "version": "1.0.0",
  "request_id": "req_8af1d",
  "timestamp": 1778500000,
  "status": 200,
  "success": true,
  "component": null,
  "props": {},
  "state": {},
  "meta": {},
  "errors": {},
  "redirect": null,
  "partials": [],
  "events": [],
  "context": {}
}
```

---

# 5. Campos Base

---

# type

Tipo de payload.

---

## Ejemplos

```txt id="r09v5v"
spa.page
spa.action
spa.error
spa.validation
spa.redirect
spa.stream
spa.patch
spa.sync
```

---

# version

Versión del protocolo.

---

# request_id

Identificador único de request.

Objetivos:

```txt id="4t4r7e"
- tracing
- debugging
- logging
- observability
```

---

# timestamp

Timestamp UNIX del payload.

---

# status

Código HTTP lógico.

Ejemplos:

```txt id="6d80yn"
200
201
401
403
404
422
500
```

---

# success

Resultado lógico del payload.

---

# component

Componente SPA objetivo.

Ejemplo:

```txt id="gzhtht"
Users/Index
Dashboard/Home
Auth/Login
```

---

# props

Datos serializables enviados al frontend.

---

# state

Estado reactivo inicial.

---

# meta

Metadata de navegación/render.

---

# errors

Errores serializados.

---

# redirect

Información de redirección.

---

# partials

Actualizaciones parciales.

---

# events

Eventos runtime.

---

# context

Contexto global compartido.

---

# 6. Tipos Oficiales de Payload

---

# 6.1 spa.page

Representa una página SPA completa.

---

## Ejemplo

```json id="r5o03k"
{
  "type": "spa.page",
  "component": "Dashboard/Home",
  "props": {
    "title": "Dashboard",
    "stats": {
      "users": 150
    }
  },
  "meta": {
    "layout": "main"
  }
}
```

---

# Uso

```txt id="s58e6f"
- Navegación
- Render principal
- Hydration
```

---

# 6.2 spa.action

Resultado de acción backend.

---

## Ejemplo

```json id="4vmhmy"
{
  "type": "spa.action",
  "success": true,
  "message": "Usuario creado",
  "data": {
    "id": 1
  }
}
```

---

# Uso

```txt id="ttrg79"
- Forms
- Commands
- Backend mutations
```

---

# 6.3 spa.validation

Errores de validación.

---

## Ejemplo

```json id="gjh63v"
{
  "type": "spa.validation",
  "status": 422,
  "errors": {
    "email": [
      "El email es requerido"
    ]
  }
}
```

---

# Uso

```txt id="i4m2i7"
- Formularios
- Validation pipelines
```

---

# 6.4 spa.error

Errores generales.

---

## Ejemplo

```json id="odjxsi"
{
  "type": "spa.error",
  "status": 500,
  "message": "Internal Server Error"
}
```

---

# Uso

```txt id="ns9x0f"
- Exceptions
- Runtime failures
```

---

# 6.5 spa.redirect

Redirección frontend.

---

## Ejemplo

```json id="4qcz40"
{
  "type": "spa.redirect",
  "redirect": {
    "to": "/login",
    "replace": true
  }
}
```

---

# Uso

```txt id="o4obgd"
- Auth redirects
- Navigation
```

---

# 6.6 spa.patch

Actualización parcial.

---

## Ejemplo

```json id="66p91r"
{
  "type": "spa.patch",
  "partials": [
    {
      "path": "stats.users",
      "value": 180
    }
  ]
}
```

---

# Uso

```txt id="9m9e8m"
- Reactive updates
- Partial hydration
```

---

# 6.7 spa.stream

Streaming progresivo.

---

## Ejemplo

```json id="ow8yye"
{
  "type": "spa.stream",
  "stream": {
    "channel": "dashboard.metrics",
    "payload": {}
  }
}
```

---

# Uso

```txt id="t3xb2k"
- Streaming UI
- Progressive rendering
- Realtime
```

---

# 6.8 spa.sync

Sincronización de estado.

---

## Ejemplo

```json id="l03tmo"
{
  "type": "spa.sync",
  "state": {
    "notifications": 5
  }
}
```

---

# Uso

```txt id="g8z5ow"
- Shared state sync
- Multi-tab sync
- Live synchronization
```

---

# 7. Payload Lifecycle

Todo payload sigue este flujo:

```txt id="6x7vnp"
Backend
   ↓
Serializer
   ↓
Payload Builder
   ↓
Transport Layer
   ↓
Frontend Runtime
   ↓
Adapter
   ↓
UI Render
```

---

# 8. Payload Serialization Rules

---

# Reglas generales

---

## Debe ser serializable

Permitido:

```txt id="thmx58"
- string
- int
- float
- bool
- array
- object serializable
- null
```

---

## No permitido

```txt id="20n79h"
- closures
- resources
- streams PHP nativos
- conexiones DB
- objetos no serializables
```

---

# Objetivo

Compatibilidad universal.

---

# 9. Component Resolution

El frontend debe resolver:

```txt id="0swhsw"
component
```

como:

```txt id="p20m5m"
Pages/Dashboard/Home.tsx
Pages/Auth/Login.vue
```

dependiendo del adapter.

---

# 10. Props Specification

---

# Objetivo

Transportar datos serializables.

---

# Reglas

```txt id="a1h20g"
- Inmutables
- Serializables
- Predictibles
```

---

# Ejemplo

```json id="j1bl2k"
{
  "props": {
    "user": {
      "id": 1,
      "name": "Francisco"
    }
  }
}
```

---

# 11. State Specification

El campo `state` contiene estado reactivo inicial.

---

# Ejemplo

```json id="lkj4l9"
{
  "state": {
    "sidebar": {
      "collapsed": false
    }
  }
}
```

---

# Objetivos

```txt id="s05z7v"
- Hydration
- Shared state
- Runtime sync
```

---

# 12. Meta Specification

El campo `meta` contiene metadata de runtime.

---

# Ejemplo

```json id="l0vhx2"
{
  "meta": {
    "layout": "main",
    "title": "Dashboard",
    "locale": "es_MX"
  }
}
```

---

# Uso

```txt id="95l6tm"
- SEO
- Layouts
- Runtime behavior
- Navigation metadata
```

---

# 13. Error Specification

Los errores deben ser normalizados.

---

# Ejemplo

```json id="rm7t3l"
{
  "errors": {
    "email": [
      "El email es inválido"
    ]
  }
}
```

---

# Objetivos

```txt id="2w6q3w"
- Predictibilidad
- Frontend consistency
```

---

# 14. Partial Update Specification

Los partials representan actualizaciones mínimas.

---

# Ejemplo

```json id="mx1k2u"
{
  "partials": [
    {
      "path": "stats.sales",
      "value": 4500
    }
  ]
}
```

---

# Beneficios

```txt id="p9f0ff"
- Menor tráfico
- Mejor performance
- UI reactiva
```

---

# 15. Event Specification

El campo `events` contiene eventos runtime.

---

# Ejemplo

```json id="8wuh9x"
{
  "events": [
    {
      "name": "toast.show",
      "payload": {
        "message": "Guardado correctamente"
      }
    }
  ]
}
```

---

# Uso

```txt id="ztdz90"
- Toasts
- Notifications
- Runtime hooks
```

---

# 16. Context Specification

El contexto es compartido globalmente.

---

# Ejemplo

```json id="2m4ny8"
{
  "context": {
    "auth": {
      "user": {
        "id": 1
      }
    }
  }
}
```

---

# Objetivos

```txt id="94f12i"
- Shared runtime data
- Auth sync
- Tenant sync
```

---

# 17. Security Rules

Todos los payloads deben:

```txt id="4f6qoz"
- Validarse
- Sanitizarse
- Firmarse opcionalmente
```

---

# Nunca incluir

```txt id="3vj3x7"
- Secrets
- Tokens privados
- Credenciales
- Objetos sensibles
```

---

# 18. Payload Compression

El protocolo debe soportar:

```txt id="trc6i4"
- gzip
- brotli
- binary transport futuro
```

---

# 19. Versioning Strategy

Todo payload debe ser compatible por versión.

---

# Ejemplo

```txt id="sr4xq8"
1.x compatible
2.x breaking changes
```

---

# 20. Future Compatibility

La especificación fue diseñada para soportar:

---

## SSR

---

## Streaming

---

## Realtime Sync

---

## Edge Rendering

---

## Server Components

---

## Offline State

---

# 21. Payload Philosophy

El payload es:

```txt id="mqj7pq"
La unidad universal de comunicación.
```

El frontend renderiza:

```txt id="d7ox8p"
Payloads.
```

El backend produce:

```txt id="4jjlwm"
Payloads.
```

Todo el sistema SPA gira alrededor de esta especificación.
