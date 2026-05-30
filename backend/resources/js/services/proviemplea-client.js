import ProviEmpleaApi from 'provi_emplea_api';

const apiClient = new ProviEmpleaApi.ApiClient('/api');

export const administracionApi = new ProviEmpleaApi.AdministracinApi(apiClient);
export const empresasApi = new ProviEmpleaApi.EmpresasApi(apiClient);
export const healthApi = new ProviEmpleaApi.HealthApi(apiClient);
export const personasApi = new ProviEmpleaApi.PersonasApi(apiClient);

export function callApi(apiMethod, ...args) {
    return new Promise((resolve, reject) => {
        apiMethod(...args, (error, data, response) => {
            if (error) {
                reject({ error, response });
                return;
            }

            resolve({ data, response });
        });
    });
}

export { apiClient };
