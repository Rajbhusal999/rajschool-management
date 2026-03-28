import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { supabase } from '../supabaseClient';
import { 
    QrCode, Smartphone, Wallet, CheckCircle2, 
    ArrowLeft, ShieldCheck, Zap, Copy, Info
} from 'lucide-react';
import '../styles/CyberBackground.css';
import esewa_qr from '../assets/esewa_qr.jpg';
import bank_qr from '../assets/bank_qr.jpg';

const Payment = () => {
    const navigate = useNavigate();
    const [selectedMethod, setSelectedMethod] = useState('esewa');
    const [transactionCode, setTransactionCode] = useState('');
    const [selectedPlan, setSelectedPlan] = useState(null);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [success, setSuccess] = useState(false);

    useEffect(() => {
        const plan = sessionStorage.getItem('selectedPlan');
        if (plan) {
            setSelectedPlan(JSON.parse(plan));
        } else {
            navigate('/subscription');
        }
    }, [navigate]);

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (!transactionCode.trim()) return;

        setIsSubmitting(true);
        const institutionId = sessionStorage.getItem('institutionId');
        
        if (!institutionId) {
            alert('Session expired. Please login again.');
            navigate('/login');
            return;
        }

        // Map the selected plan to the database insertion
        const { error } = await supabase
            .from('subscriptions')
            .insert([{
                institution_id: institutionId,
                plan_name: selectedPlan.plan,
                amount: parseFloat(selectedPlan.price.replace(',', '')),
                payment_method: selectedMethod === 'esewa' ? 'eSewa' : 'Mobile Banking',
                transaction_code: transactionCode.trim(),
                status: 'PENDING'
            }]);

        if (error) {
            alert('Error submitting transaction: ' + error.message);
        } else {
            setSuccess(true);
            setTimeout(() => navigate('/dashboard'), 3000);
        }
        setIsSubmitting(false);
    };

    if (success) {
        return (
            <div className="min-h-screen bg-[#0B0E14] flex items-center justify-center p-8">
                <div className="max-w-md w-full text-center space-y-8 animate-in zoom-in-95 duration-500">
                    <div className="w-24 h-24 bg-emerald-500/20 border-2 border-emerald-500 rounded-full flex items-center justify-center mx-auto shadow-[0_0_40px_rgba(16,185,129,0.3)]">
                        <CheckCircle2 size={48} className="text-emerald-500" />
                    </div>
                    <div className="space-y-4">
                        <h2 className="text-4xl font-[1000] text-white tracking-tighter">Verification Initiated</h2>
                        <p className="text-slate-400 font-bold leading-relaxed uppercase tracking-tight">
                            System is validating Transaction ID: <span className="text-white font-black">{transactionCode}</span>. Access will be granted shortly.
                        </p>
                    </div>
                    <div className="w-full h-1 bg-slate-800 rounded-full overflow-hidden">
                        <div className="h-full bg-indigo-500 animate-progress"></div>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="min-h-screen relative py-20 px-4 font-['Outfit',sans-serif]">
            <div className="space-background"></div>

            <div className="max-w-4xl mx-auto relative z-10">
                <button 
                    onClick={() => navigate('/subscription')}
                    className="flex items-center gap-2 text-slate-400 hover:text-white font-black uppercase text-xs tracking-[0.2em] mb-12 transition-colors group"
                >
                    <ArrowLeft size={16} className="group-hover:-translate-x-1 transition-transform" />
                    Adjust Selection
                </button>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">
                    {/* QR Display Section */}
                    <div className="bg-white/5 backdrop-blur-3xl border border-white/10 p-10 rounded-[48px] shadow-2xl space-y-8 animate-in slide-in-from-left-10 duration-700">
                        <div className="flex items-center gap-4 mb-4">
                            <div className="w-12 h-12 bg-indigo-500/20 rounded-2xl flex items-center justify-center">
                                <QrCode className="text-indigo-400" size={24} />
                            </div>
                            <div>
                                <h3 className="text-lg font-black text-white uppercase tracking-widest">Global Gateway</h3>
                                <p className="text-xs font-bold text-slate-500">Scan to initiate transfer</p>
                            </div>
                        </div>

                        <div className="aspect-square bg-white p-4 rounded-[32px] relative overflow-hidden group border border-slate-100 shadow-inner flex items-center justify-center">
                            <img 
                                src={selectedMethod === 'esewa' ? esewa_qr : bank_qr} 
                                alt="Payment QR" 
                                className="w-full h-full object-contain rounded-2xl animate-in zoom-in-95 duration-500"
                            />
                        </div>

                        <div className="p-4 bg-indigo-500/10 rounded-2xl border border-indigo-500/20 flex gap-4">
                            <Info size={20} className="text-indigo-400 shrink-0" />
                            <p className="text-[10px] font-bold text-slate-400 leading-relaxed uppercase tracking-tight">
                                Scan the QR code above with your {selectedMethod === 'esewa' ? 'eSewa' : 'Mobile Banking'} app to complete the payment.
                            </p>
                        </div>

                        <div className="space-y-4">
                            <div className="bg-black/40 p-5 rounded-2xl border border-white/5 flex items-center justify-between group cursor-pointer hover:bg-black/60 transition-all">
                                <div>
                                    <p className="text-[10px] font-black text-slate-500 uppercase mb-1">Account Holder</p>
                                    <p className="text-sm font-black text-white">RAJ BHUSAL (SYSTEM ADMIN)</p>
                                </div>
                                <Copy size={16} className="text-slate-600 group-hover:text-indigo-400 transition-colors" />
                            </div>
                            <div className="p-4 bg-indigo-500/10 rounded-2xl border border-indigo-500/20 flex gap-4">
                                <Info size={20} className="text-indigo-400 shrink-0" />
                                <p className="text-[10px] font-bold text-slate-400 leading-relaxed uppercase tracking-tight">
                                    Please ensure your transaction amount matches your selected plan: <span className="text-white font-black">Rs. {selectedPlan?.price}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    {/* Transaction Entry Section */}
                    <div className="space-y-8 animate-in slide-in-from-right-10 duration-700">
                        <div className="space-y-4">
                            <h2 className="text-4xl font-[1000] text-white tracking-tighter leading-tight">Finalize <span className="text-indigo-500">Access</span></h2>
                            <p className="text-slate-400 font-bold uppercase tracking-tight text-sm">Select method and submit proof of transfer.</p>
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <button 
                                onClick={() => setSelectedMethod('esewa')}
                                className={`h-20 rounded-[28px] border-2 flex flex-col items-center justify-center gap-1 transition-all
                                    ${selectedMethod === 'esewa' 
                                        ? 'bg-[#60BB46]/10 border-[#60BB46] text-[#60BB46] shadow-[0_0_20px_rgba(96,187,70,0.2)]' 
                                        : 'bg-white/5 border-white/5 text-slate-500 hover:border-white/20'}`}
                            >
                                <Wallet size={24} />
                                <span className="text-[10px] font-black uppercase tracking-widest">eSewa</span>
                            </button>
                            <button 
                                onClick={() => setSelectedMethod('bank')}
                                className={`h-20 rounded-[28px] border-2 flex flex-col items-center justify-center gap-1 transition-all
                                    ${selectedMethod === 'bank' 
                                        ? 'bg-[#00D1FF]/10 border-[#00D1FF] text-[#00D1FF] shadow-[0_0_20px_rgba(0,209,255,0.2)]' 
                                        : 'bg-white/5 border-white/5 text-slate-500 hover:border-white/20'}`}
                            >
                                <Smartphone size={24} />
                                <span className="text-[10px] font-black uppercase tracking-widest">Banking</span>
                            </button>
                        </div>

                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div className="space-y-3">
                                <label className="text-[11px] font-black text-slate-500 uppercase tracking-widest ml-4">
                                    Transaction ID / Verification Code
                                </label>
                                <input 
                                    type="text"
                                    placeholder="Enter Code (e.g. 7110...)"
                                    className="w-full h-20 px-8 bg-white/5 border-2 border-white/10 rounded-[32px] text-xl font-black text-white focus:border-indigo-500 focus:bg-white/10 transition-all outline-none tracking-[0.1em] placeholder:text-slate-700 placeholder:tracking-normal"
                                    value={transactionCode}
                                    onChange={(e) => setTransactionCode(e.target.value)}
                                    required
                                />
                            </div>

                            <button 
                                type="submit"
                                disabled={isSubmitting}
                                className="w-full h-20 bg-indigo-600 text-white rounded-[32px] font-black uppercase tracking-[0.2em] text-sm shadow-[0_20px_40px_rgba(79,70,229,0.3)] hover:bg-indigo-700 hover:translate-y-[-2px] active:translate-y-0 disabled:opacity-50 disabled:translate-y-0 transition-all flex items-center justify-center gap-3 group"
                            >
                                {isSubmitting ? 'Syncing...' : 'Submit Handshake'}
                                <Zap size={20} className="group-hover:text-orange-400 group-hover:scale-125 transition-all" />
                            </button>
                        </form>

                        <div className="flex items-center gap-4 px-6 py-4 bg-white/5 rounded-3xl border border-white/5">
                            <ShieldCheck className="text-emerald-500" size={32} />
                            <p className="text-[10px] font-bold text-slate-500 leading-relaxed uppercase tracking-tight">
                                This session is end-to-end encrypted. Your transaction will be manually verified by the root administrator.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Payment;
