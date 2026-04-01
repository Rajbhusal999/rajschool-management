import React from 'react';
import { useNavigate } from 'react-router-dom';
import { 
  BarChart3, Users, GraduationCap, Calendar, 
  ChevronRight, ArrowRight, Download, FileText 
} from 'lucide-react';

const Reports = () => {
  const navigate = useNavigate();

  const reportCategories = [
    {
      title: "Attendance Analytics",
      description: "Analyze daily, monthly, and annual attendance trends for the entire student body.",
      icon: Calendar,
      color: "bg-indigo-50 text-indigo-600",
      link: "/reports/attendance",
      metrics: "Daily Tracking",
      features: ["Monthly Ledgers", "Percentage Stats", "Section Summaries"]
    },
    {
      title: "Student Repository",
      description: "Generate comprehensive student profiles, alphabetical lists, and demographic reports.",
      icon: Users,
      color: "bg-emerald-50 text-emerald-600",
      link: "/reports/students",
      metrics: "Academic Database",
      features: ["Class-wise Lists", "Contact Directories", "Exportable Data"]
    },
    {
      title: "Faculty Directory",
      description: "Oversee teaching staff roles, departmental distributions, and professional summaries.",
      icon: GraduationCap,
      color: "bg-rose-50 text-rose-600",
      link: "/reports/teachers",
      metrics: "Staff Inventory",
      features: ["Departmental Audit", "Role Distribution", "Credentials Log"]
    }
  ];

  return (
    <div className="max-w-7xl mx-auto space-y-16 py-12 animate-in fade-in slide-in-from-bottom-4 duration-1000">
      
      {/* Header Section */}
      <div className="relative text-center space-y-4 max-w-3xl mx-auto">
        <div className="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-12 w-96 h-96 bg-indigo-500/5 blur-[120px] rounded-full pointer-events-none"></div>
        
        <div className="inline-flex items-center gap-2 px-4 py-1.5 bg-white border border-slate-200 rounded-full shadow-sm">
          <BarChart3 size={14} className="text-indigo-600" />
          <span className="text-[10px] font-black text-slate-500 uppercase tracking-widest">Institutional Intelligence</span>
        </div>
        
        <h1 className="text-5xl md:text-7xl font-[1000] text-slate-900 tracking-tighter leading-tight">
          Reports <span className="text-indigo-600">Terminal</span>
        </h1>
        
        <p className="text-slate-500 font-bold text-sm md:text-base leading-relaxed opacity-60 uppercase tracking-widest">
          High-performance analytics hub for tracking school-wide operational excellence.
        </p>
      </div>

      {/* Reports Grid */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 px-4">
        {reportCategories.map((report, idx) => (
          <div 
            key={idx}
            onClick={() => navigate(report.link)}
            className="group relative cursor-pointer bg-white p-10 rounded-[48px] border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)] hover:shadow-[0_40px_80px_rgba(79,70,229,0.12)] hover:-translate-y-3 transition-all duration-700 overflow-hidden flex flex-col"
          >
            {/* Background Accent */}
            <div className={`absolute -right-12 -bottom-12 w-48 h-48 opacity-[0.03] group-hover:opacity-[0.08] transition-opacity duration-700`}>
                <report.icon size={192} strokeWidth={1} />
            </div>

            <div className="flex items-start justify-between mb-10">
              <div className={`w-20 h-20 ${report.color} rounded-[28px] flex items-center justify-center shadow-sm border border-white/50 backdrop-blur-sm group-hover:scale-110 transition-transform duration-700`}>
                <report.icon size={36} strokeWidth={2.5} />
              </div>
              <div className="p-3 bg-slate-50 text-slate-400 rounded-2xl group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-500">
                <ChevronRight size={20} strokeWidth={3} />
              </div>
            </div>

            <div className="space-y-4 flex-1">
              <div className="flex items-center gap-3">
                 <span className="px-3 py-1 bg-slate-100 text-slate-500 rounded-full text-[9px] font-black uppercase tracking-widest group-hover:bg-white group-hover:text-indigo-600 transition-colors duration-500">
                   {report.metrics}
                 </span>
              </div>
              
              <h3 className="text-3xl font-[1000] text-slate-900 leading-tight tracking-tighter">
                {report.title}
              </h3>
              
              <p className="text-slate-500 text-sm font-bold leading-relaxed opacity-60">
                {report.description}
              </p>

              {/* Feature Tags */}
              <div className="pt-6 flex flex-wrap gap-2">
                {report.features.map((f, i) => (
                  <span key={i} className="text-[10px] font-bold text-slate-400 flex items-center gap-1.5 bg-slate-50 px-2 py-1 rounded-lg">
                    <div className="w-1 h-1 bg-slate-300 rounded-full" />
                    {f}
                  </span>
                ))}
              </div>
            </div>

            <div className="mt-12 pt-8 border-t border-slate-50 flex items-center justify-between">
              <span className="text-indigo-600 font-extrabold text-xs uppercase tracking-widest group-hover:translate-x-2 transition-transform duration-500 flex items-center gap-2">
                Launch Dashboard <ArrowRight size={14} />
              </span>
              <FileText size={18} className="text-slate-200 group-hover:text-indigo-200 transition-colors" />
            </div>
          </div>
        ))}
      </div>


    </div>
  );
};

export default Reports;
