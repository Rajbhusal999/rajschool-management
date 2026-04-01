import React, { useState, useEffect } from 'react';
import { studentService } from '../services/api';
import { Search, Filter, Download, User, Phone, GraduationCap, Calendar, ChevronRight, FileText, Printer } from 'lucide-react';

const StudentReport = () => {
  const [students, setStudents] = useState([]);
  const [loading, setLoading] = useState(false);
  const [search, setSearch] = useState('');
  const [classFilter, setClassFilter] = useState('');

  const classes = ['Nursery', 'LKG', 'UKG', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'];

  useEffect(() => {
    fetchStudents();
  }, [search, classFilter]);

  const fetchStudents = async () => {
    setLoading(true);
    try {
      const response = await studentService.getAll({ search, studentClass: classFilter });
      setStudents(response.data);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const exportCSV = () => {
    const headers = ['Symbol No', 'Full Name', 'Class', 'Roll No', 'Gender', 'DOB', 'Guardian', 'Contact', 'Address'];
    const rows = students.map(s => [
      s.symbolNo,
      s.fullName,
      s.studentClass,
      s.rollNo || '-',
      s.gender,
      s.dobNepali,
      s.guardianName,
      s.parentContact,
      `${s.permLocalLevel}-${s.permWardNo}`
    ]);

    const csvContent = [headers.join(','), ...rows.map(r => r.join(','))].join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `student_report_${classFilter || 'all'}_${new Date().toISOString().split('T')[0]}.csv`;
    link.click();
  };

  return (
    <div className="max-w-7xl mx-auto space-y-8 pb-20 animate-in fade-in duration-700">
      
      {/* Header & Meta */}
      <div className="flex flex-col md:flex-row md:items-end justify-between gap-6 px-2">
        <div className="space-y-2">
          <div className="flex items-center gap-3 text-emerald-600 font-black text-[10px] uppercase tracking-widest bg-emerald-50 w-fit px-3 py-1 rounded-full border border-emerald-100">
             <FileText size={12} /> Institutional Archives
          </div>
          <h1 className="text-4xl font-[1000] text-slate-900 tracking-tight leading-none uppercase">Student Repository Report</h1>
          <p className="text-slate-500 font-bold text-sm tracking-wide">Consolidated database of active academic profiles and administrative records.</p>
        </div>
        <div className="flex gap-3">
          <button onClick={() => window.print()} className="p-4 bg-white border border-slate-200 text-slate-600 rounded-2xl hover:bg-slate-50 transition shadow-sm font-bold flex items-center gap-2">
            <Printer size={18} /> <span className="hidden sm:inline">Print Report</span>
          </button>
          <button onClick={exportCSV} className="p-4 bg-emerald-600 text-white rounded-2xl hover:bg-emerald-700 transition shadow-xl shadow-emerald-100 font-black text-xs uppercase tracking-widest flex items-center gap-2">
            <Download size={18} /> Export CSV
          </button>
        </div>
      </div>

      {/* Analytics Summary */}
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4 px-2">
         {[
           { label: "Total Students", value: students.length, icon: User, color: "text-indigo-600 bg-indigo-50" },
           { label: "Class Filtered", value: classFilter || "ALL", icon: GraduationCap, color: "text-emerald-600 bg-emerald-50" },
           { label: "Academic Year", value: "2083 BS", icon: Calendar, color: "text-amber-600 bg-amber-50" },
           { label: "Verified Records", value: students.filter(s => s.symbolNo).length, icon: FileText, color: "text-rose-600 bg-rose-50" }
         ].map((stat, i) => (
           <div key={i} className="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex items-center gap-4 group hover:border-slate-200 transition-colors">
              <div className={`w-12 h-12 rounded-2xl flex items-center justify-center ${stat.color} transition-transform group-hover:scale-110`}>
                <stat.icon size={24} />
              </div>
              <div>
                <span className="block text-[10px] font-black text-slate-400 uppercase tracking-widest">{stat.label}</span>
                <span className="text-xl font-black text-slate-800">{stat.value}</span>
              </div>
           </div>
         ))}
      </div>

      {/* Filters */}
      <div className="bg-white p-4 rounded-[32px] border border-slate-100 shadow-xl shadow-slate-100/50 flex flex-col md:flex-row gap-4">
        <div className="relative flex-1 group">
          <Search className="absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-600 transition" size={20} />
          <input 
            type="text" 
            placeholder="Search by student name or symbol number..." 
            className="w-full pl-16 pr-6 h-[64px] bg-slate-50 border-none rounded-[24px] outline-none focus:ring-4 focus:ring-indigo-100 transition font-bold text-slate-700 placeholder:text-slate-300"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
          />
        </div>
        <div className="relative w-full md:w-[280px]">
          <Filter className="absolute left-6 top-1/2 -translate-y-1/2 text-slate-300" size={20} />
          <select 
            className="w-full h-[64px] pl-16 pr-10 bg-slate-50 border-none rounded-[24px] outline-none focus:ring-4 focus:ring-indigo-100 appearance-none font-black text-slate-600 uppercase tracking-widest text-[11px] cursor-pointer"
            value={classFilter}
            onChange={(e) => setClassFilter(e.target.value)}
          >
            <option value="">Full Institutional View</option>
            {classes.map(c => <option key={c} value={c}>Grade: {c}</option>)}
          </select>
          <div className="absolute right-6 top-1/2 -translate-y-1/2 pointer-events-none text-slate-300">
              <ChevronRight size={18} className="rotate-90" />
          </div>
        </div>
      </div>

      {/* Table Section */}
      <div className="bg-white border border-slate-200 rounded-[40px] shadow-sm overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="bg-slate-900 text-white text-[10px] font-black uppercase tracking-[0.2em]">
                <th className="px-10 py-7 border-r border-slate-800">Symbol No</th>
                <th className="px-8 py-7">Student Name</th>
                <th className="px-8 py-7">Class / Roll</th>
                <th className="px-8 py-7">Parent / Guardian</th>
                <th className="px-8 py-7">Contact Address</th>
                <th className="px-8 py-7 text-right">Status</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {loading ? (
                Array.from({length: 5}).map((_, i) => (
                  <tr key={i} className="animate-pulse">
                    <td colSpan="6" className="px-10 py-8 bg-slate-50/50"></td>
                  </tr>
                ))
              ) : students.length > 0 ? (
                students.map((student) => (
                  <tr key={student.id} className="hover:bg-slate-50/80 transition group">
                    <td className="px-10 py-7 border-r border-slate-50">
                      <span className="font-black text-slate-900 font-mono tracking-widest bg-slate-100 px-3 py-1.5 rounded-xl border border-slate-200">
                        {student.symbolNo || 'UNASSIGNED'}
                      </span>
                    </td>
                    <td className="px-8 py-7">
                      <div className="font-[900] text-slate-800 text-lg group-hover:text-indigo-600 transition tracking-tight">{student.fullName}</div>
                      <div className="text-[10px] text-slate-400 font-black uppercase tracking-widest mt-1 opacity-60">ID: {student.id} | {student.gender}</div>
                    </td>
                    <td className="px-8 py-7">
                      <div className="flex flex-col gap-1">
                         <span className="text-xs font-black text-indigo-600 uppercase tracking-widest bg-indigo-50 w-fit px-2 py-0.5 rounded-lg border border-indigo-100">Grade {student.studentClass}</span>
                         <span className="text-[10px] font-bold text-slate-400">Roll No: <span className="text-slate-700">{student.rollNo || 'N/A'}</span></span>
                      </div>
                    </td>
                    <td className="px-8 py-7">
                       <div className="space-y-1">
                          <div className="flex items-center gap-2 text-xs font-bold text-slate-700">
                             <User size={12} className="text-slate-300" /> {student.guardianName || student.fatherName}
                          </div>
                          <div className="flex items-center gap-2 text-xs font-medium text-slate-500">
                             <Phone size={12} className="text-slate-300" /> {student.parentContact || 'No Contact'}
                          </div>
                       </div>
                    </td>
                    <td className="px-8 py-7">
                       <div className="text-xs font-bold text-slate-600 leading-relaxed">
                          {student.permLocalLevel}, {student.permWardNo}<br/>
                          <span className="text-[10px] text-slate-300 font-black uppercase tracking-tighter opacity-70">{student.permDistrict} District</span>
                       </div>
                    </td>
                    <td className="px-8 py-7 text-right">
                       <div className="inline-flex items-center gap-2 px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-[10px] font-black uppercase tracking-widest border border-emerald-100 shadow-sm">
                          Verified
                       </div>
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                   <td colSpan="6" className="px-10 py-32 text-center text-slate-300">
                      <div className="flex flex-col items-center gap-4">
                         <Search size={48} className="opacity-20" />
                         <p className="font-black uppercase tracking-[0.2em] text-xs">No records matching repository query</p>
                      </div>
                   </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

    </div>
  );
};

export default StudentReport;
