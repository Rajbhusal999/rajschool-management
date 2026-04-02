import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { 
  GraduationCap, Clock, Calendar, Rocket, LogIn, ChevronRight, 
  Users, BarChart3, TrendingUp, Phone, FileText, CalendarCheck, CreditCard, Shield
} from 'lucide-react';

const LandingPage = () => {
    const [time, setTime] = useState(new Date().toLocaleTimeString());
    const [scrolled, setScrolled] = useState(false);

    useEffect(() => {
        const timer = setInterval(() => setTime(new Date().toLocaleTimeString()), 1000);
        const handleScroll = () => setScrolled(window.scrollY > 20);
        window.addEventListener('scroll', handleScroll);
        return () => {
            clearInterval(timer);
            window.removeEventListener('scroll', handleScroll);
        };
    }, []);


    return (
        <div className="dark-theme-landing font-['Outfit',sans-serif] selection:bg-indigo-500/30 overflow-x-hidden">
            {/* Navigation */}
            <nav className={`fixed top-0 left-0 right-0 z-50 transition-all duration-500 ${scrolled ? 'glass-nav py-3' : 'py-6 px-4'}`}>
                <div className="max-w-7xl mx-auto flex items-center justify-between px-6">
                    <div className="flex items-center gap-8">
                        <div className="flex items-center gap-3">
                            <div className="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-500/20">
                                <GraduationCap size={24} />
                            </div>
                            <span className="text-2xl font-black tracking-tight">Smart <span className="text-white">विद्यालय</span></span>
                        </div>

                        {/* Glass Clock Container */}
                        <div className="hidden lg:flex items-center gap-4 px-5 py-2 glass-card rounded-full text-xs font-bold text-slate-300">
                            <div className="flex items-center gap-2">
                                <Clock size={14} className="text-orange-400" />
                                <span className="text-white tabular-nums">{time}</span>
                            </div>


                            <div className="w-px h-3 bg-slate-700"></div>
                            <div className="flex items-center gap-2">
                                <Calendar size={14} className="text-slate-400" />
                                <span>{new Date().toLocaleDateString('en-GB', { weekday: 'long', day: '2-digit', month: 'short', year: 'numeric' })}</span>
                            </div>
                        </div>
                    </div>

                    <div className="flex items-center gap-3">
                        <Link to="/features" className="hidden sm:block px-6 py-2.5 text-slate-300 font-bold text-sm hover:text-white transition-colors">Features</Link>
                        <button className="hidden sm:block px-6 py-2.5 text-slate-300 font-bold text-sm hover:text-white transition-colors">About</button>
                        <Link to="/login" className="px-6 py-2.5 bg-rose-600/20 text-rose-400 rounded-xl font-bold text-sm hover:bg-rose-600/30 transition-all">Teacher Login</Link>
                        <Link to="/login" className="px-6 py-2.5 bg-indigo-600/20 text-indigo-400 rounded-xl font-bold text-sm hover:bg-indigo-600/30 transition-all">Login</Link>
                        <Link to="/register" className="px-6 py-2.5 bg-indigo-600 text-white rounded-xl font-bold text-sm shadow-xl shadow-indigo-500/20 hover:scale-105 transition-all">Get Started</Link>

                    </div>
                </div>
            </nav>

            {/* Hero Section */}
            <section className="relative pt-40 pb-20 px-6">
                {/* Background Glows */}
                <div className="absolute top-0 right-0 w-[800px] h-[800px] bg-glow-indigo pointer-events-none opacity-50"></div>
                <div className="absolute -bottom-40 -left-40 w-[600px] h-[600px] bg-radial from-pink-500/10 to-transparent blur-[100px] pointer-events-none"></div>

                <div className="max-w-7xl mx-auto grid lg:grid-cols-2 gap-12 items-center">
                    <div className="space-y-8 relative z-10">
                        <div className="inline-flex items-center gap-2 px-4 py-2 bg-indigo-500/10 rounded-full border border-indigo-500/20 animate-in fade-in slide-in-from-left duration-700">
                            <div className="w-2 h-2 rounded-full bg-indigo-400 animate-pulse"></div>
                            <span className="text-xs font-black uppercase tracking-widest text-indigo-300">#1 School Management Platform</span>
                        </div>

                        <h1 className="text-6xl md:text-8xl font-black leading-tight tracking-tighter text-white">
                            Manage Your <br />
                            School <br />
                            <span className="text-gradient-indigo">with Zero Gravity</span>
                        </h1>



                        <p className="text-lg text-slate-400 font-medium max-w-xl leading-relaxed">
                            Experience the next generation of school management. Automated attendance, instant billing, and powerful analytics—all in one beautiful interface.
                        </p>

                        <div className="flex flex-wrap gap-4 pt-4">
                            <Link to="/register" className="px-10 py-5 bg-indigo-600 text-white rounded-[26px] font-black text-lg shadow-2xl shadow-indigo-500/30 hover:scale-105 active:scale-95 transition-all flex items-center gap-3">
                                <Rocket size={24} />
                                Start Free Trial
                            </Link>

                            <Link to="/login" className="px-10 py-5 bg-transparent text-white border-none rounded-[26px] font-black text-lg hover:bg-white/5 transition-all flex items-center gap-3">
                                <LogIn size={24} />
                                Login
                            </Link>
                        </div>
                    </div>

                    {/* Floating Visuals */}
                    <div className="relative h-[600px] hidden lg:block">
                        {/* Attendance Card */}
                        <div className="absolute top-1/2 left-0 -translate-y-1/2 w-80 glass-card p-6 rounded-[32px] shadow-2xl animate-float-slow z-20">
                            <div className="flex items-center justify-between mb-6">
                                <h4 className="font-black text-slate-200">Attendance Overview</h4>
                                <span className="text-[10px] font-black px-2 py-1 bg-emerald-500/20 text-emerald-400 rounded-lg">LIVE</span>
                            </div>
                            <div className="space-y-4">
                                <div className="h-2 w-full bg-white/5 rounded-full overflow-hidden">
                                    <div className="h-full w-[92%] bg-gradient-to-r from-indigo-500 to-pink-500"></div>
                                </div>
                                <div className="flex justify-between text-xs font-bold text-slate-400">
                                    <span>Present: 92%</span>
                                    <span>Absent: 8%</span>
                                </div>
                            </div>
                        </div>

                        {/* Students Managed Card */}
                        <div className="absolute top-20 right-0 w-64 glass-card p-8 rounded-[32px] text-center shadow-2xl animate-float delay-700 z-30 transform hover:scale-110 transition-transform">
                            <div className="w-14 h-14 bg-indigo-600/20 text-indigo-400 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <Users size={28} />
                            </div>
                            <h3 className="text-3xl font-black mb-1">50k+</h3>
                            <p className="text-xs font-bold text-slate-400">Students Managed</p>
                        </div>

                        {/* Fees Card */}
                        <div className="absolute bottom-10 right-10 w-64 glass-card p-8 rounded-[32px] text-center shadow-2xl animate-float delay-1000 z-10">
                            <div className="w-14 h-14 bg-pink-600/20 text-pink-400 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <BarChart3 size={28} />
                            </div>
                            <h3 className="text-3xl font-black mb-1">$2M+</h3>
                            <p className="text-xs font-bold text-slate-400">Fees Processed</p>
                        </div>
                    </div>
                </div>
            </section>

            {/* Features Section */}
            <section className="py-32 px-6">
                <div className="max-w-7xl mx-auto space-y-20">
                    <div className="text-center space-y-4 max-w-2xl mx-auto">
                        <span className="text-xs font-black uppercase tracking-widest text-orange-400">Powerful Modules</span>
                        <h2 className="text-5xl font-black text-white p-4">Everything You Need</h2>
                        <p className="text-slate-400 font-medium leading-relaxed">
                            From admissions to alumni, handle every aspect of your institution with our comprehensive suite of tools.
                        </p>
                    </div>


                    <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8 ">
                        {[
                            { icon: GraduationCap, title: 'Student 360°', color: 'indigo', desc: 'Comprehensive student profiles with admission details and academic history.' },
                            { icon: Users, title: 'Expert Faculty', color: 'emerald', desc: 'Efficiently manage teacher profiles, subject allocations, and departmental roles.' },
                            { icon: FileText, title: 'Exams & Results', color: 'blue', desc: 'Create exams, generate professional cards, and publish marksheets instantly.' },
                            { icon: CreditCard, title: 'Billing System', color: 'orange', desc: 'Track student fees, manage donations, and generate digital receipts seamlessly.' },
                            { icon: CalendarCheck, title: 'Smart Attendance', color: 'pink', desc: 'Digital daily attendance tracking with automated notifications to parents.' },
                            { icon: Phone, title: 'Mobile Ready', color: 'slate', desc: 'Fully responsive design that works perfectly on all devices and platforms.' }
                        ].map((m, i) => (
                            <div key={i} className="group glass-card p-10 rounded-[40px] hover:bg-white/10 transition-all duration-500 cursor-default border-transparent hover:border-white/20 bg-white/5">
                                <div className={`w-16 h-16 rounded-2xl flex items-center justify-center mb-8 transition-transform duration-500 group-hover:scale-110 group-hover:rotate-3
                                    ${m.color === 'indigo' ? 'bg-orange-500/20 text-orange-300' : ''}
                                    ${m.color === 'emerald' ? 'bg-emerald-500/20 text-emerald-300' : ''}
                                    ${m.color === 'blue' ? 'bg-blue-500/20 text-blue-300' : ''}
                                    ${m.color === 'orange' ? 'bg-orange-500/20 text-orange-300' : ''}
                                    ${m.color === 'pink' ? 'bg-pink-500/20 text-pink-300' : ''}
                                    ${m.color === 'slate' ? 'bg-slate-500/20 text-slate-300' : ''}
                                `}>
                                    <m.icon size={32} />
                                </div>
                                <h3 className="text-2xl font-black text-white mb-4">{m.title}</h3>
                                <p className="text-slate-300 leading-bold font-medium">{m.desc}</p>
                            </div>





                        ))}
                    </div>
                </div>
            </section>

            {/* Footer */}
            <footer className="pt-32 pb-12 px-6 border-t border-white/5 bg-black/20">
                <div className="max-w-7xl mx-auto">
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-20">
                        <div className="space-y-6">
                            <div className="flex items-center gap-3">
                                <GraduationCap size={32} className="text-indigo-500" />
                                <span className="text-2xl font-black tracking-tight text-white">Smart <span className="text-indigo-500">विद्यालय</span></span>
                            </div>
                            <p className="text-slate-400 font-medium leading-relaxed">
                                Empowering educational institutions with cutting-edge technology. Join the digital revolution today.
                            </p>
                        </div>
                        
                        <div>
                            <h4 className="text-white font-black mb-8 uppercase tracking-widest text-xs">Quick Links</h4>
                            <ul className="space-y-4 text-sm font-bold text-slate-500">
                                <Link to="/features" className="hover:text-indigo-400 transition-colors cursor-pointer block">Features</Link>
                                <li className="hover:text-indigo-400 transition-colors cursor-pointer">Pricing</li>
                                <li className="hover:text-indigo-400 transition-colors cursor-pointer">Case Studies</li>
                                <li className="hover:text-indigo-400 transition-colors cursor-pointer">Support</li>
                            </ul>
                        </div>

                        <div>
                            <h4 className="text-white font-black mb-8 uppercase tracking-widest text-xs">Contact Us</h4>
                            <ul className="space-y-4 text-sm font-bold text-slate-500">
                                <li>Bharatpur, Nepal</li>
                                <li className="text-indigo-400">smartvidhyalaya9861@gmail.com</li>
                                <li>+977-9861079061</li>
                            </ul>
                        </div>

                        <div className="space-y-6">
                            <h4 className="text-white font-black mb-8 uppercase tracking-widest text-xs">Newsletter</h4>
                            <div className="flex bg-white/5 p-1 rounded-2xl">
                                <input type="email" placeholder="Email address" className="bg-transparent border-none px-4 py-2 flex-1 outline-none text-white text-sm" />
                                <button className="p-2 bg-indigo-600 rounded-xl text-white"><ChevronRight size={20} /></button>
                            </div>
                        </div>
                    </div>

                    <div className="pt-8 border-t border-white/5 flex flex-col md:flex-row justify-between items-center gap-4 text-xs font-bold text-slate-500">
                        <p>© 2026 Smart विद्यालय. Developed with ♥ by Raj Bhusal and Dibash Sharma.</p>
                        <div className="flex gap-6">
                            <span>Privacy Policy</span>
                            <span>Terms of Service</span>
                            <Link to="/attendance/entry" className="opacity-10 hover:opacity-100 transition-opacity">Staff Portal</Link>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    );
};

export default LandingPage;
