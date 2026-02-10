import axios from "axios";

// Compute base root (ensure we don't include a trailing /api)
const rawBase = import.meta.env.VITE_API_BASE_URL || "http://localhost:8000";
const BASE_ROOT = String(rawBase).replace(/\/api\/?$/i, "");
// Set axios base to the app root (so CSRF route at /sanctum/csrf-cookie works)
axios.defaults.baseURL = BASE_ROOT;
axios.defaults.withCredentials = true;
axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

// Add auth token to requests
axios.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem("auth_token");
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error),
);

// Handle 401 responses globally
axios.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem("auth_token");
      localStorage.removeItem("user_email");
      window.location.href = "/login";
    }
    return Promise.reject(error);
  },
);

export default axios;
