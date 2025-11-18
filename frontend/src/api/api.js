import axios from 'axios';

const API_BASE_URL = '/api';

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Attach JWT token if available
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('authToken');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Invoices
export const getInvoices = async (filters = {}) => {
  const params = new URLSearchParams(filters).toString();
  const { data } = await api.get(`/invoices${params ? `?${params}` : ''}`);
  return data;
};

export const getInvoice = async (id) => {
  const { data } = await api.get(`/invoices/${id}`);
  return data;
};

export const createInvoice = async (invoiceData) => {
  const { data } = await api.post('/invoices', invoiceData);
  return data;
};

export const updateInvoice = async ({ id, ...invoiceData }) => {
  const { data } = await api.put(`/invoices/${id}`, invoiceData);
  return data;
};

export const deleteInvoice = async (id) => {
  const { data } = await api.delete(`/invoices/${id}`);
  return data;
};

export const submitInvoice = async (id) => {
  const { data } = await api.put(`/invoices/${id}/submit`);
  return data;
};

// Export invoice as PDF (returns blob)
export const exportInvoice = async (id) => {
  const { data } = await api.post(`/invoice/${id}/export`);
  return data;
};

// Customers
export const getUsers = async (q) => {

  const { data } = await api.get(`/users`);
  return data;
};

export const createUser = async (payload) => {
  const { data } = await api.post('/users', payload);
  return data;
};

// Auth
export const login = async (email, password) => {
  const { data } = await api.post('/auth/login', { email, password });
  if (data && data.token) {
    localStorage.setItem('authToken', data.token);
    localStorage.setItem('authUser', JSON.stringify(data.user));
  }
  return data;
};

export const register = async (payload) => {
  const { data } = await api.post('/auth/register', payload);
  if (data && data.token) {
    localStorage.setItem('authToken', data.token);
    localStorage.setItem('authUser', JSON.stringify(data.user));
  }
  return data;
};

export const logout = async () => {
  try {
    await api.post('/auth/logout');
  } catch (e) {
    // ignore
  }
  localStorage.removeItem('authToken');
  localStorage.removeItem('authUser');
};

export const getCurrentUser = () => {
  const raw = localStorage.getItem('authUser');
  return raw ? JSON.parse(raw) : null;
};

// Payments
export const createPayment = async (paymentData) => {
  const { data } = await api.post('/payments', paymentData);
  return data;
};

export const getPayments = async (invoiceId) => {
  const params = invoiceId ? `?invoice_id=${invoiceId}` : '';
  const { data } = await api.get(`/payments${params}`);
  return data;
};

// Reports
export const getSummary = async () => {
  const { data } = await api.get('/reports/summary');
  return data;
};

export const getAnalytics = async () => {
  const { data } = await api.get('/reports/analytics');
  return data;
};

export default api;


