import React, { useState, useEffect } from 'react';
import { studentService, attendanceService, teacherService } from '../services/api';
import { Calendar, Users, ShieldCheck, CheckCircle2, XCircle, Clock, AlertCircle, Save, Send } from 'lucide-react';

const AttendanceEntry = () => {
  const [step, setStep] = useState(1); // 1: Login/Setup, 2: Marking
  const [setup, setSetup] = useState({
    date: '2082/12/18', // Default Nepali Date (Approx)
    studentClass: '',
    session: 'Morning',
    teacherId: '',
    password: ''
  });
  const [teachers, setTeachers] = useState([]);
  const [students, setStudents] = useState([]);
  const [attendance, setAttendance] = useState({}); // { studentId: status }
  const [msg, setMsg] = useState('');

  // Use static class list or fetch from students
  const classes = ['Nursery', 'LKG', 'UKG', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'];

  useEffect(() => {
    fetchTeachers();
  }, []);

  const fetchTeachers = async () => {
    try {
      const resp = await teacherService.getAll({ schoolId: sessionStorage.getItem('institutionId') });
      setTeachers(resp.data);
    } catch (err) {
      console.error(err);
    }
  };

  const handleDateChange = (e) => {
    let val = e.target.value.replace(/[^0-9/]/g, '');
    const parts = val.split('/').join('');
    let formatted = parts;
    if (parts.length > 4) formatted = parts.slice(0, 4) + '/' + parts.slice(4);
    if (parts.length > 6) formatted = formatted.slice(0, 7) + '/' + formatted.slice(7);
    if (formatted.length <= 10) setSetup({ ...setup, date: formatted });
  };

  const handleSetupSubmit = async (e) => {
    e.preventDefault();
    // Simulate Gate Login (In a real app, this would be a secure backend check)
    const teacher = teachers.find(t => t.id === parseInt(setup.teacherId));
    if (teacher && teacher.teacherPassword === setup.password) {
      setStep(2);
      fetchStudentsAndAttendance();
    } else {
      alert('Invalid credentials!');
    }
  };

  const fetchStudentsAndAttendance = async () => {
    try {
      // 1. Fetch Students in Class
      const studentsResp = await studentService.getAll({ schoolId: sessionStorage.getItem('institutionId'), studentClass: setup.studentClass });
      setStudents(studentsResp.data);

      // 2. Fetch Existing Attendance
      const dbDate = setup.date.split('/').join('-');
      const attResp = await attendanceService.get({ 
        schoolId: sessionStorage.getItem('institutionId'), 
        studentClass: setup.studentClass, 
        attendanceDate: dbDate, 
        session: setup.session 
      });
      
      const attMap = {};
      attResp.data.forEach(a => {
        attMap[a.studentId] = a.status;
      });
      
      // Default to Present if no record
      const initialAtt = { ...attMap };
      studentsResp.data.forEach(s => {
        if (!initialAtt[s.id]) initialAtt[s.id] = 'Present';
      });
      setAttendance(initialAtt);

    } catch (err) {
      console.error(err);
    }
  };

  const handleStatusChange = (studentId, status) => {
    setAttendance(prev => ({ ...prev, [studentId]: status }));
  };

  const handleSave = async (sendSms = false) => {
    try {
      const dbDate = setup.date.split('/').join('-');
      const payload = Object.entries(attendance).map(([studentId, status]) => ({
        schoolId: sessionStorage.getItem('institutionId'),
        studentId: parseInt(studentId),
        studentClass: setup.studentClass,
        attendanceDate: dbDate,
        session: setup.session,
        status: status
      }));

      await attendanceService.saveBulk(payload);
      setMsg(`Attendance saved successfully! ${sendSms ? 'Simulating SMS to guardians...' : ''}`);
      setTimeout(() => setMsg(''), 5000);
    } catch (err) {
      console.error(err);
      alert('Error saving attendance');
    }
  };

  if (step === 1) {
    return (
      <div className="max-w-md mx-auto mt-20">
        <div className="bg-white p-8 rounded-[32px] shadow-2xl border border-slate-100">
          <div className="w-16 h-16 bg-indigo-600 rounded-2xl flex items-center justify-center text-white mb-6 mx-auto shadow-lg shadow-indigo-100">
            <Calendar size={32} />
          </div>
          <h2 className="text-2xl font-black text-center text-slate-900 mb-2">Attendance Portal</h2>
          <p className="text-center text-slate-500 font-medium mb-8 uppercase text-xs tracking-widest">Authorized Access Only</p>
          
          <form onSubmit={handleSetupSubmit} className="space-y-4">
            <div>
              <label className="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Verification Date</label>
              <input 
                type="text" 
                placeholder="YYYY/MM/DD (Nepali Date)"
                className="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 font-bold" 
                value={setup.date} 
                onChange={handleDateChange} 
              />
            </div>
            
            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Class</label>
                <select className="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 font-bold capitalize" value={setup.studentClass} onChange={(e) => setSetup({...setup, studentClass: e.target.value})} required>
                  <option value="">Select</option>
                  {classes.map(c => <option key={c} value={c}>{c}</option>)}
                </select>
              </div>
              <div>
                <label className="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Session</label>
                <select className="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 font-bold" value={setup.session} onChange={(e) => setSetup({...setup, session: e.target.value})}>
                  <option value="Morning">Morning</option>
                  <option value="Evening">Evening</option>
                </select>
              </div>
            </div>

            <div>
              <label className="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Assigned Staff</label>
              <select className="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 font-bold" value={setup.teacherId} onChange={(e) => setSetup({...setup, teacherId: e.target.value})} required>
                <option value="">Select Official</option>
                {teachers.map(t => <option key={t.id} value={t.id}>{t.fullName}</option>)}
              </select>
            </div>

            <div>
              <label className="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Access PIN</label>
              <input type="password" placeholder="••••••••" className="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 font-bold" value={setup.password} onChange={(e) => setSetup({...setup, password: e.target.value})} required />
            </div>

            <button type="submit" className="w-full py-4 bg-indigo-600 text-white rounded-2xl font-black mt-4 shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition">
              Initialize Tracking
            </button>
          </form>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-5xl mx-auto space-y-6 pb-20">
      {/* Header Info */}
      <div className="bg-indigo-600 p-8 rounded-[32px] text-white flex flex-col md:flex-row justify-between items-center gap-6 shadow-xl shadow-indigo-100">
        <div className="flex items-center gap-4">
          <div className="p-3 bg-white/10 rounded-2xl backdrop-blur-md">
            <Users size={32} />
          </div>
          <div>
            <h1 className="text-3xl font-black tracking-tight">Daily Attendance</h1>
            <p className="font-bold opacity-80 uppercase text-[10px] tracking-widest mt-1">Class {setup.studentClass} • {setup.session} • {setup.date}</p>
          </div>
        </div>
        <button onClick={() => setStep(1)} className="px-6 py-2 bg-white/10 hover:bg-white/20 rounded-xl font-bold transition text-sm">Change Session</button>
      </div>

      {msg && (
        <div className="bg-emerald-50 border border-emerald-100 p-4 rounded-2xl flex items-center gap-3 text-emerald-700 font-bold animate-in fade-in slide-in-from-top-4 duration-300">
          <CheckCircle2 size={20} />
          {msg}
        </div>
      )}

      {/* Students List */}
      <div className="bg-white rounded-[32px] shadow-sm border border-slate-100 overflow-hidden">
        <table className="w-full text-left">
          <thead>
            <tr className="bg-slate-50 text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100">
              <th className="px-8 py-5">Roll / ID</th>
              <th className="px-6 py-5">Student Identity</th>
              <th className="px-10 py-5 text-right">Status Assignment</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-50">
            {students.map(s => (
              <tr key={s.id} className="group hover:bg-slate-50/50 transition">
                <td className="px-8 py-6 font-black text-slate-400">{s.symbolNo}</td>
                <td className="px-6 py-6 ring-slate-900 group-hover:text-indigo-600 transition font-extrabold text-slate-800">{s.fullName}</td>
                <td className="px-8 py-6">
                  <div className="flex justify-end gap-2">
                    {[
                      { id: 'Present', label: 'P', color: 'emerald', icon: CheckCircle2 },
                      { id: 'Absent', label: 'A', color: 'rose', icon: XCircle },
                      { id: 'Leave', label: 'L', color: 'amber', icon: AlertCircle },
                      { id: 'Extra Class', label: 'Ex', color: 'indigo', icon: Clock }
                    ].map(st => (
                      <button
                        key={st.id}
                        onClick={() => handleStatusChange(s.id, st.id)}
                        className={`w-12 h-12 rounded-xl flex items-center justify-center font-black transition relative overflow-hidden group/btn ${
                          attendance[s.id] === st.id 
                            ? `bg-${st.color}-600 text-white shadow-lg shadow-${st.color}-100 scale-105 ring-2 ring-${st.color}-500 ring-offset-2` 
                            : `bg-slate-50 text-slate-400 hover:bg-${st.color}-50 hover:text-${st.color}-600`
                        }`}
                        title={st.id}
                      >
                         <span className="text-xs">{st.label}</span>
                      </button>
                    ))}
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      <div className="fixed bottom-8 left-1/2 -translate-x-1/2 flex gap-4">
        <button 
          onClick={() => handleSave(false)}
          className="flex items-center gap-3 px-10 py-4 bg-slate-900 text-white rounded-2xl font-black shadow-2xl hover:bg-slate-800 transition group"
        >
          <Save size={20} className="group-hover:translate-y-0.5 transition" />
          Save Attendance
        </button>
        <button 
          onClick={() => handleSave(true)}
          className="flex items-center gap-3 px-10 py-4 bg-indigo-600 text-white rounded-2xl font-black shadow-2xl hover:bg-indigo-700 transition group"
        >
          <Send size={20} className="group-hover:-translate-y-0.5 group-hover:translate-x-0.5 transition" />
          Save & Notify Guardians
        </button>
      </div>
    </div>
  );
};

export default AttendanceEntry;
