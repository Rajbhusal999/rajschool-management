import React from 'react';
import { useNavigate } from 'react-router-dom';
import { 
  GraduationCap, 
  HeartHandshake, 
  Settings, 
  ArrowRight,
  ShieldCheck,
  CreditCard,
  Zap,
  History
} from 'lucide-react';

const Billing = () => {
  const navigate = useNavigate();

  const billingModules = [
    {
      id: 'student-fees',
      title: 'Student Fees',
      description: 'Collect term fees, issue digital invoices, and track outstanding payments for all students.',
      icon: GraduationCap,
      iconBg: 'bg-indigo-50 text-indigo-600',
      buttonText: 'Select Module',
      buttonClass: 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-indigo-200',
      path: '/billing/student-fees'
    },
    {
      id: 'donors-grants',
      title: 'Donors & Grants',
      description: 'Record external funding, government grants, and miscellaneous school income.',
      icon: HeartHandshake,
      iconBg: 'bg-emerald-50 text-emerald-600',
      buttonText: 'Select Module',
      buttonClass: 'bg-emerald-500 text-white hover:bg-emerald-600 shadow-emerald-200',
      path: '/billing/donor-receipts'
    },
    {
      id: 'billing-history',
      title: 'Fee History',
      description: 'Access complete transaction logs for all student fee receipts.',
      icon: History,
      iconBg: 'bg-indigo-50 text-indigo-600',
      buttonText: 'View History',
      buttonClass: 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-indigo-200',
      path: '/billing/history'
    },
    {
      id: 'donor-history',
      title: 'Donor History',
      description: 'Access complete transaction logs for all donor and grant receipts.',
      icon: History,
      iconBg: 'bg-rose-50 text-rose-600',
      buttonText: 'View History',
      buttonClass: 'bg-rose-500 text-white hover:bg-rose-600 shadow-rose-200',
      path: '/billing/donor-history'
    }
  ];

  return (
    <div className="min-h-screen relative overflow-hidden bg-slate-50/50 py-12 px-4 font-['Outfit',sans-serif]">
      {/* Background Watermark Effect */}
      <div className="absolute inset-0 z-0 pointer-events-none overflow-hidden select-none opacity-[0.03]">
        <div className="absolute top-20 left-1/2 -translate-x-1/2 text-[15rem] font-black whitespace-nowrap uppercase tracking-tighter">
          TEACHER ID CARD
        </div>
        <div className="absolute top-[40rem] left-1/2 -translate-x-1/2 text-[10rem] font-black whitespace-nowrap uppercase tracking-tighter">
          Bharatpur Metropolitan City
        </div>
      </div>

      <div className="max-w-6xl mx-auto relative z-10 space-y-16">
        
        {/* Header Section */}
        <div className="text-center space-y-6">
          <div className="inline-flex items-center gap-2 px-4 py-1.5 bg-emerald-50 border border-emerald-100 rounded-full">
            <span className="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span className="text-[10px] font-black uppercase tracking-widest text-emerald-600">Secure Financial Management</span>
          </div>
          
          <h1 className="text-6xl font-[1000] text-slate-900 tracking-tighter">
            Billing & Accounts
          </h1>
          
          <p className="max-w-2xl mx-auto text-slate-500 font-bold text-lg leading-relaxed">
            Select a specialized module to manage student fees, external donations, or school-wide subscriptions.
          </p>
        </div>

        {/* Cards Grid */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          {billingModules.map((module) => (
            <div 
              key={module.id}
              className="bg-white p-10 rounded-[48px] border border-slate-100 shadow-xl shadow-slate-200/50 flex flex-col items-center text-center space-y-8 transition-all hover:translate-y-[-8px] group"
            >
              <div className={`w-24 h-24 rounded-3xl flex items-center justify-center ${module.iconBg} transition-transform group-hover:scale-110 duration-500`}>
                <module.icon size={44} strokeWidth={2.5} />
              </div>

              <div className="space-y-4 flex-1">
                <h3 className="text-3xl font-[1000] text-slate-800 tracking-tight">
                  {module.title}
                </h3>
                <p className="text-slate-500 font-bold leading-relaxed px-2 text-sm">
                  {module.description}
                </p>
              </div>

              <button 
                onClick={() => navigate(module.path)}
                className={`w-full py-5 rounded-3xl font-black uppercase tracking-[0.15em] text-sm transition-all flex items-center justify-center gap-3 shadow-lg ${module.buttonClass}`}
              >
                {module.buttonText}
                <ArrowRight size={18} className="transition-transform group-hover:translate-x-1" />
              </button>
            </div>
          ))}
        </div>

        {/* Bottom Feature Footer (Optional, like in payment page) */}
        <div className="flex flex-col md:flex-row items-center justify-center gap-12 pt-12">
            <div className="flex items-center gap-4 text-slate-400 font-bold uppercase text-[10px] tracking-widest">
                <ShieldCheck size={24} className="text-indigo-500" />
                <span>Encrypted Data Vault</span>
            </div>
            <div className="flex items-center gap-4 text-slate-400 font-bold uppercase text-[10px] tracking-widest">
                <Settings size={24} className="text-emerald-500" />
                <span>Modular Control Center</span>
            </div>
        </div>
      </div>
    </div>
  );
};

export default Billing;
