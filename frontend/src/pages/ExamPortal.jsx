import React from 'react';
import { Link } from 'react-router-dom';
import { 
  Pencil, Contact, FileText, Award, 
  Grid3X3, CalendarCheck, Layers, ChevronRight 
} from 'lucide-react';

const ExamPortal = () => {
  const portalItems = [
    {
      title: "Mark Entry",
      description: "Fast and efficient interface for entering student scores across all exams.",
      icon: Pencil,
      color: "bg-indigo-50 text-indigo-500",
      link: "/exams/entry",
      linkText: "Go to Entry"
    },
    {
      title: "Admit Cards",
      description: "Generate bulk admit cards for students with automatic exam scheduling.",
      icon: Contact,
      color: "bg-rose-50 text-rose-500",
      link: "/exams/admit-cards",
      linkText: "Print Cards"
    },
    {
      title: "Subject Slips",
      description: "Extract detailed subject-wise performance slips for internal records.",
      icon: FileText,
      color: "bg-emerald-50 text-emerald-500",
      link: "/exams/subject-slips",
      linkText: "Generate Slips"
    },
    {
      title: "Grade Sheets",
      description: "Prepare official terminal and annual grade sheets in high-resolution print format.",
      icon: Award,
      color: "bg-amber-50 text-amber-500",
      link: "/exams/print",
      linkText: "Prepare Sheets"
    },
    {
      title: "Consolidated Ledger",
      description: "Analyze class-wide results, GPA distributions, and rank calculations in one view.",
      icon: Grid3X3,
      color: "bg-cyan-50 text-cyan-500",
      link: "/exams/results",
      linkText: "View Ledger"
    },
    {
      title: "Attendance Tracking",
      description: "Record student presence specifically during the examination period.",
      icon: CalendarCheck,
      color: "bg-teal-50 text-teal-500",
      link: "/attendance/entry",
      linkText: "Track Now"
    },
    {
      title: "Subjects Config",
      description: "Configure your academic curriculum, assign weights, and manage credit hours.",
      icon: Layers,
      color: "bg-indigo-50 text-indigo-500",
      link: "/curriculum",
      linkText: "Settings"
    }
  ];

  return (
    <div className="max-w-7xl mx-auto space-y-12 py-10 px-4 md:px-0">
      {/* Hero Section */}
      <div className="text-center space-y-6 relative">
        <div className="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-8 w-64 h-64 bg-indigo-500/5 blur-[120px] rounded-full pointer-events-none"></div>
        
        <div className="inline-flex items-center gap-2.5 px-4 py-1.5 bg-white/80 backdrop-blur-md border border-slate-200/60 rounded-full shadow-sm">
          <span className="relative flex h-2 w-2">
            <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
            <span className="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
          </span>
          <span className="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Global Exam Center</span>
        </div>
        
        <h1 className="text-5xl md:text-7xl font-black text-slate-900 tracking-tight font-outfit leading-[1.1]">
           Examination <span className="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-indigo-400">Portal</span>
        </h1>
        
        <p className="text-slate-500 font-bold max-w-2xl mx-auto text-lg md:text-xl leading-relaxed opacity-80">
           Unified environment for mark entry, result generation, and academic performance tracking.
        </p>
      </div>

      {/* Grid Section */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        {portalItems.map((item, index) => (
          <Link 
            key={index}
            to={item.link}
            className="group bg-white p-8 rounded-[40px] border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)] hover:shadow-[0_20px_50px_rgba(79,70,229,0.1)] hover:-translate-y-2 transition-all duration-500 flex flex-col items-center text-center space-y-6 no-underline"
          >
            <div className={`w-20 h-20 ${item.color} rounded-[28px] flex items-center justify-center transition-all duration-500 group-hover:rotate-6 group-hover:scale-110 shadow-sm border border-white/50 backdrop-blur-sm`}>
               <item.icon size={32} strokeWidth={2.5} />
            </div>

            <div className="space-y-3 px-2">
              <h3 className="text-2xl font-black text-slate-900 leading-tight tracking-tight font-outfit">
                {item.title}
              </h3>
              <p className="text-slate-500 text-sm font-bold leading-relaxed opacity-70">
                {item.description}
              </p>
            </div>

            <div className="pt-2">
              <span className="inline-flex items-center gap-2 text-indigo-600 font-black text-sm uppercase tracking-widest group-hover:gap-4 transition-all">
                {item.linkText}
                <ChevronRight size={16} strokeWidth={3} className="text-indigo-400" />
              </span>
            </div>
          </Link>
        ))}
      </div>
    </div>
  );
};

export default ExamPortal;
