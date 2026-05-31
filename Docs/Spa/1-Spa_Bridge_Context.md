SPA_BRIDGE_CONTEXT.md
W4 SPA Bridge
Contexto General del Proyecto

1. Visión General

W4 SPA Bridge es el sistema oficial de integración entre el backend del framework VoltStack y aplicaciones frontend SPA modernas.

El paquete actúa como un:

Puente desacoplado
Backend PHP ↔ Frontend SPA

Su propósito es permitir que cualquier aplicación frontend moderna pueda comunicarse con el backend del framework sin acoplarse a tecnologías específicas como React, Vue o Svelte.

1. Problema que Resuelve

La mayoría de frameworks modernos tienen uno de estos problemas:

Acoplamiento fuerte al frontend

Ejemplos:

Laravel + Livewire
Laravel + Inertia
Next.js Fullstack
Nuxt Fullstack

Problema:

El backend termina dependiendo del frontend.
Dependencia de tecnologías específicas

Ejemplos:

Inertia → React/Vue/Svelte
Livewire → Blade
Next.js → React
Nuxt → Vue

Problema:

El framework obliga al desarrollador a usar ciertas tecnologías.
Frontend y backend totalmente separados

Arquitectura clásica:

Backend API REST
Frontend SPA independiente

Problemas:

- Mucho boilerplate.
- Duplicación de estados.
- Problemas de autenticación.
- Configuración repetitiva.
- Pérdida de contexto compartido.
- Rutas duplicadas.
- Manejo inconsistente de errores.

3. Filosofía del Proyecto

W4 SPA Bridge sigue estos principios:

Backend agnóstico del frontend

El backend:

NO debe saber:

- React
- Vue
- Svelte
- Angular
- Solid

Solo debe conocer:

Contratos SPA.
Frontend agnóstico del backend

La SPA:

NO debe depender de:

- Blade
- Livewire
- Laravel
- VoltStack internals

Solo debe consumir:

Payloads normalizados.
Comunicación tipada y consistente

Toda respuesta SPA debe seguir contratos definidos.

Ejemplo:

{
  "type": "spa.page",
  "component": "Users/Index",
  "props": {}
}
Frontend desacoplable

La SPA puede vivir:

- Dentro del mismo proyecto
- En otro repositorio
- En otro servidor
- En CDN
- Como microfrontend
Arquitectura orientada a contratos

Toda integración debe pasar por:

Interfaces + adapters + manifests

Nunca lógica hardcodeada.

1. Objetivos del Proyecto
Objetivo principal

Crear un sistema universal de comunicación entre backend PHP y SPA modernas.

Objetivos secundarios
Simplificar el desarrollo SPA

Reducir:

- Boilerplate
- APIs manuales
- Estados duplicados
- Configuración repetitiva
Mantener desacoplamiento

Permitir:

Cambiar React por Vue sin tocar backend.
Compartir contexto automáticamente

Ejemplo:

- Usuario autenticado
- Tenant actual
- Configuración pública
- Idioma
- Permisos
- Tokens
- Navegación
Estandarizar respuestas

Ejemplo:

spa.page
spa.action
spa.error
spa.redirect
spa.validation
Facilitar SSR futuro

La arquitectura debe permitir:

Server Side Rendering
Streaming
Hydration
Edge Rendering

5. No Responsabilidades

W4 SPA Bridge NO debe:

Renderizar frontend

NO es:

- React
- Vue
- Svelte
- Blade
Ser un framework frontend

NO compite contra:

- React
- Vue
- Angular
Compilar assets

NO debe encargarse de:

- Vite
- Bun
- Webpack
- Rollup
Reemplazar APIs tradicionales

REST, GraphQL y WebSockets siguen siendo válidos.

SPA Bridge es:

Una capa de integración inteligente.
6. Arquitectura General

Arquitectura conceptual:

┌──────────────────────────┐
│      Frontend SPA        │
│ React/Vue/Svelte/Solid   │
└────────────┬─────────────┘
             │
             │ JSON Contract
             │
┌────────────▼─────────────┐
│      W4 SPA Bridge       │
│  Payloads + Contracts    │
└────────────┬─────────────┘
             │
             │
┌────────────▼─────────────┐
│      VoltStack Core      │
│ Router + Kernel + DI     │
└────────────┬─────────────┘
             │
┌────────────▼─────────────┐
│     Application Layer    │
│ Controllers / Actions    │
└──────────────────────────┘
7. Flujo Backend → SPA
Paso 1

Usuario entra:

/dashboard
Paso 2

Router resuelve controlador.

Paso 3

Controlador devuelve:

$spa->page(...)
Paso 4

SPA Bridge crea payload:

{
  "type": "spa.page",
  "component": "Dashboard/Index",
  "props": {}
}
Paso 5

Frontend resuelve componente:

Dashboard/Index
Paso 6

Frontend renderiza página.

1. Flujo SPA → Backend
Paso 1

Frontend ejecuta acción:

spa.post('/users/create', data)
Paso 2

Backend recibe request.

Paso 3

SPA Action Dispatcher resuelve acción.

Paso 4

Backend responde:

{
  "type": "spa.action",
  "success": true,
  "message": "Usuario creado"
}
Paso 5

Frontend actualiza estado.

1. Sistema de Payloads

Todos los mensajes deben ser normalizados.

Tipos base
spa.page

Representa una página SPA.

spa.action

Resultado de acción backend.

spa.validation

Errores de validación.

spa.redirect

Redirección frontend.

spa.error

Errores generales.

spa.stream

Streaming futuro.

1. Sistema de Contexto Compartido

SPA Bridge debe compartir automáticamente:

- Usuario
- Tenant
- Locale
- Permisos
- Configuración pública
- CSRF
- Tokens
- Navegación
- Metadata

Ejemplo:

$spa->share('user', $user);
11. Adaptadores Frontend

El paquete debe soportar adapters.

React Adapter
@w4/spa-react
Vue Adapter
@w4/spa-vue
Svelte Adapter
@w4/spa-svelte
Generic Adapter
@w4/spa-client
12. Sistema de Manifest

El frontend debe generar un manifest:

{
  "pages": {
    "Dashboard/Index": "/pages/dashboard/index.js"
  }
}

El backend puede usar esto para:

- Validación
- SSR
- Precarga
- Integridad

13. Seguridad

El paquete debe incluir:

Protección CSRF
Tokens SPA
Validación de origen
Sanitización de payloads
Protección de acciones
14. Integración con VoltStack

SPA Bridge debe integrarse con:

Router

Rutas SPA.

Container

Resolución de adapters y contracts.

Kernel

Middleware SPA.

Response System

Responses SPA.

Exception System

Errores SPA normalizados.

1. Posibles Evoluciones Futuras
SSR

Server Side Rendering.

Streaming UI

Render parcial progresivo.

Islands Architecture

Componentes aislados.

Offline Mode

Persistencia local.

State Synchronization

Sincronización backend ↔ frontend.

Server Components

Inspirado en React Server Components.

1. Roadmap Inicial
V1
Core SPA Bridge
Payloads
Contracts
Responses
Shared Context
Basic Routing
Action Dispatcher
V2
Frontend Adapters
React
Vue
Svelte
V3
Estado reactivo compartido

Backend ↔ frontend synchronization.

V4
SSR + Streaming
V5
Microfrontends
17. Objetivo Final

Convertir W4 SPA Bridge en una capa universal de integración SPA para PHP moderno.

La meta es que cualquier frontend pueda comunicarse con VoltStack mediante contratos estables, desacoplados y escalables.
