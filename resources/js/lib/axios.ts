import axios from 'axios';

export const satgasApi = axios.create({
    baseURL: '/satgas/api',
});
