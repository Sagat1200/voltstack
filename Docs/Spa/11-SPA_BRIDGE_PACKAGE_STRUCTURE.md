# SPA_BRIDGE_PACKAGE_STRUCTURE.md

# W4 SPA Bridge

## Estructura del Paquete `Quantum\SpaBridge`

---

# 1. Objetivo

Este documento define la estructura exacta propuesta para el paquete:

```txt
Quantum\SpaBridge
```

Su proposito es fijar:

- carpetas
- clases base
- interfaces
- responsabilidades
- dependencias permitidas
- orden de implementacion

antes de escribir la implementacion real.

---

# 2. Alcance del Paquete

`Quantum\SpaBridge` sera la capa SPA oficial integrada en el framework base.

Debe encargarse de:

- contratos SPA
- payloads estandarizados
- respuestas SPA
- shared context
- resolucion de componentes
- metadata de navegacion
- integracion con HTTP, routing y controllers
- soporte base para adapters frontend
- preparar compatibilidad futura con SSR

No debe encargarse todavia de:

- runtime reactivo server-driven
- patch diffing avanzado
- event bus reactivo completo
- partial updates complejos
- live component lifecycle
- realtime sync runtime

Todo eso pertenece despues a:

```txt
Quantum\LiveUi
```

en otro repositorio.

---

# 3. Principio Arquitectonico

La arquitectura base recomendada es:

```txt
Controllers / Actions
    ↓
SpaBridge facade / responder
    ↓
Spa payload contracts
    ↓
HTTP response normalization
    ↓
Frontend adapter contract
```

El backend nunca debe conocer React, Vue o Svelte directamente.

Debe conocer solo:

- contratos
- payloads
- adapters
- manifests

---

# 4. Arbol Base Propuesto

```txt
src/
  Quantum/
    SpaBridge/
      Adapters/
        Contracts/
          FrontendAdapterInterface.php
          ComponentResolverInterface.php
          AssetManifestInterface.php
        NullFrontendAdapter.php
      Context/
        Contracts/
          SharedContextProviderInterface.php
          SharedContextRegistryInterface.php
          SharedContextResolverInterface.php
        SharedContextRegistry.php
        SharedContextResolver.php
      Contracts/
        SpaBridgeInterface.php
        SpaPayloadInterface.php
        SpaPageInterface.php
        SpaResponderInterface.php
      Exceptions/
        InvalidSpaComponentException.php
        SpaAdapterNotFoundException.php
        SpaPayloadException.php
      Http/
        Concerns/
          InteractsWithSpaResponses.php
        Middleware/
          HandleSpaRequests.php
        SpaRequestMetadata.php
        SpaResponseFactory.php
        SpaResponseNormalizer.php
      Metadata/
        Contracts/
          NavigationMetadataFactoryInterface.php
        NavigationMetadataFactory.php
      Payloads/
        Concerns/
          SerializesPayloads.php
        AbstractSpaPayload.php
        SpaPagePayload.php
        SpaActionPayload.php
        SpaErrorPayload.php
        SpaValidationPayload.php
        SpaRedirectPayload.php
      Pages/
        Contracts/
          PageComponentResolverInterface.php
          PageResolverInterface.php
        PageComponentResolver.php
        PageDefinition.php
        PageResolver.php
      Support/
        ComponentName.php
        RequestIdGenerator.php
        ProtocolVersion.php
        SpaUrlGenerator.php
      SpaBridge.php
      SpaPage.php
      SpaResponder.php
```

---

# 5. Capa Por Capa

---

# 5.1 Contracts

Esta carpeta define la superficie publica minima del paquete.

## `SpaBridgeInterface`

Responsabilidad:

- punto de entrada principal al subsistema SPA

Metodos recomendados:

```php
public function page(string $component, array $props = [], array $meta = []): SpaPageInterface;

public function payload(SpaPayloadInterface $payload): mixed;

public function responder(): SpaResponderInterface;
```

## `SpaPayloadInterface`

Responsabilidad:

- contrato comun de payload serializable

Metodos recomendados:

```php
public function type(): string;

public function status(): int;

public function toArray(): array;
```

## `SpaPageInterface`

Responsabilidad:

- representar una pagina SPA antes de convertirse en response

Metodos recomendados:

```php
public function component(): string;

public function props(): array;

public function meta(): array;

public function toPayload(): SpaPayloadInterface;
```

## `SpaResponderInterface`

Responsabilidad:

- convertir entidades SPA a responses HTTP reales

Metodos recomendados:

```php
public function page(string $component, array $props = [], array $meta = []): mixed;

public function action(array $data = [], array $meta = [], int $status = 200): mixed;

public function validation(array $errors, array $meta = [], int $status = 422): mixed;

public function error(string $message, int $status = 500, array $meta = []): mixed;

public function redirect(string $to, int $status = 302, array $meta = []): mixed;
```

---

# 5.2 Payloads

Es la capa mas importante del paquete.

Debe materializar la especificacion de payloads documentada en:

- `3-Spa_Payload_Specification.md`

## `AbstractSpaPayload`

Responsabilidad:

- concentrar estructura base comun:

```txt
type
version
request_id
timestamp
status
success
component
props
state
meta
errors
redirect
partials
events
context
```

## Payloads concretos

### `SpaPagePayload`

Usado para:

- `spa.page`

### `SpaActionPayload`

Usado para:

- `spa.action`

### `SpaErrorPayload`

Usado para:

- `spa.error`

### `SpaValidationPayload`

Usado para:

- `spa.validation`

### `SpaRedirectPayload`

Usado para:

- `spa.redirect`

## Decision importante

Todos estos payloads deben ser:

- inmutables o casi inmutables
- serializables a array
- independientes del adapter frontend

---

# 5.3 Context

Esta capa implementa el sistema de contexto compartido.

Debe materializar la arquitectura documentada en:

- `6-SPA_SHARED_CONTEXT.md`

## `SharedContextProviderInterface`

Responsabilidad:

- proveedor desacoplado de fragmentos de contexto

Firma base recomendada:

```php
public function provide(): array;
```

Version futura posible:

```php
public function provide(Request $request): array;
```

## `SharedContextRegistryInterface`

Responsabilidad:

- registrar providers
- exponer lista activa

## `SharedContextRegistry`

Responsabilidad:

- implementacion concreta del registro

## `SharedContextResolverInterface`

Responsabilidad:

- resolver y combinar el contexto compartido final

## `SharedContextResolver`

Responsabilidad:

- invocar todos los providers
- fusionar resultados
- devolver contexto serializable

## Decision importante

El contexto compartido debe poder inyectarse automaticamente dentro de cualquier payload SPA sin que controllers o actions repitan boilerplate.

---

# 5.4 Pages

Esta capa modela la nocion de pagina SPA.

## `PageDefinition`

Responsabilidad:

- representar una pagina resoluble

Campos sugeridos:

- componente
- props
- meta
- layout opcional

## `PageComponentResolverInterface`

Responsabilidad:

- validar o resolver nombres de componente SPA

Ejemplo:

```txt
Users/Index
Dashboard/Home
Auth/Login
```

## `PageComponentResolver`

Responsabilidad:

- normalizar nombres
- validar formato
- preparar compatibilidad con manifests

## `PageResolverInterface`

Responsabilidad:

- resolver una `PageDefinition` hacia un `SpaPagePayload`

## `PageResolver`

Responsabilidad:

- tomar componente, props y meta
- anexar contexto compartido
- crear payload final

---

# 5.5 Http

Esta capa conecta `SpaBridge` con el subsistema HTTP existente.

## `SpaResponseFactory`

Responsabilidad:

- construir `Quantum\Http\Response` desde payloads SPA

Debe apoyarse en:

- `Quantum\Http\ResponseFactory`

## `SpaResponseNormalizer`

Responsabilidad:

- convertir:

```txt
SpaPage
SpaPayload
arrays SPA
```

en una respuesta HTTP consistente.

## `SpaRequestMetadata`

Responsabilidad:

- encapsular metadata del request relevante para SPA

Ejemplos:

- request id
- version de protocolo
- navigation mode
- partial reload headers
- adapter target

## `HandleSpaRequests`

Responsabilidad:

- middleware base para requests SPA

Uso inicial posible:

- detectar headers SPA
- preparar metadata de request
- exponer info al request actual

## `InteractsWithSpaResponses`

Responsabilidad:

- trait opcional para controllers

Metodos recomendados:

```php
protected function spa(string $component, array $props = [], array $meta = []): mixed;

protected function spaRedirect(string $to, int $status = 302, array $meta = []): mixed;
```

---

# 5.6 Metadata

Esta capa concentra metadata de navegacion y render.

## `NavigationMetadataFactoryInterface`

Responsabilidad:

- fabricar metadata estandar para payloads de pagina

## `NavigationMetadataFactory`

Responsabilidad:

- generar metadata como:

- url actual
- route name
- title
- layout
- breadcrumbs futuros
- cache hints futuros

Esta capa no debe mezclar rendering real ni adapter-specific behavior.

---

# 5.7 Adapters

Esta carpeta prepara la integracion con frontend adapters, pero sin acoplar el core.

## `FrontendAdapterInterface`

Responsabilidad:

- representar un adapter frontend activo

Firma base sugerida:

```php
public function name(): string;

public function version(): string;

public function entrypoints(): array;

public function resolveComponent(string $component): array|string|null;
```

## `ComponentResolverInterface`

Responsabilidad:

- resolver componentes desde manifests o mapas

## `AssetManifestInterface`

Responsabilidad:

- leer manifests de assets del frontend

## `NullFrontendAdapter`

Responsabilidad:

- adapter nulo para entorno backend-only

Decision:

El adapter no debe ser obligatorio en la primera fase.

Debe existir un modo funcional donde el backend ya emita payloads SPA correctos aunque el runtime frontend aun no este acoplado.

---

# 5.8 Support

Clases pequenas reutilizables.

## `ComponentName`

Responsabilidad:

- normalizar y validar nombres de componente

## `RequestIdGenerator`

Responsabilidad:

- generar `request_id`

## `ProtocolVersion`

Responsabilidad:

- centralizar la version actual del protocolo SPA

## `SpaUrlGenerator`

Responsabilidad:

- generar URLs utiles para redirects SPA o metadata

---

# 5.9 Exceptions

## `InvalidSpaComponentException`

Uso:

- nombre de componente invalido

## `SpaAdapterNotFoundException`

Uso:

- adapter requerido no disponible

## `SpaPayloadException`

Uso:

- payload mal formado o no serializable

---

# 5.10 Facade Interna Del Paquete

## `SpaBridge`

Responsabilidad:

- servicio principal del paquete

Debe coordinar:

- resolver pagina
- inyectar contexto
- normalizar payload
- delegar al responder

## `SpaPage`

Responsabilidad:

- objeto de alto nivel para pagina SPA

## `SpaResponder`

Responsabilidad:

- fachada de respuestas de alto nivel usada por controllers o actions

---

# 6. Dependencias Permitidas

`Quantum\SpaBridge` puede depender de:

- `Quantum\Http`
- `Quantum\Routing`
- `Quantum\Controllers`
- `Quantum\Actions`
- `Quantum\Validation`
- `Quantum\Container`
- `Quantum\Config`

No debe depender de:

- React
- Vue
- Svelte
- Solid
- bundlers
- rendering frontend concreto
- `Quantum\LiveUi`

---

# 7. Integraciones Directas

El paquete debe integrarse con estas piezas existentes del framework.

## Controllers

Integracion esperada:

- trait `InteractsWithSpaResponses`
- helper de respuesta SPA

## ControllerDispatcher

Integracion futura:

- permitir que handlers devuelvan `SpaPage`, `SpaPayload` o `SpaResponse`

## HttpKernel

Integracion futura:

- middleware SPA
- normalizacion de respuesta

## Validation

Integracion esperada:

- convertir `ValidationException` a `spa.validation`

## Actions

Integracion esperada:

- mapear resultados de actions a `spa.action`

---

# 8. Orden Recomendado De Implementacion

## Fase 1

Base contractual minima:

1. `Contracts/*`
2. `Payloads/*`
3. `Support/ProtocolVersion.php`
4. `Support/RequestIdGenerator.php`
5. `SpaPage.php`
6. `SpaResponder.php`
7. `SpaBridge.php`

## Fase 2

Contexto compartido:

1. `Context/Contracts/*`
2. `SharedContextRegistry.php`
3. `SharedContextResolver.php`

## Fase 3

HTTP integration:

1. `Http/SpaResponseFactory.php`
2. `Http/SpaResponseNormalizer.php`
3. `Http/Concerns/InteractsWithSpaResponses.php`
4. `Http/Middleware/HandleSpaRequests.php`

## Fase 4

Pages y metadata:

1. `Pages/*`
2. `Metadata/*`

## Fase 5

Adapters baseline:

1. `Adapters/Contracts/*`
2. `Adapters/NullFrontendAdapter.php`

SSR real, manifests avanzados y runtime sofisticado pueden venir despues.

---

# 9. API Publica Minima Deseable

Una primera API publica razonable seria:

```php
use Quantum\SpaBridge\SpaResponder;

$spa->page('Dashboard/Home', [
    'user' => $user,
]);

$spa->validation([
    'email' => ['The email field is required.'],
]);

$spa->redirect('/login');
```

Y en controladores:

```php
final class DashboardController extends Controller
{
    use InteractsWithSpaResponses;

    public function __invoke(): mixed
    {
        return $this->spa('Dashboard/Home', [
            'stats' => ['users' => 10],
        ]);
    }
}
```

---

# 10. Lo Que No Debe Entrar En Esta Fase

Para proteger el foco del paquete, no deben entrar aun:

- patch tree protocol complejo
- reactive component lifecycle
- event transport avanzado
- websocket sync
- diffing del arbol UI
- server components
- islands runtime

Eso debe esperar a:

```txt
Quantum\LiveUi
```

---

# 11. Tesis Final

`Quantum\SpaBridge` debe nacer como:

```txt
SPA contract layer
+ payload system
+ shared context
+ HTTP integration
+ adapter-ready foundation
```

No como runtime reactivo completo.

Si esa frontera se respeta:

- el framework queda SPA-first desde el inicio
- el core sigue siendo pequeño
- los adapters pueden crecer sin reabrir el backend
- `Quantum\LiveUi` podra construirse despues sobre una base estable

---

# 12. Siguiente Paso Recomendado

Tras aprobar esta estructura, el siguiente paso recomendado es definir:

1. el arbol real de carpetas en `src/Quantum/SpaBridge`
2. las interfaces base
3. los payloads concretos de primera fase
4. el `SpaResponder`
5. el trait para controllers

Ese seria el primer bloque de implementacion real.
