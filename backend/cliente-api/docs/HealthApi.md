# ProviEmpleaApi.HealthApi

All URIs are relative to *http://localhost:8080/api*

Method | HTTP request | Description
------------- | ------------- | -------------
[**healthCheck**](HealthApi.md#healthCheck) | **GET** /health | Verificar estado del servicio



## healthCheck

> Object healthCheck()

Verificar estado del servicio

Endpoint de observabilidad. Verifica que la API está disponible. Tiene un rate limit de 120 solicitudes por minuto por IP y encabezado Cache-Control de 15 segundos para reducir consultas repetidas.

### Example

```javascript
import ProviEmpleaApi from 'provi_emplea_api';

let apiInstance = new ProviEmpleaApi.HealthApi();
apiInstance.healthCheck((error, data, response) => {
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

**Object**

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: application/json

