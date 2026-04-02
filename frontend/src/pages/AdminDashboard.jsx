import React, { useState, useEffect, useRef } from 'react';
import { supabase } from '../supabaseClient';
import { 
    LayoutDashboard, Database, LogOut, Trash2, 
    RefreshCw, Mail, Phone, MapPin, Shield,
    AlertCircle, Download, CheckCircle, MessageCircle,
    ChevronRight, Send
} from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import '../styles/CyberBackground.css';

const AdminDashboard = () => {
    const navigate = useNavigate();
    const [institutions, setInstitutions] = useState([]);
    const [subscriptions, setSubscriptions] = useState([]);
    const [loading, setLoading] = useState(true);
    const [activeTab, setActiveTab] = useState('institutions'); // 'institutions', 'subscriptions', 'support'
    const [supportChats, setSupportChats] = useState([]);
    const [selectedChat, setSelectedChat] = useState(null);
    const [adminMessage, setAdminMessage] = useState('');
    const [chatMessages, setChatMessages] = useState([]);
    const chatEndRef = useRef(null);
    const [error, setError] = useState(null);
    const [actionStatus, setActionStatus] = useState(null);
    const [selectedScreenshot, setSelectedScreenshot] = useState(null);

    useEffect(() => {
        fetchInstitutions();
        fetchSubscriptions();
        fetchSupportChats();
    }, []);

    useEffect(() => {
        if (chatEndRef.current) {
            chatEndRef.current.scrollIntoView({ behavior: 'smooth' });
        }
    }, [chatMessages]);

    useEffect(() => {
        if (selectedChat) {
            fetchChatMessages(selectedChat.institution_id);
            const channel = subscribeToChat(selectedChat.institution_id);
            return () => supabase.removeChannel(channel);
        }
    }, [selectedChat]);

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

    const fetchSubscriptions = async () => {
        const { data, error } = await supabase
            .from('subscriptions')
            .select(`
                *,
                institutions (
                    school_name,
                    email
                )
            `)
            .order('created_at', { ascending: false });

        if (error) {
            console.error('Error fetching subscriptions:', error);
        } else {
            setSubscriptions(data);
        }
    };

    const fetchSupportChats = async () => {
        const { data, error } = await supabase
            .from('support_messages')
            .select(`
                institution_id,
                sender_name,
                created_at,
                institutions(phone)
            `)
            .order('created_at', { ascending: false });

        if (!error && data) {
            const unique = [];
            const ids = new Set();
            data.forEach(item => {
                if (!ids.has(item.institution_id)) {
                    unique.push(item);
                    ids.add(item.institution_id);
                }
            });
            setSupportChats(unique);
        }
    };

    const fetchChatMessages = async (instId) => {
        const { data, error } = await supabase
            .from('support_messages')
            .select('*')
            .eq('institution_id', instId)
            .order('created_at', { ascending: true });

        if (!error && data) {
            setChatMessages(data);
        }
    };

    const subscribeToChat = (instId) => {
        return supabase
            .channel(`admin_chat_${instId}`)
            .on(
                'postgres_changes',
                {
                    event: 'INSERT',
                    schema: 'public',
                    table: 'support_messages',
                    filter: `institution_id=eq.${instId}`
                },
                (payload) => {
                    setChatMessages(prev => [...prev, payload.new]);
                    fetchSupportChats();
                }
            )
            .subscribe();
    };

    const handleSendAdminMessage = async (e) => {
        e.preventDefault();
        if (!adminMessage.trim() || !selectedChat) return;

        const { error } = await supabase
            .from('support_messages')
            .insert([{
                institution_id: selectedChat.institution_id,
                message: adminMessage.trim(),
                sender_name: 'System Admin',
                is_from_admin: true
            }]);

        if (error) {
            alert('Failed to send reply: ' + error.message);
        } else {
            setAdminMessage('');
        }
    };

    const handleApproveSubscription = async (sub) => {
        const durationYears = sub.plan_name.includes('2 Years') ? 2 : sub.plan_name.includes('5 Years') ? 5 : 1;
        const newExpiry = new Date();
        newExpiry.setFullYear(newExpiry.getFullYear() + durationYears);

        const { error: subError } = await supabase
            .from('subscriptions')
            .update({ status: 'ACTIVE' })
            .eq('id', sub.id);

        if (subError) return alert(subError.message);

        const { error: instError } = await supabase
            .from('institutions')
            .update({ 
                expiry_date: newExpiry.toISOString(),
                status: 'ACTIVE'
            })
            .eq('id', sub.institution_id);

        if (instError) return alert(instError.message);

        setActionStatus(`Subscription activated for ${sub.institutions?.school_name}`);
        fetchSubscriptions();
        fetchInstitutions();
        setTimeout(() => setActionStatus(null), 3000);
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
            <div className="space-background"></div>

            <header className="fixed top-0 left-0 right-0 z-50 h-24 command-center-header backdrop-blur-xl px-8 flex items-center justify-between">
                <div className="flex items-center gap-8">
                    <div className="flex items-center gap-4">
                        <div className="w-12 h-12 bg-[#00D1FF] rounded-xl flex items-center justify-center shadow-[0_0_20px_rgba(0,209,255,0.4)]">
                            <LayoutDashboard className="text-black" size={24} />
                        </div>
                        <h1 className="text-3xl font-[900] neon-text-blue uppercase tracking-tighter">
                            Command <span className="text-white">Center</span>
                        </h1>
                    </div>

                    <div className="h-10 w-px bg-slate-800 mx-2"></div>

                    <nav className="flex items-center gap-2 bg-slate-900/50 p-1.5 rounded-2xl border border-white/5">
                        <button 
                            onClick={() => setActiveTab('institutions')}
                            className={`px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all
                                ${activeTab === 'institutions' ? 'bg-[#00D1FF] text-black shadow-lg shadow-[#00D1FF]/20' : 'text-slate-500 hover:text-white'}`}
                        >
                            System Oversight
                        </button>
                        <button 
                            onClick={() => setActiveTab('subscriptions')}
                            className={`px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all
                                ${activeTab === 'subscriptions' ? 'bg-[#00D1FF] text-black shadow-lg shadow-[#00D1FF]/20' : 'text-slate-500 hover:text-white'}`}
                        >
                            Transaction Approvals
                        </button>
                        <button 
                            onClick={() => setActiveTab('support')}
                            className={`px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all
                                ${activeTab === 'support' ? 'bg-[#00D1FF] text-black shadow-lg shadow-[#00D1FF]/20' : 'text-slate-500 hover:text-white'}`}
                        >
                            Customer Support
                        </button>
                    </nav>
                </div>

                <div className="flex items-center gap-6">
                    {actionStatus && (
                        <div className="px-4 py-2 bg-emerald-500/10 border border-emerald-500 rounded-lg flex items-center gap-2 animate-in fade-in slide-in-from-right-4">
                            <CheckCircle size={16} className="text-emerald-500" />
                            <span className="text-xs font-black text-emerald-500 font-mono">{actionStatus}</span>
                        </div>
                    )}
                    <button className="flex items-center gap-2 px-6 py-3 bg-[#00E096] text-black rounded-xl font-black text-sm uppercase tracking-widest hover:scale-105 transition-all shadow-[0_0_20px_rgba(0,224,150,0.3)] group text-xs">
                        <Download size={16} className="group-hover:translate-y-0.5 transition-transform" />
                        Backup Users Data
                    </button>
                    <button onClick={handleLogout} className="flex items-center gap-2 text-slate-400 hover:text-white font-black uppercase text-xs tracking-widest transition-colors">
                        Logout <LogOut size={16} />
                    </button>
                </div>
            </header>

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

                <div className="neon-table rounded-[32px] overflow-hidden bg-slate-900/20 backdrop-blur-md">
                    <div className="overflow-x-auto">
                        {activeTab === 'institutions' ? (
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
                                                <td className="px-6 py-6 font-bold text-white">{inst.school_name}</td>
                                                <td className="px-6 py-6 font-mono text-xs text-slate-400">{inst.emis_code}</td>
                                                <td className="px-6 py-6 text-sm text-slate-300">{inst.email}</td>
                                                <td className="px-6 py-6 text-sm text-slate-400">{inst.phone || '--'}</td>
                                                <td className="px-6 py-6 text-center">
                                                    <span className={`px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest ${isExpired ? 'bg-rose-500/10 text-rose-500' : 'bg-emerald-500/10 text-emerald-500'}`}>
                                                        {inst.status || 'ACTIVE'}
                                                    </span>
                                                </td>
                                                <td className="px-6 py-6 text-sm text-slate-400">{formatDate(inst.created_at)}</td>
                                                <td className="px-6 py-6 text-sm font-black text-indigo-400">{formatDate(inst.expiry_date)}</td>
                                                <td className="px-6 py-6"><span className={`px-3 py-1 rounded-lg text-[10px] font-black ${color}`}>{text}</span></td>
                                                <td className="px-6 py-6 font-mono font-black text-orange-400 tracking-widest">{inst.verification_code || '------'}</td>
                                                <td className="px-6 py-6 text-right">
                                                    <div className="flex items-center justify-end gap-2">
                                                        <button onClick={() => handleGenerateCode(inst.id)} className="p-2 bg-indigo-500/10 text-indigo-400 rounded-lg hover:bg-indigo-500 hover:text-white transition-all"><RefreshCw size={16} /></button>
                                                        <button onClick={() => handleDelete(inst.id, inst.school_name)} className="p-2 bg-rose-500/10 text-rose-500 rounded-lg hover:bg-rose-500 hover:text-white transition-all"><Trash2 size={16} /></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        ) : activeTab === 'subscriptions' ? (
                            <table className="w-full text-left border-collapse">
                                <thead>
                                    <tr className="bg-slate-900/50">
                                        <th className="px-6 py-5 text-[11px] font-black text-slate-500 uppercase tracking-[0.2em] border-b border-slate-800">School / Client</th>
                                        <th className="px-6 py-5 text-[11px] font-black text-slate-500 uppercase tracking-[0.2em] border-b border-slate-800">Plan Selected</th>
                                        <th className="px-6 py-5 text-[11px] font-black text-slate-500 uppercase tracking-[0.2em] border-b border-slate-800">Amount</th>
                                        <th className="px-6 py-5 text-[11px] font-black text-slate-500 uppercase tracking-[0.2em] border-b border-slate-800 text-center">Gateway</th>
                                        <th className="px-6 py-5 text-[11px] font-black text-slate-500 uppercase tracking-[0.2em] border-b border-slate-800">Transaction ID</th>
                                        <th className="px-6 py-5 text-[11px] font-black text-slate-500 uppercase tracking-[0.2em] border-b border-slate-800 text-center">Proof</th>
                                        <th className="px-6 py-5 text-[11px] font-black text-slate-500 uppercase tracking-[0.2em] border-b border-slate-800">Date Sent</th>
                                        <th className="px-6 py-5 text-[11px] font-black text-slate-500 uppercase tracking-[0.2em] border-b border-slate-800 text-center">Status</th>
                                        <th className="px-6 py-5 text-[11px] font-black text-slate-500 uppercase tracking-[0.2em] border-b border-slate-800 text-right">Auth Actions</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-800/50">
                                    {subscriptions.map((sub) => (
                                        <tr key={sub.id} className="hover:bg-indigo-500/5 transition-colors group">
                                            <td className="px-6 py-6">
                                                <p className="font-black text-white">{sub.institutions?.school_name}</p>
                                                <p className="text-[10px] font-bold text-slate-500 uppercase tracking-widest">{sub.institutions?.email}</p>
                                            </td>
                                            <td className="px-6 py-6 font-bold text-indigo-400 text-sm tracking-tight">{sub.plan_name}</td>
                                            <td className="px-6 py-6 font-[900] text-emerald-400 text-sm">Rs. {sub.amount?.toLocaleString()}</td>
                                            <td className="px-6 py-6 text-center">
                                                <span className={`px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest ${sub.payment_method === 'eSewa' ? 'bg-[#60BB46]/10 text-[#60BB46]' : 'bg-[#00D1FF]/10 text-[#00D1FF]'}`}>
                                                    {sub.payment_method}
                                                </span>
                                            </td>
                                            <td className="px-6 py-6 font-mono text-sm text-slate-300 tracking-widest">{sub.transaction_code}</td>
                                            <td className="px-6 py-6 text-center">
                                                {sub.screenshot_url ? (
                                                    <button 
                                                        onClick={() => setSelectedScreenshot(sub.screenshot_url)}
                                                        className="w-10 h-10 rounded-lg overflow-hidden border border-white/10 hover:border-indigo-500 transition-all bg-black/40"
                                                    >
                                                        <img src={sub.screenshot_url} alt="Proof" className="w-full h-full object-cover opacity-60 hover:opacity-100 transition-all" />
                                                    </button>
                                                ) : (
                                                    <span className="text-[10px] font-bold text-slate-600">NO PROOF</span>
                                                )}
                                            </td>
                                            <td className="px-6 py-6 text-xs font-bold text-slate-500">{formatDate(sub.created_at)}</td>
                                            <td className="px-6 py-6 text-center">
                                                <span className={`px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest 
                                                    ${sub.status === 'ACTIVE' ? 'bg-emerald-500/10 text-emerald-500 shadow-[0_0_15px_rgba(16,185,129,0.1)]' : 'bg-orange-500/10 text-orange-500 animate-pulse-slow'}`}>
                                                    {sub.status || 'PENDING'}
                                                </span>
                                            </td>
                                            <td className="px-6 py-6 text-right">
                                                {sub.status === 'PENDING' && (
                                                    <button 
                                                        onClick={() => handleApproveSubscription(sub)}
                                                        className="h-10 px-6 bg-emerald-600 text-white rounded-xl font-black text-[10px] uppercase tracking-[0.2em] shadow-lg shadow-emerald-500/20 hover:bg-emerald-500 hover:scale-105 transition-all flex items-center gap-2"
                                                    >
                                                        <Shield size={14} /> Approve
                                                    </button>
                                                )}
                                                {sub.status === 'ACTIVE' && (
                                                    <div className="flex items-center justify-end gap-2 text-emerald-500 font-black text-[10px] uppercase tracking-widest opacity-60">
                                                        <CheckCircle size={14} /> Full Access Granted
                                                    </div>
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        ) : (
                            <div className="flex bg-slate-950/20 h-[70vh]">
                                <div className="w-80 border-r border-white/5 flex flex-col">
                                    <div className="p-6 border-b border-white/5">
                                        <h3 className="text-white font-black uppercase tracking-widest text-[10px]">Active Channels</h3>
                                    </div>
                                    <div className="flex-1 overflow-y-auto">
                                        {supportChats.length === 0 ? (
                                            <div className="p-10 text-center opacity-20">
                                                <Mail size={32} className="mx-auto mb-4" />
                                                <p className="text-[10px] font-black uppercase tracking-widest">No Incoming Signals</p>
                                            </div>
                                        ) : (
                                            supportChats.map(chat => (
                                                <button 
                                                    key={chat.institution_id}
                                                    onClick={() => setSelectedChat(chat)}
                                                    className={`w-full p-6 text-left border-b border-white/5 transition-all
                                                        ${selectedChat?.institution_id === chat.institution_id ? 'bg-[#00D1FF]/10 border-r-2 border-r-[#00D1FF]' : 'hover:bg-white/5'}`}
                                                >
                                                    <p className="text-white font-bold text-sm truncate">{chat.institutions?.phone || 'N/A'}</p>
                                                    <div className="flex items-center justify-between mt-1">
                                                        <p className="text-[9px] font-black text-slate-500 uppercase tracking-widest">
                                                            Anonymous User
                                                        </p>
                                                        <p className="text-[9px] font-black text-slate-700 uppercase tracking-widest">
                                                            {new Date(chat.created_at).toLocaleDateString()}
                                                        </p>
                                                    </div>
                                                </button>
                                            ))
                                        )}
                                    </div>
                                </div>

                                <div className="flex-1 flex flex-col bg-slate-900/10 backdrop-blur-3xl">
                                    {selectedChat ? (
                                        <>
                                            <div className="p-6 border-b border-white/5 flex items-center justify-between">
                                                <div className="flex items-center gap-4">
                                                    <div className="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center text-white font-black uppercase text-xs">
                                                        AN
                                                    </div>
                                                    <div>
                                                        <h4 className="text-white font-black text-sm">{chat.institutions?.phone || 'N/A'}</h4>
                                                        <p className="text-[9px] font-black text-emerald-400 uppercase tracking-widest">Anonymous User • Active Comm-Link</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div className="flex-1 overflow-y-auto p-8 space-y-6">
                                                {chatMessages.map((m, idx) => (
                                                    <div key={idx} className={`flex ${m.is_from_admin ? 'justify-end' : 'justify-start'}`}>
                                                        <div className={`max-w-[70%] p-4 rounded-2xl text-xs font-bold
                                                            ${m.is_from_admin 
                                                                ? 'bg-[#00D1FF] text-black rounded-tr-none shadow-lg shadow-[#00D1FF]/20' 
                                                                : 'bg-slate-800 text-white rounded-tl-none border border-white/5'}`}>
                                                            {m.message}
                                                        </div>
                                                    </div>
                                                ))}
                                                <div ref={chatEndRef}></div>
                                            </div>

                                            <div className="p-6 bg-slate-950/40 border-t border-white/5">
                                                <form onSubmit={handleSendAdminMessage} className="relative">
                                                    <input 
                                                        type="text"
                                                        value={adminMessage}
                                                        onChange={(e) => setAdminMessage(e.target.value)}
                                                        placeholder="Transmit response to school..."
                                                        className="w-full h-14 bg-white/5 border border-white/10 rounded-xl px-6 text-white text-xs font-bold focus:outline-none focus:border-[#00D1FF] transition-all"
                                                    />
                                                    <button 
                                                        type="submit"
                                                        disabled={!adminMessage.trim()}
                                                        className="absolute right-2 top-2 h-10 px-6 bg-[#00D1FF] text-black rounded-lg font-black text-[10px] uppercase tracking-widest disabled:opacity-50 transition-all hover:scale-105"
                                                    >
                                                        Send Signal
                                                    </button>
                                                </form>
                                            </div>
                                        </>
                                    ) : (
                                        <div className="flex-1 flex flex-col items-center justify-center text-slate-700 space-y-4">
                                            <Shield size={64} strokeWidth={1} className="opacity-10" />
                                            <p className="text-[10px] font-black uppercase tracking-[0.4em]">Select a Channel to Start Decryption</p>
                                        </div>
                                    )}
                                </div>
                            </div>
                        )}
                    </div>

                    {((activeTab === 'institutions' && institutions.length === 0) || (activeTab === 'subscriptions' && subscriptions.length === 0)) && (
                        <div className="py-24 text-center space-y-4">
                            <Shield size={64} className="text-slate-800 mx-auto opacity-20" strokeWidth={1} />
                            <p className="text-slate-600 font-black uppercase tracking-[0.3em] text-xs">No Records Found in Current Channel</p>
                        </div>
                    )}
                </div>

                <div className="fixed bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-transparent via-[#00D1FF] to-transparent opacity-20"></div>

                {selectedScreenshot && (
                    <div 
                        className="fixed inset-0 z-[100] bg-black/90 backdrop-blur-sm flex items-center justify-center p-8 animate-in fade-in duration-300"
                        onClick={() => setSelectedScreenshot(null)}
                    >
                        <div className="relative max-w-4xl w-full max-h-[90vh] flex flex-col items-center gap-6">
                            <img 
                                src={selectedScreenshot} 
                                alt="Payment Proof Full" 
                                className="w-full h-full object-contain rounded-2xl shadow-2xl border border-white/10"
                            />
                            <button 
                                onClick={() => setSelectedScreenshot(null)}
                                className="px-8 py-3 bg-white/10 hover:bg-white/20 text-white rounded-xl font-black text-[10px] uppercase tracking-[0.3em] border border-white/10 transition-all"
                            >
                                Close Handshake Visual
                            </button>
                        </div>
                    </div>
                )}
            </main>
        </div>
    );
};

export default AdminDashboard;
