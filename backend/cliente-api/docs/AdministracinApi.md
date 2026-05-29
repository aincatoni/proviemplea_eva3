# ProviEmpleaApi.AdministracinApi

All URIs are relative to *http://localhost:8080/api*

Method | HTTP request | Description
------------- | ------------- | -------------
[**actualizarEstadoContacto**](AdministracinApi.md#actualizarEstadoContacto) | **PATCH** /admin/contactos/{contacto}/estado | Actualizar estado de contacto
[**crearContactoSolicitado**](AdministracinApi.md#crearContactoSolicitado) | **POST** /admin/contactos | Registrar solicitud de contacto
[**getContactosSolicitados**](AdministracinApi.md#getContactosSolicitados) | **GET** /admin/contactos | Listar contactos solicitados
[**getEstadisticas**](AdministracinApi.md#getEstadisticas) | **GET** /admin/estadisticas | Estadísticas generales de la plataforma



## actualizarEstadoContacto

> ContactoSolicitadoResponse actualizarEstadoContacto(contacto, actualizarEstadoContactoRequest)

Actualizar estado de contacto

Cambia el estado del proceso. Las fechas se registran automáticamente según el estado. Rate limit: 20 solicitudes por minuto por IP.

### Example

```javascript
import ProviEmpleaApi from 'provi_emplea_api';

let apiInstance = new ProviEmpleaApi.AdministracinApi();
let contacto = "550e8400-e29b-41d4-a716-446655440003"; // String | 
let actualizarEstadoContactoRequest = new ProviEmpleaApi.ActualizarEstadoContactoRequest(); // ActualizarEstadoContactoRequest | 
apiInstance.actualizarEstadoContacto(contacto, actualizarEstadoContactoRequest, (error, data, response) => {
  if (error) {
    console.error(error);
  } else {
    console.log('API called successfully. Returned data: ' + data);
  }
});
```

### Parameters


Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **contacto** | **String**|  | 
 **actualizarEstadoContactoRequest** | [**ActualizarEstadoContactoRequest**](ActualizarEstadoContactoRequest.md)|  | 

### Return type

[**ContactoSolicitadoResponse**](ContactoSolicitadoResponse.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: application/json
- **Accept**: application/json


## crearContactoSolicitado

> ContactoSolicitadoResponse crearContactoSolicitado(contactoSolicitadoInput)

Registrar solicitud de contacto

Una empresa solicita contactar a un talento. No puede existir una solicitud activa previa. Rate limit: 20 solicitudes por minuto por IP.

### Example

```javascript
import ProviEmpleaApi from 'provi_emplea_api';

let apiInstance = new ProviEmpleaApi.AdministracinApi();
let contactoSolicitadoInput = new ProviEmpleaApi.ContactoSolicitadoInput(); // ContactoSolicitadoInput | 
apiInstance.crearContactoSolicitado(contactoSolicitadoInput, (error, data, response) => {
  if (error) {
    console.error(error);
  } else {
    console.log('API called successfully. Returned data: ' + data);
  }
});
```

### Parameters


Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **contactoSolicitadoInput** | [**ContactoSolicitadoInput**](ContactoSolicitadoInput.md)|  | 

### Return type

[**ContactoSolicitadoResponse**](ContactoSolicitadoResponse.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: application/json
- **Accept**: application/json


## getContactosSolicitados

> ContactoSolicitadoListResponse getContactosSolicitados(opts)

Listar contactos solicitados

Consulta administrativa de lectura. Rate limit: 60 solicitudes por minuto por IP.

### Example

```javascript
import ProviEmpleaApi from 'provi_emplea_api';

let apiInstance = new ProviEmpleaApi.AdministracinApi();
let opts = {
  'estado': "estado_example" // String | 
};
apiInstance.getContactosSolicitados(opts, (error, data, response) => {
  if (error) {
    console.error(error);
  } else {
    console.log('API called successfully. Returned data: ' + data);
  }
});
```

### Parameters


Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **estado** | **String**|  | [optional] 

### Return type

[**ContactoSolicitadoListResponse**](ContactoSolicitadoListResponse.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: application/json


## getEstadisticas

> EstadisticasResponse getEstadisticas()

Estadísticas generales de la plataforma

Dashboard resumido de la plataforma. Rate limit: 60 solicitudes por minuto por IP. Respuesta con Cache-Control de 30 segundos por tratarse de métricas agregadas.

### Example

```javascript
import ProviEmpleaApi from 'provi_emplea_api';

let apiInstance = new ProviEmpleaApi.AdministracinApi();
apiInstance.getEstadisticas((error, data, response) => {
  if (error) {
    console.error(error);
  } else {
    console.log('API called successfully. Returned data: ' + data);
  }
});
```

### Parameters

This endpoint does not need any parameter.

### Return type

[**EstadisticasResponse**](EstadisticasResponse.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: application/json

