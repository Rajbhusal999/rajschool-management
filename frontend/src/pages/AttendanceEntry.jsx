import React, { useState, useEffect } from 'react';
import { studentService, attendanceService, teacherService, notificationService } from '../services/api';
import { 
  Calendar, Users, CheckCircle, XCircle,
  X, Clock, AlertCircle, Save, Send, 
  RefreshCw, Phone, BarChart as History
} from 'lucide-react';

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
  const [saving, setSaving] = useState(false);
  const [notificationHistory, setNotificationHistory] = useState([]);
  const [showHistory, setShowHistory] = useState(false);

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

  const fetchNotificationHistory = async () => {
    try {
      const { data } = await notificationService.getRecent(10);
      if (data) setNotificationHistory(data);
    } catch (err) {
      console.error("Failed to fetch history", err);
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
    const teacher = teachers.find(t => t.id === parseInt(setup.teacherId));
    if (teacher && teacher.teacherPassword === setup.password) {
      setStep(2);
      fetchStudentsAndAttendance();
      fetchNotificationHistory();
    } else {
      alert('Invalid credentials!');
    }
  };

  const fetchStudentsAndAttendance = async () => {
    try {
      const studentsResp = await studentService.getAll({ schoolId: sessionStorage.getItem('institutionId'), studentClass: setup.studentClass });
      setStudents(studentsResp.data);

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
    setSaving(true);
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
      
      const logs = [];
      if (sendSms) {
          students.forEach(s => {
              const status = attendance[s.id];
              let sms = '';
              const gName = s.guardianName || 'Guardian';
              const sName = s.fullName;
              
              if (setup.session === 'Morning') {
                  if (status === 'Present') sms = `Good morning ${gName} your children ${sName} is present on today`;
                  else if (status === 'Absent') sms = `Good morning ${gName} your children ${sName} is Absent on today`;
                  else if (status === 'Leave') sms = `Good morning ${gName} your children ${sName} is Leave on today`;
                  else if (status === 'Extra Class') sms = `Good morning ${gName} your children ${sName} is attend the extra class on today`;
              } else if (setup.session === 'Evening') {
                  if (status === 'Present') sms = `Good Evening ${gName} your children ${sName} is left from the school`;
                  else if (status === 'Extra Class') sms = `Good Evening ${gName} your children ${sName} is attend the extra class on today`;
              }
              
              if (sms) {
                  logs.push({
                      studentId: s.id,
                      guardianName: gName,
                      phoneNumber: s.parentContact || s.guardianContact || 'N/A',
                      message: sms,
                      session: setup.session,
                      status: 'LOGGED'
                  });
              }
          });

          if (logs.length > 0) {
              await notificationService.logBulk(logs);
              fetchNotificationHistory();
          }
      }

      setMsg(sendSms ? "Attendance saved & notifications logged!" : "Attendance saved successfully!");
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
      <div className="bg-indigo-50 border border-indigo-100 p-8 rounded-[32px] flex flex-col md:flex-row justify-between items-center gap-6 shadow-sm">
        <div className="flex items-center gap-4">
          <div>
            <h1 className="text-2xl font-black text-indigo-900">{teachers.find(t => t.id === parseInt(setup.teacherId))?.fullName || 'Academic Staff'}</h1>
            <p className="font-bold text-indigo-400 uppercase text-[10px] tracking-widest mt-1">
              Class: {setup.studentClass} | Date: {setup.date} | Session: {setup.session}
            </p>
          </div>
        </div>
        <button onClick={() => setStep(1)} className="px-6 py-2 bg-indigo-600/10 hover:bg-indigo-600/20 text-indigo-600 rounded-xl font-bold transition text-xs flex items-center gap-2">
           <Send size={14} className="rotate-180" /> Change Session
        </button>
      </div>

      {msg && (
        <div className="bg-emerald-50 border border-emerald-100 p-4 rounded-2xl flex items-center gap-3 text-emerald-700 font-bold animate-in fade-in slide-in-from-top-4 duration-300">
          <CheckCircle size={20} />
          {msg}
        </div>
      )}

      {/* Main Form Title */}
      <div className="flex justify-between items-center px-4">
        <h2 className="text-3xl font-black text-slate-900">Daily Attendance</h2>
        <button onClick={() => setStep(1)} className="flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 rounded-full text-slate-500 font-bold transition text-xs">
          <XCircle size={16} /> Close
        </button>
      </div>

      {/* Students List */}
      <div className="bg-white rounded-[32px] shadow-sm border border-slate-100 overflow-hidden">
        <table className="w-full text-left">
          <thead>
            <tr className="bg-slate-50 text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100">
              <th className="px-8 py-5">Symbol No</th>
              <th className="px-6 py-5">Student Name</th>
              <th className="px-10 py-5 text-center">Status Recording</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-50">
            {students.map(s => (
              <tr key={s.id} className="group hover:bg-slate-50/50 transition">
                <td className="px-8 py-6 font-black text-slate-400">{s.symbolNo}</td>
                <td className="px-6 py-6 ring-slate-900 group-hover:text-indigo-600 transition font-extrabold text-slate-800">{s.fullName}</td>
                <td className="px-8 py-6">
                  <div className="flex justify-center gap-3">
                    {setup.session === 'Morning' ? (
                      [
                        { id: 'Present', label: 'Present', color: 'emerald', icon: CheckCircle },
                        { id: 'Absent', label: 'Absent', color: 'rose', icon: XCircle },
                        { id: 'Leave', label: 'Leave', color: 'amber', icon: AlertCircle },
                        { id: 'Extra Class', label: 'Extra Class', color: 'indigo', icon: Clock }
                      ].map(st => {
                        const isActive = attendance[s.id] === st.id;
                        return (
                          <button
                            key={st.id}
                            onClick={() => handleStatusChange(s.id, st.id)}
                            className={`flex items-center gap-2 px-4 py-3 rounded-2xl border-2 transition-all font-bold text-xs ${
                              isActive 
                                ? `bg-${st.color}-50 border-${st.color}-500 text-${st.color}-700 shadow-sm` 
                                : `bg-white border-slate-100 text-slate-400 hover:border-slate-300`
                            }`}
                          >
                            <div className={`w-4 h-4 rounded-full border-2 flex items-center justify-center ${
                              isActive ? `border-${st.color}-600 bg-white` : 'border-slate-200'
                            }`}>
                              {isActive && <div className={`w-2 h-2 rounded-full bg-${st.color}-600 animate-in zoom-in-50`} />}
                            </div>
                            {st.label}
                          </button>
                        );
                      })
                    ) : (
                      // Evening Layout
                      [
                        { id: 'Present', label: 'Left School', color: 'emerald' },
                        { id: 'Extra Class', label: 'Extra Class', color: 'indigo' }
                      ].map(st => {
                        const isActive = attendance[s.id] === st.id;
                        return (
                          <button
                            key={st.id}
                            onClick={() => handleStatusChange(s.id, st.id)}
                            className={`flex items-center gap-3 px-6 py-3 rounded-2xl border-2 transition-all font-bold ${
                              isActive 
                                ? `bg-${st.color}-50 border-${st.color}-500 text-${st.color}-700 shadow-sm` 
                                : `bg-white border-slate-100 text-slate-400 hover:border-slate-300`
                            }`}
                          >
                            <div className={`w-5 h-5 rounded-full border-2 flex items-center justify-center ${
                              isActive ? `border-${st.color}-600 bg-white` : 'border-slate-200'
                            }`}>
                              {isActive && <div className={`w-2.5 h-2.5 rounded-full bg-${st.color}-600 animate-in zoom-in-50`} />}
                            </div>
                            {st.label}
                          </button>
                        );
                      })
                    )}
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
        <button 
          onClick={() => setShowHistory(!showHistory)}
          className={`flex items-center gap-3 px-8 py-4 rounded-2xl font-black transition-all ${
            showHistory ? 'bg-indigo-600 text-white shadow-xl' : 'bg-white border border-slate-200 text-slate-500 hover:bg-slate-50'
          }`}
        >
          <History size={20} /> {showHistory ? 'Hide History' : 'View History'}
        </button>
      </div>

      {/* Notification History Panel */}
      {showHistory && (
        <div className="mt-12 bg-white rounded-[40px] border border-slate-100 p-10 shadow-2xl animate-in slide-in-from-bottom-5 duration-500">
          <div className="flex items-center justify-between mb-10">
            <div>
              <h3 className="text-3xl font-[1000] text-slate-900 tracking-tighter">Notification Ledger</h3>
              <p className="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mt-2 italic">A detailed record of all automated parent messages</p>
            </div>
            <div className="flex items-center gap-3 px-5 py-2.5 bg-emerald-50 text-emerald-600 rounded-2xl text-[10px] font-black uppercase tracking-widest border border-emerald-100">
              <CheckCircle size={16} /> Logic Verified
            </div>
          </div>

          <div className="space-y-4">
            {notificationHistory.length > 0 ? (
              notificationHistory.map((log, idx) => (
                <div key={log.id || idx} className="group flex items-start gap-8 p-8 rounded-[32px] hover:bg-slate-50 transition-all border border-transparent hover:border-slate-100 relative overflow-hidden">
                  <div className={`mt-1 shrink-0 w-12 h-12 rounded-2xl flex items-center justify-center shadow-sm ${
                    log.session === 'Morning' ? 'bg-amber-50 text-amber-600' : 'bg-indigo-50 text-indigo-600'
                  }`}>
                    {log.session === 'Morning' ? <Clock size={24} /> : <History size={24} />}
                  </div>
                  <div className="flex-1">
                    <div className="flex items-center justify-between mb-2">
                      <div className="flex items-center gap-3">
                        <span className="text-lg font-[1000] text-slate-900 tracking-tight">{log.guardianName}</span>
                        <span className="px-2 py-0.5 bg-slate-100 text-slate-400 rounded text-[9px] font-black uppercase tracking-tighter">{log.session}</span>
                      </div>
                      <span className="text-[10px] font-black text-slate-400 font-mono tracking-tighter flex items-center gap-2">
                        <Clock size={12} /> {new Date(log.createdAt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                      </span>
                    </div>
                    <p className="text-sm font-bold text-slate-600 leading-relaxed italic pr-10 hover:text-slate-900 transition-colors">"{log.message}"</p>
                    <div className="flex items-center gap-4 mt-4">
                      <div className="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-400">
                        <Phone size={12} className="text-slate-300" /> {log.phoneNumber}
                      </div>
                      <div className="w-1.5 h-1.5 bg-slate-200 rounded-full"></div>
                      <span className="text-[10px] font-[1000] uppercase tracking-[0.2em] text-emerald-500 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-100">LOGGED</span>
                    </div>
                  </div>
                </div>
              ))
            ) : (
              <div className="py-24 text-center">
                  <div className="w-20 h-20 bg-slate-50 rounded-[28px] flex items-center justify-center mx-auto mb-6">
                      <History size={32} className="text-slate-200" />
                  </div>
                  <p className="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em]">No notification history available</p>
              </div>
            )}
          </div>

          <div className="mt-12 pt-10 border-t border-slate-50 text-center">
              <p className="text-[10px] font-black text-slate-300 uppercase tracking-[0.4em] leading-relaxed">
                  System Note: Actual SMS delivery depends on your gateway integration plan.
              </p>
          </div>
        </div>
      )}
    </div>
  );
};

export default AttendanceEntry;
