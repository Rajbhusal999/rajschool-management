import React, { useState } from 'react';
import { NavLink, useNavigate } from 'react-router-dom';
import { 
  LayoutDashboard, Users, GraduationCap, ClipboardList, 
  CreditCard, CalendarCheck, FileBarChart, CreditCard as IDCard,
  Moon, Sun, LogOut, Menu, X, Globe
} from 'lucide-react';

const TopNav = () => {
  const navigate = useNavigate();
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const schoolName = sessionStorage.getItem('schoolName') || 'Raj School';
  const schoolAddress = sessionStorage.getItem('schoolAddress') || 'Institution Campus';
  const estdYear = sessionStorage.getItem('estdYear') || '2050';

  const menuItems = [
    { name: 'Dashboard', path: '/dashboard', icon: LayoutDashboard },
    { name: 'Students', path: '/students', icon: Users },
    { name: 'Teachers', path: '/teachers', icon: GraduationCap },
    { name: 'Exams', path: '/exams/entry', icon: ClipboardList },
    { name: 'Billing', path: '/billing', icon: CreditCard },
    { name: 'Attendance', path: '/attendance/entry', icon: CalendarCheck },
    { name: 'Reports', path: '/attendance/reports', icon: FileBarChart },
    { name: 'ID Cards', path: '/id-cards', icon: IDCard },
  ];

  const logout = () => {
    sessionStorage.clear();
    navigate('/login');
  };

  return (
    <nav className="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-sm px-4 py-2">
      <div className="max-w-[1600px] mx-auto flex items-center justify-between gap-4">
        
        {/* School Branding */}
        <div className="flex items-center gap-3 min-w-fit">
          <div className="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-200 overflow-hidden">
             <img src="/logo.png" alt="R" className="w-full h-full object-cover" onError={(e) => e.target.style.display='none'} />
             <span className="font-black text-xl">R</span>
          </div>
          <div className="hidden sm:block">
            <h1 className="text-lg font-black text-slate-800 leading-tight">{schoolName}</h1>
            <p className="text-[10px] font-bold text-slate-500 uppercase tracking-tighter">
              {schoolAddress} | Estd: {estdYear}
            </p>
          </div>
        </div>

        {/* Desktop Menu */}
        <div className="hidden lg:flex items-center bg-slate-50/80 p-1.5 rounded-2xl border border-slate-100 shadow-inner overflow-x-auto no-scrollbar">
          {menuItems.map((item) => (
            <NavLink
              key={item.name}
              to={item.path}
              className={({ isActive }) => `
                flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-black transition-all whitespace-nowrap
                ${isActive 
                  ? 'bg-white text-indigo-600 shadow-md ring-1 ring-slate-100' 
                  : 'text-slate-600 hover:text-indigo-600 hover:bg-white/50'}
              `}
            >
              <item.icon size={18} strokeWidth={2.5} />
              {item.name}
            </NavLink>
          ))}
        </div>

        {/* Utilities */}
        <div className="flex items-center gap-2">
          <button className="p-2.5 text-slate-500 hover:bg-slate-100 rounded-xl transition-colors border border-slate-200 shadow-sm hidden sm:flex">
            <Moon size={18} strokeWidth={2.5} />
          </button>
          
          <button className="flex items-center gap-1.5 px-3 py-2 bg-slate-100 border border-slate-200 rounded-xl text-slate-600 text-[11px] font-black uppercase tracking-widest hover:bg-slate-200 transition-colors hidden sm:flex">
            <Globe size={14} />
            NE
          </button>

          <button 
            onClick={logout}
            className="flex items-center gap-2 px-4 py-2.5 text-rose-500 hover:bg-rose-50 rounded-xl font-black text-sm transition-all group border border-transparent hover:border-rose-100"
          >
            <LogOut size={18} strokeWidth={2.5} className="group-hover:translate-x-0.5 transition-transform" />
            <span className="hidden md:inline">Log Out</span>
          </button>

          {/* Mobile Menu Toggle */}
          <button 
            onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
            className="lg:hidden p-2.5 text-slate-600 bg-slate-50 border border-slate-200 rounded-xl"
          >
            {isMobileMenuOpen ? <X size={24} /> : <Menu size={24} />}
          </button>
        </div>
      </div>

      {/* Mobile Menu Overlay */}
      {isMobileMenuOpen && (
        <div className="lg:hidden absolute top-full left-0 right-0 bg-white border-b border-slate-200 p-4 shadow-xl animate-in slide-in-from-top-4 duration-200 z-40">
          <div className="grid grid-cols-2 gap-3 pb-4">
            {menuItems.map((item) => (
              <NavLink
                key={item.name}
                to={item.path}
                onClick={() => setIsMobileMenuOpen(false)}
                className={({ isActive }) => `
                  flex items-center gap-3 p-4 rounded-xl text-sm font-black transition-all
                  ${isActive ? 'bg-indigo-50 text-indigo-600 shadow-sm' : 'bg-slate-50 text-slate-600'}
                `}
              >
                <item.icon size={20} />
                {item.name}
              </NavLink>
            ))}
          </div>
          <div className="flex gap-3 pt-4 border-t border-slate-100">
             <button className="flex-1 flex items-center justify-center gap-2 p-4 bg-slate-50 rounded-xl text-sm font-black text-slate-600 uppercase tracking-widest">
               <Globe size={18} /> NE
             </button>
             <button className="flex-1 flex items-center justify-center gap-2 p-4 bg-slate-50 rounded-xl text-sm font-black text-slate-600 uppercase tracking-widest">
               <Moon size={18} /> Dark
             </button>
          </div>
        </div>
      )}
    </nav>
  );
};

export default TopNav;
