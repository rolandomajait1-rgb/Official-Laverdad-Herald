// API Configuration
export const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'https://official-laverdad-herald.onrender.com';

// Helper to build full URLs for non-axios requests
export const getApiUrl = (path) => {
  const cleanPath = path.startsWith('/') ? path : `/${path}`;
  return `${API_BASE_URL}${cleanPath}`;
};

// Helper for storage URLs
export const getStorageUrl = (path) => {
  if (!path) return null;
  if (path.startsWith('http')) return path;
  const cleanPath = path.startsWith('/') ? path : `/${path}`;
  return `${API_BASE_URL}${cleanPath}`;
};
