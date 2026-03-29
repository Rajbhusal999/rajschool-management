import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import axios from 'axios';
import { supabase } from '../supabaseClient';
import { 
  Library, Fingerprint, Phone, MapPin, Calendar, 
  Image as ImageIcon, Upload, Mail, Lock, CheckCircle2, Eye, EyeOff, AlertCircle
} from 'lucide-react';

const Register = () => {
    const navigate = useNavigate();
    const [showPassword, setShowPassword] = useState(false);
    const [showSecret, setShowSecret] = useState(false);
    const [errors, setErrors] = useState({});
    const [loading, setLoading] = useState(false);
    const [apiError, setApiError] = useState('');
    const [formData, setFormData] = useState({
        schoolName: '',
        emisCode: '',
        phone: '',
        address: '',
        establishment: '',
        email: '',
        password: '',
        verifySecret: '',
        logoFile: null,
        backgroundFile: null
    });

    const validate = () => {
        const newErrors = {};
        if (!formData.schoolName) newErrors.schoolName = 'Institutional Title is required';
        if (!formData.emisCode) newErrors.emisCode = 'EMIS Code is required';
        if (!/^\d{10}$/.test(formData.phone)) newErrors.phone = 'Valid 10-digit phone required';
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) newErrors.email = 'Valid administrative email required';
        
        // Password validation: 8+ chars, Upper, Lower, Symbol, Int
        const passRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        if (!passRegex.test(formData.password)) {
            newErrors.password = '8+ chars: Upper, Lower, Symbol, Int required';
        }
        
        if (formData.password !== formData.verifySecret) {
            newErrors.verifySecret = 'Secret verification does not match';
        }

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const handleFileChange = (e, type, maxSize) => {
        const file = e.target.files[0];
        if (file && file.size > maxSize * 1024 * 1024) {
            setErrors(prev => ({ ...prev, [type]: `Max ${maxSize}MB allowed` }));
        } else {
            setErrors(prev => ({ ...prev, [type]: null }));
            if (type === 'logo') {
                setFormData(prev => ({ ...prev, logoFile: file }));
            }
            if (type === 'background') {
                setFormData(prev => ({ ...prev, backgroundFile: file }));
            }
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (validate()) {
            setLoading(true);
            setApiError('');
            try {
                // Configuration Guard
                if (!import.meta.env.VITE_SUPABASE_URL || !import.meta.env.VITE_SUPABASE_ANON_KEY) {
                    throw new Error('Supabase configuration missing. Have you added the Environment Variables to Vercel?');
                }

                // 1. Upload Logo if exists
                let logoUrl = null;
                if (formData.logoFile) {
                    const fileExt = formData.logoFile.name.split('.').pop();
                    const fileName = `${formData.emisCode}_${Math.random()}.${fileExt}`;
                    const filePath = `logos/${fileName}`;

                    const { error: uploadError } = await supabase.storage
                        .from('logos')
                        .upload(fileName, formData.logoFile);

                    if (uploadError) throw uploadError;

                    const { data: publicUrlData } = supabase.storage
                        .from('logos')
                        .getPublicUrl(fileName);
                    
                    logoUrl = publicUrlData.publicUrl;
                }

                // 2. Upload Background if exists
                let bgUrl = null;
                if (formData.backgroundFile) {
                    const fileName = `${formData.emisCode}_bg_${Math.random()}.${formData.backgroundFile.name.split('.').pop()}`;
                    const { error: uploadError } = await supabase.storage
                        .from('backgrounds')
                        .upload(fileName, formData.backgroundFile);

                    if (uploadError) throw uploadError;

                    const { data: publicUrlData } = supabase.storage
                        .from('backgrounds')
                        .getPublicUrl(fileName);
                    
                    bgUrl = publicUrlData.publicUrl;
                }

                // Supabase Insertion Logic
                const { data, error } = await supabase
                    .from('institutions')
                    .insert([{
                        school_name: formData.schoolName,
                        emis_code: formData.emisCode,
                        phone: formData.phone,
                        address: formData.address,
                        establishment: formData.establishment,
                        email: formData.email,
                        password: formData.password,
                        logo_url: logoUrl,
                        background_url: bgUrl
                    }])
                    .select();

                if (error) {
                    // Handle unique constraint violations with human-readable messages
                    if (error.code === '23505') {
                        if (error.message.includes('email')) {
                            setApiError('This Administrative Email is already registered. Please authenticate or use a different address.');
                            return;
                        }
                        if (error.message.includes('emis_code')) {
                            setApiError('This EMIS Code is already associated with an institution. Please verify your credentials.');
                            return;
                        }
                    }
                    throw error;
                }

                console.log('Institutional Portal Deployed:', data);
                if (data && data[0]) {
                    sessionStorage.setItem('institutionId', data[0].id);
                    sessionStorage.setItem('schoolName', data[0].school_name);
                }
                navigate('/subscription');
            } catch (err) {
                console.error('Deployment Error:', err);
                const errorDetail = err.details || err.hint || '';
                const baseMessage = err.message || 'Connection to regional orchestration network failed.';
                
                // If we haven't already set a custom API error, set the general one
                if (!apiError) {
                    setApiError(`${baseMessage}${errorDetail ? ` (${errorDetail})` : ''}`);
                }
            } finally {
                setLoading(false);
            }
        }
    };

    return (
        <div className="min-h-screen bg-slate-50 flex items-center justify-center py-20 px-6 font-['Outfit',sans-serif]">
            <div className="max-w-4xl w-full bg-white rounded-[60px] shadow-2xl shadow-slate-200/50 p-12 md:p-20 relative overflow-hidden">
                {/* Header */}
                <div className="text-center space-y-6 mb-16">
                    <div className="w-20 h-20 bg-indigo-600 rounded-[30px] flex items-center justify-center text-white mx-auto shadow-2xl shadow-indigo-200 animate-in zoom-in duration-500">
                        <Library size={40} />
                    </div>
                    <h1 className="text-5xl font-black text-slate-900 tracking-tight">Initialize Institution</h1>
                    <p className="text-slate-400 font-medium">Construct your digital ecosystem within our zero-gravity framework.</p>
                </div>

                <form onSubmit={handleSubmit} className="space-y-10">
                    {/* School Name */}
                    <div className="space-y-3">
                        <label className="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-6">School Name</label>
                        <div className={`relative flex items-center transition-all ${errors.schoolName ? ' ring-2 ring-rose-500/20' : ''}`}>
                            <Library className="absolute left-6 text-slate-400" size={20} />
                            <input 
                                type="text" 
                                placeholder="Institutional Title"
                                className="w-full pl-16 pr-6 py-5 bg-slate-50 border-none rounded-[24px] font-bold text-slate-700 outline-none focus:ring-4 focus:ring-indigo-100 transition-all placeholder:text-slate-300"
                                value={formData.schoolName}
                                onChange={(e) => setFormData({...formData, schoolName: e.target.value})}
                            />
                        </div>
                        {errors.schoolName && <p className="text-rose-500 text-xs font-bold ml-6 flex items-center gap-1"><AlertCircle size={12} /> {errors.schoolName}</p>}
                    </div>

                    {/* EMIS and Phone */}
                    <div className="grid md:grid-cols-2 gap-8">
                        <div className="space-y-3">
                            <label className="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-6">EMIS Code</label>
                            <div className={`relative flex items-center transition-all ${errors.emisCode ? ' ring-2 ring-rose-500/20' : ''}`}>
                                <Fingerprint className="absolute left-6 text-slate-400" size={20} />
                                <input 
                                    type="text" 
                                    placeholder="Identifier"
                                    className="w-full pl-16 pr-6 py-5 bg-slate-50 border-none rounded-[24px] font-bold text-slate-700 outline-none focus:ring-4 focus:ring-indigo-100 transition-all placeholder:text-slate-300"
                                    value={formData.emisCode}
                                    onChange={(e) => setFormData({...formData, emisCode: e.target.value})}
                                />
                            </div>
                            {errors.emisCode && <p className="text-rose-500 text-xs font-bold ml-6 flex items-center gap-1"><AlertCircle size={12} /> {errors.emisCode}</p>}
                        </div>
                        <div className="space-y-3">
                            <label className="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-6">Phone Direct</label>
                            <div className={`relative flex items-center transition-all ${errors.phone ? ' ring-2 ring-rose-500/20' : ''}`}>
                                <Phone className="absolute left-6 text-slate-400" size={20} />
                                <input 
                                    type="text" 
                                    placeholder="98XXXXXXXX"
                                    className="w-full pl-16 pr-6 py-5 bg-slate-50 border-none rounded-[24px] font-bold text-slate-700 outline-none focus:ring-4 focus:ring-indigo-100 transition-all placeholder:text-slate-300"
                                    value={formData.phone}
                                    onChange={(e) => setFormData({...formData, phone: e.target.value})}
                                />
                            </div>
                            {errors.phone && <p className="text-rose-500 text-xs font-bold ml-6 flex items-center gap-1"><AlertCircle size={12} /> {errors.phone}</p>}
                        </div>
                    </div>

                    {/* Address and Establishment */}
                    <div className="grid md:grid-cols-2 gap-8">
                        <div className="space-y-3">
                            <label className="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-6">Address Matrix</label>
                            <div className="relative flex items-center">
                                <MapPin className="absolute left-6 text-slate-400" size={20} />
                                <input 
                                    type="text" 
                                    placeholder="Physical location"
                                    className="w-full pl-16 pr-6 py-5 bg-slate-50 border-none rounded-[24px] font-bold text-slate-700 outline-none focus:ring-4 focus:ring-indigo-100 transition-all placeholder:text-slate-300"
                                    value={formData.address}
                                    onChange={(e) => setFormData({...formData, address: e.target.value})}
                                />
                            </div>
                        </div>
                        <div className="space-y-3">
                            <label className="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-6">Establishment (B.S.)</label>
                            <div className="relative flex items-center">
                                <Calendar className="absolute left-6 text-slate-400" size={20} />
                                <input 
                                    type="text" 
                                    placeholder="Year B.S."
                                    className="w-full pl-16 pr-6 py-5 bg-slate-50 border-none rounded-[24px] font-bold text-slate-700 outline-none focus:ring-4 focus:ring-indigo-100 transition-all placeholder:text-slate-300"
                                    value={formData.establishment}
                                    onChange={(e) => setFormData({...formData, establishment: e.target.value})}
                                />
                            </div>
                        </div>
                    </div>

                    {/* Files */}
                    <div className="grid md:grid-cols-2 gap-8">
                        <div className="space-y-3">
                            <label className="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-6">Background Artifact</label>
                            <div className="relative flex items-center p-2 bg-slate-50 rounded-[24px]">
                                <ImageIcon className="ml-4 text-slate-400" size={20} />
                                <input 
                                    type="file" 
                                    className="w-full px-4 py-3 text-sm font-bold text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 transition-all cursor-pointer"
                                    onChange={(e) => handleFileChange(e, 'background', 5)}
                                />
                            </div>
                            <p className="text-[10px] font-bold text-slate-400 ml-6">MAX 5MB. Visual identity backdrop.</p>
                            {errors.background && <p className="text-rose-500 text-xs font-bold ml-6 mt-1">{errors.background}</p>}
                        </div>
                        <div className="space-y-3">
                            <label className="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-6">Institution Logo</label>
                            <div className="relative flex items-center p-2 bg-slate-50 rounded-[24px]">
                                <Upload className="ml-4 text-slate-400" size={20} />
                                <input 
                                    type="file" 
                                    className="w-full px-4 py-3 text-sm font-bold text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 transition-all cursor-pointer"
                                    onChange={(e) => handleFileChange(e, 'logo', 2)}
                                />
                            </div>
                            <p className="text-[10px] font-bold text-slate-400 ml-6">MAX 2MB. Credentials & receipts.</p>
                            {errors.logo && <p className="text-rose-500 text-xs font-bold ml-6 mt-1">{errors.logo}</p>}
                        </div>
                    </div>

                    {/* Email */}
                    <div className="space-y-3">
                        <label className="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-6">Administrative Email</label>
                        <div className={`relative flex items-center transition-all ${errors.email ? ' ring-2 ring-rose-500/20' : ''}`}>
                            <Mail className="absolute left-6 text-slate-400" size={20} />
                            <input 
                                type="email" 
                                placeholder="admin@institution.edu"
                                className="w-full pl-16 pr-6 py-5 bg-slate-50 border-none rounded-[24px] font-bold text-slate-700 outline-none focus:ring-4 focus:ring-indigo-100 transition-all placeholder:text-slate-300"
                                value={formData.email}
                                onChange={(e) => setFormData({...formData, email: e.target.value})}
                            />
                        </div>
                        {errors.email && <p className="text-rose-500 text-xs font-bold ml-6 flex items-center gap-1"><AlertCircle size={12} /> {errors.email}</p>}
                    </div>

                    {/* Password */}
                    <div className="grid md:grid-cols-2 gap-8">
                        <div className="space-y-3">
                            <label className="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-6">Master Password</label>
                            <div className={`relative flex items-center transition-all ${errors.password ? ' ring-2 ring-rose-500/20' : ''}`}>
                                <Lock className="absolute left-6 text-slate-400" size={20} />
                                <input 
                                    type={showPassword ? "text" : "password"}
                                    placeholder="••••••••"
                                    className="w-full pl-16 pr-16 py-5 bg-slate-50 border-none rounded-[24px] font-bold text-slate-700 outline-none focus:ring-4 focus:ring-indigo-100 transition-all placeholder:text-slate-300"
                                    value={formData.password}
                                    onChange={(e) => setFormData({...formData, password: e.target.value})}
                                />
                                <button type="button" onClick={() => setShowPassword(!showPassword)} className="absolute right-6 text-slate-400 hover:text-indigo-600 transition-colors cursor-pointer">
                                    {showPassword ? <EyeOff size={20} /> : <Eye size={20} />}
                                </button>
                            </div>
                            <p className="text-[10px] font-bold text-slate-400 ml-6">8+ chars: Upper, Lower, Symbol, Int.</p>
                            {errors.password && <p className="text-rose-500 text-xs font-bold ml-6 mt-1 flex items-center gap-1"><AlertCircle size={12} /> {errors.password}</p>}
                        </div>
                        <div className="space-y-3">
                            <label className="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-6">Verify Secret</label>
                            <div className={`relative flex items-center transition-all ${errors.verifySecret ? ' ring-2 ring-rose-500/20' : ''}`}>
                                <CheckCircle2 className="absolute left-6 text-slate-400" size={20} />
                                <input 
                                    type={showSecret ? "text" : "password"}
                                    placeholder="••••••••"
                                    className="w-full pl-16 pr-16 py-5 bg-slate-50 border-none rounded-[24px] font-bold text-slate-700 outline-none focus:ring-4 focus:ring-indigo-100 transition-all placeholder:text-slate-300"
                                    value={formData.verifySecret}
                                    onChange={(e) => setFormData({...formData, verifySecret: e.target.value})}
                                />
                                <button type="button" onClick={() => setShowSecret(!showSecret)} className="absolute right-6 text-slate-400 hover:text-indigo-600 transition-colors cursor-pointer">
                                    {showSecret ? <EyeOff size={20} /> : <Eye size={20} />}
                                </button>
                            </div>
                            {errors.verifySecret && <p className="text-rose-500 text-xs font-bold ml-6 flex items-center gap-1"><AlertCircle size={12} /> {errors.verifySecret}</p>}
                        </div>
                    </div>

                    {/* Footer Actions */}
                    <div className="text-center pt-10 space-y-8">
                        <p className="text-xs font-bold text-slate-400">
                            Submitting this form confirms agreement to our <span className="text-indigo-600 cursor-pointer hover:underline">Governance Protocol</span>.
                        </p>
                        
                        <button 
                            type="submit" 
                            disabled={loading}
                            className={`w-full py-6 bg-indigo-600 text-white rounded-[32px] font-black text-xl shadow-2xl shadow-indigo-200 hover:scale-[1.02] active:scale-[0.98] transition-all cursor-pointer ${loading ? 'opacity-70 cursor-not-allowed' : ''}`}
                        >
                            {loading ? 'Orchestrating Deployment...' : 'Deploy Institutional Portal'}
                        </button>

                        {apiError && (
                            <div className="bg-rose-50 text-rose-600 p-4 rounded-2xl text-xs font-bold border border-rose-100 animate-in fade-in slide-in-from-top-2 duration-300">
                                {apiError}
                            </div>
                        )}

                        <div className="text-xs font-bold text-slate-400">
                            Already orchestrated? <Link to="/login" className="text-indigo-600 hover:underline">Authenticate Portal</Link>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default Register;
