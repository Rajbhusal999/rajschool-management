import React, { useState, useEffect } from 'react';
import { institutionService } from '../services/api';
import { Shield, Lock, ChevronLeft, Eye, EyeOff, Loader2 } from 'lucide-react';

const SecureGateway = ({ children }) => {
  const [view, setView] = useState('LOADING'); // LOADING, SETUP, LOGIN, GRANTED
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [setupPasswords, setSetupPasswords] = useState({ new: '', confirm: '' });
  const [inst, setInst] = useState(null);
  const [error, setError] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);

  useEffect(() => {
    checkSecurityStatus();
  }, []);

  const checkSecurityStatus = async () => {
    try {
      const { data } = await institutionService.get();
      setInst(data || {});
      const hasPassword = data?.principalPassword && data.principalPassword.trim() !== '';
      
      if (!hasPassword) {
        setView('SETUP');
      } else {
        setView('LOGIN');
      }
    } catch (err) {
      console.error('Security Check Failure:', err);
      setError('Could not establish security handshake. Check your connection.');
      setView('LOGIN');
    }
  };

  const handleSetup = async (e) => {
    e.preventDefault();
    setError('');
    
    if (setupPasswords.new !== setupPasswords.confirm) {
      return setError('Passwords do not match.');
    }
    if (setupPasswords.new.length < 6) {
      return setError('Security perimeter requires at least 6 characters.');
    }

    setIsSubmitting(true);
    try {
      await institutionService.update({ principalPassword: setupPasswords.new });
      
      // Refresh local data
      const { data: updated } = await institutionService.get();
      setInst(updated);
      setView('LOGIN');
      setPassword('');
      setSetupPasswords({ new: '', confirm: '' });
    } catch (err) {
      setError('Failed to establish security perimeter.');
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleUnlock = (e) => {
    e.preventDefault();
    setError('');
    
    if (password === inst?.principalPassword) {
      setView('GRANTED');
    } else {
      setError('Password is incorrect.');
    }
  };

  if (view === 'LOADING') {
    return (
      <div className="flex flex-col items-center justify-center min-h-[60vh] space-y-4">
        <Loader2 className="animate-spin text-indigo-600" size={48} strokeWidth={2.5} />
        <p className="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] animate-pulse">Initializing Secure Perimeter...</p>
      </div>
    );
  }

  if (view === 'SETUP') {
    return (
      <div className="max-w-md mx-auto mt-10 md:mt-20 animate-in fade-in zoom-in-95 duration-500">
        <div className="bg-white p-10 md:p-14 rounded-[48px] shadow-2xl border border-slate-100 flex flex-col items-center text-center relative overflow-hidden">
            <div className="absolute top-0 right-0 w-32 h-32 bg-indigo-50 blur-[50px] -translate-y-10 translate-x-10 rounded-full"></div>
            
            <div className="w-24 h-24 bg-indigo-600 rounded-[36px] flex items-center justify-center text-white mb-10 shadow-2xl shadow-indigo-200 rotate-12 hover:rotate-0 transition-transform relative z-10">
                <Shield size={48} strokeWidth={2} />
            </div>
            
            <h2 className="text-4xl font-[1000] text-slate-900 tracking-tighter mb-4 relative z-10">Security Initialization</h2>
            <p className="text-[11px] font-black text-slate-400 leading-relaxed mb-10 px-4 uppercase tracking-widest relative z-10 opacity-70">
                Establish your master principal security key to unlock institutional analytics.
            </p>

            {error && (
              <div className="w-full mb-6 p-4 bg-rose-50 border border-rose-100 text-rose-600 text-xs font-black rounded-2xl animate-in slide-in-from-top-2">
                {error}
              </div>
            )}

            <form onSubmit={handleSetup} className="w-full space-y-6 relative z-10">
                <div className="space-y-3 text-left">
                    <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-3">New Password</label>
                    <div className="relative">
                      <input 
                          type={showPassword ? "text" : "password"} 
                          className="w-full px-8 py-5 bg-slate-50 border-2 border-transparent rounded-[24px] outline-none focus:border-indigo-500 focus:bg-white font-black transition-all"
                          placeholder="Create New Key"
                          value={setupPasswords.new}
                          onChange={(e) => setSetupPasswords({...setupPasswords, new: e.target.value})}
                          required 
                      />
                      <button type="button" onClick={() => setShowPassword(!showPassword)} className="absolute right-6 top-1/2 -translate-y-1/2 text-slate-400 hover:text-indigo-600">
                        {showPassword ? <EyeOff size={20} /> : <Eye size={20} />}
                      </button>
                    </div>
                </div>
                <div className="space-y-3 text-left">
                    <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-3">Confirm Password</label>
                    <input 
                        type="password" 
                        className="w-full px-8 py-5 bg-slate-50 border-2 border-transparent rounded-[24px] outline-none focus:border-indigo-500 focus:bg-white font-black transition-all"
                        placeholder="Confirm Master Key"
                        value={setupPasswords.confirm}
                        onChange={(e) => setSetupPasswords({...setupPasswords, confirm: e.target.value})}
                        required 
                    />
                </div>

                <button 
                  type="submit" 
                  disabled={isSubmitting}
                  className="w-full py-6 bg-indigo-600 text-white rounded-[32px] font-black text-lg shadow-2xl shadow-indigo-100 transform active:scale-95 transition-all hover:bg-indigo-700 disabled:opacity-50"
                >
                    {isSubmitting ? 'Securing...' : 'Encrypt & Establish'}
                </button>

                <div className="pt-4 border-t border-slate-50">
                    <button type="button" onClick={() => window.history.back()} className="text-[11px] font-black text-slate-400 uppercase tracking-widest hover:text-indigo-600 transition flex items-center justify-center gap-2 mx-auto">
                        <ChevronLeft size={16} /> Retreat to Safety
                    </button>
                </div>
            </form>
        </div>
      </div>
    );
  }

  if (view === 'LOGIN') {
    return (
      <div className="max-w-md mx-auto mt-10 md:mt-20 animate-in fade-in zoom-in-95 duration-500">
        <div className="bg-white p-10 md:p-14 rounded-[48px] shadow-2xl border border-slate-100 flex flex-col items-center text-center relative overflow-hidden">
            <div className="absolute top-0 right-0 w-40 h-40 bg-rose-50/50 blur-[60px] -translate-y-10 translate-x-10 rounded-full"></div>
            
            <div className="w-24 h-24 bg-[#0f172a] rounded-[36px] flex items-center justify-center text-white mb-10 shadow-2xl shadow-slate-200 group border-b-4 border-slate-800">
                <Lock size={48} strokeWidth={2} className="group-hover:rotate-12 transition-transform" />
            </div>
            
            <h2 className="text-4xl font-[1000] text-slate-900 tracking-tighter mb-4 relative z-10">Vault Access</h2>
            <p className="text-[11px] font-black text-slate-400 leading-relaxed mb-10 px-4 uppercase tracking-[0.25em] relative z-10 opacity-70">
                Identity verification required for institutional analytics.
            </p>

            {error && (
              <div className="w-full mb-6 p-4 bg-rose-50 border border-rose-100 text-rose-600 text-[10px] font-black rounded-2xl uppercase tracking-widest animate-shake">
                {error}
              </div>
            )}

            <form onSubmit={handleUnlock} className="w-full space-y-6 relative z-10 text-left">
                <div className="space-y-3">
                    <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-3">Principal Security Key</label>
                    <div className="relative">
                      <input 
                          type={showPassword ? "text" : "password"} 
                          className={`w-full px-8 py-5 bg-slate-50 border-2 rounded-[24px] outline-none font-black transition-all ${error ? 'border-rose-400 bg-rose-50/20' : 'border-transparent focus:border-indigo-600 focus:bg-white'}`}
                          placeholder="••••••••"
                          value={password}
                          onChange={(e) => setPassword(e.target.value)}
                          required 
                      />
                      <button type="button" onClick={() => setShowPassword(!showPassword)} className="absolute right-6 top-1/2 -translate-y-1/2 text-slate-400 hover:text-indigo-600">
                        {showPassword ? <EyeOff size={20} /> : <Eye size={20} />}
                      </button>
                    </div>
                </div>

                <button type="submit" className="w-full py-6 bg-[#0f172a] text-white rounded-[32px] font-black text-lg shadow-2xl shadow-slate-100 transform active:scale-95 transition-all hover:bg-black uppercase tracking-widest">
                    Unlock Terminal
                </button>

                <div className="pt-4 border-t border-slate-50">
                    <button type="button" onClick={() => window.history.back()} className="text-[11px] font-black text-slate-400 uppercase tracking-widest hover:text-indigo-600 transition flex items-center justify-center gap-2 mx-auto">
                        <ChevronLeft size={16} /> Return to Operations
                    </button>
                </div>
            </form>
        </div>
      </div>
    );
  }

  return <div className="animate-in fade-in slide-in-from-bottom-5 duration-700">{children}</div>;
};

export default SecureGateway;
