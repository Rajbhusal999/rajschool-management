import React, { useState } from 'react';
import { NavLink, useNavigate } from 'react-router-dom';
import { 
  LayoutDashboard, Users, GraduationCap, ClipboardList, 
  CreditCard, CalendarCheck, FileBarChart, CreditCard as IDCard,
  Moon, Sun, LogOut, Menu, X, Globe
} from 'lucide-react';
import { useTheme, useLanguage } from '../context/AppContext';

const TopNav = () => {
  const navigate = useNavigate();
  const { isDarkMode, toggleTheme } = useTheme();
  const { lang, toggleLang, t } = useLanguage();
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const schoolName = sessionStorage.getItem('schoolName') || 'Raj School';
  const schoolAddress = sessionStorage.getItem('schoolAddress') || 'Institution Campus';
  const estdYear = sessionStorage.getItem('estdYear') || '2050';
  const schoolLogo = sessionStorage.getItem('schoolLogo');

  const menuItems = [
    { name: t('dashboard'), path: '/dashboard', icon: LayoutDashboard },
    { name: t('students'), path: '/students', icon: Users },
    { name: t('teachers'), path: '/teachers', icon: GraduationCap },
    { name: t('exams'), path: '/exams', icon: ClipboardList },
    { name: t('billing'), path: '/billing', icon: CreditCard },
    { name: t('attendance'), path: '/attendance/entry', icon: CalendarCheck },
    { name: t('reports'), path: '/reports', icon: FileBarChart },
    { name: t('idCards'), path: '/id-cards', icon: IDCard },
  ];

  const logout = () => {
    sessionStorage.clear();
    navigate('/login');
  };

  return (
    <nav id="top-nav-bar" className="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 sticky top-0 z-50 shadow-sm px-4 py-2 print:hidden no-print transition-colors duration-300">
      <div className="max-w-[1600px] mx-auto flex items-center justify-between gap-4">
        
        {/* School Branding */}
        <div className="flex items-center gap-3 min-w-fit">
          <div className="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-200 overflow-hidden">
             {schoolLogo ? (
                <img src={schoolLogo} alt={schoolName} className="w-full h-full object-cover" />
             ) : (
                <span className="font-black text-xl">{schoolName.charAt(0)}</span>
             )}
          </div>
          <div className="hidden sm:block">
            <h1 className="text-lg font-black text-slate-800 dark:text-slate-100 leading-tight">{schoolName}</h1>
            <p className="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-tighter">
              {schoolAddress} | Estd: {estdYear}
            </p>
          </div>
        </div>

        {/* Desktop Menu */}
        <div className="hidden lg:flex items-center bg-slate-50/80 dark:bg-slate-800/50 p-1.5 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-inner overflow-x-auto no-scrollbar">
          {menuItems.map((item) => (
            <NavLink
              key={item.name}
              to={item.path}
              className={({ isActive }) => `
                flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-black transition-all whitespace-nowrap
                ${isActive 
                  ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-md ring-1 ring-slate-100 dark:ring-slate-600' 
                  : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-white/50 dark:hover:bg-slate-700/50'}
              `}
            >
              <item.icon size={18} strokeWidth={2.5} />
              {item.name}
            </NavLink>
          ))}
        </div>

        {/* Utilities */}
        <div className="flex items-center gap-2">
          <button 
            onClick={toggleTheme}
            className="p-2.5 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all active:scale-95 border border-slate-200 dark:border-slate-700 shadow-sm hidden sm:flex"
            title={isDarkMode ? t('lightMode') : t('darkMode')}
          >
            {isDarkMode ? <Sun size={20} className="text-amber-400 fill-amber-400/10" strokeWidth={2} /> : <Moon size={20} className="text-slate-400" strokeWidth={2} />}
          </button>
          
          <button 
            onClick={toggleLang}
            className="flex items-center gap-1.5 px-3 py-2 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-300 text-[11px] font-black uppercase tracking-widest hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors hidden sm:flex"
          >
            <Globe size={14} />
            {lang === 'en' ? 'EN' : 'NE'}
          </button>

          <button 
            onClick={logout}
            className="flex items-center gap-2 px-4 py-2.5 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-xl font-black text-sm transition-all group border border-transparent hover:border-rose-100 dark:hover:border-rose-900/30"
          >
            <LogOut size={18} strokeWidth={2.5} className="group-hover:translate-x-0.5 transition-transform" />
            <span className="hidden md:inline">{t('logout')}</span>
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
        <div className="lg:hidden absolute top-full left-0 right-0 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 p-4 shadow-xl animate-in slide-in-from-top-4 duration-200 z-40">
          <div className="grid grid-cols-2 gap-3 pb-4">
            {menuItems.map((item) => (
              <NavLink
                key={item.name}
                to={item.path}
                onClick={() => setIsMobileMenuOpen(false)}
                className={({ isActive }) => `
                  flex items-center gap-3 p-4 rounded-xl text-sm font-black transition-all
                  ${isActive ? 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600' : 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400'}
                `}
              >
                <item.icon size={20} />
                {item.name}
              </NavLink>
            ))}
          </div>
          <div className="flex gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
             <button 
               onClick={toggleLang}
               className="flex-1 flex items-center justify-center gap-2 p-4 bg-slate-50 dark:bg-slate-800 rounded-xl text-sm font-black text-slate-600 dark:text-slate-300 uppercase tracking-widest"
             >
               <Globe size={18} /> {lang === 'en' ? 'English' : 'Nepali'}
             </button>
             <button 
               onClick={toggleTheme}
               className="flex-1 flex items-center justify-center gap-2 p-4 bg-slate-50 dark:bg-slate-800 rounded-xl text-sm font-black text-slate-600 dark:text-slate-300"
             >
               {isDarkMode ? (
                 <><Sun size={18} className="text-amber-400" /> {t('lightMode')}</>
               ) : (
                 <><Moon size={18} className="text-slate-400" /> {t('darkMode')}</>
               )}
             </button>
          </div>
        </div>
      )}
    </nav>
  );
};

export default TopNav;
