import React, { useState, useEffect } from 'react';
import { teacherService } from '../services/api';
import TeacherForm from '../components/TeacherForm';
import { Edit2, Trash2, Eye, Search, Plus, User, Phone, MapPin, BookOpen, Calendar, ShieldCheck, X, Download, Briefcase } from 'lucide-react';

const ViewModal = ({ teacher, onClose }) => {
  if (!teacher) return null;

  const DetailRow = ({ label, value, icon: Icon }) => (
    <div className="flex items-start gap-3 p-3 bg-slate-50 rounded-xl">
      <div className="mt-1 text-slate-400">
        <Icon size={16} />
      </div>
      <div>
        <span className="block text-[10px] font-bold text-slate-400 uppercase tracking-tight">{label}</span>
        <span className="text-sm font-semibold text-slate-800">{value || 'N/A'}</span>
      </div>
    </div>
  );

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto">
      <div className="bg-white w-full max-w-4xl rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <div className="p-6 border-b border-slate-100 flex items-center justify-between bg-white">
          <h2 className="text-xl font-extrabold text-slate-900">Faculty Member Details</h2>
          <button onClick={onClose} className="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full transition">
            <X size={20} />
          </button>
        </div>
        <div className="p-8 overflow-y-auto grid md:grid-cols-3 gap-8">
          {/* Main Info */}
          <div className="md:col-span-1 flex flex-col items-center text-center space-y-4">
            <div className="w-40 h-40 bg-indigo-50 rounded-3xl flex items-center justify-center text-indigo-500 border-4 border-indigo-100 shadow-inner relative overflow-hidden">
               {teacher.teacherPhoto ? (
                  <img src={teacher.teacherPhoto} alt={teacher.fullName} className="w-full h-full object-cover" />
               ) : (
                  <User size={64} />
               )}
            </div>
            <div>
              <h3 className="text-2xl font-black text-slate-900 leading-tight">{teacher.fullName}</h3>
              <span className={`inline-block px-3 py-1 rounded-full mt-2 text-xs font-bold uppercase ${teacher.staffRole === 'Teacher' ? 'bg-indigo-100 text-indigo-700' : 'bg-amber-100 text-amber-700'}`}>
                {teacher.staffRole}
              </span>
            </div>
          </div>

          {/* Details Grid */}
          <div className="md:col-span-2 space-y-6">
            <div>
              <h4 className="flex items-center gap-2 text-xs font-black text-indigo-600 uppercase tracking-widest mb-3 border-b border-indigo-50 pb-2">Professional Info</h4>
              <div className="grid grid-cols-2 gap-3">
                <DetailRow label="Specialization" value={teacher.subject} icon={BookOpen} />
                <DetailRow label="Level (Tah)" value={teacher.tah} icon={Briefcase} />
                <DetailRow label="Employment Type" value={teacher.teacherType} icon={ShieldCheck} />
                <DetailRow label="Joining Date" value={teacher.attendanceDateNepali} icon={Calendar} />
              </div>
            </div>

            <div>
              <h4 className="flex items-center gap-2 text-xs font-black text-indigo-600 uppercase tracking-widest mb-3 border-b border-indigo-50 pb-2">Identity & Finance</h4>
              <div className="grid grid-cols-2 gap-3">
                <DetailRow label="Contact" value={teacher.contact} icon={Phone} />
                <DetailRow label="PAN No" value={teacher.panNo} icon={ShieldCheck} />
                <DetailRow label="Citizenship No" value={teacher.citizenshipNo} icon={User} />
                <DetailRow label="Bank" value={teacher.bankName} icon={Briefcase} />
                <DetailRow label="Account No" value={teacher.accountNumber} icon={Briefcase} />
                <DetailRow label="Blood Group" value={teacher.bloodGroup} icon={User} />
              </div>
            </div>

            <div className="space-y-3">
              <h4 className="text-xs font-black text-indigo-600 uppercase tracking-widest mb-3 border-b border-indigo-50 pb-2">Address</h4>
              <DetailRow label="Full Address" value={teacher.address} icon={MapPin} />
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

const TeacherList = () => {
  const [teachers, setTeachers] = useState([]);
  const [search, setSearch] = useState('');
  const [isFormOpen, setIsFormOpen] = useState(false);
  const [selectedTeacher, setSelectedTeacher] = useState(null);
  const [viewTeacher, setViewTeacher] = useState(null);

  useEffect(() => {
    fetchTeachers();
  }, [search]);

  const fetchTeachers = async () => {
    try {
      const response = await teacherService.getAll({ search });
      setTeachers(response.data);
    } catch (err) {
      console.error(err);
    }
  };

  const handleDelete = async (id) => {
    if (window.confirm('Are you sure you want to remove this staff record?')) {
      try {
        await teacherService.delete(id);
        fetchTeachers();
      } catch (err) {
        console.error(err);
      }
    }
  };

  const exportToCSV = () => {
    const headers = ['Name', 'Role', 'Subject', 'Contact', 'Level', 'Type'];
    const rows = teachers.map(t => [
      t.fullName,
      t.staffRole,
      t.subject || '',
      t.contact,
      t.tah || '',
      t.teacherType
    ]);

    const csvContent = [headers.join(','), ...rows.map(row => row.join(','))].join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `teachers_export_${new Date().toISOString().split('T')[0]}.csv`);
    link.click();
  };

  return (
    <div className="max-w-7xl mx-auto space-y-8">
      {/* Header & Actions */}
      <div className="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
          <h1 className="text-4xl font-black text-slate-900 tracking-tight">Faculty Directory</h1>
          <p className="text-slate-500 font-medium">Oversee academic staff and departmental roles.</p>
        </div>
        <div className="flex gap-3">
          <button 
            onClick={exportToCSV}
            className="bg-white border border-slate-200 text-slate-700 px-6 py-3.5 rounded-2xl font-bold flex items-center justify-center gap-2 transition hover:bg-slate-50 shadow-sm"
          >
            <Download size={18} />
            Export CSV
          </button>
          <button 
            onClick={() => { setSelectedTeacher(null); setIsFormOpen(true); }}
            className="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3.5 rounded-2xl font-bold flex items-center justify-center gap-2 transition shadow-xl shadow-indigo-100 group"
          >
            <Plus size={20} className="group-hover:rotate-90 transition-transform" />
            Add Faculty/Staff
          </button>
        </div>
      </div>

      {/* Search Bar */}
      <div className="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
        <div className="relative group">
          <Search className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-600 transition" size={18} />
          <input 
            type="text" 
            placeholder="Search by name, subject, or contact..." 
            className="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-500 transition font-medium"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
          />
        </div>
      </div>

      {/* Teachers Table */}
      <div className="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left">
            <thead>
              <tr className="bg-slate-50 text-slate-500 text-[10px] font-black uppercase tracking-widest border-b border-slate-100">
                <th className="px-8 py-5">Profile</th>
                <th className="px-6 py-5">Name & Specialization</th>
                <th className="px-6 py-5">Role</th>
                <th className="px-6 py-5">Contact</th>
                <th className="px-8 py-5 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-50">
              {teachers.map((teacher) => (
                <tr key={teacher.id} className="hover:bg-slate-50/80 transition group">
                  <td className="px-8 py-5">
                    <div className="w-12 h-12 bg-slate-100 rounded-xl overflow-hidden flex items-center justify-center text-slate-300">
                      {teacher.teacherPhoto ? (
                        <img src={teacher.teacherPhoto} alt="" className="w-full h-full object-cover" />
                      ) : (
                        <User size={24} />
                      )}
                    </div>
                  </td>
                  <td className="px-6 py-5">
                    <div className="font-bold text-slate-900 group-hover:text-indigo-600 transition">{teacher.fullName}</div>
                    <div className="text-[10px] text-indigo-500 font-bold uppercase tracking-tight">{teacher.subject || (teacher.staffRole === 'Staff' ? 'ADMIN/SUPPORT' : 'N/A')}</div>
                  </td>
                  <td className="px-6 py-5">
                    <span className={`inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black uppercase ${teacher.staffRole === 'Teacher' ? 'bg-indigo-50 text-indigo-700' : 'bg-amber-50 text-amber-700'}`}>
                      {teacher.staffRole}
                    </span>
                  </td>
                  <td className="px-6 py-5">
                    <div className="flex items-center gap-2 text-sm font-bold text-slate-600">
                      <Phone size={14} className="text-slate-300" />
                      {teacher.contact}
                    </div>
                  </td>
                  <td className="px-8 py-5 text-right">
                    <div className="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                      <button 
                        onClick={() => setViewTeacher(teacher)}
                        className="p-2 text-indigo-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="View Profile"
                      >
                        <Eye size={18} />
                      </button>
                      <button 
                        onClick={() => { setSelectedTeacher(teacher); setIsFormOpen(true); }}
                        className="p-2 text-emerald-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition" title="Edit"
                      >
                        <Edit2 size={18} />
                      </button>
                      <button 
                        onClick={() => handleDelete(teacher.id)}
                        className="p-2 text-rose-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Delete"
                      >
                        <Trash2 size={18} />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
              {teachers.length === 0 && (
                <tr>
                  <td colSpan="5" className="px-8 py-20 text-center">
                    <div className="flex flex-col items-center gap-3">
                      <div className="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-200">
                        <Users size={32} />
                      </div>
                      <p className="text-slate-400 font-bold">No faculty members found.</p>
                    </div>
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      {isFormOpen && (
        <TeacherForm 
          teacher={selectedTeacher} 
          onClose={() => setIsFormOpen(false)} 
          onSave={() => { setIsFormOpen(false); fetchTeachers(); }} 
        />
      )}

      {viewTeacher && (
        <ViewModal 
          teacher={viewTeacher} 
          onClose={() => setViewTeacher(null)} 
        />
      )}
    </div>
  );
};

export default TeacherList;
