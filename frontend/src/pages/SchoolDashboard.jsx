import React, { useState, useEffect } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { 
    Users, GraduationCap, Calendar, TrendingUp, 
    ShieldCheck, Bell, MessageSquare, BookOpen,
    ClipboardList, Trophy, Settings, LogOut,
    ChevronRight, CreditCard, Activity, Target
} from 'lucide-react';
import { supabase } from '../supabaseClient';
import '../styles/CyberBackground.css';

const SchoolDashboard = () => {
    const navigate = useNavigate();
    const [schoolName, setSchoolName] = useState('My Institution');
    const [stats, setStats] = useState({
        students: 0,
        teachers: 0,
        subjects: 0,
        attendance: '94%'
    });
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchSchoolData = async () => {
            const id = sessionStorage.getItem('institutionId');
            const name = sessionStorage.getItem('schoolName');
            if (name) setSchoolName(name);

            if (id) {
                // Fetch basic counts for dashboard
                const { count: studentCount } = await supabase
                    .from('students')
                    .select('*', { count: 'exact', head: true })
                    .eq('institution_id', id);
                
                const { count: teacherCount } = await supabase
                    .from('teachers')
                    .select('*', { count: 'exact', head: true })
                    .eq('institution_id', id);

                setStats({
                    students: studentCount || 0,
                    teachers: teacherCount || 0,
                    subjects: 12, // Default or fetch from curriculum
                    attendance: '96.8%'
                });
            }
            setLoading(false);
        };

        fetchSchoolData();
    }, []);

    const logout = () => {
        sessionStorage.clear();
        navigate('/login');
    };

    return (
        <div className="min-h-screen bg-[#0F172A] text-white font-['Outfit',sans-serif] relative overflow-hidden">
            <div className="cyber-background fixed inset-0 z-0"></div>

            <div className="relative z-10 p-8 pt-6 max-w-[1600px] mx-auto space-y-8">
                {/* Dashboard Header */}
                <header className="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div className="space-y-2">
                        <div className="flex items-center gap-3">
                            <div className="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-500/20 border border-indigo-400/30">
                                <ShieldCheck size={28} />
                            </div>
                            <h1 className="text-3xl font-[900] tracking-tight">{schoolName}</h1>
                        </div>
                        <p className="text-slate-400 font-medium flex items-center gap-2">
                            <span className="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                            Institutional Control Center • System Active
                        </p>
                    </div>

                    <div className="flex items-center gap-4 bg-slate-900/50 p-2 rounded-3xl border border-slate-800 backdrop-blur-xl">
                        <button className="p-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-2xl transition-all relative">
                            <Bell size={22} />
                            <span className="absolute top-3 right-3 w-2 h-2 bg-rose-500 rounded-full border-2 border-slate-900"></span>
                        </button>
                        <button className="p-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-2xl transition-all">
                            <Settings size={22} />
                        </button>
                        <div className="w-px h-8 bg-slate-800 mx-2"></div>
                        <button 
                            onClick={logout}
                            className="flex items-center gap-3 px-5 py-2.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-500 border border-rose-500/30 rounded-2xl font-black text-sm transition-all group"
                        >
                            <LogOut size={18} className="group-hover:-translate-x-1 transition-transform" />
                            Sign Out
                        </button>
                    </div>
                </header>

                {/* Metrics Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    {[
                        { label: 'Total Students', value: stats.students, icon: Users, color: 'indigo' },
                        { label: 'Academic Staff', value: stats.teachers, icon: GraduationCap, color: 'purple' },
                        { label: 'Curriculum Units', value: stats.subjects, icon: BookOpen, color: 'cyan' },
                        { label: 'Today\'s Attendance', value: stats.attendance, icon: Activity, color: 'emerald' }
                    ].map((card, i) => (
                        <div key={i} className="bg-slate-900/40 border border-slate-800 p-6 rounded-[32px] backdrop-blur-xl hover:border-slate-700 transition-all group relative overflow-hidden">
                            <div className={`absolute top-0 right-0 w-32 h-32 bg-${card.color}-500/5 blur-[60px] rounded-full`}></div>
                            <div className="flex items-center justify-between mb-4">
                                <div className={`w-12 h-12 bg-slate-800 rounded-2xl flex items-center justify-center text-${card.color}-400 group-hover:scale-110 transition-transform`}>
                                    <card.icon size={24} />
                                </div>
                                <div className="text-[10px] font-black uppercase tracking-widest text-slate-500 bg-slate-800/50 px-3 py-1.5 rounded-full border border-slate-700/50">
                                    Live Data
                                </div>
                            </div>
                            <div className="space-y-1">
                                <h3 className="text-3xl font-[900] tracking-tight">{card.value}</h3>
                                <p className="text-sm font-bold text-slate-400 uppercase tracking-widest">{card.label}</p>
                            </div>
                        </div>
                    ))}
                </div>

                {/* Primary Workspace Sections */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    {/* Activity Feed & Quick Actions */}
                    <div className="lg:col-span-2 space-y-8">
                        <section className="bg-slate-900/40 border border-slate-800 rounded-[40px] p-8 backdrop-blur-xl shadow-2xl overflow-hidden relative">
                            <div className="absolute top-0 right-0 w-80 h-80 bg-indigo-500/5 blur-[100px] rounded-full"></div>
                            
                            <div className="flex items-center justify-between mb-8 relative z-10">
                                <div className="space-y-1">
                                    <h2 className="text-2xl font-[900] tracking-tight">Institutional Terminal</h2>
                                    <p className="text-sm font-bold text-slate-400 uppercase tracking-widest">Execute campus-wide operations</p>
                                </div>
                                <div className="px-4 py-2 bg-indigo-500/10 border border-indigo-500/30 rounded-2xl text-indigo-400 text-xs font-black uppercase tracking-widest">
                                    System Ver 4.2
                                </div>
                            </div>

                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 relative z-10">
                                {[
                                    { title: 'Student Onboarding', desc: 'Secure digital registration', icon: Users, path: '/students', color: 'indigo' },
                                    { title: 'Attendance Log', desc: 'Execute daily verify protocol', icon: Calendar, path: '/attendance/entry', color: 'emerald' },
                                    { title: 'Grading Nexus', desc: 'Process academic performance', icon: Trophy, path: '/exams/entry', color: 'amber' },
                                    { title: 'Faculty Matrix', desc: 'Manage department staff', icon: GraduationCap, path: '/teachers', color: 'purple' }
                                ].map((action, i) => (
                                    <Link 
                                        key={i} 
                                        to={action.path}
                                        className="flex items-center gap-5 p-5 bg-slate-800/30 hover:bg-slate-800/60 border border-slate-700/50 rounded-3xl transition-all hover:scale-[1.02] active:scale-[0.98] group"
                                    >
                                        <div className={`w-14 h-14 bg-${action.color}-500/10 rounded-2xl flex items-center justify-center text-${action.color}-400 shrink-0 shadow-lg`}>
                                            <action.icon size={28} />
                                        </div>
                                        <div className="flex-1 min-w-0">
                                            <h4 className="font-[900] leading-tight text-white mb-1">{action.title}</h4>
                                            <p className="text-xs font-bold text-slate-500 uppercase tracking-tight truncate">{action.desc}</p>
                                        </div>
                                        <ChevronRight size={18} className="text-slate-600 group-hover:translate-x-1 transition-transform" />
                                    </Link>
                                ))}
                            </div>
                        </section>

                        {/* Recent Activity Mini-Shell */}
                        <section className="bg-slate-900/40 border border-slate-800 rounded-[40px] p-8 backdrop-blur-xl relative overflow-hidden">
                            <h2 className="text-xl font-[900] tracking-tight mb-6 flex items-center gap-3">
                                <Activity size={20} className="text-indigo-400" />
                                Recent Network Activity
                            </h2>
                            <div className="space-y-4">
                                {[
                                    { type: 'Attendance', detail: 'Primary Section B marked successfully', time: '12m ago', icon: Calendar, color: 'emerald' },
                                    { type: 'Exam Entry', detail: 'Mid-term maths results updated', time: '45m ago', icon: Trophy, color: 'amber' },
                                    { type: 'Security', detail: 'System login from EMIS Core 2.0', time: '1h ago', icon: ShieldCheck, color: 'indigo' }
                                ].map((item, i) => (
                                    <div key={i} className="flex items-center gap-5 p-3 hover:bg-slate-800/30 rounded-2xl transition-colors">
                                        <div className={`w-10 h-10 rounded-xl bg-${item.color}-500/10 flex items-center justify-center text-${item.color}-400`}>
                                            <item.icon size={18} />
                                        </div>
                                        <div className="flex-1">
                                            <p className="text-sm font-bold text-slate-200">{item.detail}</p>
                                            <p className="text-[10px] font-black uppercase tracking-widest text-slate-500">{item.type} • {item.time}</p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </section>
                    </div>

                    {/* Subscription & Support Sidebar */}
                    <div className="space-y-8">
                        <section className="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-[40px] p-8 shadow-2xl shadow-indigo-500/20 relative overflow-hidden border border-white/10 group">
                            <div className="absolute top-0 right-0 w-40 h-40 bg-white/10 blur-[50px] rounded-full translate-x-10 translate-y-[-10px]"></div>
                            <div className="relative z-10 space-y-6">
                                <div className="flex justify-between items-start">
                                    <div className="w-12 h-12 bg-white/20 backdrop-blur-lg rounded-2xl flex items-center justify-center border border-white/20">
                                        <TrendingUp size={24} className="text-white" />
                                    </div>
                                    <span className="px-3 py-1 bg-white/20 text-white text-[10px] font-black uppercase tracking-widest rounded-full border border-white/20">Premium Active</span>
                                </div>
                                <div className="space-y-1">
                                    <h3 className="text-2xl font-[1000] text-white tracking-tight">Enterprise Nexus</h3>
                                    <p className="text-sm font-bold text-white/70 uppercase tracking-widest">Validity: 730 Days Remaining</p>
                                </div>
                                <Link 
                                    to="/subscription"
                                    className="block w-full py-4 bg-white text-indigo-700 rounded-3xl font-[1000] text-center shadow-lg hover:bg-slate-100 transition-all uppercase tracking-widest text-sm"
                                >
                                    Renew Subscription
                                </Link>
                            </div>
                        </section>

                        <section className="bg-slate-900/40 border border-slate-800 rounded-[40px] p-8 backdrop-blur-xl space-y-6">
                            <div className="flex items-center gap-4">
                                <div className="w-12 h-12 bg-slate-800 rounded-2xl flex items-center justify-center text-cyan-400">
                                    <MessageSquare size={24} />
                                </div>
                                <div>
                                    <h3 className="font-[900] text-lg tracking-tight leading-tight">Dev Support</h3>
                                    <p className="text-xs font-bold text-slate-500 uppercase tracking-widest">Connect with our core team</p>
                                </div>
                            </div>
                            <button className="w-full py-4 bg-slate-800/50 hover:bg-slate-800 border border-slate-700 rounded-3xl text-sm font-[1000] uppercase tracking-widest text-slate-300 transition-all">
                                Open Secure Comms
                            </button>
                        </section>

                        {/* System Health */}
                        <section className="p-8 bg-slate-900/40 border border-slate-800 rounded-[40px] backdrop-blur-xl">
                            <div className="flex items-center justify-between mb-4">
                                <h3 className="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Node Status</h3>
                                <span className="text-xs font-black text-emerald-400 uppercase">Online</span>
                            </div>
                            <div className="space-y-4">
                                <div className="space-y-1">
                                    <div className="flex justify-between text-[10px] font-black uppercase text-slate-500 mb-1">
                                        <span>Cloud Sync</span>
                                        <span>99.9%</span>
                                    </div>
                                    <div className="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                                        <div className="w-[99.9%] h-full bg-indigo-500 shadow-[0_0_10px_rgba(79,70,229,0.5)]"></div>
                                    </div>
                                </div>
                                <div className="space-y-1">
                                    <div className="flex justify-between text-[10px] font-black uppercase text-slate-500 mb-1">
                                        <span>Database Load</span>
                                        <span>12.4%</span>
                                    </div>
                                    <div className="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                                        <div className="w-[12%] h-full bg-cyan-500 shadow-[0_0_10px_rgba(6,182,212,0.5)]"></div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default SchoolDashboard;
