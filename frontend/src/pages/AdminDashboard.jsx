import React, { useState, useEffect } from 'react';
import { supabase } from '../supabaseClient';
import { 
    LayoutDashboard, Database, LogOut, Trash2, 
    RefreshCw, Mail, Phone, MapPin, ShieldCheck,
    AlertCircle, Download, CheckCircle2
} from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import '../styles/CyberBackground.css';

const AdminDashboard = () => {
    const navigate = useNavigate();
    const [institutions, setInstitutions] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [actionStatus, setActionStatus] = useState(null);

    useEffect(() => {
        fetchInstitutions();
    }, []);

    const fetchInstitutions = async () => {
        setLoading(true);
        const { data, error } = await supabase
            .from('institutions')
            .select('*')
            .order('created_at', { ascending: false });

        if (error) {
            setError(error.message);
        } else {
            setInstitutions(data);
        }
        setLoading(false);
    };

    const handleGenerateCode = async (id) => {
        const newCode = Math.floor(100000 + Math.random() * 900000).toString();
        const { error } = await supabase
            .from('institutions')
            .update({ verification_code: newCode })
            .eq('id', id);

        if (error) {
            alert('Failed to generate code: ' + error.message);
        } else {
            setInstitutions(prev => prev.map(inst => 
                inst.id === id ? { ...inst, verification_code: newCode } : inst
            ));
            setActionStatus(`Code generated for record ${id}`);
            setTimeout(() => setActionStatus(null), 3000);
        }
    };

    const handleDelete = async (id, name) => {
        if (window.confirm(`Are you sure you want to permanently DELETE ${name}? This action cannot be undone.`)) {
            const { error } = await supabase
                .from('institutions')
                .delete()
                .eq('id', id);

            if (error) {
                alert('Deletion failed: ' + error.message);
            } else {
                setInstitutions(prev => prev.filter(inst => inst.id !== id));
            }
        }
    };

    const handleLogout = () => {
        navigate('/login');
    };

    const getDaysLeft = (expiryDate) => {
        if (!expiryDate) return { text: '--', color: 'text-slate-500' };
        const now = new Date();
        const expiry = new Date(expiryDate);
        const diffTime = expiry - now;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffDays <= 0) return { text: 'EXPIRED', color: 'expiry-badge-expired', isExpired: true };
        return { text: `${diffDays} days`, color: 'expiry-badge-warning', isExpired: false };
    };

    const formatDate = (dateStr) => {
        if (!dateStr) return '--';
        return new Date(dateStr).toISOString().split('T')[0];
    };

    if (loading) {
        return (
            <div className="min-h-screen bg-[#0B0E14] flex items-center justify-center">
                <div className="space-y-4 text-center">
                    <div className="w-16 h-16 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin mx-auto"></div>
                    <p className="text-indigo-400 font-black animate-pulse uppercase tracking-[0.2em]">Synchronizing Command Center...</p>
                </div>
            </div>
        );
    }

    return (
        <div className="min-h-screen relative font-['Outfit',sans-serif]">
            {/* Animated Space Background */}
            <div className="space-background"></div>

            {/* Futuristic Header */}
            <header className="fixed top-0 left-0 right-0 z-50 h-24 command-center-header backdrop-blur-xl px-8 flex items-center justify-between">
                <div className="flex items-center gap-4">
                    <div className="w-12 h-12 bg-[#00D1FF] rounded-xl flex items-center justify-center shadow-[0_0_20px_rgba(0,209,255,0.4)]">
                        <LayoutDashboard className="text-black" size={24} />
                    </div>
                    <h1 className="text-3xl font-[900] neon-text-blue uppercase tracking-tighter">
                        Command <span className="text-white">Center</span>
                    </h1>
                </div>

                <div className="flex items-center gap-6">
                    {actionStatus && (
                        <div className="px-4 py-2 bg-emerald-500/10 border border-emerald-500 rounded-lg flex items-center gap-2 animate-in fade-in slide-in-from-right-4">
                            <CheckCircle2 size={16} className="text-emerald-500" />
                            <span className="text-xs font-black text-emerald-500 font-mono">{actionStatus}</span>
                        </div>
                    )}
                    <button className="flex items-center gap-2 px-6 py-3 bg-[#00E096] text-black rounded-xl font-black text-sm uppercase tracking-widest hover:scale-105 transition-all shadow-[0_0_20px_rgba(0,224,150,0.3)] group">
                        <Download size={18} className="group-hover:translate-y-0.5 transition-transform" />
                        Backup Users Data
                    </button>
                    <button onClick={handleLogout} className="flex items-center gap-2 text-slate-400 hover:text-white font-black uppercase text-sm tracking-widest transition-colors">
                        Logout <LogOut size={18} />
                    </button>
                </div>
            </header>

            {/* Main Content Area */}
            <main className="pt-32 pb-20 px-8">
                {error && (
                    <div className="max-w-4xl mx-auto mb-10 p-6 bg-rose-500/10 border border-rose-500 rounded-2xl flex items-center gap-4">
                        <AlertCircle className="text-rose-500" size={32} />
                        <div>
                            <p className="text-rose-500 font-black uppercase text-sm tracking-widest mb-1">System Override Error</p>
                            <p className="text-slate-300 font-medium">{error}</p>
                        </div>
                    </div>
                )}

                <div className="neon-table rounded-[32px] overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left border-collapse">
                            <thead>
                                <tr className="bg-slate-900/50">
                                    <th className="px-6 py-5 text-[11px] font-black text-slate-500 uppercase tracking-[0.2em] border-b border-slate-800">ID</th>
                                    <th className="px-6 py-5 text-[11px] font-black text-slate-500 uppercase tracking-[0.2em] border-b border-slate-800">School Name</th>
                                    <th className="px-6 py-5 text-[11px] font-black text-slate-500 uppercase tracking-[0.2em] border-b border-slate-800">EMIS Code</th>
                                    <th className="px-6 py-5 text-[11px] font-black text-slate-500 uppercase tracking-[0.2em] border-b border-slate-800">Email</th>
                                    <th className="px-6 py-5 text-[11px] font-black text-slate-500 uppercase tracking-[0.2em] border-b border-slate-800">Contact</th>
                                    <th className="px-6 py-5 text-[11px] font-black text-slate-500 uppercase tracking-[0.2em] border-b border-slate-800 text-center">Status</th>
                                    <th className="px-6 py-5 text-[11px] font-black text-slate-500 uppercase tracking-[0.2em] border-b border-slate-800">Joining Date</th>
                                    <th className="px-6 py-5 text-[11px] font-black text-slate-500 uppercase tracking-[0.2em] border-b border-slate-800 transition-colors">Expiry Date</th>
                                    <th className="px-6 py-5 text-[11px] font-black text-slate-500 uppercase tracking-[0.2em] border-b border-slate-800">Days Left</th>
                                    <th className="px-6 py-5 text-[11px] font-black text-slate-500 uppercase tracking-[0.2em] border-b border-slate-800">Verif. Code</th>
                                    <th className="px-6 py-5 text-[11px] font-black text-slate-500 uppercase tracking-[0.2em] border-b border-slate-800 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-800/50">
                                {institutions.map((inst) => {
                                    const { text, color, isExpired } = getDaysLeft(inst.expiry_date);
                                    return (
                                        <tr key={inst.id} className="hover:bg-indigo-500/5 transition-colors group">
                                            <td className="px-6 py-6 text-sm font-bold text-slate-400">#{inst.id.toString().slice(-4)}</td>
                                            <td className="px-6 py-6">
                                                <p className="text-lg font-[900] text-white tracking-tight">{inst.school_name}</p>
                                                <div className="flex items-center gap-1.5 mt-1">
                                                    <MapPin size={12} className="text-slate-500" />
                                                    <span className="text-[10px] font-bold text-slate-500 uppercase tracking-widest">{inst.address || 'Location Not Set'}</span>
                                                </div>
                                            </td>
                                            <td className="px-6 py-6 font-mono text-sm text-slate-400">{inst.emis_code}</td>
                                            <td className="px-6 py-6">
                                                <a href={`mailto:${inst.email}`} className="text-sm font-bold text-[#00D1FF] hover:underline flex items-center gap-2">
                                                    <Mail size={14} />
                                                    {inst.email}
                                                </a>
                                            </td>
                                            <td className="px-6 py-6">
                                                <a href={`tel:${inst.phone}`} className="text-sm font-bold text-slate-300 flex items-center gap-2">
                                                    <Phone size={14} className="text-slate-500" />
                                                    {inst.phone || '--'}
                                                </a>
                                            </td>
                                            <td className="px-6 py-6 text-center">
                                                <span className="px-3 py-1 rounded-full text-[10px] font-black status-badge-active uppercase tracking-widest">
                                                    {inst.status || 'ACTIVE'}
                                                </span>
                                            </td>
                                            <td className="px-6 py-6 text-sm font-medium text-slate-400">{formatDate(inst.created_at)}</td>
                                            <td className={`px-6 py-6 text-sm font-black ${isExpired ? 'text-rose-500' : 'neon-text-green'}`}>
                                                {formatDate(inst.expiry_date)}
                                            </td>
                                            <td className="px-6 py-6">
                                                <span className={`px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest ${color}`}>
                                                    {text}
                                                </span>
                                            </td>
                                            <td className="px-6 py-6">
                                                <span className="text-lg font-black neon-text-yellow font-mono tracking-[0.1em]">
                                                    {inst.verification_code || '------'}
                                                </span>
                                            </td>
                                            <td className="px-6 py-6 text-right">
                                                <div className="flex items-center justify-end gap-2">
                                                    <button 
                                                        onClick={() => handleGenerateCode(inst.id)}
                                                        className="h-10 px-4 bg-[#00D1FF]/10 border border-[#00D1FF]/30 text-[#00D1FF] rounded-lg font-black text-xs uppercase tracking-widest hover:bg-[#00D1FF] hover:text-black transition-all flex items-center gap-2"
                                                        title="Generate Verification Code"
                                                    >
                                                        <RefreshCw size={14} className="group-hover:rotate-180 transition-transform duration-500" />
                                                        Generate
                                                    </button>
                                                    <button 
                                                        onClick={() => handleDelete(inst.id, inst.school_name)}
                                                        className="h-10 w-10 bg-rose-500/10 border border-rose-500/30 text-rose-500 rounded-lg flex items-center justify-center hover:bg-rose-500 hover:text-white transition-all shadow-sm"
                                                        title="Delete Instance"
                                                    >
                                                        <Trash2 size={18} />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                    {institutions.length === 0 && (
                        <div className="py-20 text-center space-y-4">
                            <ShieldCheck size={64} className="text-slate-800 mx-auto" strokeWidth={1} />
                            <p className="text-slate-500 font-black uppercase tracking-[0.2em]">No Active Systems Found</p>
                        </div>
                    )}
                </div>

                {/* Cyber Decorative Elements */}
                <div className="fixed bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-transparent via-[#00D1FF] to-transparent opacity-20"></div>
            </main>
        </div>
    );
};

export default AdminDashboard;
