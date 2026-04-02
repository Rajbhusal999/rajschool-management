import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { supabase } from '../supabaseClient';
import { School, User, Lock, ArrowRight, Loader2, Search, CheckCircle2 } from 'lucide-react';

const TeacherLogin = () => {
    const [step, setStep] = useState(1); // 1: School Info, 2: Select Teacher, 3: Password
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');
    
    // Step 1 State
    const [emisCode, setEmisCode] = useState('');
    const [schoolName, setSchoolName] = useState('');
    
    // Step 2 State
    const [teachers, setTeachers] = useState([]);
    const [selectedTeacher, setSelectedTeacher] = useState(null);
    const [schoolId, setSchoolId] = useState(null);
    const [searchQuery, setSearchQuery] = useState('');

    // Step 3 State
    const [password, setPassword] = useState('');

    const navigate = useNavigate();

    const handleLoadTeachers = async () => {
        if (!emisCode || !schoolName) {
            setError('Please provide both EMIS Code and School Name');
            return;
        }

        setLoading(true);
        setError('');

        try {
            // Find school
            const { data: school, error: schoolErr } = await supabase
                .from('institutions')
                .select('*')
                .eq('emis_code', emisCode)
                .ilike('school_name', `%${schoolName}%`)
                .single();

            if (schoolErr || !school) {
                throw new Error('School not found. Please check EMIS ID and Name.');
            }

            setSchoolId(school.id);

            // Fetch teachers
            const { data: teacherList, error: teacherErr } = await supabase
                .from('teachers')
                .select('id, full_name, staff_role')
                .eq('school_id', school.id)
                .order('full_name', { ascending: true });

            if (teacherErr) throw teacherErr;

            setTeachers(teacherList || []);
            setStep(2);
        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };

    const handleProceedToPassword = () => {
        if (!selectedTeacher) {
            setError('Please select your name from the list');
            return;
        }
        setStep(3);
        setError('');
    };

    const handleFinalLogin = async () => {
        if (!password) {
            setError('Please enter your password');
            return;
        }

        setLoading(true);
        setError('');

        try {
            const { data: teacher, error: loginErr } = await supabase
                .from('teachers')
                .select('*')
                .eq('id', selectedTeacher.id)
                .eq('teacher_password', password)
                .single();

            if (loginErr || !teacher) {
                throw new Error('Invalid password. Please try again.');
            }

            // Success: Set Session
            sessionStorage.setItem('institutionId', schoolId); 
            sessionStorage.setItem('schoolName', school.school_name);
            sessionStorage.setItem('schoolAddress', school.address || '');
            sessionStorage.setItem('estdYear', school.establishment || '');
            sessionStorage.setItem('schoolLogo', school.logo_url || '');
            sessionStorage.setItem('userType', 'teacher');
            sessionStorage.setItem('teacherId', teacher.id);
            sessionStorage.setItem('teacherName', teacher.full_name);
            
            // Redirect to Attendance Entry
            navigate('/attendance/entry');
        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };

    const filteredTeachers = teachers.filter(t => 
        t.full_name.toLowerCase().includes(searchQuery.toLowerCase())
    );

    return (
        <div className="min-h-screen bg-slate-950 flex items-center justify-center p-6 relative overflow-hidden font-['Outfit',sans-serif]">
            {/* Background Aesthetics */}
            <div className="absolute top-0 left-0 w-full h-full bg-[radial-gradient(circle_at_50%_50%,rgba(79,70,229,0.1),transparent_50%)] pointer-events-none" />
            <div className="absolute -top-24 -right-24 w-96 h-96 bg-indigo-600/10 rounded-full blur-3xl" />
            <div className="absolute -bottom-24 -left-24 w-96 h-96 bg-rose-600/10 rounded-full blur-3xl" />

            <div className="max-w-xl w-full bg-slate-900/40 backdrop-blur-3xl p-10 rounded-[40px] border border-white/10 shadow-2xl relative z-10 transition-all duration-500">
                {/* Header */}
                <div className="text-center mb-10">
                    <div className="w-16 h-16 bg-rose-500/20 text-rose-400 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-rose-500/30">
                        {step === 1 ? <School size={32} /> : step === 2 ? <User size={32} /> : <Lock size={32} />}
                    </div>
                    <h1 className="text-3xl font-black text-white tracking-tight mb-2">Teacher Portal</h1>
                    <p className="text-slate-400 text-sm font-medium">
                        {step === 1 && "Identify your institution to begin"}
                        {step === 2 && "Select your profile from the staff list"}
                        {step === 3 && `Welcome, ${selectedTeacher?.full_name}`}
                    </p>
                </div>

                {error && (
                    <div className="mb-6 p-4 bg-rose-500/10 border border-rose-500/30 rounded-2xl text-rose-400 text-sm font-bold flex items-center gap-3 animate-pulse">
                        <span className="w-2 h-2 bg-rose-500 rounded-full" />
                        {error}
                    </div>
                )}

                {/* Step 1: School Lookup */}
                {step === 1 && (
                    <div className="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
                        <div className="space-y-2">
                            <label className="text-xs font-black uppercase tracking-widest text-slate-500 ml-2">EMIS ID / School Code</label>
                            <input 
                                type="text"
                                value={emisCode}
                                onChange={(e) => setEmisCode(e.target.value)}
                                className="w-full bg-white/5 border border-white/10 rounded-2xl p-4 text-white placeholder:text-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all"
                                placeholder="Enter your school's EMIS ID"
                            />
                        </div>
                        <div className="space-y-2">
                            <label className="text-xs font-black uppercase tracking-widest text-slate-500 ml-2">School Name</label>
                            <input 
                                type="text"
                                value={schoolName}
                                onChange={(e) => setSchoolName(e.target.value)}
                                className="w-full bg-white/5 border border-white/10 rounded-2xl p-4 text-white placeholder:text-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all"
                                placeholder="Confirmed institutional name"
                            />
                        </div>
                        <button 
                            onClick={handleLoadTeachers}
                            disabled={loading}
                            className="w-full py-5 bg-indigo-600 text-white rounded-2xl font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-500/20 flex items-center justify-center gap-2"
                        >
                            {loading ? <Loader2 className="animate-spin" /> : "Load Staff Directory"}
                        </button>
                    </div>
                )}

                {/* Step 2: Teacher Selection */}
                {step === 2 && (
                    <div className="space-y-6 animate-in fade-in slide-in-from-right-4 duration-500">
                        <div className="relative">
                            <Search className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500" size={18} />
                            <input 
                                type="text"
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                placeholder="Search by name..."
                                className="w-full bg-white/5 border border-white/10 rounded-2xl py-4 pl-12 pr-4 text-white placeholder:text-slate-600 focus:outline-none focus:ring-2 focus:ring-rose-500/50 transition-all"
                            />
                        </div>

                        <div className="max-h-60 overflow-y-auto space-y-2 pr-2 scrollbar-thin scrollbar-thumb-white/10">
                            {filteredTeachers.map(teacher => (
                                <button
                                    key={teacher.id}
                                    onClick={() => setSelectedTeacher(teacher)}
                                    className={`w-full p-4 rounded-2xl border transition-all flex items-center justify-between text-left ${
                                        selectedTeacher?.id === teacher.id 
                                        ? 'bg-rose-500/20 border-rose-500 text-white shadow-lg shadow-rose-500/10' 
                                        : 'bg-white/5 border-white/10 text-slate-300 hover:bg-white/10'
                                    }`}
                                >
                                    <div>
                                        <div className="font-bold">{teacher.full_name}</div>
                                        <div className="text-[10px] uppercase font-black tracking-tighter text-slate-500">{teacher.staff_role || 'Faculty'}</div>
                                    </div>
                                    {selectedTeacher?.id === teacher.id && <CheckCircle2 size={18} className="text-rose-400" />}
                                </button>
                            ))}
                            {filteredTeachers.length === 0 && (
                                <div className="text-center py-6 text-slate-500 font-bold italic">No matching results</div>
                            )}
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <button 
                                onClick={() => setStep(1)}
                                className="py-4 bg-white/5 text-slate-400 rounded-2xl font-black uppercase tracking-widest hover:bg-white/10 transition-all"
                            >
                                Back
                            </button>
                            <button 
                                onClick={handleProceedToPassword}
                                className="py-4 bg-rose-600 text-white rounded-2xl font-black uppercase tracking-widest hover:bg-rose-700 transition-all shadow-xl shadow-rose-500/20 flex items-center justify-center gap-2"
                            >
                                Proceed <ArrowRight size={18} />
                            </button>
                        </div>
                    </div>
                )}

                {/* Step 3: Password Verification */}
                {step === 3 && (
                    <div className="space-y-6 animate-in fade-in zoom-in duration-500">
                        <div className="bg-white/5 p-6 rounded-3xl border border-white/10 flex items-center gap-4 mb-4">
                            <img 
                                src={`https://ui-avatars.com/api/?name=${selectedTeacher.full_name}&background=e11d48&color=fff`} 
                                alt="Profile" 
                                className="w-16 h-16 rounded-2xl shadow-lg"
                            />
                            <div>
                                <h3 className="text-white font-black text-lg leading-tight">{selectedTeacher.full_name}</h3>
                                <p className="text-slate-500 text-[10px] uppercase font-black tracking-widest">{selectedTeacher.staff_role || 'Faculty Member'}</p>
                            </div>
                        </div>

                        <div className="space-y-2">
                            <label className="text-xs font-black uppercase tracking-widest text-slate-500 ml-2">Access PIN / Password</label>
                            <input 
                                type="password"
                                value={password}
                                onChange={(e) => setPassword(e.target.value)}
                                className="w-full bg-white/5 border border-white/10 rounded-2xl p-4 text-white placeholder:text-slate-600 text-center text-2xl tracking-[12px] focus:outline-none focus:ring-2 focus:ring-rose-500/50 transition-all"
                                placeholder="••••"
                                autoFocus
                            />
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <button 
                                onClick={() => setStep(2)}
                                className="py-4 bg-white/5 text-slate-400 rounded-2xl font-black uppercase tracking-widest hover:bg-white/10 transition-all"
                            >
                                Clear
                            </button>
                            <button 
                                onClick={handleFinalLogin}
                                disabled={loading}
                                className="py-4 bg-rose-600 text-white rounded-2xl font-black uppercase tracking-widest hover:bg-rose-700 transition-all shadow-xl shadow-rose-500/20 flex items-center justify-center gap-2"
                            >
                                {loading ? <Loader2 className="animate-spin" /> : "Access Portal"}
                            </button>
                        </div>
                    </div>
                )}
            </div>

            {/* Footer */}
            <div className="absolute bottom-8 left-0 w-full text-center z-10">
                <p className="text-slate-600 text-xs font-bold tracking-widest uppercase">
                    &copy; {new Date().getFullYear()} RajSchool Inc. &bull; Secure Faculty Network
                </p>
            </div>
        </div>
    );
};

export default TeacherLogin;
