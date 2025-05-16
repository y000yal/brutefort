import axios, { AxiosInstance, InternalAxiosRequestConfig } from 'axios';

// Declare global BruteFortData object (adjust if it's imported)
declare const BruteFortData: { nonce: string };

// Create axios instance
const api: AxiosInstance = axios.create({
    baseURL: BruteFortData.restUrl,
});

// Properly typed request interceptor
api.interceptors.request.use(
    (config: InternalAxiosRequestConfig): InternalAxiosRequestConfig => {
        config.headers['X-WP-Nonce'] = BruteFortData.nonce;
        return config;
    },
    error => Promise.reject(error)
);

export default api;
