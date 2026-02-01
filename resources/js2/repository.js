import axios from 'axios';

let routerInstance = null;

export const setRepositoryRouter = (router) => {
    routerInstance = router;
};

const handleError = (error) => {
    const status = error && error.response ? error.response.status : null;

    if (status === 401) {
        localStorage.removeItem('token');
        if (routerInstance) {
            routerInstance.push('/blu/login');
        } else {
            window.location.assign('/blu/login');
        }
    }

    if (status === 404) {
        if (routerInstance) {
            routerInstance.push('/blu/not-found');
        } else {
            window.location.assign('/blu/not-found');
        }
    }

    return Promise.reject(error);
};

axios.interceptors.response.use(
    (response) => response,
    handleError
);

axios.interceptors.request.use((config) => {
    const token = localStorage.getItem('token');

    if (token) {
        config.headers = config.headers || {};
        config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
});

const Repository = {
    get(url, config) {
        return axios.get(url, config);
    },
    fetch(url, config) {
        return axios.get(url, config);
    },
    post(url, payload, config) {
        return axios.post(url, payload, config);
    },
    put(url, payload, config) {
        return axios.put(url, payload, config);
    },
    patch(url, payload, config) {
        return axios.patch(url, payload, config);
    },
    delete(url, config) {
        return axios.delete(url, config);
    },
};

export default Repository;
