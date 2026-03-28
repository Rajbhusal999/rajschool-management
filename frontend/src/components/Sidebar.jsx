import React from 'react';
import { NavLink } from 'react-router-dom';
import { LayoutDashboard, Users, GraduationCap, CalendarCheck, FileBarChart, Settings, BookOpen, ClipboardList, Trophy } from 'lucide-react';


const Sidebar = () => {
  return (
    <div className="w-64 bg-white h-screen border-r border-slate-200 p-4 flex flex-col">
      <div className="flex items-center gap-2 mb-8 px-2">
        <div className="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center text-white font-bold">R</div>
        <span className="text-xl font-bold text-slate-800">RajSchool</span>
      </div>
      
      <nav className="flex-1 space-y-1">
        <NavLink 
          to="/dashboard" 
          className={({ isActive }) => `flex items-center gap-3 px-3 py-2 rounded-lg transition-colors ${isActive ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50'}`}
        >
          <LayoutDashboard size={20} />
          <span className="font-medium">Dashboard</span>
        </NavLink>
        
        <NavLink 
          to="/students" 
          className={({ isActive }) => `flex items-center gap-3 px-3 py-2 rounded-lg transition-colors ${isActive ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50'}`}
        >
          <Users size={20} />
          <span className="font-medium">Students</span>
        </NavLink>
        
        <NavLink 
          to="/teachers" 
          className={({ isActive }) => `flex items-center gap-3 px-3 py-2 rounded-lg transition-colors ${isActive ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50'}`}
        >
          <GraduationCap size={20} />
          <span className="font-medium">Teachers</span>
        </NavLink>

        <div className="pt-4 pb-2 px-3">
          <span className="text-[10px] font-black uppercase tracking-widest text-slate-400">Operations</span>
        </div>

        <NavLink 
          to="/attendance/entry" 
          className={({ isActive }) => `flex items-center gap-3 px-3 py-2 rounded-lg transition-colors ${isActive ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50'}`}
        >
          <CalendarCheck size={20} />
          <span className="font-medium">Daily Attendance</span>
        </NavLink>

        <NavLink 
          to="/attendance/reports" 
          className={({ isActive }) => `flex items-center gap-3 px-3 py-2 rounded-lg transition-colors ${isActive ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50'}`}
        >
          <FileBarChart size={20} />
          <span className="font-medium">Monthly Reports</span>
        </NavLink>

        <div className="pt-4 pb-2 px-3">
          <span className="text-[10px] font-black uppercase tracking-widest text-slate-400">Exams & Grading</span>
        </div>

        <NavLink 
          to="/curriculum" 
          className={({ isActive }) => `flex items-center gap-3 px-3 py-2 rounded-lg transition-colors ${isActive ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50'}`}
        >
          <BookOpen size={20} />
          <span className="font-medium">Curriculum</span>
        </NavLink>

        <NavLink 
          to="/exams/entry" 
          className={({ isActive }) => `flex items-center gap-3 px-3 py-2 rounded-lg transition-colors ${isActive ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50'}`}
        >
          <ClipboardList size={20} />
          <span className="font-medium">Mark Entry</span>
        </NavLink>

        <NavLink 
          to="/exams/results" 
          className={({ isActive }) => `flex items-center gap-3 px-3 py-2 rounded-lg transition-colors ${isActive ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50'}`}
        >
          <Trophy size={20} />
          <span className="font-medium">Result Sheets</span>
        </NavLink>
      </nav>


      <div className="mt-auto">
        <NavLink 
          to="/settings" 
          className={({ isActive }) => `flex items-center gap-3 px-3 py-2 rounded-lg transition-colors ${isActive ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50'}`}
        >
          <Settings size={20} />
          <span className="font-medium">Settings</span>
        </NavLink>
      </div>
    </div>
  );
};

export default Sidebar;
