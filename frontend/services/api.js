const API_BASE_URL = 'http://localhost/FootwearStore%20Tarik%20Coralic/backend/rest';

export const getApiUrl = (endpoint) => {
    return `${API_BASE_URL}${endpoint}`;
};
