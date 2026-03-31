import React, { useState, useEffect } from 'react';
import { attendanceService, studentService, institutionService } from '../services/api';
import { Calendar, FileBarChart, Download, ChevronLeft, ChevronRight, User, Fingerprint, Lock } from 'lucide-react';

const AttendanceReports = () => {
  const [isLocked, setIsLocked] = useState(true);
  const [password, setPassword] = useState('');
  const [params, setParams] = useState({
    year: '2081',
    month: '1',
    studentClass: ''
  });
  const [reportData, setReportData] = useState(null);
  const [view, setView] = useState('LOADING'); // LOADING, SETUP, LOGIN, REPORT
  const [passwords, setPasswords] = useState({ new: '', confirm: '' });
  const [inst, setInst] = useState(null);

  useEffect(() => {
    checkGateway();
  }, []);

  const checkGateway = async () => {
    try {
      const { data } = await institutionService.get();
      setInst(data);
      if (!data.principalPassword) setView('SETUP');
      else setView('LOGIN');
    } catch (err) {
      console.error(err);
    }
  };

  const handleEstablish = async (e) => {
    e.preventDefault();
    if (passwords.new !== passwords.confirm) return alert('Passwords do not match!');
    if (passwords.new.length < 6) return alert('Security perimeter requires at least 6 characters.');

    try {
      await institutionService.updateSmsConfig({ principalPassword: passwords.new });
      alert('Security established. Please login to continue.');
      setView('LOGIN');
      setPassword('');
    } catch (err) {
      console.error(err);
    }
  };

  const handleLogin = (e) => {
    e.preventDefault();
    if (password === inst.principalPassword) {
      setView('REPORT');
    } else {
      alert('Invalid Principal Security Key!');
    }
  };

  const fetchReport = async () => {
    if (!params.studentClass) return;
    setLoading(true);
    try {
      const datePrefix = `${params.year}-${params.month.padStart(2, '0')}`;
      
      // 1. Fetch Students
      const studentsResp = await studentService.getAll({ schoolId: 1, studentClass: params.studentClass });
      
      // 2. Fetch Attendance for month
      const attResp = await attendanceService.get({ 
        schoolId: 1, 
        studentClass: params.studentClass, 
        datePrefix: datePrefix 
      });

      // Organize: { studentId: { day: { Morning: status, Evening: status } } }
      const attMap = {};
      attResp.data.forEach(a => {
        const day = parseInt(a.attendanceDate.split('-')[2]);
        if (!attMap[a.studentId]) attMap[a.studentId] = {};
        if (!attMap[a.studentId][day]) attMap[a.studentId][day] = {};
        attMap[a.studentId][day][a.session] = a.status;
      });

      setReportData({
        students: studentsResp.data,
        attendance: attMap,
        days: 32 // Simplified Nepali month
      });
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const exportCSV = () => {
    const headers = ['Symbol No', 'Name', ...Array.from({length: 32}, (_, i) => i + 1), 'Total'];
    const rows = reportData.students.map(s => {
      let total = 0;
      const dayCells = Array.from({length: 32}, (_, i) => {
        const d = i + 1;
        const m = reportData.attendance[s.id]?.[d]?.Morning || '-';
        const e = reportData.attendance[s.id]?.[d]?.Evening || '-';
        if (m === 'Present') total++;
        return `${m}/${e}`;
      });
      return [s.symbolNo, s.fullName, ...dayCells, total];
    });

    const csv = [headers.join(','), ...rows.map(r => r.join(','))].join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `attendance_${params.studentClass}_${params.year}_${params.month}.csv`;
    link.click();
  };

  if (view === 'LOADING') return <div className="flex items-center justify-center h-screen font-black text-slate-300 uppercase tracking-widest text-xs">Initializing Secure Perimeter...</div>;

  if (view === 'SETUP') {
    return (
      <div className="max-w-md mx-auto mt-20 animate-in fade-in zoom-in-95 duration-500">
        <div className="bg-white p-12 rounded-[48px] shadow-2xl border border-slate-100 flex flex-col items-center text-center">
            <div className="w-20 h-20 bg-indigo-600 rounded-[32px] flex items-center justify-center text-white mb-10 shadow-2xl shadow-indigo-200 rotate-12 transition-transform hover:rotate-0">
                <Fingerprint size={42} />
            </div>
            
            <h2 className="text-4xl font-[1000] text-slate-900 tracking-tighter mb-4">Principal Verification</h2>
            <p className="text-sm font-bold text-slate-400 leading-relaxed mb-10 px-4">
                Establish a secure analytical perimeter. Set your principal access credentials.
            </p>

            <form onSubmit={handleEstablish} className="w-full space-y-8">
                <div className="space-y-3 text-left">
                    <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">New Password</label>
                    <input 
                        type="password" 
                        className="w-full px-8 py-5 bg-slate-50 border-2 border-transparent rounded-[24px] outline-none focus:border-indigo-500 focus:bg-white font-black transition-all"
                        placeholder="••••••••"
                        value={passwords.new}
                        onChange={(e) => setPasswords({...passwords, new: e.target.value})}
                        required 
                    />
                </div>
                <div className="space-y-3 text-left">
                    <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">Confirm Password</label>
                    <input 
                        type="password" 
                        className="w-full px-8 py-5 bg-slate-50 border-2 border-transparent rounded-[24px] outline-none focus:border-indigo-500 focus:bg-white font-black transition-all"
                        placeholder="••••••••"
                        value={passwords.confirm}
                        onChange={(e) => setPasswords({...passwords, confirm: e.target.value})}
                        required 
                    />
                </div>

                <button type="submit" className="w-full py-6 bg-indigo-600 text-white rounded-[28px] font-black text-lg shadow-2xl shadow-indigo-100 transform active:scale-95 transition-all hover:bg-indigo-700">
                    Establish Security
                </button>

                <div className="pt-4">
                    <button type="button" onClick={() => window.history.back()} className="text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-indigo-600 transition flex items-center justify-center gap-2 mx-auto">
                        <ChevronLeft size={14} /> Return to Command Center
                    </button>
                </div>
            </form>
        </div>
      </div>
    );
  }

  if (view === 'LOGIN') {
    return (
      <div className="max-w-md mx-auto mt-20 animate-in fade-in zoom-in-95 duration-500">
        <div className="bg-white p-12 rounded-[48px] shadow-2xl border border-slate-100 flex flex-col items-center text-center">
            <div className="w-20 h-20 bg-rose-600 rounded-[32px] flex items-center justify-center text-white mb-10 shadow-2xl shadow-rose-200">
                <Lock size={42} />
            </div>
            
            <h2 className="text-4xl font-[1000] text-slate-900 tracking-tighter mb-4">Secure Ledger</h2>
            <p className="text-sm font-bold text-slate-400 leading-relaxed mb-10 px-4 uppercase tracking-[0.2em]">Identity verification required</p>

            <form onSubmit={handleLogin} className="w-full space-y-8">
                <div className="space-y-3 text-left">
                    <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">Principal Security Key</label>
                    <input 
                        type="password" 
                        className="w-full px-8 py-5 bg-slate-50 border-2 border-transparent rounded-[24px] outline-none focus:border-rose-500 focus:bg-white font-black transition-all"
                        placeholder="••••••••"
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        required 
                    />
                </div>

                <button type="submit" className="w-full py-6 bg-slate-900 text-white rounded-[28px] font-black text-lg shadow-2xl shadow-slate-100 transform active:scale-95 transition-all hover:bg-slate-800">
                    Unlock Analytical Matrix
                </button>

                <div className="pt-4">
                    <button type="button" onClick={() => window.history.back()} className="text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-indigo-600 transition flex items-center justify-center gap-2 mx-auto">
                        <ChevronLeft size={14} /> Back to Dashboard
                    </button>
                </div>
            </form>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-7xl mx-auto space-y-8">
      {/* Search Controls */}
      <div className="bg-white p-8 rounded-[32px] shadow-sm border border-slate-100 flex flex-col md:flex-row gap-6 items-end">
        <div className="flex-1 space-y-2">
          <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Academic Cycle</label>
          <div className="grid grid-cols-2 gap-3">
             <select className="px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 font-bold" value={params.year} onChange={(e) => setParams({...params, year: e.target.value})}>
                <option value="2080">2080 BS</option>
                <option value="2081">2081 BS</option>
                <option value="2082">2082 BS</option>
             </select>
             <select className="px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 font-bold" value={params.month} onChange={(e) => setParams({...params, month: e.target.value})}>
                {months.map(m => <option key={m.id} value={m.id}>{m.name}</option>)}
             </select>
          </div>
        </div>

        <div className="flex-1 space-y-2">
          <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Class</label>
          <select className="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 font-bold" value={params.studentClass} onChange={(e) => setParams({...params, studentClass: e.target.value})}>
            <option value="">Select Target Class</option>
            {classes.map(c => <option key={c} value={c}>{c}</option>)}
          </select>
        </div>

        <button 
          onClick={fetchReport}
          className="px-10 py-3.5 bg-indigo-600 text-white rounded-xl font-black shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition flex items-center gap-2 h-[52px]"
          disabled={loading}
        >
          {loading ? 'Synchronizing...' : 'Generate Analytics'}
        </button>
      </div>

      {reportData ? (
        <div className="bg-white rounded-[40px] shadow-sm border border-slate-100 p-8 space-y-6">
          <div className="flex justify-between items-center">
             <div>
               <h2 className="text-2xl font-black text-slate-900 leading-tight">Monthly Ledger</h2>
               <p className="text-slate-500 font-bold text-xs uppercase tracking-widest mt-1">
                 Class {params.studentClass} • {months.find(m => m.id === parseInt(params.month))?.name} {params.year}
               </p>
             </div>
             <button onClick={exportCSV} className="p-3 bg-emerald-50 text-emerald-600 rounded-2xl hover:bg-emerald-100 transition shadow-sm border border-emerald-100 flex items-center gap-2 font-bold px-6">
               <Download size={18} />
               Export XLSX
             </button>
          </div>

          <div className="overflow-x-auto rounded-3xl border border-slate-50">
            <table className="w-full text-xs text-center border-collapse">
              <thead>
                <tr className="bg-slate-900 text-white font-bold">
                  <th className="px-4 py-3 border border-slate-800 sticky left-0 z-20 bg-slate-900">ID</th>
                  <th className="px-6 py-3 border border-slate-800 text-left sticky left-12 z-20 bg-slate-900">Name</th>
                  {Array.from({length: reportData.days}, (_, i) => (
                    <th key={i} className="px-2 py-3 border border-slate-800 w-8">{i + 1}</th>
                  ))}
                  <th className="px-4 py-3 border border-slate-800 bg-indigo-600">Total</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {reportData.students.map(s => {
                  let total = 0;
                  return (
                    <tr key={s.id} className="hover:bg-slate-50 transition">
                      <td className="px-4 py-3 border border-slate-100 font-black text-slate-400 sticky left-0 z-10 bg-white group-hover:bg-slate-50">{s.symbolNo}</td>
                      <td className="px-6 py-3 border border-slate-100 text-left font-extrabold text-slate-800 sticky left-12 z-10 bg-white group-hover:bg-slate-50">{s.fullName}</td>
                      {Array.from({length: reportData.days}, (_, i) => {
                        const d = i + 1;
                        const m = reportData.attendance[s.id]?.[d]?.Morning || '-';
                        const e = reportData.attendance[s.id]?.[d]?.Evening || '-';
                        if (m === 'Present') total++;
                        
                        let bg = '';
                        if (m === 'Absent' || e === 'Absent') bg = 'bg-rose-50 text-rose-600';
                        else if (m === 'Leave' || e === 'Leave') bg = 'bg-amber-50 text-amber-600';
                        else if (m === 'Present') bg = 'bg-emerald-50 text-emerald-600';

                        return (
                          <td key={i} className={`px-1 py-3 border border-slate-100 font-bold ${bg}`}>
                             <div className="scale-75 origin-center">{m === e ? m[0] : `${m[0]}/${e[0]}`}</div>
                          </td>
                        );
                      })}
                      <td className="px-4 py-3 border border-slate-100 font-black text-indigo-600 bg-indigo-50/50">{total}</td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        </div>
      ) : (
        <div className="h-[400px] bg-white rounded-[40px] border-2 border-dashed border-slate-100 flex flex-col items-center justify-center text-slate-300 space-y-4">
           <FileBarChart size={64} className="opacity-20" />
           <p className="font-bold uppercase text-xs tracking-widest opacity-40">Ready to synchronize data matrix</p>
        </div>
      )}
    </div>
  );
};

export default AttendanceReports;
