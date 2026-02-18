import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { Provider } from 'react-redux'
import { store } from './store/store'
import App from './App.jsx'
import ErrorBoundary from './components/ErrorBoundary';
import axios from './utils/axiosConfig';

const BASE_URL = import.meta.env.VITE_API_BASE_URL || "http://localhost:8000";
axios.defaults.baseURL = BASE_URL;
axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Fetch CSRF cookie
axios.get('/sanctum/csrf-cookie').catch((error) => {
  console.warn('CSRF cookie fetch failed:', error.message);
});

createRoot(document.getElementById('root')).render(
  <StrictMode>
    <Provider store={store}>
      <ErrorBoundary>
        <App />
      </ErrorBoundary>
    </Provider>
  </StrictMode>,
)
