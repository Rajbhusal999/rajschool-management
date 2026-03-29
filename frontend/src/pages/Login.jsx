import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { supabase } from '../supabaseClient';
import { 
    GraduationCap, Key, Lock, Eye, EyeOff, Check, UserPlus, ShieldAlert,
    ChevronRight, Globe, ShieldCheck
} from 'lucide-react';

const Login = () => {
    const navigate = useNavigate();
    const [showPassword, setShowPassword] = useState(false);
    const [formData, setFormData] = useState({
        emisCode: '',
        password: '',
        remember: false
    });
    const [loading, setLoading] = useState(false);
    const [error, setError] = React.useState('');

    React.useEffect(() => {
        const id = sessionStorage.getItem('institutionId');
        if (id) {
            navigate('/dashboard');
        }
    }, [navigate]);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setError('');

        try {
            const { data, error } = await supabase
                .from('institutions')
                .select('id, school_name, status, expiry_date, address, establishment')
                .eq('emis_code', formData.emisCode)
                .eq('password', formData.password)
                .single();

            if (error || !data) {
                throw new Error('Invalid EMIS code or password. Please verify your credentials.');
            }

            // Store session info
            sessionStorage.setItem('institutionId', data.id);
            sessionStorage.setItem('schoolName', data.school_name);
            if (data.address) sessionStorage.setItem('schoolAddress', data.address);
            if (data.establishment) sessionStorage.setItem('estdYear', data.establishment);
            
            // Redirection logic based on subscription
            const now = new Date();
            const expiryDate = data.expiry_date ? new Date(data.expiry_date) : null;
            
            // Allow access if ACTIVE (not expired) or if a subscription is PENDING verification
            if ((data.status === 'ACTIVE' && expiryDate && expiryDate > now) || data.status === 'PENDING') {
                navigate('/dashboard');
            } else {
                navigate('/subscription');
            }
        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="min-h-screen bg-[#FDFDFF] flex items-center justify-center py-20 px-6 font-['Outfit',sans-serif]">
            {/* Background Decorative Elements */}
            <div className="fixed inset-0 overflow-hidden pointer-events-none">
                <div className="absolute top-[-10%] right-[-10%] w-[500px] h-[500px] bg-indigo-500/5 blur-[120px] rounded-full"></div>
                <div className="absolute bottom-[-10%] left-[-10%] w-[400px] h-[400px] bg-indigo-500/5 blur-[100px] rounded-full"></div>
            </div>

            <div className="max-w-[480px] w-full relative z-10 flex flex-col items-center">
                {/* Visual Identity Logo */}
                <div className="w-24 h-24 bg-indigo-600 rounded-[32px] flex items-center justify-center text-white shadow-[0_20px_40px_rgba(79,70,229,0.3)] mb-10 transform -rotate-3 hover:rotate-0 transition-transform duration-500">
                    <GraduationCap size={48} />
                </div>

                {/* Header Context */}
                <div className="text-center space-y-3 mb-12">
                    <h1 className="text-[44px] font-[900] text-[#1A1C2E] leading-tight tracking-[-0.04em]">
                        School Portal
                    </h1>
                    <p className="text-slate-400 font-bold text-lg max-w-[280px] mx-auto leading-relaxed">
                        Secure gateway to institutional intelligence.
                    </p>
                </div>

                {error && (
                    <div className="w-full mb-8 p-5 bg-rose-50/50 border-[2px] border-rose-100 rounded-[28px] flex items-center gap-4 animate-in slide-in-from-top-4 duration-500">
                        <div className="w-10 h-10 bg-rose-500 rounded-2xl flex items-center justify-center text-white shrink-0 shadow-lg shadow-rose-200">
                            <ShieldAlert size={20} />
                        </div>
                        <p className="text-sm font-black text-rose-600 leading-snug tracking-tight">{error}</p>
                    </div>
                )}

                <form onSubmit={handleSubmit} className="w-full space-y-8">
                    {/* EMIS Code Interface */}
                    <div className="space-y-3">
                        <label className="text-[11px] font-[900] uppercase tracking-[0.14em] text-slate-400 ml-6">
                            EMIS Code
                        </label>
                        <div className="relative group">
                            <div className="absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-600 transition-colors">
                                <Key size={22} />
                            </div>
                            <input 
                                type="text"
                                placeholder="Institutional Identity"
                                name="emisCode"
                                autoComplete="username"
                                className="w-full h-[72px] pl-16 pr-6 bg-white border-[2px] border-slate-100 rounded-[28px] font-bold text-[#1A1C2E] outline-none shadow-sm focus:border-indigo-600 focus:shadow-[0_0_0_10px_rgba(79,70,229,0.04)] transition-all placeholder:text-slate-300"
                                value={formData.emisCode}
                                onChange={(e) => setFormData({...formData, emisCode: e.target.value.trim()})}
                                required
                            />
                        </div>
                    </div>

                    {/* Access Password Interface */}
                    <div className="space-y-3">
                        <label className="text-[11px] font-[900] uppercase tracking-[0.14em] text-slate-400 ml-6">
                            Access Password
                        </label>
                        <div className="relative group">
                            <div className="absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-600 transition-colors">
                                <Lock size={22} />
                            </div>
                            <input 
                                type={showPassword ? "text" : "password"}
                                placeholder="••••••••"
                                name="password"
                                autoComplete="current-password"
                                className="w-full h-[72px] pl-16 pr-16 bg-white border-[2px] border-slate-100 rounded-[28px] font-bold text-[#1A1C2E] outline-none shadow-sm focus:border-indigo-600 focus:shadow-[0_0_0_10px_rgba(79,70,229,0.04)] transition-all placeholder:text-slate-300"
                                value={formData.password}
                                onChange={(e) => setFormData({...formData, password: e.target.value})}
                                required
                            />
                            <button 
                                type="button"
                                onClick={() => setShowPassword(!showPassword)}
                                className="absolute right-6 top-1/2 -translate-y-1/2 text-slate-300 hover:text-indigo-600 transition-colors"
                            >
                                {showPassword ? <EyeOff size={22} /> : <Eye size={22} />}
                            </button>
                        </div>
                    </div>

                    {/* Auxiliary Options */}
                    <div className="flex items-center justify-between px-2">
                        <label className="flex items-center gap-3 cursor-pointer group">
                            <div className="relative">
                                <input 
                                    type="checkbox" 
                                    className="peer sr-only"
                                    checked={formData.remember}
                                    onChange={(e) => setFormData({...formData, remember: e.target.checked})}
                                />
                                <div className="w-6 h-6 border-2 border-slate-200 rounded-lg group-hover:border-indigo-500 transition-all peer-checked:bg-indigo-600 peer-checked:border-indigo-600"></div>
                                <Check className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-white opacity-0 peer-checked:opacity-100 transition-opacity" size={14} strokeWidth={4} />
                            </div>
                            <span className="text-sm font-black text-slate-500 group-hover:text-indigo-600 transition-colors">Remember</span>
                        </label>
                        <Link to="#" className="text-sm font-black text-indigo-600 hover:text-indigo-700 transition-colors">
                            Forgot Key?
                        </Link>
                    </div>

                    {/* Primary Engagement */}
                    <button 
                        type="submit"
                        className="w-full h-[80px] bg-indigo-600 text-white rounded-[32px] font-[900] text-xl shadow-[0_20px_40px_rgba(79,70,229,0.2)] hover:bg-indigo-700 hover:scale-[1.02] active:scale-[0.98] transition-all duration-300 flex items-center justify-center gap-3"
                    >
                        Unlock Portal
                    </button>

                    <Link 
                        to="/admin-login"
                        className="w-full h-[72px] bg-transparent border-[2.5px] border-dashed border-slate-200 rounded-[28px] text-slate-500 font-black flex items-center justify-center gap-3 hover:border-indigo-400 hover:text-indigo-600 hover:bg-indigo-50/10 transition-all cursor-pointer group"
                    >
                        <ShieldCheck size={20} className="group-hover:scale-110 transition-transform" />
                        System Administrator Access
                    </Link>
                </form>

                {/* Footer Orchestration */}
                <div className="mt-16 text-center space-y-4">
                    <p className="text-sm font-black text-slate-400">
                        Institutional Orchestration not yet initiated? 
                    </p>
                    <Link to="/register" className="inline-flex items-center gap-2 px-8 py-3 bg-indigo-50 text-indigo-600 rounded-full font-[900] text-xs uppercase tracking-widest hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                        Initialize Register <ChevronRight size={14} />
                    </Link>
                </div>

                {/* Operational Status */}
                <div className="mt-12 flex items-center gap-3 px-5 py-2.5 bg-slate-50 rounded-full border border-slate-100 backdrop-blur-md">
                    <div className="w-2 h-2 bg-emerald-500 rounded-full animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.4)]"></div>
                    <span className="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                        <Globe size={12} /> Regional Network Status: Optimal
                    </span>
                </div>
            </div>
        </div>
    );
};

export default Login;
