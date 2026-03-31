import React, { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { supabase } from '../supabaseClient';
import { Check, Star, Zap, Shield, ArrowRight } from 'lucide-react';
import '../styles/CyberBackground.css';

const PricingCard = ({ plan, price, features, isPopular, onSelect }) => (
    <div className={`relative bg-white rounded-[48px] p-10 flex flex-col h-full border-[3px] transition-all duration-500 hover:scale-[1.02] ${isPopular ? 'border-indigo-600 shadow-[0_30px_60px_rgba(79,70,229,0.15)]' : 'border-slate-100 shadow-xl shadow-slate-200/50'}`}>
        {isPopular && (
            <div className="absolute top-0 right-10 -translate-y-1/2 bg-gradient-to-r from-orange-400 to-orange-600 text-white text-[10px] font-black uppercase tracking-[0.2em] px-6 py-2 rounded-full shadow-lg shadow-orange-500/30">
                Popular
            </div>
        )}
        
        <div className="text-center mb-10">
            <h3 className="text-lg font-bold text-slate-400 uppercase tracking-widest mb-6">{plan}</h3>
            <div className="flex items-center justify-center gap-1">
                <span className="text-2xl font-black text-slate-900 mb-4">Rs.</span>
                <span className="text-6xl font-[1000] text-slate-900 tracking-tighter">{price}</span>
            </div>
        </div>

        <div className="space-y-6 flex-grow">
            {features.map((feature, i) => (
                <div key={i} className="flex items-center gap-4 group">
                    <div className="w-6 h-6 rounded-full bg-indigo-50 flex items-center justify-center shrink-0 group-hover:bg-indigo-600 transition-colors">
                        <Check size={14} className="text-indigo-600 group-hover:text-white" strokeWidth={3} />
                    </div>
                    <span className="text-sm font-bold text-slate-600">{feature}</span>
                </div>
            ))}
        </div>

        <button 
            onClick={() => onSelect({ plan, price })}
            className={`w-full h-16 rounded-[24px] mt-12 font-black text-sm uppercase tracking-widest transition-all duration-300 flex items-center justify-center gap-3
                ${isPopular 
                    ? 'bg-indigo-600 text-white shadow-xl shadow-indigo-600/20 hover:bg-indigo-700 hover:shadow-indigo-600/40' 
                    : 'bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white'}
            `}
        >
            Select Plan <ArrowRight size={18} />
        </button>
    </div>
);

const Subscription = () => {
    const navigate = useNavigate();

    useEffect(() => {
        const checkAccess = async () => {
            const id = sessionStorage.getItem('institutionId');
            if (!id) return;

            const { data: instData, error } = await supabase
                .from('institutions')
                .select('status, expiry_date')
                .eq('id', id)
                .single();

            if (instData) {
                const now = new Date();
                const expiryDate = instData.expiry_date ? new Date(instData.expiry_date) : null;
                
                // Subscription validation: Must be ACTIVE (not expired) or PENDING verification
                const hasValidAccess = (instData.status === 'ACTIVE' && expiryDate && expiryDate > now) || instData.status === 'PENDING';

                if (hasValidAccess) {
                    navigate('/dashboard');
                }
            }
        };
        checkAccess();
    }, [navigate]);

    const plans = [
        {
            plan: "1 Year Access",
            price: "5,000",
            features: [
                "Student Management",
                "Teacher Management",
                "Exams & Results"
            ]
        },
        {
            plan: "2 Years Access",
            price: "8,000",
            isPopular: true,
            features: [
                "Student & Teacher Management",
                "Exams & Marksheets",
                "Billing & Accounts",
                "ID Card Generation"
            ]
        },
        {
            plan: "5 Years Access",
            price: "20,000",
            features: [
                "Student Management",
                "Teacher Management",
                "Exams & Results",
                "Billing System",
                "Smart Attendance",
                "ID Card Generation"
            ]
        }
    ];

    const handleSelectPlan = (planData) => {
        // Store the selected plan in sessionStorage or passed via state
        sessionStorage.setItem('selectedPlan', JSON.stringify(planData));
        navigate('/payment');
    };

    return (
        <div className="min-h-screen relative py-24 px-8 font-['Outfit',sans-serif]">
            {/* Reusable Space Background */}
            <div className="space-background"></div>

            <div className="max-w-7xl mx-auto relative z-10">
                <div className="text-center mb-20 space-y-6 animate-in fade-in slide-in-from-top-10 duration-1000">
                    <h1 className="text-6xl md:text-7xl font-[1000] text-white tracking-tighter drop-shadow-2xl">
                        Choose Your <span className="text-indigo-500">Subscription</span>
                    </h1>
                    <p className="text-xl md:text-2xl font-bold text-slate-300 max-w-2xl mx-auto leading-relaxed opacity-80 uppercase tracking-tight">
                        Unlock the full power of Smart विद्यालय Management System.
                    </p>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-3 gap-10 lg:gap-14 animate-in fade-in slide-in-from-bottom-10 duration-1000">
                    {plans.map((p, i) => (
                        <PricingCard 
                            key={i} 
                            {...p} 
                            onSelect={handleSelectPlan} 
                        />
                    ))}
                </div>

                {/* Footer Note */}
                <div className="mt-20 max-w-4xl mx-auto">
                    <div className="bg-white/5 backdrop-blur-3xl border border-white/10 p-10 rounded-[32px] text-center shadow-2xl">
                        <p className="text-lg font-black text-white leading-relaxed opacity-90">
                            Note: For attendance tracking, the cost of SMS is to be borne by the school itself, and we will assist in its integration.
                        </p>
                    </div>
                </div>

                {/* Secure Badge */}
                <div className="mt-16 flex items-center justify-center gap-6 opacity-40 hover:opacity-100 transition-opacity duration-500">
                    <div className="flex items-center gap-2 text-white font-black text-xs uppercase tracking-[0.3em]">
                        <Shield size={20} className="text-emerald-400" /> Secure Gateway
                    </div>
                    <div className="w-1.5 h-1.5 bg-slate-600 rounded-full"></div>
                    <div className="flex items-center gap-2 text-white font-black text-xs uppercase tracking-[0.3em]">
                        <Zap size={20} className="text-orange-400" /> Instant Activation
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Subscription;
