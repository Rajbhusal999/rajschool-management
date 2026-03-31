import React, { useState, useEffect } from 'react';
import { useSearchParams, useNavigate } from 'react-router-dom';
import { 
  studentService, 
  examService 
} from '../services/api';
import { 
  Calendar, 
  Users, 
  Save, 
  Printer, 
  ChevronLeft, 
  Clock, 
  CheckCircle,
  ChevronDown
} from 'lucide-react';

const AdmitCardConfig = () => {
    const [searchParams] = useSearchParams();
    const navigate = useNavigate();
    
    // Config from URL
    const selectedClass = searchParams.get('class');
    const examType = searchParams.get('exam');
    const year = searchParams.get('year');

    // UI State
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    
    // Data State
    const [students, setStudents] = useState([]);
    const [selectedStudentIds, setSelectedStudentIds] = useState([]);
    
    // Schedule State
    const [shift, setShift] = useState('DAY');
    const [examTime, setExamTime] = useState('10:00 - 01:00');
    const [subjectData, setSubjectData] = useState(
        Array.from({ length: 12 }, () => ({ subject: '', date: '' }))
    );

    useEffect(() => {
        fetchData();
    }, [selectedClass, examType, year]);

    const fetchData = async () => {
        setLoading(true);
        try {
            const [stdRes, schRes] = await Promise.all([
                studentService.getAll({ schoolId: sessionStorage.getItem('institutionId'), studentClass: selectedClass }),
                examService.getSchedule({ class: selectedClass, examType, year })
            ]);
            
            setStudents(stdRes.data);
            setSelectedStudentIds(stdRes.data.map(s => s.id)); // Default select all
            
            if (schRes.data && schRes.data.id) {
                setShift(schRes.data.shift || 'DAY');
                setExamTime(schRes.data.examTime || '10:00 - 01:00');
                if (schRes.data.subjectData) {
                    // Fill existing data and pad with empty rows if needed
                    const filled = Array.isArray(schRes.data.subjectData) ? [...schRes.data.subjectData] : [];
                    while (filled.length < 12) filled.push({ subject: '', date: '' });
                    setSubjectData(filled);
                }
            }
        } catch (error) {
            console.error('Error fetching data:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleSubjectChange = (index, field, value) => {
        const newData = [...subjectData];
        if (field === 'date') {
            const prevValue = subjectData[index].date;
            // If deleting, don't auto-format
            if (value.length < prevValue.length) {
                newData[index].date = value;
            } else {
                let formatted = value.replace(/\D/g, '');
                if (formatted.length > 4) {
                    formatted = formatted.slice(0, 4) + '/' + formatted.slice(4);
                }
                if (formatted.length > 7) {
                    formatted = formatted.slice(0, 7) + '/' + formatted.slice(7);
                }
                newData[index].date = formatted.slice(0, 10);
            }
        } else {
            newData[index][field] = value;
        }
        setSubjectData(newData);
    };

    const toggleStudentSelection = (id) => {
        setSelectedStudentIds(prev => 
            prev.includes(id) ? prev.filter(sid => sid !== id) : [...prev, id]
        );
    };

    const handleSelectAll = () => setSelectedStudentIds(students.map(s => s.id));
    const handleDeselectAll = () => setSelectedStudentIds([]);

    const handleSaveSchedule = async () => {
        setSaving(true);
        try {
            // Filter out empty rows
            const cleanSubjectData = subjectData.filter(s => s.subject.trim() !== '');
            await examService.saveSchedule({
                class: selectedClass,
                examType,
                year: parseInt(year),
                shift,
                examTime,
                subjectData: cleanSubjectData
            });
            alert('Schedule saved successfully!');
        } catch (error) {
            console.error('Save failed:', error);
        } finally {
            setSaving(false);
        }
    };

    const handleGenerate = () => {
        if (selectedStudentIds.length === 0) {
            alert('Please select at least one student.');
            return;
        }
        // Navigate to the print view (to be implemented)
        navigate(`/exams/admit-cards/print?class=${selectedClass}&exam=${examType}&year=${year}&ids=${selectedStudentIds.join(',')}`);
    };

    if (loading) return (
        <div className="h-screen flex items-center justify-center">
             <div className="w-12 h-12 border-4 border-rose-100 border-t-rose-500 rounded-full animate-spin"></div>
        </div>
    );

    return (
        <div className="max-w-7xl mx-auto space-y-8 pb-32 px-4 pt-10 font-outfit">
             {/* Header Section */}
             <div className="space-y-4">
                <div className="flex items-center justify-between">
                    <div 
                        onClick={() => navigate('/exams/admit-cards')}
                        className="flex items-center gap-2 text-rose-500 font-bold text-sm cursor-pointer hover:opacity-80 transition-all inline-flex"
                    >
                        <ChevronLeft size={20} strokeWidth={3} />
                        <span>Change Selection</span>
                        <span className="mx-2 text-slate-200">/</span>
                        <span className="text-slate-400">Configure Details</span>
                    </div>
                </div>
                
                <div className="flex flex-col md:flex-row md:items-end justify-between gap-6">
                    <div>
                        <h1 className="text-5xl font-black text-slate-900 tracking-tight">Admit Card Setup</h1>
                        <p className="text-slate-500 font-bold mt-2 flex items-center gap-3">
                            <span className="capitalize">{examType?.replace(/_/g, ' ')}</span>
                            <span className="w-1.5 h-1.5 bg-slate-200 rounded-full"></span>
                            <span>Class {selectedClass}</span>
                            <span className="w-1.5 h-1.5 bg-slate-200 rounded-full"></span>
                            <span>{year}</span>
                        </p>
                    </div>
                </div>
             </div>

             <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start pb-20">
                {/* Left Panel: Exam Schedule */}
                <div className="lg:col-span-7 bg-white rounded-[40px] shadow-sm border border-slate-100 overflow-hidden">
                    <div className="p-8 bg-slate-50/50 border-b border-slate-100 flex items-center gap-4">
                        <Calendar className="text-rose-500" size={24} />
                        <h2 className="text-xl font-black text-slate-800 tracking-tight">Exam Schedule & Subjects</h2>
                    </div>
                    
                    <div className="p-8 space-y-8">
                        {/* Global Settings */}
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div className="space-y-2">
                                <label className="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Shift Selection</label>
                                <div className="relative">
                                    <select 
                                        value={shift}
                                        onChange={(e) => setShift(e.target.value)}
                                        className="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl font-bold focus:ring-2 focus:ring-rose-500 text-slate-700 appearance-none cursor-pointer"
                                    >
                                        <option value="DAY">DAY SHIFT</option>
                                        <option value="MORNING">MORNING SHIFT</option>
                                    </select>
                                    <ChevronDown className="absolute right-6 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" size={18} />
                                </div>
                            </div>
                            <div className="space-y-2">
                                <label className="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Exam Time (e.g. 10:00 - 01:00)</label>
                                <div className="relative">
                                    <Clock className="absolute left-6 top-1/2 -translate-y-1/2 text-slate-300" size={18} />
                                    <input 
                                        value={examTime}
                                        onChange={(e) => setExamTime(e.target.value)}
                                        className="w-full pl-14 pr-6 py-4 bg-slate-50 border-none rounded-2xl font-bold focus:ring-2 focus:ring-rose-500 text-slate-700"
                                        placeholder="10:00 - 01:00"
                                    />
                                </div>
                            </div>
                        </div>

                        {/* Subject Rows */}
                        <div className="space-y-4">
                            <div className="grid grid-cols-12 gap-4 px-1">
                                <div className="col-span-12 md:col-span-7 text-[10px] font-black uppercase text-slate-400 tracking-widest">Subject Name</div>
                                <div className="hidden md:block col-span-5 text-[10px] font-black uppercase text-slate-400 tracking-widest">Exam Date</div>
                            </div>
                            
                            <div className="space-y-3">
                                {subjectData.map((row, idx) => (
                                    <div key={idx} className="grid grid-cols-12 gap-3 md:gap-4">
                                        <div className="col-span-12 md:col-span-7">
                                            <input 
                                                value={row.subject}
                                                onChange={(e) => handleSubjectChange(idx, 'subject', e.target.value)}
                                                placeholder="Enter Subject"
                                                className="w-full px-5 py-4 bg-slate-50/50 border border-slate-100 rounded-2xl font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-rose-500 transition-all placeholder:font-normal placeholder:opacity-50"
                                            />
                                        </div>
                                        <div className="col-span-12 md:col-span-5">
                                            <input 
                                                value={row.date}
                                                onChange={(e) => handleSubjectChange(idx, 'date', e.target.value)}
                                                placeholder="2082/MM/DD"
                                                className="w-full px-5 py-4 bg-slate-50/50 border border-slate-100 rounded-2xl font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-rose-500 transition-all text-center"
                                            />
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>

                        <div className="p-5 bg-slate-50 rounded-2xl border border-slate-100">
                            <p className="text-xs font-bold text-slate-400 leading-relaxed">
                                * Enter subjects in the order you want them to appear on the admit card.
                            </p>
                        </div>
                    </div>
                </div>

                {/* Right Panel: Student Selection */}
                <div className="lg:col-span-5 space-y-6">
                    <div className="bg-white rounded-[40px] shadow-sm border border-slate-100 overflow-hidden lg:sticky lg:top-10">
                        <div className="p-8 bg-slate-50/50 border-b border-slate-100 flex items-center gap-4">
                            <Users size={24} className="text-slate-400" />
                            <h2 className="text-xl font-black text-slate-800 tracking-tight">Select Students</h2>
                        </div>
                        
                        <div className="p-8 space-y-6">
                            <div className="flex gap-3">
                                <button 
                                    onClick={handleSelectAll}
                                    className="flex-1 py-4 bg-slate-100 text-slate-600 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-slate-200 transition-colors"
                                >
                                    Select All
                                </button>
                                <button 
                                    onClick={handleDeselectAll}
                                    className="flex-1 py-4 bg-slate-100 text-slate-600 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-slate-200 transition-colors"
                                >
                                    Deselect All
                                </button>
                            </div>

                            <div className="max-h-[500px] overflow-y-auto pr-2 space-y-3 custom-scrollbar">
                                {students.map(student => (
                                    <div 
                                        key={student.id}
                                        onClick={() => toggleStudentSelection(student.id)}
                                        className={`p-5 rounded-[24px] border-2 transition-all cursor-pointer flex items-center gap-5 ${
                                            selectedStudentIds.includes(student.id) 
                                            ? 'bg-rose-50/30 border-rose-500/20 shadow-sm shadow-rose-100/50' 
                                            : 'bg-white border-slate-50 hover:border-slate-100'
                                        }`}
                                    >
                                        <div className={`w-7 h-7 rounded-xl flex items-center justify-center transition-all ${
                                            selectedStudentIds.includes(student.id) ? 'bg-rose-500 text-white' : 'bg-slate-100 text-transparent'
                                        }`}>
                                            <CheckCircle size={18} strokeWidth={3} />
                                        </div>
                                        <div className="flex-1 min-w-0">
                                            <h4 className="font-extrabold text-slate-800 truncate leading-tight uppercase tracking-tight">{student.fullName}</h4>
                                            <p className="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1.5 opacity-70">
                                                Symbol No: <span className="text-slate-600">{student.symbolNo || 'N/A'}</span>
                                            </p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
             </div>

             {/* Sticky Footer */}
             <div className="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-slate-100 py-6 z-[100] shadow-[0_-15px_40px_rgba(0,0,0,0.04)]">
                <div className="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6 px-6">
                    <div className="flex items-center gap-5">
                        <div className="w-14 h-14 bg-rose-50 rounded-[20px] flex items-center justify-center shadow-inner">
                            <Users size={28} className="text-rose-500" />
                        </div>
                        <div className="space-y-0.5">
                           <p className="text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] opacity-80">Configuration Status</p>
                           <p className="font-black text-slate-800 text-lg">
                               Configuring: <span className="text-rose-500">{selectedStudentIds.length} Students</span>
                           </p>
                        </div>
                    </div>
                    
                    <div className="flex items-center gap-4 w-full md:w-auto">
                        <button 
                            onClick={handleSaveSchedule}
                            disabled={saving}
                            className="flex-1 md:flex-none px-10 py-5 bg-rose-500 text-white rounded-[26px] font-black text-lg flex items-center justify-center gap-3 hover:bg-rose-600 hover:-translate-y-1 active:translate-y-0 transition-all shadow-xl shadow-rose-200/50 disabled:opacity-50"
                        >
                            <Save size={22} strokeWidth={2.5} />
                            {saving ? 'Processing...' : 'Save Schedule'}
                        </button>
                        
                        <button 
                            onClick={handleGenerate}
                            className="flex-1 md:flex-none px-10 py-5 bg-emerald-500 text-white rounded-[26px] font-black text-lg flex items-center justify-center gap-3 hover:bg-emerald-600 hover:-translate-y-1 active:translate-y-0 transition-all shadow-xl shadow-emerald-200/50"
                        >
                            <Printer size={22} strokeWidth={2.5} />
                            Generate & Print
                        </button>
                    </div>
                </div>
             </div>
        </div>
    );
};

export default AdmitCardConfig;
