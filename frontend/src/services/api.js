import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8080/api',
  headers: {
    'Content-Type': 'application/json'
  }
});

export const studentService = {
  getAll: (params) => api.get('/students', { params }),
  getById: (id) => api.get(`/students/${id}`),
  create: (data) => api.post('/students', data),
  update: (id, data) => api.put(`/students/${id}`, data),
  delete: (id) => api.delete(`/students/${id}`)
};

export const teacherService = {
  getAll: (params) => api.get('/teachers', { params }),
  getById: (id) => api.get(`/teachers/${id}`),
  create: (data) => api.post('/teachers', data),
  update: (id, data) => api.put(`/teachers/${id}`, data),
  delete: (id) => api.delete(`/teachers/${id}`)
};

export const attendanceService = {
  get: (params) => api.get('/attendance', { params }),
  saveBulk: (data) => api.post('/attendance/bulk', data)
};

export const examService = {
  getSubjects: (params) => api.get('/exams/subjects', { params }),
  saveSubject: (data) => api.post('/exams/subjects', data),
  deleteSubject: (id) => api.delete(`/exams/subjects/${id}`),
  getMarks: (params) => api.get('/exams/marks', { params }),
  saveMarksBulk: (data) => api.post('/exams/marks/bulk', data),
  getLedger: (params) => api.get('/exams/ledger', { params }),
  getAttendance: (params) => api.get('/exams/attendance', { params }),
  saveAttendance: (data) => api.post('/exams/attendance', data)
};

export default api;

