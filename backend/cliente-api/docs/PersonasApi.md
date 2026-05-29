# ProviEmpleaApi.PersonasApi

All URIs are relative to *http://localhost:8080/api*

Method | HTTP request | Description
------------- | ------------- | -------------
[**createPersona**](PersonasApi.md#createPersona) | **POST** /personas | Registrar nueva persona/talento
[**deletePersona**](PersonasApi.md#deletePersona) | **DELETE** /personas/{persona} | Desactivar persona
[**getPersona**](PersonasApi.md#getPersona) | **GET** /personas/{persona} | Obtener persona por ID
[**getPersonas**](PersonasApi.md#getPersonas) | **GET** /personas | Listar personas (CV ciego)
[**updatePersona**](PersonasApi.md#updatePersona) | **PUT** /personas/{persona} | Actualizar persona
[**validarPersona**](PersonasApi.md#validarPersona) | **PATCH** /personas/{persona}/validar | Validar persona (solo administración)



## createPersona

> PersonaResponse createPersona(personaInput)

Registrar nueva persona/talento

Crea un perfil de talento. El código se genera automáticamente.

### Example

```javascript
import ProviEmpleaApi from 'provi_emplea_api';

let apiInstance = new ProviEmpleaApi.PersonasApi();
let personaInput = new ProviEmpleaApi.PersonaInput(); // PersonaInput | 
apiInstance.createPersona(personaInput, (error, data, response) => {
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
 **personaInput** | [**PersonaInput**](PersonaInput.md)|  | 

### Return type

[**PersonaResponse**](PersonaResponse.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: application/json
- **Accept**: application/json


## deletePersona

> MessageResponse deletePersona(persona)

Desactivar persona

Desactiva el perfil sin eliminarlo de la base de datos. Rate limit: 30 solicitudes por minuto por IP.

### Example

```javascript
import ProviEmpleaApi from 'provi_emplea_api';

let apiInstance = new ProviEmpleaApi.PersonasApi();
let persona = "550e8400-e29b-41d4-a716-446655440000"; // String | 
apiInstance.deletePersona(persona, (error, data, response) => {
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
 **persona** | **String**|  | 

### Return type

[**MessageResponse**](MessageResponse.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: application/json


## getPersona

> PersonaResponse getPersona(persona)

Obtener persona por ID

Consulta de lectura con rate limit de 120 solicitudes por minuto por IP.

### Example

```javascript
import ProviEmpleaApi from 'provi_emplea_api';

let apiInstance = new ProviEmpleaApi.PersonasApi();
let persona = "550e8400-e29b-41d4-a716-446655440000"; // String | 
apiInstance.getPersona(persona, (error, data, response) => {
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
 **persona** | **String**|  | 

### Return type

[**PersonaResponse**](PersonaResponse.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: application/json


## getPersonas

> PersonaListResponse getPersonas(opts)

Listar personas (CV ciego)

Obtiene talentos activos en formato de CV ciego (sin datos personales identificables). Rate limit: 120 solicitudes por minuto por IP. Se recomienda aplicar filtros para reducir payload y paginar si el volumen crece.

### Example

```javascript
import ProviEmpleaApi from 'provi_emplea_api';

let apiInstance = new ProviEmpleaApi.PersonasApi();
let opts = {
  'validado': true, // Boolean | Filtrar por validación
  'nivelEducacional': "nivelEducacional_example" // String | 
};
apiInstance.getPersonas(opts, (error, data, response) => {
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
 **validado** | **Boolean**| Filtrar por validación | [optional] 
 **nivelEducacional** | **String**|  | [optional] 

### Return type

[**PersonaListResponse**](PersonaListResponse.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: application/json


## updatePersona

> PersonaResponse updatePersona(persona, personaInput)

Actualizar persona

### Example

```javascript
import ProviEmpleaApi from 'provi_emplea_api';

let apiInstance = new ProviEmpleaApi.PersonasApi();
let persona = "550e8400-e29b-41d4-a716-446655440000"; // String | 
let personaInput = new ProviEmpleaApi.PersonaInput(); // PersonaInput | 
apiInstance.updatePersona(persona, personaInput, (error, data, response) => {
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
 **persona** | **String**|  | 
 **personaInput** | [**PersonaInput**](PersonaInput.md)|  | 

### Return type

[**PersonaResponse**](PersonaResponse.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: application/json
- **Accept**: application/json


## validarPersona

> PersonaValidationResponse validarPersona(persona)

Validar persona (solo administración)

Marca a una persona como validada para que aparezca en la vitrina. Rate limit: 30 solicitudes por minuto por IP.

### Example

```javascript
import ProviEmpleaApi from 'provi_emplea_api';

let apiInstance = new ProviEmpleaApi.PersonasApi();
let persona = "550e8400-e29b-41d4-a716-446655440000"; // String | 
apiInstance.validarPersona(persona, (error, data, response) => {
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
 **persona** | **String**|  | 

### Return type

[**PersonaValidationResponse**](PersonaValidationResponse.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: application/json

