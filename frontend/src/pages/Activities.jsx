import React, { useState, useEffect } from 'react';
import { supabase } from '../supabaseClient';
import { useLanguage } from '../context/AppContext';
import { 
    UserPlus, 
    CreditCard, 
    Calendar, 
    Search, 
    Filter, 
    ChevronRight,
    ArrowLeft,
    Clock,
    ShieldCheck
} from 'lucide-react';
import { useNavigate } from 'react-router-dom';

const Activities = () => {
    const { t } = useLanguage();
    const navigate = useNavigate();
    const [activities, setActivities] = useState([]);
    const [loading, setLoading] = useState(true);
    const [dateRange, setDateRange] = useState({
        start: new Date(new Date().setDate(new Date().getDate() - 30)).toISOString().split('T')[0], // Last 30 days
        end: new Date().toISOString().split('T')[0]
    });

    const fetchActivities = async () => {
        setLoading(true);
        const isTrial = sessionStorage.getItem('isTrialMode') === 'true';
        const rawId = sessionStorage.getItem('institutionId');
        if (!rawId) {
            navigate('/login');
            return;
        }

        if (isTrial) {
            setActivities([
                { id: 'trial-1', type: 'admission', title: 'New Student Admitted: Arjun Thapa', date: new Date(), icon: UserPlus, color: 'emerald', metadata: 'New Admission' },
                { id: 'trial-2', type: 'fee', title: 'Fee Collection: NPR 5,000 from Sita Rai', date: new Date(Date.now() - 3600000), icon: CreditCard, color: 'indigo', metadata: 'Fee Collection' },
                { id: 'trial-3', type: 'admission', title: 'New Student Admitted: Biraj Kumar', date: new Date(Date.now() - 7200000), icon: UserPlus, color: 'emerald', metadata: 'New Admission' },
                { id: 'trial-4', type: 'fee', title: 'Fee Collection: NPR 12,500 from Grishma Joshi', date: new Date(Date.now() - 86400000), icon: CreditCard, color: 'indigo', metadata: 'Fee Collection' }
            ]);
            setLoading(false);
            return;
        }

        const id = Number(rawId);

        try {
            // Fetch students within date range
            const { data: students } = await supabase
                .from('students')
                .select('id, name, created_at')
                .eq('school_id', id)
                .gte('created_at', `${dateRange.start}T00:00:00`)
                .lte('created_at', `${dateRange.end}T23:59:59`)
                .order('created_at', { ascending: false });

            // Fetch fees within date range
            const { data: fees } = await supabase
                .from('fee_receipts')
                .select('id, student_name, total_amount, created_at')
                .eq('institution_id', id)
                .gte('created_at', `${dateRange.start}T00:00:00`)
                .lte('created_at', `${dateRange.end}T23:59:59`)
                .order('created_at', { ascending: false });

            const combined = [
                ...(students || []).map(s => ({
                    id: `std-${s.id}`,
                    type: 'admission',
                    title: t('newAdmitMsg').replace('{name}', s.name),
                    date: new Date(s.created_at),
                    icon: UserPlus,
                    color: 'emerald',
                    metadata: 'New Admission'
                })),
                ...(fees || []).map(f => ({
                    id: `fee-${f.id}`,
                    type: 'fee',
                    title: t('feePayMsg').replace('{amount}', f.total_amount).replace('{name}', f.student_name),
                    date: new Date(f.created_at),
                    icon: CreditCard,
                    color: 'indigo',
                    metadata: 'Fee Collection'
                }))
            ].sort((a, b) => b.date - a.date);

            setActivities(combined);
        } catch (err) {
            console.error('Error fetching activities:', err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchActivities();
    }, [dateRange]);

    return (
        <div className="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
            {/* Header Section */}
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div className="space-y-1">
                    <div className="flex items-center gap-3 text-indigo-600 dark:text-indigo-400">
                        <ShieldCheck size={20} strokeWidth={2.5} />
                        <span className="text-[10px] font-black uppercase tracking-[0.2em]">{t('monitoringCore')}</span>
                    </div>
                    <h1 className="text-4xl font-[1000] text-slate-900 dark:text-white tracking-tight leading-none uppercase">
                        {t('activityLog')}
                    </h1>
                </div>

                {/* Filter Controls */}
                <div className="flex flex-wrap items-center gap-4 bg-white dark:bg-slate-900/50 backdrop-blur-xl p-4 rounded-[32px] border border-slate-200 dark:border-slate-800 shadow-sm shadow-slate-100 dark:shadow-none">
                    <div className="flex items-center gap-3 px-4 py-2 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-transparent hover:border-indigo-200 transition-all">
                        <Calendar size={16} className="text-indigo-500" />
                        <input 
                            type="date" 
                            value={dateRange.start}
                            onChange={(e) => setDateRange(prev => ({ ...prev, start: e.target.value }))}
                            className="bg-transparent text-xs font-black text-slate-700 dark:text-slate-200 focus:outline-none uppercase"
                        />
                    </div>
                    <ChevronRight size={16} className="text-slate-300 hidden md:block" />
                    <div className="flex items-center gap-3 px-4 py-2 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-transparent hover:border-indigo-200 transition-all">
                        <Calendar size={16} className="text-indigo-500" />
                        <input 
                            type="date" 
                            value={dateRange.end}
                            onChange={(e) => setDateRange(prev => ({ ...prev, end: e.target.value }))}
                            className="bg-transparent text-xs font-black text-slate-700 dark:text-slate-200 focus:outline-none uppercase"
                        />
                    </div>
                </div>
            </div>

            {/* Main Log Section */}
            <div className="bg-white dark:bg-slate-900/50 backdrop-blur-3xl rounded-[48px] border-2 border-slate-100 dark:border-slate-800/50 shadow-2xl shadow-indigo-100/20 overflow-hidden relative">
                {loading ? (
                    <div className="py-32 flex flex-col items-center justify-center space-y-6">
                        <div className="w-12 h-12 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
                        <p className="text-indigo-600 font-bold uppercase tracking-widest text-[10px]">Retrieving Operations...</p>
                    </div>
                ) : activities.length > 0 ? (
                    <div className="divide-y divide-slate-50 dark:divide-slate-800/50">
                        {activities.map((activity, idx) => (
                            <div 
                                key={activity.id} 
                                className="p-8 flex items-center justify-between group hover:bg-slate-50 dark:hover:bg-indigo-950/20 transition-all duration-300"
                            >
                                <div className="flex items-center gap-8">
                                    <div className={`w-16 h-16 rounded-[24px] flex items-center justify-center shadow-lg transition-transform group-hover:scale-110
                                        ${activity.type === 'admission' ? 'bg-emerald-100 text-emerald-600 shadow-emerald-100 dark:bg-emerald-950 dark:text-emerald-400 dark:shadow-none' : 'bg-indigo-100 text-indigo-600 shadow-indigo-100 dark:bg-indigo-950 dark:text-indigo-400 dark:shadow-none'}`}>
                                        <activity.icon size={28} strokeWidth={2.5} />
                                    </div>
                                    <div className="space-y-1">
                                        <div className="flex items-center gap-3">
                                            <span className={`text-[9px] font-black uppercase tracking-widest px-2 py-0.5 rounded-full
                                                ${activity.type === 'admission' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30' : 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30'}`}>
                                                {activity.metadata}
                                            </span>
                                            <div className="flex items-center gap-1.5 text-slate-400 dark:text-slate-500 font-bold text-[10px]">
                                                <Clock size={10} />
                                                {activity.date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                                            </div>
                                        </div>
                                        <h3 className="text-xl font-[1000] text-slate-800 dark:text-slate-200 tracking-tight leading-tight uppercase underline decoration-2 decoration-transparent group-hover:decoration-indigo-500/30 transition-all">
                                            {activity.title}
                                        </h3>
                                        <p className="text-sm font-bold text-slate-400 dark:text-slate-500">
                                            {activity.date.toLocaleDateString(undefined, { 
                                                weekday: 'short', 
                                                year: 'numeric', 
                                                month: 'short', 
                                                day: 'numeric' 
                                            })}
                                        </p>
                                    </div>
                                </div>
                                <div className="opacity-0 group-hover:opacity-100 transition-all flex items-center gap-2 text-indigo-600 dark:text-indigo-400">
                                    <span className="text-[10px] font-black uppercase tracking-widest">Details</span>
                                    <ChevronRight size={18} />
                                </div>
                            </div>
                        ))}
                    </div>
                ) : (
                    <div className="py-32 flex flex-col items-center justify-center space-y-6 text-center px-8">
                        <div className="w-24 h-24 bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center text-slate-300">
                            <Search size={40} />
                        </div>
                        <div className="space-y-2">
                            <h3 className="text-2xl font-[1000] text-slate-800 dark:text-white uppercase tracking-tight">{t('noDataInRange')}</h3>
                            <p className="text-slate-400 dark:text-slate-500 font-bold text-sm max-w-xs">{t('noRecentOps')}</p>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
};

export default Activities;
