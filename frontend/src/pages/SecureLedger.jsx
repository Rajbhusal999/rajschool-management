import React from 'react';
import { useNavigate } from 'react-router-dom';
import { 
  BarChart, Calendar, Trophy, ChevronRight, 
  Shield, Activity, Download, ArrowLeft 
} from 'lucide-react';
import SecureGateway from '../components/SecureGateway';

const SecureLedger = () => {
  const navigate = useNavigate();

  const ledgerItems = [
    {
      title: "Attendance Matrix",
      description: "Detailed daily attendance tracking for students and staff with percentage analytics.",
      icon: Calendar,
      color: "bg-indigo-50 text-indigo-600",
      link: "/attendance/reports",
      tag: "Active Monitoring"
    },
    {
      title: "Academic Results",
      description: "Consolidated terminal and annual mark ledgers with automatic GPA calculation.",
      icon: Trophy,
      color: "bg-rose-50 text-rose-600",
      link: "/exams/results",
      tag: "Rankings & Stats"
    },
    {
      title: "Financial Reports",
      description: "Overview of institutional billing, fee collections, and donor contributions.",
      icon: BarChart,
      color: "bg-emerald-50 text-emerald-600",
      link: "/billing/history",
      tag: "Secure Audit"
    }
  ];

  return (
    <SecureGateway>
      <div className="max-w-7xl mx-auto space-y-12 py-10">
        {/* Header Section */}
        <div className="text-center space-y-6 relative">
          <div className="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-10 w-80 h-80 bg-indigo-500/5 blur-[120px] rounded-full pointer-events-none"></div>
          
          <div className="inline-flex items-center gap-2.5 px-4 py-1.5 bg-white/80 backdrop-blur-md border border-slate-200/60 rounded-full shadow-sm">
            <Shield size={14} className="text-indigo-600" />
            <span className="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Institutional Vault • Verified Access</span>
          </div>
          
          <h1 className="text-5xl md:text-7xl font-[1000] text-slate-900 tracking-tighter leading-tight">
            Secure <span className="text-indigo-600">Ledger</span>
          </h1>
          
          <p className="text-slate-500 font-bold max-w-2xl mx-auto text-lg md:text-xl leading-relaxed opacity-80 uppercase tracking-wide text-xs">
            Centralized terminal for high-level institutional analytics and sensitive data matrices.
          </p>
        </div>

        {/* Action Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 px-4">
          {ledgerItems.map((item, index) => (
            <div 
              key={index}
              onClick={() => navigate(item.link)}
              className="group cursor-pointer bg-white p-10 rounded-[48px] border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)] hover:shadow-[0_20px_60px_rgba(79,70,229,0.08)] hover:-translate-y-2 transition-all duration-500 flex flex-col items-center text-center space-y-6 relative overflow-hidden"
            >
              <div className={`w-24 h-24 ${item.color} rounded-[32px] flex items-center justify-center transition-all duration-500 group-hover:rotate-6 group-hover:scale-110 shadow-sm border border-white/50 backdrop-blur-sm`}>
                <item.icon size={40} strokeWidth={2} />
              </div>

              <div className="space-y-3 px-2">
                <div className="px-3 py-1 bg-slate-50 rounded-full text-[9px] font-black tracking-widest text-slate-400 uppercase inline-block mb-2 group-hover:bg-white group-hover:text-indigo-600 transition-colors">
                  {item.tag}
                </div>
                <h3 className="text-3xl font-[1000] text-slate-900 leading-tight tracking-tighter">
                  {item.title}
                </h3>
                <p className="text-slate-500 text-sm font-bold leading-relaxed opacity-60">
                  {item.description}
                </p>
              </div>

              <div className="pt-2">
                <span className="inline-flex items-center gap-2 text-indigo-600 font-black text-xs uppercase tracking-[0.2em] group-hover:gap-4 transition-all">
                  Open Subsystem
                  <ChevronRight size={16} strokeWidth={3} className="text-indigo-300" />
                </span>
              </div>
            </div>
          ))}
        </div>

        {/* Security Footer */}
        <div className="max-w-2xl mx-auto p-8 rounded-[40px] bg-slate-900 text-white flex items-center justify-between gap-8 group hover:bg-black transition-colors shadow-2xl shadow-slate-200">
           <div className="flex items-center gap-6">
              <div className="w-14 h-14 bg-white/10 rounded-2xl flex items-center justify-center text-indigo-400">
                <Activity size={28} />
              </div>
              <div className="space-y-1">
                <h4 className="font-black uppercase tracking-widest text-xs">Security Pulse</h4>
                <p className="text-xs font-bold text-slate-400">All access attempts are logged to the institutional root.</p>
              </div>
           </div>
           <button onClick={() => navigate('/settings/sms')} className="p-4 bg-white/5 rounded-2xl hover:bg-white/10 transition-colors text-indigo-400">
              <Shield size={24} />
           </button>
        </div>
      </div>
    </SecureGateway>
  );
};

export default SecureLedger;
