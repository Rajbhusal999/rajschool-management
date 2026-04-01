import React, { useState, useEffect, useMemo } from 'react';
import { studentService, teacherService } from '../services/api';
import { 
  Users, GraduationCap, Calendar, BookOpen, 
  Printer, ChevronRight, Search, LayoutGrid,
  Mail, Phone, MapPin, Award, User
} from 'lucide-react';
import './IdCardPrint.css';

const IdCardPrint = () => {
  const [userType, setUserType] = useState('student');
  const [selectedClass, setSelectedClass] = useState('1');
  const [year, setYear] = useState('2083');
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(false);

  const schoolName = sessionStorage.getItem('schoolName') || 'Raj High School';
  const schoolAddress = sessionStorage.getItem('schoolAddress') || 'Kathmandu, Nepal';
  const schoolPhone = sessionStorage.getItem('schoolPhone') || '+977 01-440000';
  const schoolLogo = sessionStorage.getItem('schoolLogo');

  const classes = ['Nursery', 'LKG', 'UKG', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'];
  const years = Array.from({length: 5}, (_, i) => (2080 + i).toString());

  const fetchData = async () => {
    setLoading(true);
    try {
      if (userType === 'student') {
        const res = await studentService.getAll({ studentClass: selectedClass });
        setData(res.data || []);
      } else {
        const res = await teacherService.getAll();
        setData(res.data || []);
      }
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, [userType, selectedClass]);

  const handlePrint = () => {
    window.print();
  };

  const getExpiryDate = (selectedYear) => {
    return `Chaitra ${selectedYear}`;
  };

  return (
    <div className="max-w-7xl mx-auto space-y-8 pb-20 animate-in fade-in duration-700">
      
      {/* Header & Controls */}
      <div className="flex flex-col md:flex-row md:items-end justify-between gap-6 px-2 no-print">
        <div className="space-y-2">
          <div className="flex items-center gap-3 text-indigo-600 font-black text-[10px] uppercase tracking-widest bg-indigo-50 w-fit px-3 py-1 rounded-full border border-indigo-100">
             <Award size={12} /> ID Card Generation Engine
          </div>
          <h1 className="text-4xl font-[1000] text-slate-900 tracking-tight leading-none uppercase">Identity Badge Center</h1>
          <p className="text-slate-500 font-bold text-sm tracking-wide">Generate professional-grade ID cards for your students and faculty members.</p>
        </div>
        <div className="flex gap-3">
          <button onClick={handlePrint} className="p-4 bg-indigo-600 text-white rounded-2xl hover:bg-indigo-700 transition shadow-xl shadow-indigo-100 font-black text-xs uppercase tracking-widest flex items-center gap-2">
            <Printer size={18} /> Print All Cards
          </button>
        </div>
      </div>

      {/* Control Panel */}
      <div className="bg-white p-6 rounded-[32px] border border-slate-100 shadow-xl shadow-slate-100/5 flex flex-wrap gap-6 no-print">
        
        {/* User Type Toggle */}
        <div className="space-y-3 flex-1 min-w-[240px]">
          <span className="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-4">Credential Type</span>
          <div className="grid grid-cols-2 p-1.5 bg-slate-100 rounded-2xl">
            <button 
              onClick={() => setUserType('student')}
              className={`flex items-center justify-center gap-2 py-3 rounded-xl font-black text-xs uppercase tracking-widest transition-all ${userType === 'student' ? 'bg-white text-indigo-600 shadow-md' : 'text-slate-500 hover:text-slate-700'}`}
            >
              <Users size={16} /> Student
            </button>
            <button 
              onClick={() => setUserType('teacher')}
              className={`flex items-center justify-center gap-2 py-3 rounded-xl font-black text-xs uppercase tracking-widest transition-all ${userType === 'teacher' ? 'bg-white text-indigo-600 shadow-md' : 'text-slate-500 hover:text-slate-700'}`}
            >
              <GraduationCap size={16} /> Teacher
            </button>
          </div>
        </div>

        {/* Academic Year */}
        <div className="space-y-3 w-[150px]">
          <span className="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-4">Session</span>
          <div className="relative">
            <Calendar className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" size={18} />
            <select 
              value={year}
              onChange={(e) => setYear(e.target.value)}
              className="w-full h-[56px] pl-12 pr-4 bg-slate-50 border-none rounded-2xl outline-none focus:ring-4 focus:ring-indigo-100 transition font-black text-slate-700 text-xs uppercase tracking-widest appearance-none cursor-pointer"
            >
              {years.map(y => <option key={y} value={y}>{y} BS</option>)}
            </select>
          </div>
        </div>

        {/* Class Selection (Only for Student) */}
        {userType === 'student' && (
          <div className="space-y-3 w-[200px]">
            <span className="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-4">Target Class</span>
            <div className="relative">
              <BookOpen className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" size={18} />
              <select 
                value={selectedClass}
                onChange={(e) => setSelectedClass(e.target.value)}
                className="w-full h-[56px] pl-12 pr-4 bg-slate-50 border-none rounded-2xl outline-none focus:ring-4 focus:ring-indigo-100 transition font-black text-slate-700 text-xs uppercase tracking-widest appearance-none cursor-pointer"
              >
                {classes.map(c => <option key={c} value={c}>Class {c}</option>)}
              </select>
            </div>
          </div>
        )}
      </div>

      {/* ID Cards Preview Area */}
      {loading ? (
        <div className="flex flex-col items-center justify-center py-20 gap-4 text-slate-400">
           <div className="w-12 h-12 border-4 border-indigo-100 border-t-indigo-600 rounded-full animate-spin"></div>
           <span className="font-black text-xs uppercase tracking-[0.2em]">Synchronizing Records...</span>
        </div>
      ) : data.length > 0 ? (
        <div className="id-card-container">
          {data.map((item, index) => (
            <div key={index} className="id-card">
              {/* Header */}
              <div className="id-card-header">
                <div className="id-card-logo">
                  {schoolLogo ? (
                    <img src={schoolLogo} alt="Logo" />
                  ) : (
                    <span className="text-indigo-600 font-bold text-lg leading-none">{schoolName[0]}</span>
                  )}
                </div>
                <div className="id-card-school-info">
                  <h2>{schoolName}</h2>
                  <p>{schoolAddress}</p>
                </div>
              </div>

              {/* Body */}
              <div className="id-card-body">
                <div className="id-card-photo-box">
                  {userType === 'student' && item.studentPhoto ? (
                    <img src={item.studentPhoto} alt={item.fullName} />
                  ) : userType === 'teacher' && item.teacherPhoto ? (
                    <img src={item.teacherPhoto} alt={item.fullName} />
                  ) : (
                    <User size={40} className="text-slate-300" />
                  )}
                </div>
                <div className="id-card-details">
                  <h3 className="id-card-name underline decoration-indigo-500/20 underline-offset-4">{item.fullName}</h3>
                  
                  <div className="id-card-row">
                    <span className="id-card-label">{userType === 'student' ? 'Class' : 'Role'}:</span>
                    <span className="id-card-value">{userType === 'student' ? item.studentClass : item.staffRole || 'Faculty'}</span>
                  </div>

                  {userType === 'student' && (
                    <div className="id-card-row">
                      <span className="id-card-label">Roll No:</span>
                      <span className="id-card-value">{item.rollNo || '-'}</span>
                    </div>
                  )}

                  <div className="id-card-row">
                    <span className="id-card-label">Parent:</span>
                    <span className="id-card-value">{userType === 'student' ? item.guardianName || '-' : 'Principal'}</span>
                  </div>

                  <div className="id-card-row">
                    <span className="id-card-label">Phone:</span>
                    <span className="id-card-value">{item.contact || schoolPhone}</span>
                  </div>
                </div>
              </div>

              {/* Footer */}
              <div className="id-card-footer">
                <span className={`id-card-role-tag ${userType === 'student' ? 'tag-student' : 'tag-teacher'}`}>
                  {userType === 'student' ? 'Student Card' : 'Staff Badge'}
                </span>
                <div className="text-right">
                  <span className="block text-[6pt] font-black text-slate-400 uppercase tracking-tighter">Expires</span>
                  <span className="block text-[6pt] font-bold text-slate-800">{getExpiryDate(year)}</span>
                </div>
              </div>
            </div>
          ))}
        </div>
      ) : (
        <div className="bg-white p-20 rounded-[40px] border border-dashed border-slate-200 text-center space-y-4">
           <div className="w-16 h-16 bg-slate-50 text-slate-300 rounded-2xl flex items-center justify-center mx-auto">
             <LayoutGrid size={32} />
           </div>
           <h3 className="text-xl font-black text-slate-800 tracking-tight">No Records Found</h3>
           <p className="text-slate-400 font-bold text-sm">No {userType} data available for the selected criteria.</p>
        </div>
      )}

    </div>
  );
};

export default IdCardPrint;
