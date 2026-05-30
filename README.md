# ProviEmplea EVA3

API REST desarrollada en Laravel para la evaluacion U3 de backend. El proyecto expone endpoints para personas, empresas, administracion de contactos y documentacion OpenAPI con Swagger UI.

## Puesta en marcha

1. Instalar dependencias del backend:

```bash
cd backend
composer install
```

2. Configurar variables de entorno:

```bash
cp .env.example .env
php artisan key:generate
```

3. Levantar servicios con Docker desde la raiz del proyecto:

```bash
docker compose up -d --build
```

4. Ejecutar migraciones si corresponde:

```bash
docker compose exec app php artisan migrate
```

## Swagger / OpenAPI

- Swagger UI: `http://localhost:8000/api/documentation`
- JSON generado: `backend/storage/api-docs/api-docs.json`
- Regenerar documentacion:

```bash
cd backend
php artisan l5-swagger:generate
```

## Tests

Suite principal validada:

```bash
cd backend
php artisan test --filter='EmpresaApiTest|AdministracionApiTest|PersonaApiTest|HealthTest'
```

Resultado de referencia de la sesion anterior:

- `38 passed`
- `178 assertions`

## Pruebas Automáticas Con Cliente OpenAPI

Se agregó una suite de integración real usando el cliente generado en `backend/cliente-api` para consumir el backend levantado por Docker.

Cobertura validada con el cliente generado:

- `GET /api/health`
- flujo CRUD principal de `personas`
- flujo CRUD principal de `empresas`
- flujo administrativo de `admin/contactos`
- `GET /api/admin/estadisticas`
- respuestas de error `404`, `409` y `422`

Ejecución:

```bash
cd backend/cliente-api
npm run test:integration
```

Consideraciones:

- la suite apunta por defecto a `http://localhost:8080/api`
- antes de correr, reinicia la base con `docker compose exec -T app php artisan migrate:fresh --force`
- la suite hace ese reinicio automáticamente por defecto para evitar datos residuales
- ese proceso borra y recrea la base del contenedor de aplicación

Si necesitas usar otra URL o un comando distinto de reinicio:

```bash
cd backend/cliente-api
PROVIEMPLEA_API_BASE_URL=http://localhost:8000/api \
PROVIEMPLEA_RESET_COMMAND="php artisan migrate:fresh --force" \
npm run test:integration
```

Resultado verificado en esta sesión:

- `5 passing`

## Evidencia De Validacion Con Swagger

Se contrasto la documentacion OpenAPI con el comportamiento real cubierto por feature tests del backend.

| Endpoint | Caso validado | Resultado esperado | Resultado verificado | Ajuste realizado |
| --- | --- | --- | --- | --- |
| `GET /api/health` | Estado del servicio | Respuesta `200` con `status`, `service`, `version`, `timestamp` | Cubierto por `HealthTest` | Se mantuvo respuesta plana y se documento asi en Swagger |
| `POST /api/personas` | Alta exitosa | `201` con wrapper `{ success, data }` | Cubierto por `PersonaApiTest::test_can_create_persona` | Se alineo Swagger con el wrapper real |
| `GET /api/personas/{id}` | Consulta por UUID | `200` con UUID valido | Cubierto por `PersonaApiTest::test_can_show_persona_by_uuid` | Se corrigio tipado OpenAPI de `integer` a `string/uuid` |
| `PATCH /api/personas/{id}/validar` | Validacion administrativa | `200` con mensaje y recurso actualizado | Cubierto por `PersonaApiTest::test_can_validar_persona` | Se documento el wrapper real de respuesta |
| `POST /api/empresas` | Alta exitosa | `201` con empresa persistida | Cubierto por `EmpresaApiTest::test_can_create_empresa` | Se alineo esquema de entrada/salida |
| `GET /api/empresas/{id}` | Consulta por UUID | `200` con empresa existente | Cubierto por `EmpresaApiTest::test_can_show_empresa_by_uuid` | Se corrigio tipado UUID en Swagger |
| `POST /api/admin/contactos` | Creacion de solicitud | `201` con `estado = pendiente` | Cubierto por `AdministracionApiTest::test_can_create_contacto_solicitado` | Se corrigio `crearContacto` para responder el modelo persistido con defaults |
| `POST /api/admin/contactos` | Duplicado activo | `409` si ya existe proceso abierto | Cubierto por `AdministracionApiTest::test_cannot_create_duplicate_active_contacto` | Se mantuvo contrato de error en Swagger |
| `PATCH /api/admin/contactos/{id}/estado` | Cambio de estado | `200` y registro automatico de fechas | Cubierto por tests de `contactado`, `entrevista` y `seleccionado` | Se verifico consistencia entre comportamiento real y documentacion |
| `GET /api/admin/estadisticas` | Resumen agregado | `200` con contadores globales | Cubierto por `AdministracionApiTest::test_can_get_estadisticas` | Se documento wrapper y metricas agregadas |

## Mejoras Tecnicas Aplicadas

### Contrato API y Swagger

- Se alinearon respuestas documentadas con el wrapper real `{ success, data }` y los errores `{ success, message, errors }`.
- Se corrigio el uso de UUID en schemas y parametros OpenAPI.
- Se regenero la salida Swagger para mantener `backend/storage/api-docs/api-docs.json` consistente.

### Rendimiento y uso responsable

Se agrego rate limiting por IP segun tipo de operacion:

- Lecturas publicas: `120 req/min`
- Escrituras publicas: `30 req/min`
- Lecturas administrativas: `60 req/min`
- Escrituras administrativas: `20 req/min`

Las operaciones expuestas en Swagger documentan tambien la posible respuesta `429 Too Many Requests` cuando se supera el limite configurado para cada grupo de rutas.

Se agregaron encabezados `Cache-Control` de corta duracion donde tiene sentido:

- `GET /api/health`: `public, max-age=15`
- `GET /api/admin/estadisticas`: `private, max-age=30`

Decisiones tecnicas:

- No se cachearon listados CRUD (`personas`, `empresas`, `contactos`) para evitar respuestas obsoletas durante validaciones y cambios de estado.
- Se recomienda a clientes externos reutilizar resultados de lectura reciente y enviar filtros para reducir payload.

Tiempos de respuesta esperados:

Los siguientes valores son objetivos de respuesta esperados en entorno local/Docker, con baja concurrencia y volumen de datos acotado. No representan una garantia contractual bajo alta carga.

| Endpoint | Tiempo esperado |
| --- | --- |
| `GET /api/health` | menor a `200 ms` |
| `GET /api/personas` | menor a `500 ms` |
| `POST /api/personas` | menor a `700 ms` |
| `GET /api/personas/{id}` | menor a `300 ms` |
| `PUT /api/personas/{id}` | menor a `700 ms` |
| `PATCH /api/personas/{id}/validar` | menor a `400 ms` |
| `DELETE /api/personas/{id}` | menor a `400 ms` |
| `GET /api/empresas` | menor a `500 ms` |
| `POST /api/empresas` | menor a `700 ms` |
| `GET /api/empresas/{id}` | menor a `300 ms` |
| `PUT /api/empresas/{id}` | menor a `700 ms` |
| `PATCH /api/empresas/{id}/validar` | menor a `400 ms` |
| `DELETE /api/empresas/{id}` | menor a `400 ms` |
| `GET /api/admin/contactos` | menor a `500 ms` |
| `POST /api/admin/contactos` | menor a `700 ms` |
| `PATCH /api/admin/contactos/{id}/estado` | menor a `500 ms` |
| `GET /api/admin/estadisticas` | menor a `400 ms` |

## Endpoints Cubiertos

- `GET /api/health`
- CRUD de `personas`
- CRUD de `empresas`
- `PATCH /api/personas/{id}/validar`
- `PATCH /api/empresas/{id}/validar`
- `POST /api/admin/contactos`
- `GET /api/admin/contactos`
- `PATCH /api/admin/contactos/{id}/estado`
- `GET /api/admin/estadisticas`
- Casos de error `404`, `409` y `422`

## Pendiente Para El Entregable

- Preparar nombre del equipo e integrantes.
- Generar el `.zip` final con el formato solicitado por la evaluacion.
