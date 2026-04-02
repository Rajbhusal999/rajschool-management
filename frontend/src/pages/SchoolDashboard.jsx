import React, { useState, useEffect } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { 
    Users, GraduationCap, Calendar, TrendingUp, 
    Shield, Bell, MessageSquare, BookOpen,
    ClipboardList, Trophy, Settings, LogOut,
    ChevronRight, CreditCard, Activity, Crosshair,
    Star, Zap, Home, BarChart, Target, UserPlus
} from 'lucide-react';
import { supabase } from '../supabaseClient';
import DigitalClock from '../components/DigitalClock';
import { ActivityTrendChart, CompositionChart } from '../components/Charts';
import { useLanguage } from '../context/AppContext';

const SchoolDashboard = () => {
    const { t } = useLanguage();
    const navigate = useNavigate();
    const [schoolName, setSchoolName] = useState('Raj School');
    const [schoolLogo, setSchoolLogo] = useState(null);
    const [schoolBackground, setSchoolBackground] = useState(null);
    const [stats, setStats] = useState({
        students: 0,
        teachers: 0,
        subjects: 12,
        attendance: '96.8%',
        daysRemaining: 0,
        status: 'ACTIVE',
        plan: '5_years',
        link: "/attendance/reports"
    });
    const [loading, setLoading] = useState(true);
    const [recentOps, setRecentOps] = useState([]);

    useEffect(() => {
        const fetchSchoolData = async () => {
            const rawId = sessionStorage.getItem('institutionId');
            const name = sessionStorage.getItem('schoolName');
            const logo = sessionStorage.getItem('schoolLogo');
            const bg = sessionStorage.getItem('schoolBackground');
            
            if (!rawId) {
                navigate('/login');
                return;
            }
            if (name) setSchoolName(name);
            if (logo) setSchoolLogo(logo);
            if (bg) setSchoolBackground(bg);

            const id = Number(rawId);

            try {
                // Pre-check for already stored data to speed up display
                const storedAddress = sessionStorage.getItem('schoolAddress');
                const storedEstd = sessionStorage.getItem('estdYear');
                
                // Fetch the latest status to ensure subscription is still valid
                const { data: instData, error: instError } = await supabase
                    .from('institutions')
                    .select('status, expiry_date, address, establishment')
                    .eq('id', id)
                    .single();

                if (instError) {
                    console.error('Database Error:', instError);
                    // If the institution ID is invalid or missing, clear session and bail out
                    if (instError.code === 'PGRST116') {
                        sessionStorage.clear();
                        navigate('/login');
                        return;
                    }
                }

                if (!instData) {
                    sessionStorage.clear();
                    navigate('/login');
                    return;
                }

                // Update session storage if data has changed
                if (instData.address) sessionStorage.setItem('schoolAddress', instData.address);
                if (instData.establishment) sessionStorage.setItem('estdYear', instData.establishment);
                if (instData.logo_url) {
                    sessionStorage.setItem('schoolLogo', instData.logo_url);
                    setSchoolLogo(instData.logo_url);
                }
                if (instData.background_url) {
                    sessionStorage.setItem('schoolBackground', instData.background_url);
                    setSchoolBackground(instData.background_url);
                }

                // Subscription metadata
                const now = new Date();
                const expiryDate = instData.expiry_date ? new Date(instData.expiry_date) : null;

                let diffDays = 0;
                if (expiryDate) {
                    const diffTime = Math.abs(expiryDate - now);
                    diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                }

                const { count: studentCount } = await supabase.from('students').select('*', { count: 'exact', head: true }).eq('school_id', id);
                const { count: teacherCount } = await supabase.from('teachers').select('*', { count: 'exact', head: true }).eq('school_id', id);

                setStats({
                    students: studentCount || 0,
                    teachers: teacherCount || 0,
                    subjects: 12,
                    attendance: '96.8%',
                    daysRemaining: diffDays,
                    status: instData.status,
                    plan: instData.status === 'PENDING' ? 'Pending' : '5_years'
                });
            } catch (err) {
                console.error('Fatal dashboard error:', err);
            } finally {
                setLoading(false);
            }
        };

        const fetchRecentOps = async () => {
            const rawId = sessionStorage.getItem('institutionId');
            if (!rawId) return;
            const id = Number(rawId);

            try {
                // Fetch recent students
                const { data: recentStudents } = await supabase
                    .from('students')
                    .select('id, name, created_at')
                    .eq('school_id', id)
                    .order('created_at', { ascending: false })
                    .limit(3);

                // Fetch recent fee receipts
                const { data: recentFees } = await supabase
                    .from('fee_receipts')
                    .select('id, student_name, total_amount, created_at')
                    .eq('institution_id', id)
                    .order('created_at', { ascending: false })
                    .limit(3);

                const activities = [
                    ...(recentStudents || []).map(s => ({
                        id: `std-${s.id}`,
                        type: 'admission',
                        title: t('newAdmitMsg').replace('{name}', s.name),
                        date: new Date(s.created_at),
                        icon: UserPlus,
                        color: 'emerald'
                    })),
                    ...(recentFees || []).map(f => ({
                        id: `fee-${f.id}`,
                        type: 'fee',
                        title: t('feePayMsg').replace('{amount}', f.total_amount).replace('{name}', f.student_name),
                        date: new Date(f.created_at),
                        icon: CreditCard,
                        color: 'indigo'
                    }))
                ].sort((a, b) => b.date - a.date).slice(0, 5);

                setRecentOps(activities);
            } catch (err) {
                console.error('Recent Ops Error:', err);
            }
        };

        fetchSchoolData();
        fetchRecentOps();
    }, [navigate]);

    if (loading) return <div className="flex items-center justify-center min-h-[60vh] text-indigo-600 font-black animate-pulse">Initializing Terminal...</div>;

    return (
        <div className="space-y-10 animate-in fade-in duration-700 relative">
            
            {/* Background Light Vision Artifact */}
            {schoolBackground && (
                <div 
                    className="fixed inset-0 pointer-events-none z-0 opacity-[0.04] transition-opacity duration-1000"
                    style={{
                        backgroundImage: `url(${schoolBackground})`,
                        backgroundSize: '80% auto', // Watermark style
                        backgroundPosition: 'center',
                        backgroundRepeat: 'no-repeat',
                        filter: 'grayscale(100%) brightness(1.2)'
                    }}
                />
            )}

            <div className="relative z-10 space-y-10">
                {/* Unpaid Initial Access Banner */}
                {(!stats.status || stats.status === 'UNPAID') && (
                    <div className="bg-indigo-50 border-2 border-indigo-200 rounded-[40px] p-8 flex flex-col lg:flex-row items-center justify-between gap-8 shadow-sm group hover:border-indigo-300 transition-all mb-10">
                        <div className="flex items-center gap-6">
                            <div className="w-20 h-20 bg-indigo-600 rounded-[30px] flex items-center justify-center text-white shadow-xl shadow-indigo-200 shrink-0 group-hover:scale-105 transition-transform">
                                <Zap size={36} className="animate-bounce" strokeWidth={2.5} />
                            </div>
                            <div className="space-y-1">
                                <h3 className="text-2xl font-[1000] text-indigo-900 tracking-tight uppercase">Ready to Launch?</h3>
                                <p className="text-indigo-600/70 text-sm font-bold max-w-xl">Your institutional portal is initialized. Finalize your subscription to unlock persistence, unlimited students, and official certification tools.</p>
                            </div>
                        </div>
                        <button 
                            onClick={() => navigate('/subscription')}
                            className="w-full lg:w-auto px-10 py-5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-[24px] font-black text-sm uppercase tracking-widest shadow-xl shadow-indigo-200 transition-all flex items-center justify-center gap-3 group/btn"
                        >
                            Select Plan <ChevronRight size={18} className="group-hover/btn:translate-x-1 transition-transform" />
                        </button>
                    </div>
                )}

                {/* Verification Pending Banner */}
            {stats.status === 'PENDING' && (
                <div className="bg-amber-50 border-2 border-amber-200 rounded-[32px] p-6 flex flex-col md:flex-row items-center justify-between gap-6 shadow-sm border-dashed">
                    <div className="flex items-center gap-4 text-amber-700">
                        <div className="w-12 h-12 bg-amber-200/50 rounded-2xl flex items-center justify-center shrink-0">
                            <Activity size={24} className="animate-pulse" />
                        </div>
                        <div>
                            <h3 className="font-black uppercase tracking-widest text-sm">Security Handshake in Progress</h3>
                            <p className="text-amber-800/70 text-xs font-bold font-mono">Our core is validating your transaction. Full features will activate post-verification.</p>
                        </div>
                    </div>
                    <div className="px-6 py-2 bg-amber-200 ring-2 ring-amber-100 rounded-full text-amber-800 text-[10px] font-black uppercase tracking-[0.2em] whitespace-nowrap">
                        Mode: Observation Only
                    </div>
                </div>
            )}

            {/* Title & Clock Header */}
            <header className="flex flex-col xl:flex-row xl:items-end justify-between gap-8">
                <div className="flex flex-col md:flex-row md:items-center gap-6">
                    {schoolLogo ? (
                        <div className="w-24 h-24 bg-white dark:bg-slate-800 p-2 rounded-[32px] shadow-xl border border-slate-100 dark:border-slate-700 shrink-0">
                            <img src={schoolLogo} alt={schoolName} className="w-full h-full object-contain rounded-[24px]" />
                        </div>
                    ) : (
                        <div className="w-20 h-20 bg-indigo-600 rounded-[28px] flex items-center justify-center text-white shadow-lg shadow-indigo-200 dark:shadow-indigo-900/20 shrink-0">
                            <Home size={36} strokeWidth={2.5} />
                        </div>
                    )}
                    <div className="space-y-1">
                        <h1 className="text-[52px] font-[1000] text-indigo-600 dark:text-indigo-400 tracking-tight leading-none drop-shadow-sm">
                            {schoolName}
                        </h1>
                        <p className="text-lg font-black text-slate-400 dark:text-slate-500 max-w-md">
                            {t('dashboardOverview')}
                        </p>
                    </div>
                </div>
                <div className="w-full xl:w-auto">
                    <DigitalClock showBSDate={false} />
                </div>
            </header>

            {/* Quick Metrics Grid */}
            <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                {[
                    { label: t('totalStudents'), value: stats.students, sub: t('activeEnrollment'), icon: Users, color: 'indigo' },
                    { label: t('totalTeachers'), value: stats.teachers, sub: t('academicStaff'), icon: GraduationCap, color: 'emerald' },
                    { label: t('planLevel'), value: stats.plan, sub: t('premiumStatus'), icon: Star, color: 'amber' }
                ].map((card, i) => (
                    <div key={i} className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-8 rounded-[40px] shadow-sm flex items-center gap-6 group hover:border-indigo-300 dark:hover:border-indigo-700 transition-all hover:shadow-xl hover:shadow-indigo-500/5">
                        <div className={`w-20 h-20 bg-${card.color}-500/10 rounded-[28px] flex items-center justify-center text-${card.color}-600 dark:text-${card.color}-400 group-hover:scale-110 transition-transform`}>
                            <card.icon size={36} strokeWidth={2.5} />
                        </div>
                        <div className="space-y-1">
                            <h4 className="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">{card.label}</h4>
                            <p className="text-4xl font-[1000] text-slate-800 dark:text-slate-100 tracking-tighter tabular-nums">{card.value}</p>
                            <p className={`text-xs font-black text-${card.color}-500 dark:text-${card.color}-400 uppercase tracking-tight`}>{card.sub}</p>
                        </div>
                    </div>
                ))}
            </div>

            {/* Teacher Salary - Exclusive Banner */}
            <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[40px] p-8 shadow-sm flex flex-col md:flex-row items-center justify-between gap-8 group hover:border-indigo-200 transition-all">
                <div className="flex items-center gap-8">
                    <div className="w-20 h-20 bg-indigo-600 rounded-[28px] flex items-center justify-center text-white shadow-lg shadow-indigo-200 dark:shadow-indigo-900/20">
                        <CreditCard size={36} strokeWidth={2.5} />
                    </div>
                    <div className="space-y-1 text-center md:text-left">
                        <div className="flex flex-col md:flex-row md:items-center gap-3">
                            <h3 className="text-3xl font-[1000] text-slate-800 dark:text-slate-100 tracking-tight">{t('teacherSalary')}</h3>
                            <span className="px-4 py-1.5 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest rounded-full shadow-md shadow-indigo-200 dark:shadow-indigo-900/20">
                                {t('exclusive')}
                            </span>
                        </div>
                        <p className="text-sm font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">{t('manageSalariesDesc')}</p>
                    </div>
                </div>
                <button className="w-full md:w-auto px-10 py-5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-[24px] font-black text-sm uppercase tracking-widest shadow-xl shadow-indigo-200 transition-all flex items-center justify-center gap-3 group">
                    <Calendar size={20} />
                    Manage Salaries
                </button>
            </div>

            {/* Middle Section: Chart and Tools */}
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {/* Activity Trend Card */}
                <div className="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[40px] p-8 shadow-sm">
                    <div className="flex items-center justify-between mb-10">
                        <div className="flex items-center gap-3">
                            <Activity size={24} className="text-indigo-600 dark:text-indigo-400" />
                            <h3 className="text-xl font-[1000] tracking-tight text-slate-800 dark:text-slate-100 uppercase">{t('activityTrend')}</h3>
                        </div>
                        <span className="px-5 py-2 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-[10px] font-black uppercase tracking-widest rounded-full border border-emerald-100 dark:border-emerald-800/50">Live Insight</span>
                    </div>
                    <div className="h-[300px] w-full">
                        <ActivityTrendChart />
                    </div>
                    <div className="flex justify-center gap-6 mt-6">
                        {['Admissions', 'Exam Activity', 'Billing'].map((l, i) => (
                            <div key={i} className="flex items-center gap-2">
                                <div className={`w-3 h-3 rounded-full ${i===0?'bg-emerald-500':i===1?'bg-indigo-500':'bg-purple-500'}`}></div>
                                <span className="text-[10px] font-black text-slate-500 uppercase tracking-widest">{l}</span>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Today's Attendance */}
                <div className="bg-emerald-500/5 dark:bg-emerald-500/10 border border-emerald-500/20 dark:border-emerald-500/30 rounded-[40px] p-10 flex flex-col items-center justify-center text-center shadow-sm relative overflow-hidden group">
                    <div className="absolute top-0 right-0 w-32 h-32 bg-emerald-500/5 blur-[40px] rounded-full translate-x-10 translate-y-[-10px]"></div>
                    <div className="w-16 h-16 bg-emerald-500/10 rounded-2xl flex items-center justify-center text-emerald-600 dark:text-emerald-400 mb-6 group-hover:scale-110 transition-transform">
                        <ClipboardList size={32} />
                    </div>
                    <h3 className="text-2xl font-[1000] text-emerald-700 dark:text-emerald-400 tracking-tight mb-4">{t('attendanceSummary')}</h3>
                    <p className="text-xs font-bold text-emerald-600/60 dark:text-slate-500 uppercase tracking-widest leading-relaxed mb-10 max-w-[200px]">
                        {t('attendanceDesc')}
                    </p>
                    <button 
                        onClick={() => navigate('/attendance')}
                        className="w-full py-5 bg-white dark:bg-slate-800 border-2 border-emerald-100 dark:border-slate-700 hover:border-emerald-200 dark:hover:border-slate-600 text-emerald-700 dark:text-emerald-400 rounded-[24px] font-black text-sm uppercase tracking-widest shadow-xl shadow-emerald-500/5 transition-all flex items-center justify-center gap-3"
                    >
                        <Zap size={18} />
                        {t('markAttendance')}
                    </button>
                </div>

                {/* Fee Collection */}
                <div className="bg-indigo-900 dark:bg-slate-900 border border-indigo-700 dark:border-indigo-500/20 rounded-[40px] p-10 flex flex-col items-center justify-center text-center shadow-sm relative overflow-hidden group">
                    <div className="absolute top-0 right-0 w-32 h-32 bg-white/5 blur-[40px] rounded-full translate-x-10 translate-y-[-10px]"></div>
                    <div className="w-16 h-16 bg-white/10 rounded-2xl flex items-center justify-center text-white mb-6 group-hover:scale-110 transition-transform">
                        <CreditCard size={32} />
                    </div>
                    <h3 className="text-2xl font-[1000] text-white tracking-tight mb-4">{t('feeCollection')}</h3>
                    <p className="text-xs font-bold text-indigo-200/60 uppercase tracking-widest leading-relaxed mb-10 max-w-[200px]">
                        {t('feeDesc')}
                    </p>
                    <button 
                        onClick={() => navigate('/billing')}
                        className="w-full py-5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-[24px] font-black text-sm uppercase tracking-widest shadow-xl shadow-black/20 transition-all flex items-center justify-center gap-3"
                    >
                        <Shield size={18} />
                        {t('enterLedger')}
                    </button>
                </div>
            </div>

            {/* Bottom Row: Operations and Composition */}
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {/* Recent Operations Card */}
                <div className="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[40px] p-10 shadow-sm overflow-hidden flex flex-col">
                    <div className="flex items-center justify-between mb-10 border-b border-slate-100 dark:border-slate-800 pb-6">
                        <div className="flex items-center gap-3">
                            <Calendar size={24} className="text-indigo-600 dark:text-indigo-400" />
                            <h3 className="text-xl font-[1000] tracking-tight text-slate-800 dark:text-slate-100 uppercase">{t('recentOperations')}</h3>
                        </div>
                        <Link to="/reports" className="text-[10px] font-black text-indigo-600 dark:text-indigo-400 hover:underline uppercase tracking-widest">{t('viewAll')}</Link>
                    </div>
                    
                    <div className="flex-1 space-y-6">
                        {recentOps.length > 0 ? (
                            recentOps.map((op) => (
                                <div key={op.id} className="flex items-center justify-between group cursor-default">
                                    <div className="flex items-center gap-6">
                                        <div className={`w-14 h-14 bg-${op.color}-500/10 rounded-2xl flex items-center justify-center text-${op.color}-600 dark:text-${op.color}-400 group-hover:scale-110 transition-transform`}>
                                            <op.icon size={24} />
                                        </div>
                                        <div>
                                            <h4 className="text-sm font-black text-slate-800 dark:text-slate-200 uppercase tracking-tight mb-1">{op.title}</h4>
                                            <p className="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em]">
                                                {op.date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })} • {op.date.toLocaleDateString()}
                                            </p>
                                        </div>
                                    </div>
                                    <div className="opacity-0 group-hover:opacity-100 transition-opacity">
                                        <ChevronRight size={18} className="text-slate-300 dark:text-slate-700" />
                                    </div>
                                </div>
                            ))
                        ) : (
                            <div className="h-full min-h-[250px] flex flex-col justify-center items-center text-slate-300 dark:text-slate-700">
                                <div className="w-20 h-20 bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-3xl flex items-center justify-center mb-6">
                                    <Activity size={32} />
                                </div>
                                <p className="text-[10px] font-black uppercase tracking-[0.3em]">{t('noRecentOps')}</p>
                            </div>
                        )}
                    </div>
                </div>

                {/* Composition Card */}
                <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[40px] p-10 shadow-sm flex flex-col items-center">
                    <div className="flex items-center gap-3 mb-8 w-full border-b border-slate-100 dark:border-slate-800 pb-6">
                        <Target size={24} className="text-indigo-600 dark:text-indigo-400" />
                        <h3 className="text-xl font-[1000] tracking-tight text-slate-800 dark:text-slate-100 uppercase">{t('composition')}</h3>
                    </div>
                    <div className="w-full h-[250px]">
                        <CompositionChart />
                    </div>
                    <div className="mt-8 text-center bg-slate-50 dark:bg-slate-800 p-6 rounded-3xl w-full border border-slate-100 dark:border-slate-700">
                        <h4 className="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">{t('genderDistribution')}</h4>
                        <p className="text-xl font-[1000] text-slate-700 dark:text-slate-200 tracking-tight">{t('balancedRatio')}</p>
                    </div>
                </div>
            </div>

            {/* Income Expenditure Footer Section */}
            <div className="bg-white border border-slate-200 rounded-[40px] p-10 shadow-sm flex flex-col md:flex-row items-center justify-between gap-8 group hover:border-emerald-200 transition-all border-l-8 border-l-emerald-500">
                <div className="flex flex-col md:flex-row items-center gap-8">
                    <div className="w-20 h-20 bg-emerald-100 rounded-[28px] flex items-center justify-center text-emerald-600">
                        <Home size={36} strokeWidth={2.5} />
                    </div>
                    <div className="space-y-1 text-center md:text-left">
                        <h3 className="text-2xl font-[1000] text-[#8B4513] tracking-tight mb-2">Income Expenditure Management</h3>
                        <p className="text-xs font-bold text-slate-400 uppercase tracking-widest">If your school is a government school, you can also use income expenditure management.</p>
                    </div>
                </div>
                <button className="w-full md:w-auto px-10 py-5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-[24px] font-black text-sm uppercase tracking-widest shadow-xl shadow-emerald-200 transition-all flex items-center justify-center gap-3">
                    Open System
                    <ChevronRight size={18} />
                </button>
            </div>

        </div>
    </div>
    );
};

export default SchoolDashboard;
