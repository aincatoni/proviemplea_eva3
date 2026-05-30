const expect = require('expect.js');
const path = require('path');
const { execSync } = require('child_process');
const ProviEmpleaApi = require(path.join(process.cwd(), 'src/index'));

const API_BASE_URL = process.env.PROVIEMPLEA_API_BASE_URL || 'http://localhost:8080/api';
const PROJECT_ROOT = path.resolve(process.cwd(), '..', '..');
const RESET_COMMAND = process.env.PROVIEMPLEA_RESET_COMMAND || 'docker compose exec -T app php artisan migrate:fresh --force';

function configureApiClient() {
  ProviEmpleaApi.ApiClient.instance.basePath = API_BASE_URL;
  ProviEmpleaApi.ApiClient.instance.timeout = 15000;
}

function resetBackendState() {
  try {
    execSync(RESET_COMMAND, {
      cwd: PROJECT_ROOT,
      stdio: 'inherit',
    });
  } catch (error) {
    throw new Error(
      `No se pudo reiniciar el backend con \`${RESET_COMMAND}\`. ` +
      'Levanta los contenedores y/o define PROVIEMPLEA_RESET_COMMAND con el comando correcto.'
    );
  }
}

function callApi(api, method, ...args) {
  return new Promise((resolve, reject) => {
    api[method](...args, (error, data, response) => {
      if (error) {
        if (response && !error.response) {
          error.response = response;
        }

        reject(error);
        return;
      }

      resolve({ data, response });
    });
  });
}

async function expectApiError(request, expectedStatus, expectedMessage) {
  try {
    await request;
  } catch (error) {
    const status = error.status || (error.response && error.response.status);
    const body = error.response && error.response.body ? error.response.body : {};

    expect(status).to.be(expectedStatus);

    if (expectedMessage) {
      expect(body.message).to.be(expectedMessage);
    }

    return body;
  }

  throw new Error(`Se esperaba un error HTTP ${expectedStatus} y la llamada fue exitosa.`);
}

function uniqueValue(prefix) {
  return `${prefix}-${Date.now()}-${Math.floor(Math.random() * 100000)}`;
}

describe('Cliente OpenAPI contra backend real', function() {
  this.timeout(120000);

  const state = {};
  let healthApi;
  let personasApi;
  let empresasApi;
  let administracionApi;

  before(function() {
    configureApiClient();
    resetBackendState();

    healthApi = new ProviEmpleaApi.HealthApi();
    personasApi = new ProviEmpleaApi.PersonasApi();
    empresasApi = new ProviEmpleaApi.EmpresasApi();
    administracionApi = new ProviEmpleaApi.AdministracinApi();
  });

  it('verifica el endpoint de health', async function() {
    const { response } = await callApi(healthApi, 'healthCheck');

    expect(response.status).to.be(200);
    expect(response.body.status).to.be('online');
    expect(response.body.service).to.be('ProviEmplea API');
    expect(response.body.version).to.be('1.0.0');
    expect(response.body.timestamp).to.be.ok();
  });

  it('ejecuta el flujo CRUD principal de personas', async function() {
    const createPayload = {
      email: `${uniqueValue('persona')}@example.com`,
      telefono: '+56912345678',
      resumen: 'Perfil orientado a desarrollo backend.',
      nivel_educacional: 'universitaria',
      titulo_carrera: 'Ingenieria en Informatica',
      anio_egreso: 2022,
      anios_experiencia: 2,
      areas_experiencia: ['Desarrollo Web', 'APIs REST'],
      competencias: ['PHP', 'Laravel'],
      rango_renta: '800k-1.2M',
      tipo_jornada: 'completa',
      modalidad: 'hibrido',
      portafolio_url: 'https://example.com/portafolio',
      persona_discapacidad: false,
    };

    const createResult = await callApi(personasApi, 'createPersona', createPayload);
    const createBody = createResult.response.body;

    expect(createResult.response.status).to.be(201);
    expect(createBody.success).to.be(true);
    expect(createBody.data.email).to.be(createPayload.email);
    expect(createBody.data.codigo_talento.indexOf('PROV-')).to.be(0);

    state.personaId = createBody.data.id;

    const validationBody = await expectApiError(
      callApi(personasApi, 'createPersona', {
        email: 'correo-invalido',
        nivel_educacional: 'doctorado',
        portafolio_url: 'no-es-url',
      }),
      422,
      'Los datos enviados no son válidos.'
    );

    expect(validationBody.errors.email).to.be.ok();
    expect(validationBody.errors.nivel_educacional).to.be.ok();
    expect(validationBody.errors.portafolio_url).to.be.ok();

    const showResult = await callApi(personasApi, 'getPersona', state.personaId);
    expect(showResult.response.status).to.be(200);
    expect(showResult.response.body.data.id).to.be(state.personaId);
    expect(showResult.response.body.data.email).to.be(createPayload.email);

    const updateResult = await callApi(personasApi, 'updatePersona', state.personaId, {
      email: `${uniqueValue('persona-actualizada')}@example.com`,
      resumen: 'Perfil actualizado',
      modalidad: 'remoto',
      anios_experiencia: 4,
    });

    expect(updateResult.response.status).to.be(200);
    expect(updateResult.response.body.success).to.be(true);
    expect(updateResult.response.body.data.modalidad).to.be('remoto');
    state.personaEmail = updateResult.response.body.data.email;

    const validateResult = await callApi(personasApi, 'validarPersona', state.personaId);
    expect(validateResult.response.status).to.be(200);
    expect(validateResult.response.body.data.message).to.be('Persona validada exitosamente.');
    expect(validateResult.response.body.data.data.validado).to.be(true);

    const listResult = await callApi(personasApi, 'getPersonas', { validado: true });
    expect(listResult.response.status).to.be(200);

    const listedPersona = listResult.response.body.data.find((item) => item.id === state.personaId);
    expect(listedPersona).to.be.ok();
    expect(listedPersona.email).to.be(undefined);

    const missingBody = await expectApiError(
      callApi(personasApi, 'getPersona', '550e8400-e29b-41d4-a716-446655440099'),
      404,
      'Persona no encontrada.'
    );

    expect(missingBody.success).to.be(false);
  });

  it('ejecuta el flujo CRUD principal de empresas', async function() {
    const createPayload = {
      nombre_empresa: `Tech Corp ${uniqueValue('empresa')}`,
      rut_empresa: '76123456-7',
      email: `${uniqueValue('empresa')}@example.com`,
      logo_url: 'https://example.com/logo.png',
      rubro: 'Tecnologia',
      tipo_empresa: 'contratacion-directa',
      presentacion: 'Empresa enfocada en desarrollo de software.',
      beneficios: ['Trabajo remoto', 'Seguro complementario'],
      contacto_nombre: 'Ana Lopez',
      contacto_email: `${uniqueValue('contacto-empresa')}@example.com`,
      contacto_telefono: '+56912345678',
    };

    const createResult = await callApi(empresasApi, 'createEmpresa', createPayload);
    const createBody = createResult.response.body;

    expect(createResult.response.status).to.be(201);
    expect(createBody.success).to.be(true);
    expect(createBody.data.email).to.be(createPayload.email);
    expect(createBody.data.tipo_empresa).to.be(createPayload.tipo_empresa);

    state.empresaId = createBody.data.id;

    const validationBody = await expectApiError(
      callApi(empresasApi, 'createEmpresa', {
        nombre_empresa: '',
        rut_empresa: '',
        email: 'correo-invalido',
        tipo_empresa: 'otra',
        contacto_email: 'no-email',
      }),
      422,
      'Los datos enviados no son válidos.'
    );

    expect(validationBody.errors.nombre_empresa).to.be.ok();
    expect(validationBody.errors.rut_empresa).to.be.ok();
    expect(validationBody.errors.email).to.be.ok();
    expect(validationBody.errors.tipo_empresa).to.be.ok();

    const showResult = await callApi(empresasApi, 'getEmpresa', state.empresaId);
    expect(showResult.response.status).to.be(200);
    expect(showResult.response.body.data.id).to.be(state.empresaId);
    expect(showResult.response.body.data.email).to.be(createPayload.email);

    const updateResult = await callApi(empresasApi, 'updateEmpresa', state.empresaId, {
      email: `${uniqueValue('empresa-actualizada')}@example.com`,
      tipo_empresa: 'outsourcing',
      contacto_nombre: 'Nuevo Contacto',
      beneficios: ['Capacitaciones'],
    });

    expect(updateResult.response.status).to.be(200);
    expect(updateResult.response.body.success).to.be(true);
    expect(updateResult.response.body.data.tipo_empresa).to.be('outsourcing');

    const validateResult = await callApi(empresasApi, 'validarEmpresa', state.empresaId);
    expect(validateResult.response.status).to.be(200);
    expect(validateResult.response.body.data.message).to.be('Empresa validada exitosamente.');
    expect(validateResult.response.body.data.data.validado).to.be(true);

    const listResult = await callApi(empresasApi, 'getEmpresas', { tipoEmpresa: 'outsourcing' });
    expect(listResult.response.status).to.be(200);

    const listedEmpresa = listResult.response.body.data.find((item) => item.id === state.empresaId);
    expect(listedEmpresa).to.be.ok();
    expect(listedEmpresa.tipo_empresa).to.be('outsourcing');

    const missingBody = await expectApiError(
      callApi(empresasApi, 'getEmpresa', '550e8400-e29b-41d4-a716-446655440199'),
      404,
      'Empresa no encontrada.'
    );

    expect(missingBody.success).to.be(false);
  });

  it('ejecuta el flujo administrativo de contactos y estadisticas', async function() {
    const createResult = await callApi(administracionApi, 'crearContactoSolicitado', {
      empresa_id: state.empresaId,
      persona_id: state.personaId,
      notas_admin: 'Contacto prioritario.',
    });

    expect(createResult.response.status).to.be(201);
    expect(createResult.response.body.success).to.be(true);
    expect(createResult.response.body.data.estado).to.be('pendiente');
    expect(createResult.response.body.data.empresa_id).to.be(state.empresaId);
    expect(createResult.response.body.data.persona_id).to.be(state.personaId);

    state.contactoId = createResult.response.body.data.id;

    const duplicateBody = await expectApiError(
      callApi(administracionApi, 'crearContactoSolicitado', {
        empresa_id: state.empresaId,
        persona_id: state.personaId,
      }),
      409,
      'Ya existe una solicitud activa entre esta empresa y talento.'
    );

    expect(duplicateBody.success).to.be(false);

    const listPendingResult = await callApi(administracionApi, 'getContactosSolicitados', { estado: 'pendiente' });
    expect(listPendingResult.response.status).to.be(200);

    const pendingContact = listPendingResult.response.body.data.find((item) => item.id === state.contactoId);
    expect(pendingContact).to.be.ok();
    expect(pendingContact.estado).to.be('pendiente');

    const contactadoResult = await callApi(administracionApi, 'actualizarEstadoContacto', state.contactoId, {
      estado: 'contactado',
      notas_admin: 'Primer contacto realizado.',
    });

    expect(contactadoResult.response.status).to.be(200);
    expect(contactadoResult.response.body.data.estado).to.be('contactado');
    expect(contactadoResult.response.body.data.notas_admin).to.be('Primer contacto realizado.');

    const entrevistaResult = await callApi(administracionApi, 'actualizarEstadoContacto', state.contactoId, {
      estado: 'entrevista',
    });

    expect(entrevistaResult.response.status).to.be(200);
    expect(entrevistaResult.response.body.data.estado).to.be('entrevista');

    const seleccionadoResult = await callApi(administracionApi, 'actualizarEstadoContacto', state.contactoId, {
      estado: 'seleccionado',
    });

    expect(seleccionadoResult.response.status).to.be(200);
    expect(seleccionadoResult.response.body.data.estado).to.be('seleccionado');

    const invalidStateBody = await expectApiError(
      callApi(administracionApi, 'actualizarEstadoContacto', state.contactoId, {
        estado: 'otro',
      }),
      422
    );

    expect(invalidStateBody.success).to.be(false);
    expect(invalidStateBody.errors.estado).to.be.ok();

    const missingBody = await expectApiError(
      callApi(administracionApi, 'actualizarEstadoContacto', '550e8400-e29b-41d4-a716-446655440299', {
        estado: 'contactado',
      }),
      404,
      'Contacto no encontrado.'
    );

    expect(missingBody.success).to.be(false);

    const statsResult = await callApi(administracionApi, 'getEstadisticas');
    expect(statsResult.response.status).to.be(200);
    expect(statsResult.response.body.success).to.be(true);
    expect(statsResult.response.body.data.total_personas).to.be(1);
    expect(statsResult.response.body.data.personas_validadas).to.be(1);
    expect(statsResult.response.body.data.total_empresas).to.be(1);
    expect(statsResult.response.body.data.empresas_validadas).to.be(1);
    expect(statsResult.response.body.data.contactos_pendientes).to.be(0);
    expect(statsResult.response.body.data.contactos_en_proceso).to.be(0);
    expect(statsResult.response.body.data.contactos_exitosos).to.be(1);
  });

  it('desactiva persona y empresa al final del flujo', async function() {
    const personaDeleteResult = await callApi(personasApi, 'deletePersona', state.personaId);
    expect(personaDeleteResult.response.status).to.be(200);
    expect(personaDeleteResult.response.body.data.message).to.be('Persona desactivada exitosamente.');

    const empresaDeleteResult = await callApi(empresasApi, 'deleteEmpresa', state.empresaId);
    expect(empresaDeleteResult.response.status).to.be(200);
    expect(empresaDeleteResult.response.body.data.message).to.be('Empresa desactivada exitosamente.');
  });
});
