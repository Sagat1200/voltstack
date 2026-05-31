# SPA_ACTION_SYSTEM.md

# W4 SPA Bridge

## Sistema de Acciones SPA

---

# 1. Introducción

El `SPA Action System` es el mecanismo oficial mediante el cual el frontend interactúa con lógica backend dentro de `VoltStack`.

El sistema fue diseñado para reemplazar la necesidad de:

```txt id="yljlwm"
- APIs manuales repetitivas
- Controllers CRUD excesivos
- Endpoints ad-hoc
- Boilerplate frontend/backend
```

mediante una arquitectura basada en:

```txt id="vmp0m4"
Actions desacopladas y serializables.
```

---

# 2. Filosofía del Sistema

El frontend NO llama controllers directamente.

El frontend ejecuta:

```txt id="2r4z7w"
Acciones.
```

El backend NO expone lógica de negocio arbitraria.

El backend expone:

```txt id="hbm6z3"
Contratos de ejecución.
```

---

# 3. Objetivos

---

# Objetivos principales

```txt id="8fjlwm"
- Simplificar comunicación frontend/backend
- Eliminar boilerplate CRUD
- Estandarizar mutaciones
- Mejorar DX
- Soportar async execution
- Facilitar SSR y streaming futuros
```

---

# Objetivos secundarios

```txt id="txqqj8"
- Typed actions
- Runtime validation
- Queue compatibility
- Transaction support
- Event integration
- Realtime compatibility
```

---

# 4. Arquitectura General

```txt id="nvjlwm"
┌──────────────────────────┐
│      Frontend Runtime    │
└────────────┬─────────────┘
             │
             │ execute()
             │
┌────────────▼─────────────┐
│       SPA Runtime        │
│ Action Transport Layer   │
└────────────┬─────────────┘
             │
             │ payload
             │
┌────────────▼─────────────┐
│   SPA Action Dispatcher  │
└────────────┬─────────────┘
             │
             │ resolve
             │
┌────────────▼─────────────┐
│       Application        │
│        Actions           │
└────────────┬─────────────┘
             │
             │ result
             │
┌────────────▼─────────────┐
│     SpaActionResult      │
└──────────────────────────┘
```

---

# 5. Concepto de Acción

Una acción representa:

```txt id="39g4qy"
Una operación backend ejecutable.
```

---

# Ejemplos

```txt id="qjlwmz"
CreateUserAction
UpdateProfileAction
DeleteInvoiceAction
GenerateReportAction
UploadFileAction
```

---

# 6. Características Principales

---

# 6.1 Desacopladas

Las acciones no dependen de:

```txt id="t4yphu"
- React
- Vue
- Controllers
- HTTP específico
```

---

# 6.2 Serializables

Toda ejecución debe generar payloads serializables.

---

# 6.3 Tipadas

Las acciones deben tener:

```txt id="6djlwm"
- input contracts
- validation contracts
- result contracts
```

---

# 6.4 Reutilizables

Una acción puede ejecutarse desde:

```txt id="rlf60m"
- SPA
- CLI
- Queue
- Scheduler
- Webhooks
- APIs
```

---

# 7. Estructura Base

---

# Clase base

```php id="q5sljk"
abstract class SpaAction
{
    abstract public function handle(
        SpaActionRequest $request
    ): SpaActionResult;
}
```

---

# Ejemplo

```php id="a4gxjp"
class CreateUserAction extends SpaAction
{
    public function handle(
        SpaActionRequest $request
    ): SpaActionResult {

        return SpaActionResult::success([
            'message' => 'Usuario creado',
        ]);
    }
}
```

---

# 8. Action Lifecycle

Toda acción sigue este flujo:

```txt id="2tjlwm"
Frontend Runtime
    ↓
Action Payload
    ↓
Transport Layer
    ↓
Action Dispatcher
    ↓
Validation
    ↓
Authorization
    ↓
Execution
    ↓
Serialization
    ↓
SpaActionResult
    ↓
Frontend Runtime
```

---

# 9. Action Request System

Las acciones reciben un request tipado.

---

# Ejemplo

```php id="zhkrwp"
SpaActionRequest
```

---

# Responsabilidades

```txt id="ojlwmz"
- payload access
- user access
- tenant access
- files
- metadata
- runtime info
```

---

# Ejemplo

```php id="jv3m5p"
$request->input('email');
$request->user();
$request->tenant();
```

---

# 10. Action Result System

Toda acción devuelve:

```php id="bjp6xf"
SpaActionResult
```

---

# Objetivos

```txt id="f7jlwm"
- Responses consistentes
- Runtime compatibility
- Serialization
```

---

# Ejemplo

```php id="ps5n5l"
SpaActionResult::success([
    'user' => $user,
]);
```

---

# Resultado JSON

```json id="z4t4ws"
{
  "type": "spa.action",
  "success": true,
  "data": {}
}
```

---

# 11. Action Dispatcher

El dispatcher resuelve y ejecuta acciones.

---

# Responsabilidades

```txt id="jlwmz9"
- Resolve action
- Validation
- Authorization
- Dependency injection
- Exception handling
- Serialization
```

---

# Componentes

```txt id="a3jlwm"
SpaActionDispatcher
SpaActionResolver
SpaActionRegistry
```

---

# 12. Action Registry

El sistema puede registrar acciones automáticamente.

---

# Ejemplo

```php id="jlwmq1"
Action::register(
    'users.create',
    CreateUserAction::class
);
```

---

# Objetivos

```txt id="jlwmq2"
- Discovery
- Runtime resolution
- Typed actions
```

---

# 13. Validation System

Las acciones deben validar entrada.

---

# Ejemplo

```php id="jlwmq3"
public function rules(): array
{
    return [
        'email' => ['required', 'email'],
    ];
}
```

---

# Resultado

```json id="jlwmq4"
{
  "type": "spa.validation",
  "errors": {
    "email": [
      "El email es requerido"
    ]
  }
}
```

---

# 14. Authorization System

Las acciones deben soportar autorización.

---

# Ejemplo

```php id="jlwmq5"
public function authorize(): bool
{
    return auth()->user()->isAdmin();
}
```

---

# Objetivos

```txt id="jlwmq6"
- Security
- Permission isolation
- Tenant isolation
```

---

# 15. Async Actions

El sistema debe soportar acciones asíncronas.

---

# Ejemplo

```php id="jlwmq7"
class GenerateReportAction extends AsyncSpaAction
{
}
```

---

# Uso

```txt id="jlwmq8"
- Reportes
- Exportaciones
- IA
- Procesos largos
```

---

# 16. Queue Integration

Las acciones async deben integrarse con queues.

---

# Compatibilidad

```txt id="jlwmq9"
- Redis
- SQS
- RabbitMQ
- Kafka futuro
```

---

# 17. Transaction Support

Las acciones pueden ejecutarse en transacciones.

---

# Ejemplo

```php id="jlwmqa"
public bool $transactional = true;
```

---

# Objetivos

```txt id="jlwmqb"
- Integridad
- Consistencia
```

---

# 18. Event Integration

Las acciones pueden emitir eventos runtime.

---

# Ejemplo

```php id="jlwmqc"
return SpaActionResult::success()
    ->event('toast.show', [
        'message' => 'Guardado',
    ]);
```

---

# Uso

```txt id="jlwmqd"
- Toasts
- Notifications
- Live updates
```

---

# 19. File Upload Actions

Las acciones deben soportar archivos.

---

# Ejemplo

```php id="jlwmqe"
$request->file('avatar');
```

---

# Compatibilidad futura

```txt id="jlwmqf"
- chunk upload
- streaming upload
- resumable upload
```

---

# 20. Partial Update Support

Las acciones pueden devolver updates parciales.

---

# Ejemplo

```php id="jlwmqg"
return SpaActionResult::patch([
    'stats.users' => 180,
]);
```

---

# Objetivos

```txt id="jlwmqh"
- Mejor performance
- UI reactiva
```

---

# 21. Action State Synchronization

Las acciones pueden sincronizar estado runtime.

---

# Ejemplo

```php id="ileswiqi"
return SpaActionResult::sync([
    'notifications' => 4,
]);
```

---

# Uso

```txt id="’winiqj"
- Shared state
- Multi-tab sync
```

---

# 22. Streaming Actions

Las acciones podrán soportar streaming futuro.

---

# Ejemplo conceptual

```php id="ịtịqk"
return SpaActionResult::stream();
```

---

# Uso

```txt id="5p8gq2"
- IA
- Logs en vivo
- Exportaciones progresivas
```

---

# 23. Action Metadata

Las acciones pueden definir metadata.

---

# Ejemplo

```php id="0e2l3n"
public function meta(): array
{
    return [
        'name' => 'Create User',
        'icon' => 'users',
    ];
}
```

---

# Uso

```txt id="jlwmql"
- Developer tools
- Runtime inspector
```

---

# 24. Action Middleware

Las acciones soportan middleware.

---

# Ejemplo

```php id="jlwmqm"
public function middleware(): array
{
    return [
        'auth',
        'tenant',
    ];
}
```

---

# Objetivos

```txt id="jlwmqn"
- Shared logic
- Security
```

---

# 25. Retry System

Las acciones async deben soportar retries.

---

# Ejemplo

```php id="jlwmqo"
public int $tries = 3;
```

---

# 26. Rate Limiting

Las acciones pueden limitar ejecución.

---

# Ejemplo

```php id="jlwmqp"
public int $rateLimit = 10;
```

---

# Objetivos

```txt id="jlwmqq"
- Protección
- Abuse prevention
```

---

# 27. Observability

Toda acción debe generar telemetry.

---

# Datos mínimos

```txt id="jlwmqr"
- request_id
- action_name
- execution_time
- memory_usage
- user_id
```

---

# Objetivos

```txt id="jlwmqs"
- Monitoring
- Debugging
- Metrics
```

---

# 28. Error Handling

Las excepciones deben serializarse.

---

# Ejemplo

```json id="jlwmqt"
{
  "type": "spa.error",
  "status": 500,
  "message": "Unexpected error"
}
```

---

# Objetivos

```txt id="jlwmqu"
- Predictibilidad
- Runtime consistency
```

---

# 29. Security Architecture

Las acciones deben soportar:

```txt id="jlwmqv"
- CSRF
- Signed payloads
- Tenant isolation
- Authorization
- Origin validation
```

---

# 30. Future Compatibility

El sistema fue diseñado para soportar:

---

## Server Components

---

## Realtime Sync

---

## AI Actions

---

## Distributed Execution

---

## Edge Actions

---

## Streaming Responses

---

# 31. Filosofía del Sistema

Una acción NO es:

```txt id="jlwmqw"
Un endpoint CRUD.
```

Una acción es:

```txt id="jlwmqx"
Una unidad ejecutable de negocio.
```

---

# 32. Objetivo Final

El objetivo final del sistema es permitir:

```txt id="jlwmqy"
Frontend moderno
+
Backend desacoplado
+
Lógica reutilizable
+
Mutaciones tipadas
+
Escalabilidad empresarial
```

sin depender de APIs CRUD tradicionales.
