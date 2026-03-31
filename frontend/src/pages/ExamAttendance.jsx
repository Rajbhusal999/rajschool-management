import React, { useState, useEffect } from 'react';
import { studentService, examService } from '../services/api';
import { CalendarCheck, Save, Search, AlertCircle, CheckCircle, Users } from 'lucide-react';

const ExamAttendance = () => {
    const [loading, setLoading] = useState(false);
    const [saving, setSaving] = useState(false);
    const [message, setMessage] = useState(null);
    
    // Filters
    const [year, setYear] = useState('2081');
    const [selectedClass, setSelectedClass] = useState('');
    const [examType, setExamType] = useState('first_terminal');
    
    // Data
    const [students, setStudents] = useState([]);
    const [attendance, setAttendance] = useState({}); // { studentId: { totalDays: X, presentDays: Y } }
    
    const classes = ['PG', 'NURSERY', 'LKG', 'UKG', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'];
    const examTypes = [
        { id: 'first_terminal', name: 'First Terminal' },
        { id: 'second_terminal', name: 'Second Terminal' },
        { id: 'third_terminal', name: 'Third Terminal' },
        { id: 'final', name: 'Final Examination' }
    ];

    const handleSearch = async () => {
        if (!selectedClass) return;
        setLoading(true);
        setMessage(null);
        try {
            const [stdRes, attRes] = await Promise.all([
                studentService.getAll({ schoolId: sessionStorage.getItem('institutionId'), studentClass: selectedClass }),
                examService.getAttendance({ schoolId: sessionStorage.getItem('institutionId'), class: selectedClass, examType, year })
            ]);
            
            setStudents(stdRes.data);
            
            const attMap = {};
            // Initialize with students or existing data
            stdRes.data.forEach(s => {
                const existing = attRes.data.find(a => a.studentId === s.id);
                attMap[s.id] = existing ? {
                    totalDays: existing.totalDays || '',
                    presentDays: existing.presentDays || '',
                    id: existing.id
                } : { totalDays: '', presentDays: '' };
            });
            setAttendance(attMap);
        } catch (error) {
            console.error('Error fetching data:', error);
            setMessage({ type: 'error', text: 'Failed to load records.' });
        } finally {
            setLoading(false);
        }
    };

    const handleAttendanceChange = (studentId, field, value) => {
        setAttendance(prev => ({
            ...prev,
            [studentId]: {
                ...prev[studentId],
                [field]: value
            }
        }));
    };

    // Bulk update total working days for all students
    const handleBulkTotalDays = (value) => {
        setAttendance(prev => {
            const newAtt = { ...prev };
            Object.keys(newAtt).forEach(id => {
                newAtt[id].totalDays = value;
            });
            return newAtt;
        });
    };

    const handleSave = async () => {
        setSaving(true);
        try {
            const payload = Object.keys(attendance).map(studentId => ({
                ...attendance[studentId],
                studentId: parseInt(studentId),
                examType,
                year: parseInt(year),
                studentClass: selectedClass
            }));

            await examService.saveAttendanceBulk(payload);
            setMessage({ type: 'success', text: 'Attendance saved successfully!' });
        } catch (error) {
            console.error('Error saving attendance:', error);
            setMessage({ type: 'error', text: 'Failed to save records.' });
        } finally {
            setSaving(true); // Keep success visible
            setTimeout(() => setSaving(false), 2000);
        }
    };

    return (
        <div className="max-w-6xl mx-auto space-y-6 pb-20">
            {/* Header */}
            <div className="bg-indigo-600 p-10 rounded-[48px] text-white shadow-xl shadow-indigo-100 flex flex-col md:flex-row justify-between items-center gap-8">
                <div className="flex items-center gap-6">
                    <div className="p-4 bg-white/10 rounded-3xl backdrop-blur-md">
                        <CalendarCheck size={40} />
                    </div>
                    <div>
                        <h1 className="text-4xl font-black tracking-tight">Exam Attendance</h1>
                        <p className="font-bold opacity-80 uppercase text-xs tracking-widest mt-1">Terminal Assessment Portal</p>
                    </div>
                </div>
            </div>

            {/* Selection Filters */}
            <div className="bg-white p-8 rounded-[40px] shadow-sm border border-slate-100">
                <div className="flex flex-col md:flex-row gap-6 items-end">
                    <div className="flex-1 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div className="space-y-2">
                            <label className="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Academic Year</label>
                            <input 
                                type="number" 
                                value={year} 
                                onChange={(e) => setYear(e.target.value)} 
                                className="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl font-bold focus:ring-2 focus:ring-indigo-500 transition-all" 
                            />
                        </div>
                        <div className="space-y-2">
                            <label className="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Target Class</label>
                            <select 
                                value={selectedClass} 
                                onChange={(e) => setSelectedClass(e.target.value)} 
                                className="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl font-bold focus:ring-2 focus:ring-indigo-500 transition-all"
                            >
                                <option value="">Choose Class</option>
                                {classes.map(c => <option key={c} value={c}>Class {c}</option>)}
                            </select>
                        </div>
                        <div className="space-y-2">
                            <label className="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Terminal Exam</label>
                            <select 
                                value={examType} 
                                onChange={(e) => setExamType(e.target.value)} 
                                className="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl font-bold focus:ring-2 focus:ring-indigo-500 transition-all"
                            >
                                {examTypes.map(t => <option key={t.id} value={t.id}>{t.name}</option>)}
                            </select>
                        </div>
                    </div>
                    <button 
                        onClick={handleSearch} 
                        disabled={loading}
                        className="px-10 py-4 bg-indigo-600 text-white rounded-2xl font-bold flex items-center gap-3 hover:bg-indigo-700 transition shadow-xl shadow-indigo-100 disabled:opacity-50"
                    >
                        {loading ? <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></div> : <Search size={22} />}
                        Fetch Roster
                    </button>
                </div>
            </div>

            {message && (
                <div className={`p-5 rounded-[24px] flex items-center gap-4 animate-in slide-in-from-top-4 duration-300 ${message.type === 'success' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'}`}>
                    {message.type === 'success' ? <CheckCircle size={24} /> : <AlertCircle size={24} />}
                    <span className="font-extrabold">{message.text}</span>
                </div>
            )}

            {/* Entry Table */}
            {students.length > 0 && (
                <div className="bg-white rounded-[48px] shadow-sm border border-slate-100 overflow-hidden">
                    <div className="p-6 bg-slate-50 border-b border-slate-100 flex justify-between items-center px-10">
                        <div className="flex items-center gap-3">
                            <Users size={20} className="text-slate-400" />
                            <span className="text-xs font-black uppercase text-slate-400 tracking-widest">Student Attendance List</span>
                        </div>
                        <div className="flex items-center gap-4">
                             <label className="text-[10px] font-black uppercase text-slate-400 tracking-widest">Set Bulk Working Days:</label>
                             <input 
                                type="number" 
                                placeholder="Auto-fill"
                                onChange={(e) => handleBulkTotalDays(e.target.value)}
                                className="w-24 px-4 py-2 bg-white border border-slate-200 rounded-xl font-bold text-sm text-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                             />
                        </div>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="w-full text-left">
                            <thead>
                                <tr className="text-[10px] font-black uppercase tracking-widest text-slate-400">
                                    <th className="px-10 py-6">ID / Symbol</th>
                                    <th className="px-6 py-6">Student Full Name</th>
                                    <th className="px-6 py-6 text-center">Total Working Days</th>
                                    <th className="px-10 py-6 text-center">Days Present</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-50">
                                {students.map(s => (
                                    <tr key={s.id} className="group hover:bg-slate-50/50 transition-all">
                                        <td className="px-10 py-6 font-black text-slate-400">{s.symbolNo}</td>
                                        <td className="px-6 py-6 font-black text-slate-800 text-lg group-hover:text-indigo-600 transition-colors">{s.fullName}</td>
                                        <td className="px-6 py-6">
                                            <div className="flex justify-center">
                                                <input 
                                                    type="number" 
                                                    value={attendance[s.id]?.totalDays || ''}
                                                    onChange={(e) => handleAttendanceChange(s.id, 'totalDays', e.target.value)}
                                                    className="w-24 px-4 py-3 bg-slate-50 border-none rounded-xl text-center font-bold text-slate-600 focus:ring-2 focus:ring-indigo-500"
                                                    placeholder="Total"
                                                />
                                            </div>
                                        </td>
                                        <td className="px-10 py-6">
                                            <div className="flex justify-center">
                                                <input 
                                                    type="number" 
                                                    value={attendance[s.id]?.presentDays || ''}
                                                    onChange={(e) => handleAttendanceChange(s.id, 'presentDays', e.target.value)}
                                                    className="w-32 px-5 py-4 bg-indigo-50 border-none rounded-2xl text-center font-black text-indigo-600 focus:ring-2 focus:ring-indigo-500 text-xl"
                                                    placeholder="Present"
                                                />
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <div className="p-10 bg-slate-50 border-t border-slate-100 flex justify-end">
                        <button 
                            onClick={handleSave} 
                            disabled={saving}
                            className="px-16 py-5 bg-indigo-600 text-white rounded-[24px] font-black text-lg flex items-center gap-3 hover:bg-indigo-700 transition-all shadow-2xl shadow-indigo-100 disabled:opacity-50"
                        >
                            {saving ? <div className="w-6 h-6 border-2 border-white/30 border-t-white rounded-full animate-spin"></div> : <Save size={24} />}
                            {saving ? 'Saving Records...' : 'Save All Attendance'}
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
};

export default ExamAttendance;
