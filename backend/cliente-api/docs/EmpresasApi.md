# ProviEmpleaApi.EmpresasApi

All URIs are relative to *http://localhost:8080/api*

Method | HTTP request | Description
------------- | ------------- | -------------
[**createEmpresa**](EmpresasApi.md#createEmpresa) | **POST** /empresas | Registrar nueva empresa
[**deleteEmpresa**](EmpresasApi.md#deleteEmpresa) | **DELETE** /empresas/{empresa} | Desactivar empresa
[**getEmpresa**](EmpresasApi.md#getEmpresa) | **GET** /empresas/{empresa} | Obtener empresa por ID
[**getEmpresas**](EmpresasApi.md#getEmpresas) | **GET** /empresas | Listar empresas validadas
[**updateEmpresa**](EmpresasApi.md#updateEmpresa) | **PUT** /empresas/{empresa} | Actualizar empresa
[**validarEmpresa**](EmpresasApi.md#validarEmpresa) | **PATCH** /empresas/{empresa}/validar | Validar empresa (solo administración)



## createEmpresa

> EmpresaResponse createEmpresa(empresaInput)

Registrar nueva empresa

### Example

```javascript
import ProviEmpleaApi from 'provi_emplea_api';

let apiInstance = new ProviEmpleaApi.EmpresasApi();
let empresaInput = new ProviEmpleaApi.EmpresaInput(); // EmpresaInput | 
apiInstance.createEmpresa(empresaInput, (error, data, response) => {
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
 **empresaInput** | [**EmpresaInput**](EmpresaInput.md)|  | 

### Return type

[**EmpresaResponse**](EmpresaResponse.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: application/json
- **Accept**: application/json


## deleteEmpresa

> MessageResponse deleteEmpresa(empresa)

Desactivar empresa

Desactiva el perfil sin eliminarlo de la base de datos. Rate limit: 30 solicitudes por minuto por IP.

### Example

```javascript
import ProviEmpleaApi from 'provi_emplea_api';

let apiInstance = new ProviEmpleaApi.EmpresasApi();
let empresa = "550e8400-e29b-41d4-a716-446655440001"; // String | 
apiInstance.deleteEmpresa(empresa, (error, data, response) => {
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
 **empresa** | **String**|  | 

### Return type

[**MessageResponse**](MessageResponse.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: application/json


## getEmpresa

> EmpresaResponse getEmpresa(empresa)

Obtener empresa por ID

Consulta de lectura con rate limit de 120 solicitudes por minuto por IP.

### Example

```javascript
import ProviEmpleaApi from 'provi_emplea_api';

let apiInstance = new ProviEmpleaApi.EmpresasApi();
let empresa = "550e8400-e29b-41d4-a716-446655440001"; // String | 
apiInstance.getEmpresa(empresa, (error, data, response) => {
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
 **empresa** | **String**|  | 

### Return type

[**EmpresaResponse**](EmpresaResponse.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: application/json


## getEmpresas

> EmpresaListResponse getEmpresas(opts)

Listar empresas validadas

Obtiene empresas activas. Rate limit: 120 solicitudes por minuto por IP. Se recomienda filtrar por tipo cuando se integre desde clientes externos.

### Example

```javascript
import ProviEmpleaApi from 'provi_emplea_api';

let apiInstance = new ProviEmpleaApi.EmpresasApi();
let opts = {
  'tipoEmpresa': "tipoEmpresa_example" // String | 
};
apiInstance.getEmpresas(opts, (error, data, response) => {
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
 **tipoEmpresa** | **String**|  | [optional] 

### Return type

[**EmpresaListResponse**](EmpresaListResponse.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: application/json


## updateEmpresa

> EmpresaResponse updateEmpresa(empresa, empresaInput)

Actualizar empresa

### Example

```javascript
import ProviEmpleaApi from 'provi_emplea_api';

let apiInstance = new ProviEmpleaApi.EmpresasApi();
let empresa = "550e8400-e29b-41d4-a716-446655440001"; // String | 
let empresaInput = new ProviEmpleaApi.EmpresaInput(); // EmpresaInput | 
apiInstance.updateEmpresa(empresa, empresaInput, (error, data, response) => {
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
 **empresa** | **String**|  | 
 **empresaInput** | [**EmpresaInput**](EmpresaInput.md)|  | 

### Return type

[**EmpresaResponse**](EmpresaResponse.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: application/json
- **Accept**: application/json


## validarEmpresa

> EmpresaValidationResponse validarEmpresa(empresa)

Validar empresa (solo administración)

Marca una empresa como validada. Rate limit: 30 solicitudes por minuto por IP.

### Example

```javascript
import ProviEmpleaApi from 'provi_emplea_api';

let apiInstance = new ProviEmpleaApi.EmpresasApi();
let empresa = "550e8400-e29b-41d4-a716-446655440001"; // String | 
apiInstance.validarEmpresa(empresa, (error, data, response) => {
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
 **empresa** | **String**|  | 

### Return type

[**EmpresaValidationResponse**](EmpresaValidationResponse.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: application/json

