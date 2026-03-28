import React, { useState, useEffect } from 'react';
import { studentService } from '../services/api';
import StudentForm from '../components/StudentForm';
import { Edit2, Trash2, Eye, Search, Filter, Plus, User, Phone, MapPin, GraduationCap, Calendar, ShieldCheck, X, Download, ChevronLeft, ChevronRight } from 'lucide-react';

const ViewModal = ({ student, onClose }) => {
  if (!student) return null;

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
          <h2 className="text-xl font-extrabold text-slate-900">Student Profile Details</h2>
          <button onClick={onClose} className="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full transition">
            <X size={20} />
          </button>
        </div>
        <div className="p-8 overflow-y-auto grid md:grid-cols-3 gap-8">
          {/* Main Info */}
          <div className="md:col-span-1 flex flex-col items-center text-center space-y-4">
            <div className="w-40 h-40 bg-indigo-50 rounded-3xl flex items-center justify-center text-indigo-500 border-4 border-indigo-100 shadow-inner relative overflow-hidden">
               {student.studentPhoto ? (
                  <img src={student.studentPhoto} alt={student.fullName} className="w-full h-full object-cover" />
               ) : (
                  <User size={64} />
               )}
            </div>
            <div>
              <h3 className="text-2xl font-black text-slate-900 leading-tight">{student.fullName}</h3>
              <span className="inline-block px-3 py-1 bg-indigo-100 text-indigo-700 text-xs font-bold rounded-full mt-2 uppercase">
                {student.symbolNo}
              </span>
            </div>
          </div>

          {/* Details Grid */}
          <div className="md:col-span-2 space-y-6">
            <div>
              <h4 className="flex items-center gap-2 text-xs font-black text-indigo-600 uppercase tracking-widest mb-3 border-b border-indigo-50 pb-2">Academic & Personal</h4>
              <div className="grid grid-cols-2 gap-3">
                <DetailRow label="Class" value={student.studentClass} icon={GraduationCap} />
                <DetailRow label="Roll No" value={student.rollNo} icon={ShieldCheck} />
                <DetailRow label="DOB (BS)" value={student.dobNepali} icon={Calendar} />
                <DetailRow label="Gender" value={student.gender} icon={User} />
                <DetailRow label="Caste" value={student.caste} icon={Info} />
                <DetailRow label="EMIS No" value={student.emisNo} icon={Info} />
              </div>
            </div>

            <div>
              <h4 className="flex items-center gap-2 text-xs font-black text-indigo-600 uppercase tracking-widest mb-3 border-b border-indigo-50 pb-2">Parent / Guardian</h4>
              <div className="grid grid-cols-2 gap-3">
                <DetailRow label="Father" value={student.fatherName} icon={Users} />
                <DetailRow label="Mother" value={student.motherName} icon={Users} />
                <DetailRow label="Guardian" value={student.guardianName} icon={User} />
                <DetailRow label="Contact" value={student.parentContact} icon={Phone} />
              </div>
            </div>

            <div className="grid grid-cols-2 gap-6">
              <div>
                <h4 className="text-xs font-black text-indigo-600 uppercase tracking-widest mb-3 border-b border-indigo-50 pb-2">Permanent Address</h4>
                <div className="space-y-2 text-sm text-slate-600">
                  <p><span className="font-bold text-slate-400 uppercase text-[10px] mr-2">Province:</span> {student.permProvince || '-'}</p>
                  <p><span className="font-bold text-slate-400 uppercase text-[10px] mr-2">District:</span> {student.permDistrict || '-'}</p>
                  <p><span className="font-bold text-slate-400 uppercase text-[10px] mr-2">Local:</span> {student.permLocalLevel || '-'}</p>
                  <p><span className="font-bold text-slate-400 uppercase text-[10px] mr-2">Ward/Tole:</span> {student.permWardNo} / {student.permTole}</p>
                </div>
              </div>
              <div>
                <h4 className="text-xs font-black text-indigo-600 uppercase tracking-widest mb-3 border-b border-indigo-50 pb-2">Temporary Address</h4>
                <div className="space-y-2 text-sm text-slate-600">
                   <p><span className="font-bold text-slate-400 uppercase text-[10px] mr-2">Province:</span> {student.tempProvince || '-'}</p>
                  <p><span className="font-bold text-slate-400 uppercase text-[10px] mr-2">District:</span> {student.tempDistrict || '-'}</p>
                  <p><span className="font-bold text-slate-400 uppercase text-[10px] mr-2">Local:</span> {student.tempLocalLevel || '-'}</p>
                  <p><span className="font-bold text-slate-400 uppercase text-[10px] mr-2">Ward/Tole:</span> {student.tempWardNo} / {student.tempTole}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

const Info = ({ size, className }) => <span className={className}>ℹ️</span>;
const Users = ({ size, className }) => <UsersIcon size={size} className={className} />;
import { Users as UsersIcon } from 'lucide-react';

const StudentList = () => {
  const [students, setStudents] = useState([]);
  const [search, setSearch] = useState('');
  const [classFilter, setClassFilter] = useState('');
  const [isFormOpen, setIsFormOpen] = useState(false);
  const [selectedStudent, setSelectedStudent] = useState(null);
  const [viewStudent, setViewStudent] = useState(null);

  useEffect(() => {
    fetchStudents();
  }, [search, classFilter]);

  const fetchStudents = async () => {
    try {
      const response = await studentService.getAll({ search, studentClass: classFilter });
      setStudents(response.data);
    } catch (err) {
      console.error(err);
    }
  };

  const handleDelete = async (id) => {
    if (window.confirm('Are you sure you want to delete this student record?')) {
      try {
        await studentService.delete(id);
        fetchStudents();
      } catch (err) {
        console.error(err);
      }
    }
  };

  const exportToCSV = () => {
    const headers = ['Symbol No', 'Name', 'Class', 'Roll No', 'Contact', 'Gender', 'DOB'];
    const rows = students.map(s => [
      s.symbolNo,
      s.fullName,
      s.studentClass,
      s.rollNo || '',
      s.parentContact,
      s.gender,
      s.dobNepali
    ]);

    const csvContent = [
      headers.join(','),
      ...rows.map(row => row.join(','))
    ].join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `students_export_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  return (
    <div className="max-w-7xl mx-auto space-y-8">
      {/* Header & Stats */}
      <div className="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
          <h1 className="text-4xl font-black text-slate-900 tracking-tight">Student Records</h1>
          <p className="text-slate-500 font-medium">Manage academic profiles and enrollment.</p>
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
            onClick={() => { setSelectedStudent(null); setIsFormOpen(true); }}
            className="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3.5 rounded-2xl font-bold flex items-center justify-center gap-2 transition shadow-xl shadow-indigo-100 group"
          >
            <Plus size={20} className="group-hover:rotate-90 transition-transform" />
            Enroll New Student
          </button>
        </div>
      </div>

      {/* Persistence Bar */}
      <div className="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex flex-col md:flex-row gap-4 items-center">
        <div className="relative flex-1 group">
          <Search className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-600 transition" size={18} />
          <input 
            type="text" 
            placeholder="Search by name, symbol no, or contact..." 
            className="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-500 transition font-medium"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
          />
        </div>
        <div className="relative w-full md:w-64">
          <Filter className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" size={18} />
          <select 
            className="w-full pl-12 pr-10 py-3 bg-slate-50 border border-slate-100 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-500 appearance-none font-bold text-slate-600"
            value={classFilter}
            onChange={(e) => setClassFilter(e.target.value)}
          >
            <option value="">All Classes</option>
            {['Nursery', 'LKG', 'UKG', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'].map(c => <option key={c} value={c}>Class {c}</option>)}
          </select>
        </div>
      </div>

      {/* Student Table */}
      <div className="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left">
            <thead>
              <tr className="bg-slate-50 text-slate-500 text-[10px] font-black uppercase tracking-widest border-b border-slate-100">
                <th className="px-8 py-5">Symbol No</th>
                <th className="px-6 py-5">Student Name</th>
                <th className="px-6 py-5">Class</th>
                <th className="px-6 py-5">Roll No</th>
                <th className="px-6 py-5">Contact</th>
                <th className="px-8 py-5 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-50">
              {students.map((student) => (
                <tr key={student.id} className="hover:bg-slate-50/80 transition group">
                  <td className="px-8 py-5">
                    <span className="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700">
                      {student.symbolNo}
                    </span>
                  </td>
                  <td className="px-6 py-5">
                    <div className="font-bold text-slate-900 group-hover:text-indigo-600 transition">{student.fullName}</div>
                    <div className="text-[10px] text-slate-400 font-bold uppercase tracking-tighter">{student.gender || 'N/A'}</div>
                  </td>
                  <td className="px-6 py-5">
                    <span className="text-sm font-semibold text-slate-600">Class {student.studentClass}</span>
                  </td>
                  <td className="px-6 py-5 font-mono text-xs text-slate-500">{student.rollNo || '-'}</td>
                  <td className="px-6 py-5">
                    <div className="flex items-center gap-2 text-sm font-bold text-slate-600">
                      <Phone size={14} className="text-slate-300" />
                      {student.parentContact}
                    </div>
                  </td>
                  <td className="px-8 py-5 text-right">
                    <div className="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                      <button 
                        onClick={() => setViewStudent(student)}
                        className="p-2 text-indigo-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="View Profile"
                      >
                        <Eye size={18} />
                      </button>
                      <button 
                        onClick={() => { setSelectedStudent(student); setIsFormOpen(true); }}
                        className="p-2 text-emerald-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition" title="Edit"
                      >
                        <Edit2 size={18} />
                      </button>
                      <button 
                        onClick={() => handleDelete(student.id)}
                        className="p-2 text-rose-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Delete"
                      >
                        <Trash2 size={18} />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
              {students.length === 0 && (
                <tr>
                  <td colSpan="6" className="px-8 py-20 text-center">
                    <div className="flex flex-col items-center gap-3">
                      <div className="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-200">
                        <User size={32} />
                      </div>
                      <p className="text-slate-400 font-bold">No students found matching your search.</p>
                    </div>
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      <div className="flex justify-between items-center text-xs font-bold text-slate-400 uppercase tracking-widest px-2">
        <span>Showing {students.length} student records</span>
        <div className="flex items-center gap-2">
           <button className="p-2 hover:bg-slate-100 rounded-lg transition text-slate-300 pointer-events-none">
             <ChevronLeft size={16} />
           </button>
           <span className="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg">1</span>
           <button className="p-2 hover:bg-slate-100 rounded-lg transition text-slate-400">
             <ChevronRight size={16} />
           </button>
        </div>
      </div>

      {isFormOpen && (
        <StudentForm 
          student={selectedStudent} 
          onClose={() => setIsFormOpen(false)} 
          onSave={() => { setIsFormOpen(false); fetchStudents(); }} 
        />
      )}

      {viewStudent && (
        <ViewModal 
          student={viewStudent} 
          onClose={() => setViewStudent(null)} 
        />
      )}
    </div>
  );
};

export default StudentList;
