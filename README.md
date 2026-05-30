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

Nota de alcance:

- esta evidencia corresponde a validacion automatizada de contrato e integracion basada en la especificacion OpenAPI generada.
- se complementa con la evidencia de `Postman`, que fue usada para validacion funcional secuencial y registro de tiempos observados.

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

## Validacion Funcional Con Postman

Nota de alcance:

- `Postman` se uso como evidencia de validacion funcional manual/secuencial y como registro de tiempos observados en entorno local.
- `backend/cliente-api` se uso como evidencia automatizada derivada directamente de la especificacion OpenAPI generada por Swagger.

Tambien se dejo una coleccion Postman versionable en la raiz del proyecto:

- `Desarrollo backend (eva3).postman_collection.json`

La coleccion cubre el flujo funcional principal de la API:

- `health`
- CRUD principal de `personas`
- CRUD principal de `empresas`
- flujo administrativo de `admin/contactos`
- `GET /api/admin/estadisticas`
- limpieza final con `DELETE` de persona y empresa creadas durante la corrida

La coleccion genera datos unicos por corrida para evitar conflictos de unicidad y guarda automaticamente `personaId`, `empresaId` y `contactoId` entre requests.

Ejecucion recomendada:

1. Importar `Desarrollo backend (eva3).postman_collection.json`.
2. Configurar `baseUrl = http://localhost:8080/api`.
3. Ejecutar la coleccion con `Runner` en modo `Functional`.
4. Usar `Iterations = 1` y ejecucion secuencial.

Resultado verificado en esta sesion:

- `25 passed`
- `0 failed`
- `Avg. Resp. Time: 18 ms`

### Evidencia Postman

Capturas copiadas al repositorio en `docs/evidencia/postman`.

| Evidencia | Descripcion |
| --- | --- |
| [`01_postman_runner_config.png`](docs/evidencia/postman/01_postman_runner_config.png) | Configuracion del Collection Runner en modo funcional con orden completo de requests |
| [`02_postman_runner_results.png`](docs/evidencia/postman/02_postman_runner_results.png) | Resultado de la corrida funcional completa con `25 passed`, `0 failed` y tiempos observados por request |

### Evidencia De Tiempos Observados

La medicion se realizo con Postman Collection Runner sobre entorno local en Docker usando `http://localhost:8080/api`. Estos tiempos son referenciales y no corresponden a una prueba de carga formal; su objetivo es dejar evidencia de tiempos de respuesta observados durante la validacion funcional.

| Endpoint | Metodo | Tiempo observado |
| --- | --- | --- |
| `/api/health` | `GET` | `119 ms` |
| `/api/personas` | `POST` | `32 ms` |
| `/api/personas/{id}` | `GET` | `11 ms` |
| `/api/personas` | `GET` | `12 ms` |
| `/api/personas/{id}` | `PUT` | `12 ms` |
| `/api/personas/{id}/validar` | `PATCH` | `10 ms` |
| `/api/empresas` | `POST` | `14 ms` |
| `/api/empresas/{id}` | `GET` | `9 ms` |
| `/api/empresas` | `GET` | `9 ms` |
| `/api/empresas/{id}` | `PUT` | `12 ms` |
| `/api/empresas/{id}/validar` | `PATCH` | `10 ms` |
| `/api/admin/contactos` | `POST` | `16 ms` |
| `/api/admin/contactos?estado=pendiente` | `GET` | `15 ms` |
| `/api/admin/contactos/{id}/estado` | `PATCH` | `11 ms` |
| `/api/admin/estadisticas` | `GET` | `19 ms` |

### Vista Previa De Evidencia Postman

Configuracion del Runner funcional:

![Postman Runner Config](docs/evidencia/postman/01_postman_runner_config.png)

Resultado de la corrida funcional:

![Postman Runner Results](docs/evidencia/postman/02_postman_runner_results.png)

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

### Plan De Pruebas Manuales En Swagger UI

Usa esta tabla como bitacora para la validacion manual en `http://localhost:8000/api/documentation`.

| # | Endpoint | Metodo | Datos de prueba | Resultado esperado | Evidencia sugerida |
| --- | --- | --- | --- | --- | --- |
| 1 | `/api/health` | `GET` | Sin body | `200` con `status`, `service`, `version`, `timestamp` | captura de respuesta exitosa |
| 2 | `/api/personas` | `POST` | `{"email":"camila.rojas@example.com","telefono":"+56911112222","resumen":"Desarrolladora backend con experiencia en APIs REST y Laravel.","nivel_educacional":"universitaria","titulo_carrera":"Ingenieria en Informatica","anio_egreso":2022,"anios_experiencia":3,"areas_experiencia":["Desarrollo Web","APIs REST"],"competencias":["PHP","Laravel","MySQL"],"rango_renta":"900k-1.2M","tipo_jornada":"completa","modalidad":"hibrido","cursos":[{"nombre":"Laravel Avanzado","institucion":"Udemy","anio":2024}],"idiomas":[{"idioma":"Ingles","nivel":"intermedio"}],"portafolio_url":"https://example.com/portafolio-camila","persona_discapacidad":false}` | `201` con wrapper `{ success, data }` y UUID generado | captura de request y response |
| 3 | `/api/personas/{id}` | `GET` | Usar el UUID generado al crear `camila.rojas@example.com` | `200` con los datos persistidos | captura con el UUID visible |
| 4 | `/api/personas` | `GET` | Sin body | `200` con listado de personas activas en formato CV ciego | captura del listado |
| 5 | `/api/personas/{id}` | `PUT` | `{"email":"camila.rojas.actualizada@example.com","resumen":"Perfil actualizado con foco en integraciones y mantenimiento de APIs.","modalidad":"remoto","anios_experiencia":4}` | `200` con datos actualizados | captura de request y response |
| 6 | `/api/personas/{id}/validar` | `PATCH` | Sin body | `200` con mensaje `Persona validada exitosamente.` y `validado = true` | captura del cambio de estado |
| 7 | `/api/personas` | `POST` | `{"email":"correo-invalido","nivel_educacional":"doctorado","portafolio_url":"no-es-url"}` | `422` con errores en `email`, `nivel_educacional`, `portafolio_url` | captura del error de validacion |
| 8 | `/api/empresas` | `POST` | `{"nombre_empresa":"NovaTalent SpA","rut_empresa":"88997766-5","email":"contacto+novatalent@example.com","logo_url":"https://example.com/logo-novatalent.png","rubro":"Tecnologia","tipo_empresa":"contratacion-directa","presentacion":"Empresa orientada a soluciones digitales y desarrollo de software.","beneficios":["Trabajo remoto","Seguro complementario"],"contacto_nombre":"Daniela Perez","contacto_email":"daniela.perez@example.com","contacto_telefono":"+56933334444"}` | `201` con wrapper `{ success, data }` y UUID generado | captura de request y response |
| 9 | `/api/empresas/{id}` | `GET` | Usar el UUID generado al crear `NovaTalent SpA` | `200` con la empresa persistida | captura con el UUID visible |
| 10 | `/api/empresas` | `GET` | Sin body | `200` con listado de empresas activas | captura del listado |
| 11 | `/api/empresas/{id}` | `PUT` | `{"email":"contacto+novatalent-actualizada@example.com","tipo_empresa":"outsourcing","contacto_nombre":"Daniela Perez Actualizada","beneficios":["Capacitaciones","Trabajo remoto"]}` | `200` con datos actualizados | captura de request y response |
| 12 | `/api/empresas/{id}/validar` | `PATCH` | Sin body | `200` con mensaje `Empresa validada exitosamente.` y `validado = true` | captura del cambio de estado |
| 13 | `/api/empresas` | `POST` | `{"nombre_empresa":"","rut_empresa":"","email":"correo-invalido","tipo_empresa":"otra","contacto_email":"no-email"}` | `422` con errores de validacion | captura del error de validacion |
| 14 | `/api/admin/contactos` | `POST` | `{"empresa_id":"UUID_EMPRESA","persona_id":"UUID_PERSONA","notas_admin":"Perfil alineado con vacante backend."}` | `201` con `estado = pendiente` | captura con ambos UUID |
| 15 | `/api/admin/contactos` | `GET` | Sin body o `?estado=pendiente` | `200` con listado de contactos y al menos un registro con los UUID creados | captura del listado |
| 16 | `/api/admin/contactos` | `POST` | Repetir los mismos `empresa_id` y `persona_id` activos | `409` con mensaje de solicitud activa existente | captura del conflicto |
| 17 | `/api/admin/contactos/{id}/estado` | `PATCH` | `{"estado":"contactado","notas_admin":"Primer contacto realizado por correo."}` | `200` con estado `contactado` y fecha de contacto | captura del cambio de estado |
| 18 | `/api/admin/contactos/{id}/estado` | `PATCH` | `{"estado":"otro"}` | `422` con error en `estado` | captura del error de validacion |
| 19 | `/api/admin/estadisticas` | `GET` | Sin body | `200` con contadores agregados | captura del resumen final |
| 20 | `/api/personas/{id}` | `DELETE` | Usar el UUID de Camila al final del flujo | `200` con mensaje `Persona desactivada exitosamente.` | captura de la desactivacion |
| 21 | `/api/empresas/{id}` | `DELETE` | Usar el UUID de NovaTalent al final del flujo | `200` con mensaje `Empresa desactivada exitosamente.` | captura de la desactivacion |

Notas de uso:

- reemplaza `UUID_PERSONA`, `UUID_EMPRESA` y el `id` del contacto por valores creados en Swagger UI
- si repites pruebas de alta, cambia `rut_empresa` y `email` porque ambos campos deben ser unicos
- ejecuta los `DELETE` al final para no romper las pruebas de `contactos` y `estadisticas`
- los `PATCH /validar` no requieren body; solo debes enviar el UUID correcto en la URL
- si quieres evidencia mas formal, agrega una columna manual de `resultado obtenido` y otra de `observacion`
- conviene guardar al menos una captura por caso exitoso y una por caso de error

### Evidencia Capturada

Capturas copiadas al repositorio en `docs/evidencia/swagger-ui`.

| Prueba | Endpoint | Estado | Evidencia |
| --- | --- | --- | --- |
| 1 | `GET /api/health` | completada | [`01_health.png`](docs/evidencia/swagger-ui/01_health.png) |
| 2 | `POST /api/personas` valido | completada | [`02_personas_post_valido_00.png`](docs/evidencia/swagger-ui/02_personas_post_valido_00.png), [`02_personas_post_valido_01.png`](docs/evidencia/swagger-ui/02_personas_post_valido_01.png) |
| 3 | `GET /api/personas/{id}` | completada | [`03_personas_get_id_00.png`](docs/evidencia/swagger-ui/03_personas_get_id_00.png), [`03_personas_get_id_01.png`](docs/evidencia/swagger-ui/03_personas_get_id_01.png) |
| 4 | `GET /api/personas` | completada | [`04_personas_get_listado.png`](docs/evidencia/swagger-ui/04_personas_get_listado.png) |
| 5 | `PUT /api/personas/{id}` | completada | [`05_personas_put_00.png`](docs/evidencia/swagger-ui/05_personas_put_00.png), [`05_personas_put_01.png`](docs/evidencia/swagger-ui/05_personas_put_01.png) |
| 6 | `PATCH /api/personas/{id}/validar` | completada | [`06_personas_patch_validar.png`](docs/evidencia/swagger-ui/06_personas_patch_validar.png) |
| 7 | `POST /api/personas` invalido | completada | [`07_personas_post_invalido_00.png`](docs/evidencia/swagger-ui/07_personas_post_invalido_00.png), [`07_personas_post_invalido_01.png`](docs/evidencia/swagger-ui/07_personas_post_invalido_01.png) |
| 8 | `POST /api/empresas` valido | completada | [`08_empresas_post_valido_00.png`](docs/evidencia/swagger-ui/08_empresas_post_valido_00.png), [`08_empresas_post_valido_01.png`](docs/evidencia/swagger-ui/08_empresas_post_valido_01.png) |
| 9 | `GET /api/empresas/{id}` | completada | [`09_empresas_get_id_00.png`](docs/evidencia/swagger-ui/09_empresas_get_id_00.png), [`09_empresas_get_id_01.png`](docs/evidencia/swagger-ui/09_empresas_get_id_01.png) |
| 10 | `GET /api/empresas` | completada | [`10_empresas_get_listado.png`](docs/evidencia/swagger-ui/10_empresas_get_listado.png) |
| 11 | `PUT /api/empresas/{id}` | completada | [`11_empresas_put_00.png`](docs/evidencia/swagger-ui/11_empresas_put_00.png), [`11_empresas_put_01.png`](docs/evidencia/swagger-ui/11_empresas_put_01.png) |
| 12 | `PATCH /api/empresas/{id}/validar` | completada | [`12_empresas_patch_validar.png`](docs/evidencia/swagger-ui/12_empresas_patch_validar.png) |
| 13 | `POST /api/empresas` invalido | completada | [`13_empresas_post_invalido_00.png`](docs/evidencia/swagger-ui/13_empresas_post_invalido_00.png), [`13_empresas_post_invalido_01.png`](docs/evidencia/swagger-ui/13_empresas_post_invalido_01.png) |
| 14 | `POST /api/admin/contactos` valido | completada | [`14_contactos_post_valido_00.png`](docs/evidencia/swagger-ui/14_contactos_post_valido_00.png), [`14_contactos_post_valido_01.png`](docs/evidencia/swagger-ui/14_contactos_post_valido_01.png) |
| 15 | `GET /api/admin/contactos` | completada | [`15_admin_contactos_get.png`](docs/evidencia/swagger-ui/15_admin_contactos_get.png) |
| 16 | `POST /api/admin/contactos` duplicado | completada | [`16_contactos_post_duplicado.png`](docs/evidencia/swagger-ui/16_contactos_post_duplicado.png) |
| 17 | `PATCH /api/admin/contactos/{id}/estado` valido | completada | [`17_contactos_patch_valido_00.png`](docs/evidencia/swagger-ui/17_contactos_patch_valido_00.png), [`17_contactos_patch_valido_01.png`](docs/evidencia/swagger-ui/17_contactos_patch_valido_01.png) |
| 18 | `PATCH /api/admin/contactos/{id}/estado` invalido | completada | [`18_contactos_patch_invalido_00.png`](docs/evidencia/swagger-ui/18_contactos_patch_invalido_00.png), [`18_contactos_patch_invalido_01.png`](docs/evidencia/swagger-ui/18_contactos_patch_invalido_01.png) |
| 19 | `GET /api/admin/estadisticas` | completada | [`19_estadisticas.png`](docs/evidencia/swagger-ui/19_estadisticas.png) |
| 20 | `DELETE /api/personas/{id}` | completada | [`20_personas_delete.png`](docs/evidencia/swagger-ui/20_personas_delete.png) |
| 21 | `DELETE /api/empresas/{id}` | completada | [`21_empresas_delete.png`](docs/evidencia/swagger-ui/21_empresas_delete.png) |

### Vista Previa De Evidencias Completadas

Prueba 1. `GET /api/health`

![Prueba 1](docs/evidencia/swagger-ui/01_health.png)

Prueba 2. `POST /api/personas` valido

![Prueba 2 - solicitud](docs/evidencia/swagger-ui/02_personas_post_valido_00.png)
![Prueba 2 - respuesta](docs/evidencia/swagger-ui/02_personas_post_valido_01.png)

Prueba 3. `GET /api/personas/{id}`

![Prueba 3 - solicitud](docs/evidencia/swagger-ui/03_personas_get_id_00.png)
![Prueba 3 - respuesta](docs/evidencia/swagger-ui/03_personas_get_id_01.png)

Prueba 4. `GET /api/personas`

![Prueba 4](docs/evidencia/swagger-ui/04_personas_get_listado.png)

Prueba 5. `PUT /api/personas/{id}`

![Prueba 5 - solicitud](docs/evidencia/swagger-ui/05_personas_put_00.png)
![Prueba 5 - respuesta](docs/evidencia/swagger-ui/05_personas_put_01.png)

Prueba 6. `PATCH /api/personas/{id}/validar`

![Prueba 6](docs/evidencia/swagger-ui/06_personas_patch_validar.png)

Prueba 7. `POST /api/personas` invalido

![Prueba 7 - solicitud](docs/evidencia/swagger-ui/07_personas_post_invalido_00.png)
![Prueba 7 - respuesta](docs/evidencia/swagger-ui/07_personas_post_invalido_01.png)

Prueba 8. `POST /api/empresas` valido

![Prueba 8 - solicitud](docs/evidencia/swagger-ui/08_empresas_post_valido_00.png)
![Prueba 8 - respuesta](docs/evidencia/swagger-ui/08_empresas_post_valido_01.png)

Prueba 9. `GET /api/empresas/{id}`

![Prueba 9 - solicitud](docs/evidencia/swagger-ui/09_empresas_get_id_00.png)
![Prueba 9 - respuesta](docs/evidencia/swagger-ui/09_empresas_get_id_01.png)

Prueba 10. `GET /api/empresas`

![Prueba 10](docs/evidencia/swagger-ui/10_empresas_get_listado.png)

Prueba 11. `PUT /api/empresas/{id}`

![Prueba 11 - solicitud](docs/evidencia/swagger-ui/11_empresas_put_00.png)
![Prueba 11 - respuesta](docs/evidencia/swagger-ui/11_empresas_put_01.png)

Prueba 12. `PATCH /api/empresas/{id}/validar`

![Prueba 12](docs/evidencia/swagger-ui/12_empresas_patch_validar.png)

Prueba 13. `POST /api/empresas` invalido

![Prueba 13 - solicitud](docs/evidencia/swagger-ui/13_empresas_post_invalido_00.png)
![Prueba 13 - respuesta](docs/evidencia/swagger-ui/13_empresas_post_invalido_01.png)

Prueba 14. `POST /api/admin/contactos` valido

![Prueba 14 - solicitud](docs/evidencia/swagger-ui/14_contactos_post_valido_00.png)
![Prueba 14 - respuesta](docs/evidencia/swagger-ui/14_contactos_post_valido_01.png)

Prueba 15. `GET /api/admin/contactos`

![Prueba 15](docs/evidencia/swagger-ui/15_admin_contactos_get.png)

Prueba 16. `POST /api/admin/contactos` duplicado

![Prueba 16](docs/evidencia/swagger-ui/16_contactos_post_duplicado.png)

Prueba 17. `PATCH /api/admin/contactos/{id}/estado` valido

![Prueba 17 - solicitud](docs/evidencia/swagger-ui/17_contactos_patch_valido_00.png)
![Prueba 17 - respuesta](docs/evidencia/swagger-ui/17_contactos_patch_valido_01.png)

Prueba 18. `PATCH /api/admin/contactos/{id}/estado` invalido

![Prueba 18 - solicitud](docs/evidencia/swagger-ui/18_contactos_patch_invalido_00.png)
![Prueba 18 - respuesta](docs/evidencia/swagger-ui/18_contactos_patch_invalido_01.png)

Prueba 19. `GET /api/admin/estadisticas`

![Prueba 19](docs/evidencia/swagger-ui/19_estadisticas.png)

Prueba 20. `DELETE /api/personas/{id}`

![Prueba 20](docs/evidencia/swagger-ui/20_personas_delete.png)

Prueba 21. `DELETE /api/empresas/{id}`

![Prueba 21](docs/evidencia/swagger-ui/21_empresas_delete.png)

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
