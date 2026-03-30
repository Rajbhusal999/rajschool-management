import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Contact, ChevronRight, LayoutGrid, CalendarDays, ScrollText } from 'lucide-react';
import BackButton from '../components/BackButton';

const AdmitCard = () => {
    const navigate = useNavigate();
    const [year, setYear] = useState('2082');
    const [selectedClass, setSelectedClass] = useState('');
    const [examType, setExamType] = useState('first_terminal');

    const classes = ['PG', 'NURSERY', 'LKG', 'UKG', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'];
    const examTypes = [
        { id: 'first_terminal', name: 'First Terminal Examination' },
        { id: 'second_terminal', name: 'Second Terminal Examination' },
        { id: 'third_terminal', name: 'Third Terminal Examination' },
        { id: 'final', name: 'Final Examination' }
    ];

    const handleNext = () => {
        if (!selectedClass) {
            alert('Please select a class first.');
            return;
        }
        // Navigate to the configuration page (to be implemented)
        navigate(`/exams/admit-cards/configure?class=${selectedClass}&exam=${examType}&year=${year}`);
    };

    return (
        <div className="max-w-4xl mx-auto space-y-8 pb-20 px-4 pt-10">
            {/* Breadcrumb / Top Bar */}
            <div className="flex items-center justify-between">
                <div className="flex items-center gap-3 text-slate-400 font-bold text-sm">
                    <div className="flex items-center gap-2 text-rose-500">
                        <LayoutGrid size={18} />
                        <span>Dashboard</span>
                    </div>
                    <ChevronRight size={14} />
                    <span className="text-slate-500">Assessment Nexus</span>
                </div>
                <BackButton />
            </div>

            {/* Main Gateway Card */}
            <div className="bg-white rounded-[48px] shadow-[0_20px_50px_rgba(0,0,0,0.04)] border border-slate-100 overflow-hidden relative group">
                {/* Decorative Elements */}
                <div className="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-rose-500 to-rose-400"></div>
                <div className="absolute -top-24 -right-24 w-64 h-64 bg-rose-50 rounded-full blur-3xl opacity-50 group-hover:opacity-80 transition-opacity"></div>
                
                <div className="p-12 md:p-16 flex flex-col items-center text-center space-y-10 relative">
                    {/* Icon Header */}
                    <div className="w-24 h-24 bg-rose-50 rounded-[32px] flex items-center justify-center shadow-inner relative">
                        <div className="absolute inset-0 bg-rose-500/5 blur-xl rounded-full"></div>
                        <Contact size={44} className="text-rose-500 relative z-10" />
                    </div>

                    {/* Titles */}
                    <div className="space-y-4">
                        <h1 className="text-5xl font-black text-slate-900 tracking-tight font-outfit">
                            Admit Card Gateway
                        </h1>
                        <p className="text-slate-500 font-bold max-w-md mx-auto leading-relaxed">
                            Configure and generate official examination entrance credentials for your students.
                        </p>
                    </div>

                    {/* Form Section */}
                    <div className="w-full max-w-2xl grid grid-cols-1 md:grid-cols-2 gap-8 text-left pt-6">
                        {/* Year Field */}
                        <div className="space-y-3">
                            <label className="text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] ml-1">
                                Academic Year
                            </label>
                            <div className="relative group">
                                <div className="absolute left-6 top-1/2 -translate-y-1/2 text-slate-300">
                                    <CalendarDays size={20} />
                                </div>
                                <input 
                                    type="text" 
                                    value={year}
                                    onChange={(e) => setYear(e.target.value)}
                                    className="w-full pl-14 pr-6 py-5 bg-slate-50 border-none rounded-3xl font-black text-slate-700 focus:ring-4 focus:ring-rose-500/10 transition-all text-lg"
                                    placeholder="e.g. 2082"
                                />
                            </div>
                        </div>

                        {/* Class Field */}
                        <div className="space-y-3">
                            <label className="text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] ml-1">
                                Target Class
                            </label>
                            <div className="relative group">
                                <div className="absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none">
                                    <LayoutGrid size={20} />
                                </div>
                                <select 
                                    value={selectedClass}
                                    onChange={(e) => setSelectedClass(e.target.value)}
                                    className="w-full pl-14 pr-6 py-5 bg-slate-50 border-none rounded-3xl font-black text-slate-700 focus:ring-4 focus:ring-rose-500/10 transition-all text-lg appearance-none cursor-pointer"
                                >
                                    <option value="" disabled>Select Class</option>
                                    {classes.map(c => <option key={c} value={c}>Class {c}</option>)}
                                </select>
                                <div className="absolute right-6 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                    <ChevronRight size={18} className="rotate-90" />
                                </div>
                            </div>
                        </div>

                        {/* Exam Type Field */}
                        <div className="md:col-span-2 space-y-3">
                            <label className="text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] ml-1">
                                Examination Type
                            </label>
                            <div className="relative group">
                                <div className="absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none">
                                    <ScrollText size={20} />
                                </div>
                                <select 
                                    value={examType}
                                    onChange={(e) => setExamType(e.target.value)}
                                    className="w-full pl-14 pr-6 py-5 bg-slate-50 border-none rounded-3xl font-black text-slate-700 focus:ring-4 focus:ring-rose-500/10 transition-all text-lg appearance-none cursor-pointer"
                                >
                                    {examTypes.map(t => <option key={t.id} value={t.id}>{t.name}</option>)}
                                </select>
                                <div className="absolute right-6 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                    <ChevronRight size={18} className="rotate-90" />
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Action Button */}
                    <div className="pt-10 w-full max-w-2xl">
                        <button 
                            onClick={handleNext}
                            className="w-full py-6 bg-rose-600 text-white rounded-[28px] font-black text-xl flex items-center justify-center gap-4 hover:bg-rose-700 hover:scale-[1.02] active:scale-[0.98] transition-all shadow-[0_20px_40px_rgba(225,29,72,0.3)] shadow-rose-200/50"
                        >
                            Next: Configure Dates & Subjects
                            <ChevronRight size={24} strokeWidth={3} />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default AdmitCard;
