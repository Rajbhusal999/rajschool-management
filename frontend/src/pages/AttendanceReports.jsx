import React, { useState, useEffect } from 'react';
import { attendanceService, studentService } from '../services/api';
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
  const [loading, setLoading] = useState(false);

  const classes = ['Nursery', 'LKG', 'UKG', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'];
  const months = [
    { id: 1, name: 'Baishakh' }, { id: 2, name: 'Jestha' }, { id: 3, name: 'Ashadh' },
    { id: 4, name: 'Shrawan' }, { id: 5, name: 'Bhadra' }, { id: 6, name: 'Ashwin' },
    { id: 7, name: 'Kartik' }, { id: 8, name: 'Mangsir' }, { id: 9, name: 'Poush' },
    { id: 10, name: 'Magh' }, { id: 11, name: 'Falgun' }, { id: 12, name: 'Chaitra' }
  ];

  const handleLogin = (e) => {
    e.preventDefault();
    if (password === 'admin123') { // Simple simulation
      setIsLocked(false);
    } else {
      alert('Invalid Principal Password!');
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
    // Basic CSV implementation
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

  if (isLocked) {
    return (
      <div className="max-w-md mx-auto mt-20">
        <div className="bg-white p-10 rounded-[40px] shadow-2xl border border-slate-100 flex flex-col items-center">
          <div className="w-20 h-20 bg-rose-50 rounded-3xl flex items-center justify-center text-rose-500 mb-8 border-4 border-rose-100 shadow-inner">
            <Lock size={40} />
          </div>
          <h2 className="text-3xl font-black text-slate-900 mb-2">Secure Ledger</h2>
          <p className="text-slate-500 font-bold uppercase text-[10px] tracking-widest mb-10 text-center leading-relaxed">Identity verification required for institutional analytics.</p>
          
          <form onSubmit={handleLogin} className="w-full space-y-6">
            <div className="space-y-2">
              <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Principal Credentials</label>
              <input 
                type="password" 
                placeholder="••••••••" 
                className="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none focus:ring-2 focus:ring-rose-500 font-bold transition"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required 
              />
            </div>
            <button type="submit" className="w-full py-4 bg-slate-900 text-white rounded-2xl font-black shadow-xl hover:bg-slate-800 transition active:scale-95">
              Unlock Terminal
            </button>
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
