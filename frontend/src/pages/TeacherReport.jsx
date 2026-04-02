import React, { useState, useEffect, useMemo, useRef } from 'react';
import { teacherService } from '../services/api';
import { Search, Filter, Download, User, Phone, BookOpen, Calendar, Briefcase, Printer, FileText, ChevronRight } from 'lucide-react';

const TeacherReport = () => {
  const [teachers, setTeachers] = useState([]);
  const [loading, setLoading] = useState(false);
  const [search, setSearch] = useState('');
  const [roleFilter, setRoleFilter] = useState('');
  const [year, setYear] = useState('2083');

  const years = Array.from({length: 11}, (_, i) => (2080 + i).toString());

  const fetchTeachers = async (searchTerm = search, filter = roleFilter) => {
    setLoading(true);
    const isTrial = sessionStorage.getItem('isTrialMode') === 'true';

    if (isTrial) {
      const mockData = [
        { id: 't1', fullName: 'Dr. Ramesh Sharma', staffRole: 'Teacher', subject: 'Mathematics', contact: '9841234567', tah: 'Secondary', teacherType: 'Permanent', teacherPhoto: null, attendanceDateNepali: '2075-01-15', panNo: '123456789' },
        { id: 't2', fullName: 'Sita Kumari', staffRole: 'Teacher', subject: 'Science', contact: '9841112233', tah: 'Basic', teacherType: 'Temporary', teacherPhoto: null, attendanceDateNepali: '2078-04-10', panNo: '987654321' },
        { id: 't3', fullName: 'Anil Bisht', staffRole: 'Admin', subject: 'Administration', contact: '9851099887', tah: 'Admin', teacherType: 'Permanent', teacherPhoto: null, attendanceDateNepali: '2070-10-20', panNo: '456789123' },
        { id: 't4', fullName: 'Maya Tamang', staffRole: 'Teacher', subject: 'English', contact: '9865432100', tah: 'Primary', teacherType: 'Contract', teacherPhoto: null, attendanceDateNepali: '2080-02-05', panNo: '321654987' }
      ];
      
      let data = mockData;
      if (filter) {
        data = data.filter(t => t.staffRole?.toLowerCase() === filter.toLowerCase());
      }
      setTeachers(data);
      setLoading(false);
      return;
    }

    try {
      const response = await teacherService.getAll({ search: searchTerm });
      // Manual filter since backend might not support it yet
      let data = response.data || [];
      if (filter) {
        data = data.filter(t => t.staffRole?.toLowerCase() === filter.toLowerCase());
      }
      setTeachers(data);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const searchTimer = useRef(null);

  useEffect(() => {
    if (searchTimer.current) clearTimeout(searchTimer.current);
    searchTimer.current = setTimeout(() => {
      fetchTeachers(search, roleFilter);
    }, 500); // Increased grace period

    return () => clearTimeout(searchTimer.current);
  }, [search, roleFilter]);

  const handlePrint = () => {
    // Clear any pending search to avoid UI contention during print
    if (searchTimer.current) clearTimeout(searchTimer.current);
    
    // Asynchronous call allows the browser to wrap up the click interaction
    // before starting the heavy blocking print preview task.
    setTimeout(() => {
      window.print();
    }, 50);
  };

  const stats = useMemo(() => [
    { label: "Total Faculty", value: teachers.length, icon: User, color: "text-rose-600 bg-rose-50" },
    { label: "Teaching Staff", value: teachers.filter(t => t.staffRole === 'Teacher').length, icon: BookOpen, color: "text-indigo-600 bg-indigo-50" },
    { label: "Administrative", value: teachers.filter(t => t.staffRole !== 'Teacher').length, icon: Briefcase, color: "text-amber-600 bg-amber-50" },
    { label: "Employment Cycles", value: `${year}/${(parseInt(year) + 1).toString().slice(-2)}`, icon: Calendar, color: "text-emerald-600 bg-emerald-50" }
  ], [teachers, year]);

  const exportCSV = () => {
    const headers = ['Full Name', 'Role', 'Subject', 'Level (Tah)', 'Type', 'Contact', 'Joining Date', 'PAN No'];
    const rows = teachers.map(t => [
      t.fullName,
      t.staffRole,
      t.subject || '-',
      t.tah || '-',
      t.teacherType,
      t.contact,
      t.attendanceDateNepali,
      t.panNo || '-'
    ]);

    const csvContent = [headers.join(','), ...rows.map(r => r.join(','))].join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `faculty_report_${roleFilter || 'all'}_${new Date().toISOString().split('T')[0]}.csv`;
    link.click();
  };

  return (
    <div className="max-w-7xl mx-auto space-y-8 pb-20 animate-in fade-in duration-700">
      
      {/* Header */}
      <div className="flex flex-col md:flex-row md:items-end justify-between gap-6 px-2">
        <div className="space-y-2">
          <div className="flex items-center gap-3 text-rose-600 font-black text-[10px] uppercase tracking-widest bg-rose-50 w-fit px-3 py-1 rounded-full border border-rose-100 no-print">
             <Briefcase size={12} /> Human Capital Analytics
          </div>
          <h1 className="text-4xl font-[1000] text-slate-900 tracking-tight leading-none uppercase">Faculty Directory Report</h1>
          <p className="text-slate-500 font-bold text-sm tracking-wide no-print">Institutional summary of academic staff, specialized faculty and administrative roles.</p>
        </div>
        <div className="flex gap-3 no-print">
          <button onClick={handlePrint} className="p-4 bg-white border border-slate-200 text-slate-600 rounded-2xl hover:bg-slate-50 transition shadow-sm font-bold flex items-center gap-2">
            <Printer size={18} /> <span className="hidden sm:inline">Print Report</span>
          </button>
          <button onClick={exportCSV} className="p-4 bg-rose-600 text-white rounded-2xl hover:bg-rose-700 transition shadow-xl shadow-rose-100 font-black text-xs uppercase tracking-widest flex items-center gap-2">
            <Download size={18} /> Export Faculty Log
          </button>
        </div>
      </div>

      {/* Analytics */}
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4 px-2 no-print">
         {stats.map((stat, i) => (
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

      {/* Control Panel */}
      <div className="bg-white p-4 rounded-[32px] border border-slate-100 shadow-xl shadow-slate-100/5 flex flex-col md:flex-row gap-4 no-print">
        <div className="relative flex-1 group">
          <Search className="absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-rose-600 transition" size={20} />
          <input 
            type="text" 
            placeholder="Search faculty by name..." 
            className="w-full pl-16 pr-6 h-[64px] bg-slate-50 border-none rounded-[24px] outline-none focus:ring-4 focus:ring-rose-100 transition font-bold text-slate-700 placeholder:text-slate-300"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
          />
        </div>
        <div className="relative w-full md:w-[280px]">
          <Filter className="absolute left-6 top-1/2 -translate-y-1/2 text-slate-300" size={20} />
          <select 
            className="w-full h-[64px] pl-16 pr-10 bg-slate-50 border-none rounded-[24px] outline-none focus:ring-4 focus:ring-rose-100 appearance-none font-black text-slate-600 uppercase tracking-widest text-[11px] cursor-pointer"
            value={roleFilter}
            onChange={(e) => setRoleFilter(e.target.value)}
          >
            <option value="">All Institutional Staff</option>
            <option value="Teacher">Academic Faculty</option>
            <option value="Admin">Administrative</option>
            <option value="Support">Support Staff</option>
          </select>
          <div className="absolute right-6 top-1/2 -translate-y-1/2 pointer-events-none text-slate-300">
              <ChevronRight size={18} className="rotate-90" />
          </div>
        </div>
        <div className="relative w-full md:w-[180px]">
          <Calendar className="absolute left-6 top-1/2 -translate-y-1/2 text-slate-300" size={20} />
          <select 
            className="w-full h-[64px] pl-16 pr-10 bg-slate-50 border-none rounded-[24px] outline-none focus:ring-4 focus:ring-rose-100 appearance-none font-black text-slate-800 uppercase tracking-widest text-[11px] cursor-pointer"
            value={year}
            onChange={(e) => setYear(e.target.value)}
          >
            {years.map(y => <option key={y} value={y}>{y} BS</option>)}
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
              <tr className="bg-slate-900 text-white text-[10px] font-black uppercase tracking-[0.2em] print:bg-white print:text-slate-900 print:border-b-2 print:border-slate-200">
                <th className="px-10 py-7 border-r border-slate-800">Photo ID</th>
                <th className="px-8 py-7">Faculty Identity</th>
                <th className="px-8 py-7">Specialization / Role</th>
                <th className="px-8 py-7">Employment Metrics</th>
                <th className="px-8 py-7">Communication</th>
                <th className="px-8 py-7 text-right no-print">Audit</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100 text-sm">
              {loading ? (
                Array.from({length: 4}).map((_, i) => (
                  <tr key={i} className="animate-pulse h-24 bg-slate-50/20">
                    <td colSpan="6"></td>
                  </tr>
                ))
              ) : teachers.length > 0 ? (
                teachers.map((teacher) => (
                  <tr key={teacher.id} className="hover:bg-slate-50/80 transition group">
                    <td className="px-10 py-7 border-r border-slate-50">
                       <div className="w-14 h-14 bg-slate-100 rounded-2xl overflow-hidden border-2 border-white shadow-sm ring-1 ring-slate-100">
                          {teacher.teacherPhoto ? (
                            <img src={teacher.teacherPhoto} alt="" className="w-full h-full object-cover" />
                          ) : (
                            <div className="w-full h-full flex items-center justify-center text-slate-300">
                              <User size={24} />
                            </div>
                          )}
                       </div>
                    </td>
                    <td className="px-8 py-7">
                       <div className="font-black text-slate-800 text-base group-hover:text-rose-600 transition">{teacher.fullName}</div>
                       <div className="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">ID: FAC-{teacher.id.toString().padStart(4, '0')}</div>
                    </td>
                    <td className="px-8 py-7">
                       <div className="space-y-1.5">
                          <span className={`px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest ${teacher.staffRole === 'Teacher' ? 'bg-indigo-50 text-indigo-600 border border-indigo-100' : 'bg-amber-50 text-amber-600 border border-amber-100'}`}>
                             {teacher.staffRole}
                          </span>
                          <div className="flex items-center gap-1.5 text-[11px] font-bold text-slate-500 pl-1">
                             <BookOpen size={12} className="text-slate-300" /> {teacher.subject || 'Institutional Admin'}
                          </div>
                       </div>
                    </td>
                    <td className="px-8 py-7">
                       <div className="space-y-1">
                          <div className="text-xs font-black text-slate-700 uppercase">{teacher.tah || 'N/A Level'}</div>
                          <div className="text-[10px] font-bold text-slate-400">{teacher.teacherType || 'Permanent'} Since {teacher.attendanceDateNepali || 'N/A'}</div>
                       </div>
                    </td>
                    <td className="px-8 py-7">
                       <div className="flex items-center gap-2.5 text-slate-600 font-bold">
                          <div className="w-8 h-8 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-500">
                             <Phone size={14} />
                          </div>
                          {teacher.contact}
                       </div>
                    </td>
                    <td className="px-8 py-7 text-right no-print">
                       <div className="text-[10px] font-black text-slate-400 uppercase tracking-widest flex flex-col items-end gap-1">
                          PAN: {teacher.panNo || 'N/A'}
                          <span className="text-[9px] font-bold text-slate-300">Verified institutional Profile</span>
                       </div>
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                   <td colSpan="6" className="px-10 py-32 text-center text-slate-300">
                      <div className="flex flex-col items-center gap-4">
                         <Search size={48} className="opacity-20" />
                         <p className="font-black uppercase tracking-[0.2em] text-xs">No faculty records found</p>
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

export default TeacherReport;
