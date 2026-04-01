import React, { useState, useEffect } from 'react';
import { studentService, examService } from '../services/api';
import { ClipboardList, Save, Search, AlertCircle, CheckCircle, ChevronRight, GraduationCap } from 'lucide-react';

const MarkEntry = () => {
    const [loading, setLoading] = useState(false);
    const [saving, setSaving] = useState(false);
    const [message, setMessage] = useState(null);
    
    // Filters
    const [year, setYear] = useState('2083');
    const [selectedClass, setSelectedClass] = useState('');
    const [examType, setExamType] = useState('first_terminal');
    
    // Data
    const [students, setStudents] = useState([]);
    const [subjects, setSubjects] = useState([]);
    const [marks, setMarks] = useState({}); // { studentId: { subjectName: { ...markData } } }
    
    const classes = ['PG', 'Nursery', 'LKG', 'UKG', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10'];
    const years = Array.from({ length: 11 }, (_, i) => (2080 + i).toString());
    const examTypes = [
        { id: 'first_terminal', name: 'First Terminal' },
        { id: 'second_terminal', name: 'Second Terminal' },
        { id: 'third_terminal', name: 'Third Terminal' },
        { id: 'final', name: 'Final Examination' },
        { id: 'monthly', name: 'Monthly' }
    ];

    const getClassGroup = (cls) => {
        if (['PG', 'Nursery', 'LKG', 'UKG'].includes(cls)) return cls;
        const n = parseInt(cls);
        if (n >= 1 && n <= 3) return '1-3';
        if (n >= 4 && n <= 5) return '4-5';
        if (n >= 6 && n <= 8) return '6-8';
        if (n >= 9 && n <= 10) return '9-10';
        return '1-3';
    };

    const handleSearch = async () => {
        if (!selectedClass) return;
        setLoading(true);
        setMessage(null);
        try {
            const group = getClassGroup(selectedClass);
            const [stdRes, subRes] = await Promise.all([
                studentService.getAll({ schoolId: sessionStorage.getItem('institutionId'), studentClass: selectedClass }),
                examService.getSubjects({ schoolId: sessionStorage.getItem('institutionId'), classGroup: group })
            ]);
            
            setStudents(stdRes.data);
            setSubjects(subRes.data);
            
            // Initialize/Fetch marks
            const markRequests = stdRes.data.map(s => 
                examService.getMarks({ schoolId: sessionStorage.getItem('institutionId'), studentId: s.id, examType, year })
            );
            const markResults = await Promise.all(markRequests);
            
            const markMap = {};
            markResults.forEach((res, index) => {
                const studentId = stdRes.data[index].id;
                markMap[studentId] = {};
                res.data.forEach(m => {
                    markMap[studentId][m.subject] = m;
                });
            });
            setMarks(markMap);
        } catch (error) {
            console.error('Error fetching data:', error);
            setMessage({ type: 'error', text: 'Failed to load data.' });
        } finally {
            setLoading(false);
        }
    };

    const handleMarkChange = (studentId, subjectName, field, value) => {
        setMarks(prev => ({
            ...prev,
            [studentId]: {
                ...prev[studentId],
                [subjectName]: {
                    ...prev[studentId][subjectName],
                    [field]: value
                }
            }
        }));
    };

    const handleSave = async () => {
        setSaving(true);
        try {
            const payload = [];
            Object.keys(marks).forEach(studentId => {
                const subjectsForStudent = marks[studentId];
                Object.keys(subjectsForStudent).forEach(subjectName => {
                    payload.push({
                        ...subjectsForStudent[subjectName],
                        schoolId: sessionStorage.getItem('institutionId'),
                        studentId: parseInt(studentId),
                        subject: subjectName,
                        examType,
                        year: parseInt(year),
                        studentClass: selectedClass
                    });
                });
            });

            if (payload.length > 0) {
                await examService.saveMarksBulk(payload);
                setMessage({ type: 'success', text: 'Marks saved successfully!' });
            }
        } catch (error) {
            console.error('Error saving marks:', error);
            setMessage({ type: 'error', text: 'Failed to save marks.' });
        } finally {
            setSaving(false);
        }
    };

    const isClass1to3 = ['1', '2', '3'].includes(selectedClass);
    const isPrePrimary = ['PG', 'NURSERY', 'LKG', 'UKG'].includes(selectedClass);

    return (
        <div className="max-w-7xl mx-auto space-y-6">
            {/* Header & Filter */}
            <div className="bg-white p-8 rounded-[40px] shadow-sm border border-slate-100">
                <div className="flex flex-col md:flex-row gap-6 items-end">
                    <div className="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div className="space-y-2">
                            <label className="text-xs font-black uppercase text-slate-400 tracking-wider">Academic Year (B.S.)</label>
                            <select value={year} onChange={(e) => setYear(e.target.value)} className="w-full px-5 py-3 bg-slate-50 border-none rounded-2xl font-bold appearance-none cursor-pointer">
                                {years.map(y => <option key={y} value={y}>{y} BS</option>)}
                            </select>
                        </div>
                        <div className="space-y-2">
                            <label className="text-xs font-black uppercase text-slate-400 tracking-wider">Class</label>
                            <select value={selectedClass} onChange={(e) => setSelectedClass(e.target.value)} className="w-full px-5 py-3 bg-slate-50 border-none rounded-2xl font-bold">
                                <option value="">Select Class</option>
                                {classes.map(c => <option key={c} value={c}>Class {c}</option>)}
                            </select>
                        </div>
                        <div className="space-y-2">
                            <label className="text-xs font-black uppercase text-slate-400 tracking-wider">Exam Type</label>
                            <select value={examType} onChange={(e) => setExamType(e.target.value)} className="w-full px-5 py-3 bg-slate-50 border-none rounded-2xl font-bold">
                                {examTypes.map(t => <option key={t.id} value={t.id}>{t.name}</option>)}
                            </select>
                        </div>
                    </div>
                    <button onClick={handleSearch} className="px-8 py-3 bg-indigo-600 text-white rounded-2xl font-bold flex items-center gap-2 hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
                        <Search size={20} />
                        Fetch Records
                    </button>
                </div>
            </div>

            {message && (
                <div className={`p-4 rounded-2xl flex items-center gap-3 animate-in slide-in-from-top duration-300 ${message.type === 'success' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'}`}>
                    {message.type === 'success' ? <CheckCircle size={20} /> : <AlertCircle size={20} />}
                    <span className="font-bold">{message.text}</span>
                </div>
            )}

            {/* Mark Table */}
            {students.length > 0 && subjects.length > 0 && (
                <div className="bg-white rounded-[40px] shadow-sm border border-slate-100 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full border-collapse">
                            <thead>
                                <tr className="bg-slate-50 border-b border-slate-100">
                                    <th className="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-widest sticky left-0 bg-slate-50 shadow-[2px_0_5px_rgba(0,0,0,0.05)]">Student Name</th>
                                    {subjects.map(s => (
                                        <th key={s.id} className="px-4 py-4 text-center border-l border-slate-100 min-w-[200px]">
                                            <div className="text-indigo-600 font-black text-sm uppercase">{s.subjectName}</div>
                                            <div className="text-[10px] text-slate-400 font-bold">{isPrePrimary ? 'RW / LS' : (isClass1to3 ? 'Learning Achievement' : 'P / PR / T / EX')}</div>
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-50">
                                {students.map(student => (
                                    <tr key={student.id} className="hover:bg-slate-50/50 transition-colors">
                                        <td className="px-6 py-4 font-bold text-slate-700 sticky left-0 bg-white shadow-[2px_0_5px_rgba(0,0,0,0.05)]">
                                            <div>{student.fullName}</div>
                                            <div className="text-[10px] text-slate-400">Symbol: {student.symbolNo}</div>
                                        </td>
                                        {subjects.map(subject => (
                                            <td key={subject.id} className="px-4 py-4 border-l border-slate-100">
                                                <div className="flex gap-2 justify-center">
                                                    {isClass1to3 ? (
                                                        <div className="flex items-center gap-2">
                                                            <input 
                                                                type="number" 
                                                                placeholder="Obt"
                                                                value={marks[student.id]?.[subject.subjectName]?.laObtained || ''}
                                                                onChange={(e) => handleMarkChange(student.id, subject.subjectName, 'laObtained', e.target.value)}
                                                                className="w-16 px-2 py-2 bg-slate-50 border-none rounded-xl text-center font-bold text-indigo-600"
                                                            />
                                                            <span className="text-slate-300 font-bold">/</span>
                                                            <input 
                                                                type="number"
                                                                placeholder="Tot"
                                                                value={marks[student.id]?.[subject.subjectName]?.laTotal || subject.creditHour || ''}
                                                                onChange={(e) => handleMarkChange(student.id, subject.subjectName, 'laTotal', e.target.value)}
                                                                className="w-16 px-2 py-2 bg-slate-100 border-none rounded-xl text-center font-bold text-slate-400"
                                                            />
                                                        </div>
                                                    ) : isPrePrimary ? (
                                                        <div className="flex items-center gap-2">
                                                            <input 
                                                                type="number" 
                                                                placeholder="RW"
                                                                value={marks[student.id]?.[subject.subjectName]?.practical || ''}
                                                                onChange={(e) => handleMarkChange(student.id, subject.subjectName, 'practical', e.target.value)}
                                                                className="w-16 px-2 py-2 bg-emerald-50 border-none rounded-xl text-center font-bold text-emerald-600"
                                                            />
                                                            <input 
                                                                type="number"
                                                                placeholder="LS"
                                                                value={marks[student.id]?.[subject.subjectName]?.terminal || ''}
                                                                onChange={(e) => handleMarkChange(student.id, subject.subjectName, 'terminal', e.target.value)}
                                                                className="w-16 px-2 py-2 bg-sky-50 border-none rounded-xl text-center font-bold text-sky-600"
                                                            />
                                                        </div>
                                                    ) : (
                                                        <div className="grid grid-cols-4 gap-1">
                                                            <input type="number" title="Participation" placeholder="P" value={marks[student.id]?.[subject.subjectName]?.participation || ''} onChange={(e) => handleMarkChange(student.id, subject.subjectName, 'participation', e.target.value)} className="w-10 px-1 py-2 bg-amber-50 border-none rounded-lg text-center text-[10px] font-bold" />
                                                            <input type="number" title="Practical" placeholder="PR" value={marks[student.id]?.[subject.subjectName]?.practical || ''} onChange={(e) => handleMarkChange(student.id, subject.subjectName, 'practical', e.target.value)} className="w-10 px-1 py-2 bg-emerald-50 border-none rounded-lg text-center text-[10px] font-bold" />
                                                            <input type="number" title="Terminal" placeholder="T" value={marks[student.id]?.[subject.subjectName]?.terminal || ''} onChange={(e) => handleMarkChange(student.id, subject.subjectName, 'terminal', e.target.value)} className="w-10 px-1 py-2 bg-sky-50 border-none rounded-lg text-center text-[10px] font-bold" />
                                                            {examType === 'final' && (
                                                                <input type="number" title="External" placeholder="EX" value={marks[student.id]?.[subject.subjectName]?.external || ''} onChange={(e) => handleMarkChange(student.id, subject.subjectName, 'external', e.target.value)} className="w-10 px-1 py-2 bg-rose-50 border-none rounded-lg text-center text-[10px] font-bold" />
                                                            )}
                                                        </div>
                                                    )}
                                                </div>
                                            </td>
                                        ))}
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <div className="p-8 bg-slate-50 flex justify-end">
                        <button 
                            onClick={handleSave} 
                            disabled={saving}
                            className="px-12 py-4 bg-indigo-600 text-white rounded-2xl font-bold flex items-center gap-2 hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-100 disabled:opacity-50"
                        >
                            {saving ? <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></div> : <Save size={24} />}
                            {saving ? 'Processing...' : 'Save All Marks'}
                        </button>
                    </div>
                </div>
            )}

            {students.length === 0 && !loading && selectedClass && (
                <div className="py-20 bg-white rounded-[40px] border border-dashed border-slate-200 flex flex-col items-center justify-center text-center">
                    <div className="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center text-slate-300 mb-4">
                        <GraduationCap size={32} />
                    </div>
                    <h3 className="text-xl font-bold text-slate-400">No students found for Class {selectedClass}</h3>
                    <p className="text-slate-400 text-sm mt-1 max-w-sm">Please verify the class assignment or add students to this class first.</p>
                </div>
            )}
        </div>
    );
};

export default MarkEntry;
