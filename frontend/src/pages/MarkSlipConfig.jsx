import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { 
  FileText, 
  ArrowRight,
  Sparkles,
  BookOpen,
  CalendarDays,
  Layers
} from 'lucide-react';
import { examService } from '../services/api';

const MarkSlipConfig = () => {
    const navigate = useNavigate();
    const [loading, setLoading] = useState(false);
    const [subjects, setSubjects] = useState([]);
    
    // Form State
    const [formData, setFormData] = useState({
        year: '2082',
        examType: 'First Terminal',
        selectedClass: '',
        selectedSubject: ''
    });

    const classes = ['PG', 'Nursery', 'LKG', 'UKG', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10'];
    const examTypes = ['First Terminal', 'Second Terminal', 'Third Terminal', 'Final Examination', 'Monthly Test'];

    const getClassGroup = (cls) => {
        if (!cls) return '';
        if (['PG', 'Nursery', 'LKG', 'UKG'].includes(cls)) return cls;
        const n = parseInt(cls);
        if (n >= 1 && n <= 3) return '1-3';
        if (n >= 4 && n <= 5) return '4-5';
        if (n >= 6 && n <= 8) return '6-8';
        if (n >= 9 && n <= 10) return '9-10';
        return '1-3';
    };

    useEffect(() => {
        if (formData.selectedClass) {
            fetchSubjects();
        } else {
            setSubjects([]);
            setFormData(prev => ({ ...prev, selectedSubject: '' }));
        }
    }, [formData.selectedClass]);

    const fetchSubjects = async () => {
        setLoading(true);
        try {
            const group = getClassGroup(formData.selectedClass);
            const response = await examService.getSubjects({ 
                schoolId: sessionStorage.getItem('institutionId'), 
                classGroup: group 
            });
            // Ensure we handle subjects correctly filtering by name
            setSubjects(response.data || []);
            setFormData(prev => ({ ...prev, selectedSubject: '' }));
        } catch (error) {
            console.error('Error fetching subjects:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleGenerate = () => {
        if (!formData.selectedClass || !formData.selectedSubject) {
            alert('Please select both Class and Subject');
            return;
        }
        
        const params = new URLSearchParams({
            year: formData.year,
            examType: formData.examType,
            class: formData.selectedClass,
            subject: formData.selectedSubject
        });
        
        // This will be the next page to build or integrated view
        navigate(`/exams/subject-slips/view?${params.toString()}`);
    };

    return (
        <div className="min-h-[80vh] flex items-center justify-center p-4">
            <div className="max-w-2xl w-full bg-white rounded-[48px] shadow-2xl shadow-indigo-500/10 border border-slate-100 overflow-hidden group hover:shadow-indigo-500/15 transition-all duration-700">
                {/* Visual Header Decoration */}
                <div className="h-3 bg-gradient-to-r from-emerald-400 via-indigo-500 to-emerald-400 bg-[length:200%_auto] animate-pulse"></div>
                
                <div className="p-8 md:p-12 space-y-10">
                    {/* Icon & Title */}
                    <div className="text-center space-y-4">
                        <div className="w-20 h-20 bg-emerald-50 rounded-[32px] flex items-center justify-center mx-auto group-hover:scale-110 group-hover:rotate-3 transition-all duration-500 shadow-sm border border-emerald-100">
                            <FileText size={36} className="text-emerald-500" strokeWidth={2.5} />
                        </div>
                        <div className="space-y-2">
                            <h1 className="text-3xl md:text-4xl font-black text-slate-800 tracking-tight leading-tight">
                                Generate <span className="text-emerald-500">Mark Slip</span>
                            </h1>
                            <p className="text-slate-400 font-bold text-sm tracking-wide uppercase opacity-70">
                                Specific subject and class level report generation
                            </p>
                        </div>
                    </div>

                    {/* Configuration Form */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-8 relative">
                        {/* Year */}
                        <div className="space-y-2 group/field">
                            <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1 flex items-center gap-2">
                                <CalendarDays size={12} className="text-slate-300" />
                                Academic Year
                            </label>
                            <input 
                                type="text"
                                value={formData.year}
                                onChange={(e) => setFormData(prev => ({ ...prev, year: e.target.value }))}
                                className="w-full px-6 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl font-black text-slate-700 transition-all focus:bg-white focus:border-indigo-400 focus:shadow-lg focus:shadow-indigo-500/5 outline-none"
                                placeholder="e.g. 2082"
                            />
                        </div>

                        {/* Exam Type */}
                        <div className="space-y-2 group/field">
                            <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1 flex items-center gap-2">
                                <Sparkles size={12} className="text-slate-300" />
                                Exam Type
                            </label>
                            <select 
                                value={formData.examType}
                                onChange={(e) => setFormData(prev => ({ ...prev, examType: e.target.value }))}
                                className="w-full px-6 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl font-black text-slate-700 transition-all focus:bg-white focus:border-indigo-400 focus:shadow-lg focus:shadow-indigo-500/5 outline-none appearance-none cursor-pointer"
                            >
                                {examTypes.map(t => <option key={t} value={t}>{t}</option>)}
                            </select>
                        </div>

                        {/* Class Selection */}
                        <div className="space-y-2 group/field">
                            <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1 flex items-center gap-2">
                                <Layers size={12} className="text-slate-300" />
                                Select Class
                            </label>
                            <select 
                                value={formData.selectedClass}
                                onChange={(e) => setFormData(prev => ({ ...prev, selectedClass: e.target.value }))}
                                className={`w-full px-6 py-4 bg-slate-50 border-2 ${formData.selectedClass ? 'border-emerald-100 bg-emerald-50/20' : 'border-slate-50'} rounded-2xl font-black text-slate-700 transition-all focus:bg-white focus:border-indigo-400 focus:shadow-lg focus:shadow-indigo-500/5 outline-none appearance-none cursor-pointer`}
                            >
                                <option value="">Choose Class</option>
                                {classes.map(c => <option key={c} value={c}>Class {c}</option>)}
                            </select>
                        </div>

                        {/* Subject Selection */}
                        <div className="space-y-2 group/field">
                            <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1 flex items-center gap-2">
                                <BookOpen size={12} className="text-slate-300" />
                                Select Subject
                            </label>
                            <select 
                                value={formData.selectedSubject}
                                onChange={(e) => setFormData(prev => ({ ...prev, selectedSubject: e.target.value }))}
                                disabled={!formData.selectedClass || loading}
                                className={`w-full px-6 py-4 bg-slate-50 border-2 ${formData.selectedSubject ? 'border-emerald-100 bg-emerald-50/20' : 'border-slate-50'} rounded-2xl font-black text-slate-700 transition-all focus:bg-white focus:border-indigo-400 focus:shadow-lg focus:shadow-indigo-500/5 outline-none appearance-none cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed`}
                            >
                                <option value="">{loading ? 'Fetching Subjects...' : !formData.selectedClass ? 'Wait for Class...' : 'Choose Subject'}</option>
                                {subjects.map(s => <option key={s.id} value={s.subjectName}>{s.subjectName}</option>)}
                            </select>
                        </div>
                    </div>

                    {/* Action Button */}
                    <button 
                        onClick={handleGenerate}
                        disabled={!formData.selectedClass || !formData.selectedSubject || loading}
                        className="w-full py-6 bg-emerald-500 hover:bg-emerald-600 disabled:bg-slate-200 text-white rounded-[28px] font-black uppercase tracking-widest transition-all duration-300 shadow-2xl shadow-emerald-200 disabled:shadow-none translate-y-0 hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-3 group/btn"
                    >
                        <Sparkles size={20} className="group-hover/btn:rotate-12 transition-transform" strokeWidth={2.5} />
                        Generate Report
                        <ArrowRight size={20} className="group-hover/btn:translate-x-2 transition-transform" strokeWidth={2.5} />
                    </button>
                </div>
            </div>
        </div>
    );
};

export default MarkSlipConfig;
