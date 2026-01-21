import axios from 'axios';

let routerInstance = null;

export const setRepositoryRouter = (router) => {
    routerInstance = router;
};

const handleError = (error) => {
    const status = error && error.response ? error.response.status : null;

    if (status === 401) {
        if (routerInstance) {
            routerInstance.push('/login');
        } else {
            window.location.assign('/login');
        }
    }

    if (status === 404) {
        if (routerInstance) {
            routerInstance.push('/not-found');
        } else {
            window.location.assign('/not-found');
        }
    }

    return Promise.reject(error);
};

axios.interceptors.response.use(
    (response) => response,
    handleError
);

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
    delete(url, config) {
        return axios.delete(url, config);
    },
};

export default Repository;
