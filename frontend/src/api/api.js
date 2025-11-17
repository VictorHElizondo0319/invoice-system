import axios from 'axios';

const API_BASE_URL = (typeof window !== 'undefined' && window.__env && window.__env.VITE_API_URL)
  || import.meta.env.VITE_API_URL
  || 'http://localhost:8000/api';

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
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
  const response = await api.post(`/invoice/${id}/export`, null, {
    responseType: 'blob',
  });
  return response;
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


