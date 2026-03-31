import React, { useState, useEffect } from 'react';
import { institutionService, notificationService } from '../services/api';
import { 
  Settings, Key, Phone, 
  CheckCircle, AlertCircle, Save, 
  Send, Shield, HelpCircle 
} from 'lucide-react';

const SmsSettings = () => {
    const [config, setConfig] = useState({
        smsProvider: 'SPARROW',
        smsToken: '',
        smsIdentity: 'SmartSchool'
    });
    const [loading, setLoading] = useState(false);
    const [testPhone, setTestPhone] = useState('');
    const [msg, setMsg] = useState({ type: '', text: '' });

    useEffect(() => {
        fetchConfig();
    }, []);

    const fetchConfig = async () => {
        const { data } = await institutionService.get();
        if (data) {
            setConfig({
                smsProvider: data.smsProvider || 'SPARROW',
                smsToken: data.smsToken || '',
                smsIdentity: data.smsIdentity || 'SmartSchool'
            });
        }
    };

    const handleSave = async (e) => {
        e.preventDefault();
        setLoading(true);
        try {
            await institutionService.update(config);
            setMsg({ type: 'success', text: 'Gateway configuration updated successfully!' });
            setTimeout(() => setMsg({ type: '', text: '' }), 5000);
        } catch (err) {
            setMsg({ type: 'error', text: 'Failed to update configuration.' });
        } finally {
            setLoading(false);
        }
    };

    const handleTest = async () => {
        if (!testPhone) return alert('Enter a mobile number to test.');
        setLoading(true);
        const { success } = await notificationService.sendRealSms(testPhone, 'SmartSchool SMS Gateway Test: Success!');
        if (success) alert('Test SMS triggered! Check your mobile phone.');
        else alert('Test failed. Please check your token and provider.');
        setLoading(false);
    };

    return (
        <div className="max-w-4xl mx-auto space-y-10 pb-20">
            {/* Header */}
            <div className="bg-indigo-600 rounded-[40px] p-12 text-white shadow-2xl relative overflow-hidden group">
                <div className="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-3xl group-hover:bg-white/20 transition-all duration-700"></div>
                <div className="relative z-10 flex flex-col md:flex-row justify-between items-center gap-8">
                    <div>
                        <div className="flex items-center gap-4 mb-4">
                            <div className="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-xl">
                                <Phone size={28} />
                            </div>
                            <h1 className="text-4xl font-[1000] tracking-tighter">Communication Gateway</h1>
                        </div>
                        <p className="text-indigo-100 font-bold max-w-md leading-relaxed">
                            Configure your SMS provider to bridge the gap between school analytics and guardian notifications.
                        </p>
                    </div>
                    <div className="flex items-center gap-3 px-6 py-3 bg-white/10 backdrop-blur-md rounded-2xl border border-white/20 text-xs font-black uppercase tracking-[0.2em]">
                        <Settings size={18} className="animate-spin-slow" /> System Node 01
                    </div>
                </div>
            </div>

            {msg.text && (
                <div className={`p-6 rounded-[24px] border-2 flex items-center gap-4 font-black transition-all animate-in slide-in-from-top-5 ${
                    msg.type === 'success' ? 'bg-emerald-50 border-emerald-100 text-emerald-600' : 'bg-rose-50 border-rose-100 text-rose-600'
                }`}>
                    {msg.type === 'success' ? <CheckCircle size={24} /> : <AlertCircle size={24} />}
                    {msg.text}
                </div>
            )}

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {/* Main Config */}
                <div className="lg:col-span-2">
                    <form onSubmit={handleSave} className="bg-white rounded-[40px] border border-slate-100 p-10 shadow-sm space-y-8">
                        <div>
                            <h3 className="text-xl font-black text-slate-900 mb-6 flex items-center gap-3">
                                <Key size={24} className="text-indigo-600" /> Gateway Credentials
                            </h3>
                            
                            <div className="space-y-6">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div className="space-y-2">
                                        <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">SMS Provider</label>
                                        <select 
                                            className="w-full px-6 py-4 bg-slate-50 border-2 border-transparent rounded-[24px] outline-none focus:border-indigo-600 focus:bg-white font-black transition-all"
                                            value={config.smsProvider}
                                            onChange={(e) => setConfig({...config, smsProvider: e.target.value})}
                                        >
                                            <option value="SPARROW">Sparrow SMS (Nepal)</option>
                                            <option value="AAKASH">Aakash SMS (Nepal)</option>
                                        </select>
                                    </div>
                                    <div className="space-y-2">
                                        <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">Sender Identity</label>
                                        <input 
                                            type="text" 
                                            className="w-full px-6 py-4 bg-slate-50 border-2 border-transparent rounded-[24px] outline-none focus:border-indigo-600 focus:bg-white font-black transition-all"
                                            placeholder="SmartSchool"
                                            value={config.smsIdentity}
                                            onChange={(e) => setConfig({...config, smsIdentity: e.target.value})}
                                        />
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">API Authentication Token</label>
                                    <input 
                                        type="password" 
                                        className="w-full px-8 py-5 bg-slate-50 border-2 border-transparent rounded-[28px] outline-none focus:border-indigo-600 focus:bg-white font-mono font-black transition-all"
                                        placeholder="••••••••••••••••"
                                        value={config.smsToken}
                                        onChange={(e) => setConfig({...config, smsToken: e.target.value})}
                                    />
                                    <p className="text-[10px] text-slate-400 font-bold ml-2 italic">* Obtain this from your SMS provider dashboard.</p>
                                </div>
                            </div>
                        </div>

                        <button 
                            type="submit" 
                            disabled={loading}
                            className="w-full py-6 bg-slate-900 text-white rounded-[32px] font-black text-lg flex items-center justify-center gap-3 hover:bg-slate-800 transition transform active:scale-95 shadow-2xl"
                        >
                            <Save size={24} /> {loading ? 'Synchronizing...' : 'Update Global Config'}
                        </button>
                    </form>
                </div>

                {/* Sidebar - Test & Info */}
                <div className="space-y-8">
                    <div className="bg-white rounded-[40px] border border-slate-100 p-8 shadow-sm">
                        <h4 className="text-sm font-black text-slate-900 mb-6 uppercase tracking-widest flex items-center gap-2">
                            <Send size={18} className="text-emerald-600" /> Verify Link
                        </h4>
                        <p className="text-xs font-bold text-slate-500 leading-relaxed mb-6">
                            Send a test packet to verify the handshake between this terminal and the gateway.
                        </p>
                        <div className="space-y-4">
                            <input 
                                type="text" 
                                className="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none focus:ring-2 focus:ring-emerald-500 font-black text-sm"
                                placeholder="98XXXXXXXX"
                                value={testPhone}
                                onChange={(e) => setTestPhone(e.target.value)}
                            />
                            <button 
                                onClick={handleTest}
                                disabled={loading}
                                className="w-full py-4 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-emerald-100 transition"
                            >
                                Trigger Test SMS
                            </button>
                        </div>
                    </div>

                    <div className="bg-indigo-50 rounded-[40px] p-8 border border-indigo-100/50">
                        <div className="flex items-center gap-3 mb-4 text-indigo-700">
                           <Shield size={20} />
                           <h4 className="font-black text-xs uppercase tracking-widest">Security Protocol</h4>
                        </div>
                        <p className="text-[11px] font-bold text-indigo-400 leading-relaxed mb-4">
                            All API tokens are encrypted at rest and stored within your private institutional vault. 
                        </p>
                        <div className="flex items-center gap-2 text-[10px] font-black uppercase text-indigo-600/50">
                            <HelpCircle size={14} /> Documentation
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default SmsSettings;
