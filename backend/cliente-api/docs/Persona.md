# ProviEmpleaApi.Persona

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **String** |  | [optional] 
**email** | **String** |  | 
**telefono** | **String** |  | [optional] 
**codigoTalento** | **String** |  | 
**resumen** | **String** |  | [optional] 
**nivelEducacional** | **String** |  | [optional] 
**tituloCarrera** | **String** |  | [optional] 
**anioEgreso** | **Number** |  | [optional] 
**aniosExperiencia** | **Number** |  | [optional] 
**areasExperiencia** | **[String]** |  | [optional] 
**competencias** | **[String]** |  | [optional] 
**rangoRenta** | **String** |  | [optional] 
**tipoJornada** | **String** |  | [optional] 
**modalidad** | **String** |  | [optional] 
**cursos** | [**[PersonaCursosInner]**](PersonaCursosInner.md) |  | [optional] 
**idiomas** | [**[PersonaIdiomasInner]**](PersonaIdiomasInner.md) |  | [optional] 
**portafolioUrl** | **String** |  | [optional] 
**personaDiscapacidad** | **Boolean** |  | [optional] 
**validado** | **Boolean** |  | [optional] 
**activo** | **Boolean** |  | [optional] 
**porcentajeCompletitud** | **Number** |  | [optional] 
**createdAt** | **Date** |  | [optional] 
**updatedAt** | **Date** |  | [optional] 



## Enum: NivelEducacionalEnum


* `basica` (value: `"basica"`)

* `media` (value: `"media"`)

* `tecnica` (value: `"tecnica"`)

* `universitaria` (value: `"universitaria"`)

* `postgrado` (value: `"postgrado"`)





## Enum: TipoJornadaEnum


* `completa` (value: `"completa"`)

* `part-time` (value: `"part-time"`)

* `por-horas` (value: `"por-horas"`)





## Enum: ModalidadEnum


* `presencial` (value: `"presencial"`)

* `remoto` (value: `"remoto"`)

* `hibrido` (value: `"hibrido"`)




