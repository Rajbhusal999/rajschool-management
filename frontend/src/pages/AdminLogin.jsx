import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { 
  Shield, Lock, Mail, Eye, EyeOff, ArrowLeft, 
  Terminal, AlertTriangle, Shield, Activity
} from 'lucide-react';

const AdminLogin = () => {
    const navigate = useNavigate();
    const [showPassword, setShowPassword] = useState(false);
    const [formData, setFormData] = useState({
        adminEmail: '',
        secureKey: '',
        rememberSession: false
    });
    const [error, setError] = useState('');

    const handleSubmit = (e) => {
        e.preventDefault();
        setError('');
        
        // Simulation of high-security authentication
        console.log('Initiating System Level Authentication:', formData.adminEmail);
        
        if (formData.adminEmail === 'smartscchool2082@gmail.com' && formData.secureKey === 'qWERTYUIOP@1234') {
            navigate('/admin/nexus');
        } else {
            setError('ACCESS DENIED: Invalid Administrator Credentials. Security logs recorded.');
        }
    };

    return (
        <div className="min-h-screen bg-[#0F172A] flex items-center justify-center py-20 px-6 font-['Outfit',sans-serif] overflow-hidden">
            {/* Cyberpunk Decorative Elements */}
            <div className="fixed inset-0 pointer-events-none opacity-20">
                <div className="absolute top-[-20%] right-[-10%] w-[600px] h-[600px] bg-indigo-500/10 blur-[150px] rounded-full"></div>
                <div className="absolute bottom-[-20%] left-[-10%] w-[500px] h-[500px] bg-purple-500/10 blur-[120px] rounded-full"></div>
                <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-full bg-[radial-gradient(circle,rgba(30,41,59,0.5)_1px,transparent_1px)] bg-[size:32px_32px]"></div>
            </div>

            <div className="max-w-[480px] w-full relative z-10">
                {/* Visual Identity Logo - Shield based for Admin */}
                <div className="flex justify-center mb-10">
                    <div className="w-24 h-24 bg-gradient-to-tr from-slate-800 to-slate-700 rounded-[32px] flex items-center justify-center text-indigo-400 shadow-2xl border border-slate-700/50 backdrop-blur-xl group hover:scale-105 transition-transform duration-500">
                        <Shield size={48} className="group-hover:animate-pulse" />
                    </div>
                </div>

                {/* Header Context */}
                <div className="text-center space-y-3 mb-12">
                    <div className="inline-flex items-center gap-2 px-3 py-1 bg-indigo-500/10 border border-indigo-500/20 rounded-full text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] mb-4">
                        <Activity size={12} /> Root Level Access
                    </div>
                    <h1 className="text-[40px] font-[900] text-white leading-tight tracking-[-0.04em]">
                        System Nexus
                    </h1>
                    <p className="text-slate-400 font-medium text-lg max-w-[320px] mx-auto leading-relaxed">
                        Authorized personnel only. Encrypted session required.
                    </p>
                </div>

                {error && (
                    <div className="mb-8 p-5 bg-rose-500/10 border border-rose-500/30 rounded-[28px] flex items-center gap-4 animate-in fade-in slide-in-from-top-4 backdrop-blur-md">
                        <AlertTriangle className="text-rose-500 shrink-0" size={24} />
                        <div>
                            <p className="text-[10px] font-black text-rose-500 uppercase tracking-widest mb-1">Security Alert</p>
                            <p className="text-xs font-bold text-slate-300 leading-relaxed uppercase tracking-tight">
                                {error}
                            </p>
                        </div>
                    </div>
                )}

                <form onSubmit={handleSubmit} className="space-y-8 bg-slate-900/50 p-8 rounded-[40px] border border-slate-800 backdrop-blur-2xl shadow-2xl">
                    {/* Admin Email Interface */}
                    <div className="space-y-3">
                        <label className="text-[11px] font-[900] uppercase tracking-[0.14em] text-slate-500 ml-6">
                            Administrator ID
                        </label>
                        <div className="relative group">
                            <div className="absolute left-6 top-1/2 -translate-y-1/2 text-slate-600 group-focus-within:text-indigo-400 transition-colors">
                                <Mail size={22} />
                            </div>
                            <input 
                                type="email"
                                placeholder="root@system.io"
                                className="w-full h-[72px] pl-16 pr-6 bg-slate-800/40 border-[2px] border-slate-700/50 rounded-[28px] font-bold text-white outline-none shadow-sm focus:border-indigo-500/50 focus:bg-slate-800 transition-all placeholder:text-slate-600"
                                value={formData.adminEmail}
                                onChange={(e) => setFormData({...formData, adminEmail: e.target.value})}
                                required
                            />
                        </div>
                    </div>

                    {/* Secure Key Interface */}
                    <div className="space-y-3">
                        <label className="text-[11px] font-[900] uppercase tracking-[0.14em] text-slate-500 ml-6">
                            Master Encryption Key
                        </label>
                        <div className="relative group">
                            <div className="absolute left-6 top-1/2 -translate-y-1/2 text-slate-600 group-focus-within:text-indigo-400 transition-colors">
                                <Shield size={22} />
                            </div>
                            <input 
                                type={showPassword ? "text" : "password"}
                                placeholder="••••••••"
                                className="w-full h-[72px] pl-16 pr-16 bg-slate-800/40 border-[2px] border-slate-700/50 rounded-[28px] font-bold text-white outline-none shadow-sm focus:border-indigo-500/50 focus:bg-slate-800 transition-all placeholder:text-slate-600"
                                value={formData.secureKey}
                                onChange={(e) => setFormData({...formData, secureKey: e.target.value})}
                                required
                            />
                            <button 
                                type="button"
                                onClick={() => setShowPassword(!showPassword)}
                                className="absolute right-6 top-1/2 -translate-y-1/2 text-slate-600 hover:text-indigo-400 transition-colors"
                            >
                                {showPassword ? <EyeOff size={22} /> : <Eye size={22} />}
                            </button>
                        </div>
                    </div>

                    {/* Authentication Engagement */}
                    <button 
                        type="submit"
                        className="w-full h-[80px] bg-indigo-600 text-white rounded-[32px] font-[900] text-xl shadow-[0_20px_40px_rgba(79,70,229,0.2)] hover:bg-indigo-700 hover:scale-[1.02] active:scale-[0.98] transition-all duration-300 flex items-center justify-center gap-3 group"
                    >
                        <Terminal size={24} className="group-hover:translate-x-1 transition-transform" />
                        Initiate Handshake
                    </button>

                    {/* Auxiliary Actions */}
                    <div className="pt-4 text-center">
                        <Link to="/login" className="inline-flex items-center gap-2 text-sm font-black text-slate-500 hover:text-indigo-400 transition-colors group">
                            <ArrowLeft size={16} className="group-hover:-translate-x-1 transition-transform" />
                            Return to Institutional Access
                        </Link>
                    </div>
                </form>

                {/* Secure Status */}
                <div className="mt-12 flex flex-col items-center gap-4">
                    <div className="flex items-center gap-3 px-5 py-2.5 bg-slate-900 border border-slate-800 rounded-full">
                        <AlertTriangle size={14} className="text-amber-500" />
                        <span className="text-[10px] font-black text-slate-500 uppercase tracking-widest">
                            Hardware isolation protocol: ACTIVE
                        </span>
                    </div>
                    <p className="text-[10px] text-slate-600 font-bold uppercase tracking-widest text-center opacity-50">
                        Rajschool Management System • System Kernel v4.2.0-LTS
                    </p>
                </div>
            </div>
        </div>
    );
};

export default AdminLogin;
