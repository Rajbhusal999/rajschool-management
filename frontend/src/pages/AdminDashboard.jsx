import React from 'react';
import { 
    Users, GraduationCap, CalendarCheck, CreditCard, 
    TrendingUp, ArrowUpRight, ArrowDownRight, 
    Plus, ClipboardList, Trophy, Settings,
    Bell, Search, User
} from 'lucide-react';
import { Link } from 'react-router-dom';

const StatCard = ({ title, value, change, trend, icon: Icon, color }) => (
    <div className="bg-white p-8 rounded-[40px] shadow-sm border border-slate-100 hover:shadow-xl hover:shadow-slate-200/50 transition-all duration-500 group">
        <div className="flex justify-between items-start mb-6">
            <div className={`w-14 h-14 rounded-2xl flex items-center justify-center transition-transform group-hover:scale-110 group-hover:rotate-3
                ${color === 'indigo' ? 'bg-indigo-50 text-indigo-600' : ''}
                ${color === 'emerald' ? 'bg-emerald-50 text-emerald-600' : ''}
                ${color === 'orange' ? 'bg-orange-50 text-orange-600' : ''}
                ${color === 'pink' ? 'bg-pink-50 text-pink-600' : ''}
            `}>
                <Icon size={28} />
            </div>
            <div className={`flex items-center gap-1 px-3 py-1 rounded-full text-xs font-black
                ${trend === 'up' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'}
            `}>
                {trend === 'up' ? <ArrowUpRight size={14} /> : <ArrowDownRight size={14} />}
                {change}
            </div>
        </div>
        <div>
            <p className="text-sm font-bold text-slate-400 uppercase tracking-widest mb-1">{title}</p>
            <h3 className="text-4xl font-black text-slate-900 tracking-tight">{value}</h3>
        </div>
    </div>
);

const ActionCard = ({ title, desc, icon: Icon, to, color }) => (
    <Link to={to} className="bg-white p-8 rounded-[40px] border border-slate-100 hover:border-indigo-200 hover:shadow-2xl hover:shadow-indigo-100 transition-all duration-500 flex flex-col items-center text-center group">
        <div className={`w-16 h-16 rounded-3xl flex items-center justify-center mb-6 transition-all group-hover:scale-110 group-hover:bg-indigo-600 group-hover:text-white
            ${color === 'indigo' ? 'bg-slate-50 text-indigo-600' : ''}
        `}>
            <Icon size={32} />
        </div>
        <h4 className="text-xl font-black text-slate-900 mb-2">{title}</h4>
        <p className="text-sm font-medium text-slate-400 leading-relaxed">{desc}</p>
    </Link>
);

const AdminDashboard = () => {
    return (
        <div className="max-w-7xl mx-auto space-y-10 animate-in fade-in slide-in-from-bottom-4 duration-700">
            {/* Header Section */}
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div className="space-y-2">
                    <h1 className="text-4xl font-black text-slate-900 tracking-tight">Institutional <span className="text-indigo-600">Overview</span></h1>
                    <p className="text-slate-500 font-medium">Monitoring the digital pulse of your academic ecosystem.</p>
                </div>
                
                <div className="flex items-center gap-4">
                    <div className="hidden lg:flex items-center bg-white px-5 py-3 rounded-2xl border border-slate-100 shadow-sm focus-within:ring-2 ring-indigo-500 transition-all">
                        <Search size={18} className="text-slate-400 mr-3" />
                        <input type="text" placeholder="Global search..." className="bg-transparent border-none outline-none text-sm font-bold text-slate-700 placeholder:text-slate-300 w-48" />
                    </div>
                    <button className="p-3 bg-white border border-slate-100 rounded-2xl text-slate-600 hover:bg-slate-50 transition-all shadow-sm relative">
                        <Bell size={22} />
                        <span className="absolute top-2 right-2 w-2.5 h-2.5 bg-rose-500 border-2 border-white rounded-full"></span>
                    </button>
                    <div className="h-10 w-px bg-slate-200 mx-2"></div>
                    <div className="flex items-center gap-3 bg-white px-4 py-2 rounded-2xl border border-slate-100 shadow-sm">
                        <div className="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white">
                            <User size={20} />
                        </div>
                        <div className="hidden sm:block">
                            <p className="text-xs font-black text-slate-900 leading-tight">Admin Console</p>
                            <p className="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Super Administrator</p>
                        </div>
                    </div>
                </div>
            </div>

            {/* Stats Grid */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <StatCard title="Enrolled Students" value="2,845" change="+12.5%" trend="up" icon={Users} color="indigo" />
                <StatCard title="Active Faculty" value="142" change="+4.2%" trend="up" icon={GraduationCap} color="emerald" />
                <StatCard title="Monthly Attendance" value="94.8%" change="-1.2%" trend="down" icon={CalendarCheck} color="orange" />
                <StatCard title="Net Collection" value="रू 45.2M" change="+18.4%" trend="up" icon={CreditCard} color="pink" />
            </div>

            {/* ActionNexus */}
            <div className="space-y-6">
                <div className="flex items-center gap-3 ml-2">
                    <Plus size={20} className="text-indigo-600" />
                    <h2 className="text-xl font-black text-slate-900">Priority Operations</h2>
                </div>
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    <ActionCard title="New Admission" desc="Process student onboarding and documentation." icon={Plus} to="/students" color="indigo" />
                    <ActionCard title="Mark Attendance" desc="Record faculty and student daily logs." icon={ClipboardList} to="/attendance/entry" color="indigo" />
                    <ActionCard title="Verify Results" desc="Authenticate marks and generate transcripts." icon={Trophy} to="/exams/results" color="indigo" />
                    <ActionCard title="System Settings" desc="Configure institutional protocols and security." icon={Settings} to="/settings" color="indigo" />
                </div>
            </div>

            {/* Bottom Section: Activity & Performance */}
            <div className="grid lg:grid-cols-3 gap-8 pb-10">
                <div className="lg:col-span-2 bg-white rounded-[40px] border border-slate-100 p-10 space-y-8">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <TrendingUp size={24} className="text-indigo-600" />
                            <h2 className="text-xl font-black text-slate-900">Performance Metrics</h2>
                        </div>
                        <select className="bg-slate-50 border-none px-4 py-2 rounded-xl text-sm font-bold text-slate-600 outline-none">
                            <option>Academic Session 2082</option>
                            <option>Academic Session 2081</option>
                        </select>
                    </div>
                    
                    <div className="h-64 flex items-end justify-between gap-4 pt-10 px-4">
                        {[45, 60, 48, 75, 90, 65, 80, 55, 70, 85, 95, 100].map((h, i) => (
                            <div key={i} className="flex-1 space-y-3 flex flex-col items-center group">
                                <div className="w-full bg-slate-50 rounded-full h-48 relative overflow-hidden">
                                    <div 
                                        className="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-indigo-600 to-indigo-400 group-hover:from-indigo-500 group-hover:to-pink-500 transition-all duration-700"
                                        style={{ height: `${h}%` }}
                                    ></div>
                                </div>
                                <span className="text-[10px] font-black text-slate-400 uppercase tracking-tighter">M{i+1}</span>
                            </div>
                        ))}
                    </div>
                    <p className="text-center text-xs font-bold text-slate-400 mt-4">Average Academic Progression Index (API): <span className="text-indigo-600">84.2</span></p>
                </div>

                <div className="bg-slate-900 rounded-[40px] p-10 text-white space-y-8 relative overflow-hidden">
                    <div className="absolute top-0 right-0 w-40 h-40 bg-indigo-600/20 blur-[60px]"></div>
                    <div className="relative z-10 flex items-center justify-between mb-8">
                        <h2 className="text-xl font-black">System Logs</h2>
                        <Bell size={20} className="text-indigo-400" />
                    </div>
                    <div className="relative z-10 space-y-8">
                        {[
                            { user: 'Admin', action: 'Published Marksheets', time: '2m ago', color: 'bg-indigo-500' },
                            { user: 'Finance', action: 'Processed Payroll', time: '1h ago', color: 'bg-pink-500' },
                            { user: 'Registrar', action: 'Enrolled 5 Students', time: '4h ago', color: 'bg-emerald-500' },
                            { user: 'Officer', action: 'Updated Attendance', time: 'Yesterday', color: 'bg-orange-500' }
                        ].map((log, i) => (
                            <div key={i} className="flex items-start gap-4">
                                <div className={`w-2 h-2 rounded-full mt-2 ${log.color} shadow-[0_0_10px_rgba(255,255,255,0.3)]`}></div>
                                <div>
                                    <p className="text-sm font-bold text-slate-100">{log.action}</p>
                                    <p className="text-[10px] font-bold text-slate-500 uppercase tracking-widest">{log.user} • {log.time}</p>
                                </div>
                            </div>
                        ))}
                    </div>
                    <button className="relative z-10 w-full py-4 border border-white/10 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-white/5 transition-all mt-6">View Historical Logs</button>
                </div>
            </div>
        </div>
    );
};

export default AdminDashboard;
