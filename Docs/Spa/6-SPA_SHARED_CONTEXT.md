# SPA_SHARED_CONTEXT.md

# W4 SPA Bridge

## Sistema de Contexto Compartido

---

# 1. Introducción

El `Shared Context System` es el mecanismo oficial mediante el cual `VoltStack` comparte información global entre backend y frontend SPA.

El objetivo principal es evitar:

```txt id="shctx01"
- Duplicación de estados
- Requests innecesarios
- Configuración repetitiva
- Contextos inconsistentes
- Hydration manual excesiva
```

---

# 2. Filosofía del Sistema

El frontend NO debe solicitar constantemente:

```txt id="shctx02"
- usuario autenticado
- permisos
- locale
- tenant
- configuración pública
```

El backend debe entregar automáticamente:

```txt id="shctx03"
Contexto global serializado.
```

---

# 3. Objetivos

---

# Objetivos principales

```txt id="shctx04"
- Shared runtime state
- Runtime synchronization
- Hydration automática
- Consistencia frontend/backend
- Menor boilerplate
```

---

# Objetivos secundarios

```txt id="shctx05"
- SSR compatibility
- Streaming compatibility
- Multi-tenant compatibility
- Offline compatibility futura
```

---

# 4. Arquitectura General

```txt id="shctx06"
┌──────────────────────────┐
│      Backend Runtime     │
│ User / Tenant / Locale   │
└────────────┬─────────────┘
             │
             │ Shared Context
             │
┌────────────▼─────────────┐
│      SPA Bridge Core     │
│ Context Serialization    │
└────────────┬─────────────┘
             │
             │ Payload
             │
┌────────────▼─────────────┐
│     Frontend Runtime     │
│ Reactive Context Store   │
└──────────────────────────┘
```

---

# 5. Concepto de Shared Context

El Shared Context representa:

```txt id="shctx07"
Estado global compartido.
```

---

# Ejemplos

```txt id="shctx08"
- Auth User
- Tenant
- Locale
- Theme
- Permissions
- Feature Flags
- Navigation Metadata
- Runtime Metadata
```

---

# 6. Estructura Base

---

# Ejemplo

```json id="shctx09"
{
  "context": {
    "auth": {
      "user": {
        "id": 1,
        "name": "Francisco"
      }
    },
    "tenant": {
      "id": 10,
      "name": "Acme"
    },
    "locale": "es_MX"
  }
}
```

---

# 7. Context Lifecycle

Todo contexto compartido sigue este flujo:

```txt id="shctx10"
Backend Runtime
    ↓
Context Providers
    ↓
Context Serializer
    ↓
SpaPayload
    ↓
Frontend Runtime
    ↓
Reactive Store
    ↓
UI Components
```

---

# 8. Context Providers

El sistema debe usar providers desacoplados.

---

# Objetivo

Permitir registrar contexto dinámicamente.

---

# Ejemplo

```php id="shctx11"
class AuthContextProvider
{
    public function handle(): array
    {
        return [
            'auth' => [
                'user' => auth()->user(),
            ],
        ];
    }
}
```

---

# 9. Context Registry

Todos los providers deben registrarse.

---

# Ejemplo

```php id="shctx12"
SpaContext::register(
    AuthContextProvider::class
);
```

---

# Objetivos

```txt id="shctx13"
- Extensibilidad
- Modularidad
- Lazy loading
```

---

# 10. Tipos de Contexto

---

# 10.1 Auth Context

Información del usuario autenticado.

---

# Ejemplo

```json id="shctx14"
{
  "auth": {
    "user": {
      "id": 1,
      "name": "Francisco"
    }
  }
}
```

---

# Uso

```txt id="shctx15"
- User UI
- Permissions
- Navigation
```

---

# 10.2 Tenant Context

Información multitenancy.

---

# Ejemplo

```json id="shctx16"
{
  "tenant": {
    "id": 10,
    "slug": "acme"
  }
}
```

---

# Uso

```txt id="shctx17"
- Tenant isolation
- Tenant branding
```

---

# 10.3 Locale Context

Información regional.

---

# Ejemplo

```json id="shctx18"
{
  "locale": {
    "language": "es",
    "timezone": "America/Mexico_City"
  }
}
```

---

# Uso

```txt id="shctx19"
- i18n
- Formatting
```

---

# 10.4 Theme Context

Configuración visual.

---

# Ejemplo

```json id="shctx20"
{
  "theme": {
    "mode": "dark",
    "palette": "native"
  }
}
```

---

# Uso

```txt id="shctx21"
- Dynamic themes
- Runtime styling
```

---

# 10.5 Permissions Context

Permisos del usuario.

---

# Ejemplo

```json id="shctx22"
{
  "permissions": [
    "users.create",
    "users.update"
  ]
}
```

---

# Uso

```txt id="shctx23"
- UI guards
- Conditional rendering
```

---

# 10.6 Feature Flags Context

Features activas.

---

# Ejemplo

```json id="shctx24"
{
  "features": {
    "ai_assistant": true
  }
}
```

---

# Uso

```txt id="shctx25"
- Experimental features
- Rollouts
```

---

# 10.7 Navigation Context

Información de navegación.

---

# Ejemplo

```json id="shctx26"
{
  "navigation": {
    "current": "/dashboard"
  }
}
```

---

# Uso

```txt id="shctx27"
- Breadcrumbs
- Active links
```

---

# 10.8 Runtime Metadata Context

Metadata runtime.

---

# Ejemplo

```json id="shctx28"
{
  "runtime": {
    "request_id": "abc123",
    "environment": "production"
  }
}
```

---

# Uso

```txt id="shctx29"
- Debugging
- Observability
```

---

# 11. Context Serialization Rules

Todo contexto debe ser serializable.

---

# Permitido

```txt id="shctx30"
- string
- int
- float
- bool
- array
- serializable object
- null
```

---

# No permitido

```txt id="shctx31"
- closures
- DB connections
- streams
- non-serializable objects
```

---

# 12. Lazy Context Loading

El sistema debe soportar lazy loading.

---

# Objetivo

Evitar cargar contexto innecesario.

---

# Ejemplo

```php id="shctx32"
public bool $lazy = true;
```

---

# Uso

```txt id="shctx33"
- Performance
- Large applications
```

---

# 13. Partial Context Updates

El runtime debe soportar updates parciales.

---

# Ejemplo

```json id="shctx34"
{
  "type": "spa.sync",
  "context": {
    "notifications": 5
  }
}
```

---

# Objetivos

```txt id="shctx35"
- Realtime updates
- Reduced traffic
```

---

# 14. Context Synchronization

Frontend y backend deben sincronizar contexto.

---

# Ejemplos

```txt id="shctx36"
- logout
- locale change
- tenant switch
- permission updates
```

---

# 15. Reactive Context Store

El frontend debe mantener contexto reactivo.

---

# Objetivo

Actualizar UI automáticamente.

---

# Ejemplo conceptual

```ts id="shctx37"
spa.context.auth.user
```

---

# 16. Context Isolation

El sistema debe soportar aislamiento.

---

# Tipos

```txt id="shctx38"
- Tenant isolation
- User isolation
- Session isolation
```

---

# Objetivos

```txt id="shctx39"
- Seguridad
- Multi-tenant architecture
```

---

# 17. Context Security

Nunca exponer:

```txt id="shctx40"
- passwords
- private tokens
- secrets
- credentials
```

---

# El contexto debe

```txt id="shctx41"
- Sanitizarse
- Validarse
- Filtrarse
```

---

# 18. Context Middleware

El contexto puede usar middleware.

---

# Ejemplo

```php id="shctx42"
Context::middleware([
    'auth',
    'tenant',
]);
```

---

# Objetivos

```txt id="shctx43"
- Security
- Conditional loading
```

---

# 19. Context Caching

El sistema puede cachear contexto.

---

# Objetivos

```txt id="shctx44"
- Mejor performance
- Menos queries
```

---

# Compatibilidad futura

```txt id="shctx45"
- Redis
- Memory cache
- Edge cache
```

---

# 20. SSR Compatibility

El contexto fue diseñado para SSR.

---

# Compatibilidad futura

```txt id="shctx46"
- Initial hydration
- Streaming SSR
- Edge rendering
```

---

# 21. Offline Compatibility

El runtime podrá persistir contexto.

---

# Ejemplo futuro

```txt id="shctx47"
- IndexedDB
- LocalStorage
- Offline hydration
```

---

# 22. Observability

El contexto debe generar metadata.

---

# Ejemplo

```txt id="shctx48"
- request_id
- tenant_id
- user_id
```

---

# Objetivos

```txt id="shctx49"
- Monitoring
- Debugging
```

---

# 23. Extensibility

El sistema debe permitir:

```txt id="shctx50"
- Custom providers
- Custom serializers
- Custom sync drivers
```

---

# 24. Filosofía del Sistema

El contexto compartido NO es:

```txt id="shctx51"
Un simple objeto global.
```

El contexto compartido es:

```txt id="shctx52"
Un runtime synchronization layer.
```

---

# 25. Objetivo Final

El objetivo final del sistema es permitir:

```txt id="shctx53"
Backend state
+
Frontend runtime
+
Reactive synchronization
+
Shared metadata
+
Enterprise scalability
```

sobre una arquitectura SPA moderna desacoplada.
