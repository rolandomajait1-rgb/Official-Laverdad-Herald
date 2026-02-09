const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';

export const apiEndpoints = {
  articles: `${API_BASE_URL}/articles`,
  login: `${API_BASE_URL}/login`,
  register: `${API_BASE_URL}/register`,
  categories: `${API_BASE_URL}/categories`,
};

export const apiRequest = async (url, options = {}) => {
  const token = localStorage.getItem('auth_token');
  
  const defaultHeaders = {
    'Accept': 'application/json',
    ...(token && { 'Authorization': `Bearer ${token}` }),
  };

  const config = {
    ...options,
    headers: {
      ...defaultHeaders,
      ...options.headers,
    },
  };

  const response = await fetch(url, config);
  
  if (!response.ok) {
    const errorData = await response.json();
    throw new Error(errorData.message || 'API request failed');
  }
  
  return response.json();
};