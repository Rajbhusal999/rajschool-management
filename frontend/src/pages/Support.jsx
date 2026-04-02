import React, { useState, useEffect, useRef } from 'react';
import { supabase } from '../supabaseClient';
import { 
    MessageCircle, Send, Phone, Shield, 
    Headphones, CheckCircle2, ArrowLeft,
    Clock, ExternalLink, User
} from 'lucide-react';
import { Link } from 'react-router-dom';
import '../styles/CyberBackground.css';

const Support = () => {
    const [messages, setMessages] = useState([]);
    const [newMessage, setNewMessage] = useState('');
    const [loading, setLoading] = useState(false);
    const [isChatOpen, setIsChatOpen] = useState(false);
    const [institution, setInstitution] = useState(null);
    const [loginPhone, setLoginPhone] = useState('');
    const [isLoginView, setIsLoginView] = useState(false);
    const [loginError, setLoginError] = useState('');
    const scrollRef = useRef(null);

    const WHATSAPP_NUMBER = '+9779706829056';

    useEffect(() => {
        const fetchSavedSupportSession = async () => {
            const supportSession = sessionStorage.getItem('support_active_session');
            if (supportSession) {
                const data = JSON.parse(supportSession);
                setInstitution(data);
                fetchMessages(data.id);
                subscribeToMessages(data.id);
            }
        };
        fetchSavedSupportSession();
    }, []);

    useEffect(() => {
        if (scrollRef.current) {
            scrollRef.current.scrollTop = scrollRef.current.scrollHeight;
        }
    }, [messages, isChatOpen]);

    const fetchMessages = async (instId) => {
        const { data, error } = await supabase
            .from('support_messages')
            .select('*')
            .eq('institution_id', instId)
            .order('created_at', { ascending: true });

        if (!error && data) {
            setMessages(data);
        }
    };

    const subscribeToMessages = (instId) => {
        const channel = supabase
            .channel(`support_chat_${instId}`)
            .on(
                'postgres_changes',
                {
                    event: 'INSERT',
                    schema: 'public',
                    table: 'support_messages',
                    filter: `institution_id=eq.${instId}`
                },
                (payload) => {
                    setMessages(prev => [...prev, payload.new]);
                }
            )
            .subscribe();

        return () => supabase.removeChannel(channel);
    };

    const handleSendMessage = async (e) => {
        e.preventDefault();
        if (!newMessage.trim() || !institution) return;

        setLoading(true);
        const { error } = await supabase
            .from('support_messages')
            .insert([{
                institution_id: institution.id,
                message: newMessage.trim(),
                sender_name: institution.school_name,
                is_from_admin: false
            }]);

        if (error) {
            alert('Failed to send message: ' + error.message);
        } else {
            setNewMessage('');
        }
        setLoading(false);
    };

    const openWhatsApp = () => {
        const url = `https://wa.me/${WHATSAPP_NUMBER.replace('+', '')}`;
        window.open(url, '_blank');
    };

    const handlePhoneLogin = async (e) => {
        e.preventDefault();
        if (!loginPhone.trim()) return;

        setLoading(true);
        setLoginError('');
        
        const { data, error } = await supabase
            .from('institutions')
            .select('*')
            .eq('phone', loginPhone.trim().replace(/\s+/g, ''))
            .single();

        if (error || !data) {
            setLoginError('Institution not found with this phone number. Please register first or use the registered number.');
        } else {
            setInstitution(data);
            setIsLoginView(false);
            fetchMessages(data.id);
            subscribeToMessages(data.id);
            // Optional: persistence for the session
            sessionStorage.setItem('support_active_session', JSON.stringify(data));
        }
        setLoading(false);
    };

    const handleChatButtonClick = () => {
        const savedSession = sessionStorage.getItem('support_active_session');
        if (savedSession) {
            const data = JSON.parse(savedSession);
            setInstitution(data);
            fetchMessages(data.id);
            subscribeToMessages(data.id);
            setIsChatOpen(true);
            setIsLoginView(false);
        } else {
            setIsChatOpen(true);
            setIsLoginView(true);
        }
    };

    return (
        <div className="min-h-screen relative py-24 px-8 font-['Outfit',sans-serif]">
            <div className="space-background"></div>

            <div className="max-w-4xl mx-auto relative z-10">
                {/* Header */}
                <div className="flex items-center justify-between mb-12">
                    <Link to="/" className="inline-flex items-center gap-2 text-slate-400 hover:text-white font-black text-xs uppercase tracking-widest transition-colors group">
                        <ArrowLeft size={16} className="group-hover:-translate-x-1 transition-transform" /> Back to Home
                    </Link>
                    <div className="flex items-center gap-3 bg-indigo-500/10 px-4 py-2 rounded-full border border-indigo-500/20">
                        <div className="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                        <span className="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Support Online</span>
                    </div>
                </div>

                <div className="text-center mb-16 space-y-4">
                    <h1 className="text-5xl md:text-6xl font-[1000] text-white tracking-tighter drop-shadow-2xl">
                        Help & <span className="text-indigo-500">Support</span>
                    </h1>
                    <p className="text-lg font-bold text-slate-400 max-w-xl mx-auto leading-relaxed">
                        Need assistance? Our team is available via direct chat or WhatsApp to resolve your queries.
                    </p>
                </div>

                {/* Support Cards */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-8 mb-16">
                    {/* WhatsApp Card */}
                    <button 
                        onClick={openWhatsApp}
                        className="group relative bg-[#128C7E]/10 backdrop-blur-3xl border border-[#128C7E]/20 p-10 rounded-[40px] text-left hover:bg-[#128C7E]/20 transition-all duration-500 hover:scale-[1.02] shadow-2xl"
                    >
                        <div className="w-16 h-16 bg-[#25D366] rounded-2xl flex items-center justify-center mb-8 shadow-lg shadow-[#25D366]/30 group-hover:rotate-12 transition-transform">
                            <Phone className="text-white" size={32} />
                        </div>
                        <h3 className="text-2xl font-black text-white mb-4">WhatsApp Support</h3>
                        <p className="text-slate-400 font-bold mb-8 leading-relaxed">
                            Connect with us directly on WhatsApp for instant replies and media sharing.
                        </p>
                        <div className="flex items-center gap-3 text-[#25D366] font-black text-xs uppercase tracking-[0.2em]">
                            Chat on WhatsApp <ExternalLink size={16} />
                        </div>
                    </button>

                    {/* Admin Chat Card */}
                    <button 
                        onClick={handleChatButtonClick}
                        className="group relative bg-indigo-500/10 backdrop-blur-3xl border border-indigo-500/20 p-10 rounded-[40px] text-left hover:bg-indigo-500/20 transition-all duration-500 hover:scale-[1.02] shadow-2xl"
                    >
                        <div className="w-16 h-16 bg-indigo-600 rounded-2xl flex items-center justify-center mb-8 shadow-lg shadow-indigo-600/30 group-hover:-rotate-12 transition-transform">
                            <MessageCircle className="text-white" size={32} />
                        </div>
                        <h3 className="text-2xl font-black text-white mb-4">Admin Dashboard Chat</h3>
                        <p className="text-slate-400 font-bold mb-8 leading-relaxed">
                            Open a secure support ticket and chat directly with our technical team.
                        </p>
                        <div className="flex items-center gap-3 text-indigo-400 font-black text-xs uppercase tracking-[0.2em]">
                            Chat with Admin Panel <Send size={16} />
                        </div>
                    </button>
                </div>

                {/* Trust Indicators */}
                <div className="flex flex-wrap items-center justify-center gap-8 opacity-40">
                    <div className="flex items-center gap-2 text-white font-black text-[10px] uppercase tracking-widest">
                        <Shield className="text-indigo-400" size={18} /> End-to-End Encryption
                    </div>
                    <div className="w-1.5 h-1.5 bg-slate-700 rounded-full"></div>
                    <div className="flex items-center gap-2 text-white font-black text-[10px] uppercase tracking-widest">
                        <Headphones className="text-emerald-400" size={18} /> 24/7 Priority Tickets
                    </div>
                    <div className="w-1.5 h-1.5 bg-slate-700 rounded-full"></div>
                    <div className="flex items-center gap-2 text-white font-black text-[10px] uppercase tracking-widest">
                        <CheckCircle2 className="text-orange-400" size={18} /> Verified Support Team
                    </div>
                </div>
            </div>

            {/* Support Chat Sidebar/Modal */}
            {isChatOpen && (
                <div className="fixed inset-0 z-[100] flex items-center justify-end p-4 md:p-8 animate-in fade-in duration-300">
                    <div className="absolute inset-0 bg-black/60 backdrop-blur-sm" onClick={() => setIsChatOpen(false)}></div>
                    
                    <div className="relative w-full max-w-lg h-[85vh] bg-[#0B0F17] rounded-[40px] border border-white/10 shadow-3xl overflow-hidden flex flex-col animate-in slide-in-from-right-10 duration-500">
                        {/* Chat Header */}
                        <div className="p-8 bg-slate-900/50 border-b border-white/5 flex items-center justify-between">
                            <div className="flex items-center gap-4">
                                <div className="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-600/20">
                                    <Shield className="text-white" size={24} />
                                </div>
                                <div>
                                    <h4 className="text-white font-black text-lg">System Admin</h4>
                                    <p className="text-emerald-400 text-[10px] font-black uppercase tracking-widest flex items-center gap-1">
                                        <span className="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span> Active Online
                                    </p>
                                </div>
                            </div>
                            <button 
                                onClick={() => setIsChatOpen(false)}
                                className="p-3 bg-white/5 hover:bg-white/10 rounded-2xl text-slate-400 hover:text-white transition-all"
                            >
                                <ArrowLeft size={20} />
                            </button>
                        </div>

                        {/* Messages Area / Login View */}
                        <div 
                            ref={scrollRef}
                            className="flex-1 overflow-y-auto p-8 space-y-6 scrollbar-hide"
                        >
                            {isLoginView ? (
                                <div className="h-full flex flex-col items-center justify-center text-center space-y-8 px-12">
                                    <div className="w-20 h-20 bg-indigo-500/10 rounded-[32px] flex items-center justify-center border border-indigo-500/20 shadow-2xl shadow-indigo-500/10">
                                        <Phone className="text-indigo-500" size={32} />
                                    </div>
                                    <div className="space-y-3">
                                        <h3 className="text-white font-[1000] text-xl uppercase tracking-tighter">Identity Verification</h3>
                                        <p className="text-slate-500 text-xs font-bold leading-relaxed">
                                            Enter your registered phone number to establish a secure link with the system administrator.
                                        </p>
                                    </div>

                                    <form onSubmit={handlePhoneLogin} className="w-full space-y-4">
                                        <div className="relative">
                                            <Phone className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500" size={18} />
                                            <input 
                                                type="tel"
                                                value={loginPhone}
                                                onChange={(e) => setLoginPhone(e.target.value)}
                                                placeholder="Enter Registered Phone Number"
                                                className="w-full h-14 bg-white/5 border border-white/10 rounded-2xl pl-12 pr-6 text-white font-bold text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all"
                                                required
                                            />
                                        </div>
                                        {loginError && <p className="text-rose-500 text-[10px] font-black uppercase tracking-widest">{loginError}</p>}
                                        <button 
                                            disabled={loading}
                                            className="w-full h-14 bg-indigo-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:scale-105 transition-all shadow-xl shadow-indigo-600/20 disabled:opacity-50"
                                        >
                                            {loading ? 'Decrypting Access...' : 'Open Secure Channel'}
                                        </button>
                                    </form>

                                    <p className="text-[10px] font-black text-slate-700 uppercase tracking-widest">
                                        Access is restricted to verified institutions only
                                    </p>
                                </div>
                            ) : !institution ? (
                                <div className="h-full flex flex-col items-center justify-center text-center space-y-6 px-12">
                                    <div className="w-20 h-20 bg-rose-500/10 rounded-[32px] flex items-center justify-center border border-rose-500/20">
                                        <Shield className="text-rose-500" size={40} />
                                    </div>
                                    <div className="space-y-2">
                                        <p className="text-white font-black uppercase text-sm tracking-widest">Authentication Required</p>
                                        <p className="text-slate-500 text-xs font-bold leading-relaxed">
                                            Please verify your phone number to use the direct dashboard chat system.
                                        </p>
                                    </div>
                                    <button 
                                        onClick={() => setIsLoginView(true)}
                                        className="px-8 py-3 bg-indigo-600 text-white rounded-xl font-black text-xs uppercase tracking-widest hover:scale-105 transition-all"
                                    >
                                        Back to Login
                                    </button>
                                </div>
                            ) : messages.length === 0 ? (
                                <div className="h-full flex flex-col items-center justify-center text-center space-y-6 px-12 opacity-50">
                                    <div className="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center">
                                        <MessageCircle className="text-slate-600" size={32} />
                                    </div>
                                    <p className="text-slate-500 font-bold text-sm italic">
                                        No messages yet. Send a message to start a conversation with the system administrator.
                                    </p>
                                </div>
                            ) : (
                                messages.map((m, idx) => (
                                    <div 
                                        key={idx}
                                        className={`flex ${m.is_from_admin ? 'justify-start' : 'justify-end'}`}
                                    >
                                        <div className={`max-w-[85%] space-y-2`}>
                                            <div className={`p-5 rounded-[24px] text-sm font-bold shadow-xl leading-relaxed
                                                ${m.is_from_admin 
                                                    ? 'bg-slate-800 text-white rounded-tl-none border border-white/5' 
                                                    : 'bg-indigo-600 text-white rounded-br-none shadow-indigo-600/20'}`}
                                            >
                                                {m.message}
                                            </div>
                                            <div className={`flex items-center gap-2 text-[9px] font-black uppercase tracking-widest text-slate-500
                                                ${m.is_from_admin ? 'justify-start' : 'justify-end'}`}
                                            >
                                                {m.is_from_admin ? <Shield size={10} className="text-indigo-400" /> : <User size={10} />}
                                                {m.is_from_admin ? 'System Admin' : institution.school_name}
                                                <span className="mx-1 opacity-20">•</span>
                                                <Clock size={10} /> {new Date(m.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                                            </div>
                                        </div>
                                    </div>
                                ))
                            )}
                        </div>

                        {/* Input Area */}
                        {institution && !isLoginView && (
                            <div className="p-8 bg-slate-900/30 border-t border-white/5">
                                <form onSubmit={handleSendMessage} className="relative">
                                    <input 
                                        type="text"
                                        value={newMessage}
                                        onChange={(e) => setNewMessage(e.target.value)}
                                        placeholder="Type your message to system administrator..."
                                        className="w-full h-16 bg-white/5 border border-white/10 rounded-2xl px-6 pr-20 text-white font-bold text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all"
                                    />
                                    <button 
                                        disabled={loading || !newMessage.trim()}
                                        className="absolute right-2 top-2 h-12 px-6 bg-indigo-600 text-white rounded-xl font-black text-xs uppercase tracking-widest disabled:opacity-50 disabled:bg-slate-700 transition-all flex items-center gap-2 shadow-lg shadow-indigo-600/20"
                                    >
                                        <Send size={16} /> Send
                                    </button>
                                </form>
                            </div>
                        )}
                    </div>
                </div>
            )}
        </div>
    );
};

export default Support;
