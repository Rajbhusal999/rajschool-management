import React from 'react';
import { Link } from 'react-router-dom';
import { 
    GraduationCap, Rocket, Eye, Users, 
    ChevronRight, Globe, Share2, Phone, Mail, MapPin
} from 'lucide-react';

const About = () => {
    return (
        <div className="font-['Outfit',sans-serif] selection:bg-indigo-500/30 overflow-x-hidden bg-[#FDFDFF]">
            {/* Hero Section */}
            <section className="relative pt-32 pb-20 px-6 text-center">
                <div className="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[600px] bg-indigo-500/10 blur-[120px] rounded-full pointer-events-none"></div>
                
                <div className="max-w-4xl mx-auto space-y-8 relative z-10">
                    <h1 className="text-4xl md:text-7xl font-[900] text-[#1A1C2E] leading-tight tracking-[-0.04em]">
                        Empowering Education <br />
                        <span className="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500">
                            Through Innovation
                        </span>
                    </h1>
                    <p className="text-lg md:text-xl text-slate-400 font-bold max-w-2xl mx-auto leading-relaxed">
                        Smart विद्यालय is more than just software. We are a team of educators and technologists dedicated to transforming how schools operate, making administration seamless and learning accessible.
                    </p>
                </div>
            </section>

            {/* Mission & Vision Section */}
            <section className="py-20 px-6">
                <div className="max-w-6xl mx-auto grid md:grid-cols-2 gap-12">
                    {/* Mission Card */}
                    <div className="bg-white p-12 rounded-[50px] shadow-[0_20px_60px_rgba(0,0,0,0.03)] border border-slate-50 hover:shadow-2xl hover:shadow-indigo-100 transition-all duration-500 group">
                        <div className="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-3xl flex items-center justify-center mb-10 transition-transform group-hover:scale-110 group-hover:rotate-3">
                            <Rocket size={32} />
                        </div>
                        <h2 className="text-3xl font-[900] text-[#1A1C2E] mb-6">Our Mission</h2>
                        <p className="text-slate-400 font-bold leading-relaxed text-lg">
                            To simplify school management by providing a comprehensive, user-friendly, and affordable digital platform that connects schools, teachers, students, and parents.
                        </p>
                    </div>

                    {/* Vision Card */}
                    <div className="bg-white p-12 rounded-[50px] shadow-[0_20px_60px_rgba(0,0,0,0.03)] border border-slate-50 hover:shadow-2xl hover:shadow-pink-100 transition-all duration-500 group">
                        <div className="w-16 h-16 bg-pink-50 text-pink-500 rounded-3xl flex items-center justify-center mb-10 transition-transform group-hover:scale-110 group-hover:-rotate-3">
                            <Eye size={32} />
                        </div>
                        <h2 className="text-3xl font-[900] text-[#1A1C2E] mb-6">Our Vision</h2>
                        <p className="text-slate-400 font-bold leading-relaxed text-lg">
                            To be the leading education technology provider in Nepal, fostering a digital ecosystem where every school has access to world-class management tools.
                        </p>
                    </div>
                </div>
            </section>

            {/* Passion Section */}
            <section className="py-32 px-6 bg-white/50">
                <div className="max-w-7xl mx-auto grid lg:grid-cols-2 gap-20 items-center">
                    <div className="space-y-10">
                        <h2 className="text-4xl md:text-5xl font-[900] text-[#1A1C2E] leading-tight">
                            Driven by Passion, <br />
                            Built for Impact.
                        </h2>
                        <div className="space-y-6">
                            <p className="text-slate-400 font-bold text-lg leading-relaxed">
                                Founded in 2024, Smart विद्यालय started with a simple observation: schools were spending too much time on paperwork and not enough time on students.
                            </p>
                            <p className="text-slate-400 font-bold text-lg leading-relaxed">
                                Our founder, Raj Bhusal, envisioned a platform that would automate the mundane, illuminate the important, and bring joy back to school administration. Today, we serve hundreds of institutions across the region.
                            </p>
                        </div>
                    </div>

                    <div className="relative">
                        <div className="w-full aspect-[4/3] bg-indigo-600 rounded-[60px] shadow-2xl overflow-hidden relative group">
                            <div className="absolute inset-0 bg-gradient-to-br from-indigo-500 via-purple-600 to-pink-500 opacity-90 group-hover:scale-110 transition-transform duration-700"></div>
                            <div className="absolute inset-0 flex items-center justify-center text-white/20">
                                <Users size={160} strokeWidth={1} />
                            </div>
                        </div>
                        {/* Stat Card Overlay */}
                        <div className="absolute -bottom-10 -left-10 bg-white p-10 rounded-[40px] shadow-2xl border border-slate-50 animate-in zoom-in duration-700 delay-300">
                            <h3 className="text-5xl font-[900] text-indigo-600 mb-2">3+</h3>
                            <p className="text-xs font-black text-slate-400 uppercase tracking-widest">Years of Excellence</p>
                        </div>
                    </div>
                </div>
            </section>

            {/* Same Footer as LandingPage for Consistency */}
            <footer className="pt-32 pb-12 px-6 bg-[#0B0D17] text-white">
                <div className="max-w-7xl mx-auto">
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-20">
                        <div className="space-y-8">
                            <div className="flex items-center gap-3">
                                <GraduationCap size={40} className="text-indigo-500" />
                                <span className="text-3xl font-[900] tracking-tight">Smart <span className="text-indigo-500">विद्यालय</span></span>
                            </div>
                            <p className="text-slate-400 font-bold leading-relaxed max-w-sm">
                                Empowering educational institutions with cutting-edge technology. Join the digital revolution today.
                            </p>
                            <div className="flex gap-4">
                                {[Globe, Share2].map((Icon, i) => (
                                    <button key={i} className="w-12 h-12 bg-white/5 rounded-2xl flex items-center justify-center text-slate-400 hover:bg-indigo-600 hover:text-white transition-all transform hover:-translate-y-1">
                                        <Icon size={20} />
                                    </button>
                                ))}
                                <button className="w-12 h-12 bg-white/5 rounded-2xl flex items-center justify-center text-slate-400 hover:bg-emerald-600 hover:text-white transition-all transform hover:-translate-y-1">
                                    <Phone size={20} className="fill-current" />
                                </button>
                            </div>
                        </div>
                        
                        <div>
                            <h4 className="font-[900] mb-10 uppercase tracking-[0.2em] text-[11px] text-slate-500">Quick Links</h4>
                            <ul className="space-y-5 text-[15px] font-[900] text-slate-400">
                                <Link to="/" className="hover:text-indigo-500 transition-colors cursor-pointer block">Features</Link>
                                <Link to="/pricing" className="hover:text-indigo-500 transition-colors cursor-pointer block">Pricing</Link>
                                <li className="hover:text-indigo-500 transition-colors cursor-pointer">Case Studies</li>
                                <li className="hover:text-indigo-500 transition-colors cursor-pointer">Support</li>
                            </ul>
                        </div>

                        <div>
                            <h4 className="font-[900] mb-10 uppercase tracking-[0.2em] text-[11px] text-slate-500">Contact Us</h4>
                            <ul className="space-y-6 text-[15px] font-[900]">
                                <li className="flex items-center gap-4 text-slate-400">
                                    <MapPin size={18} className="text-indigo-500" /> Bharatpur, Nepal
                                </li>
                                <li className="flex items-center gap-4 text-indigo-400">
                                    <Mail size={18} /> smartvidhyalaya9861@gmail.com
                                </li>
                                <li className="flex items-center gap-4 text-slate-400">
                                    <Phone size={18} className="text-indigo-500" /> +977-9861079061
                                </li>
                            </ul>
                        </div>

                        <div className="space-y-8">
                            <h4 className="font-[900] mb-10 uppercase tracking-[0.2em] text-[11px] text-slate-500">Newsletter</h4>
                            <div className="flex bg-white/5 p-2 rounded-[24px] border border-white/5">
                                <input type="email" placeholder="Email address" className="bg-transparent border-none px-4 flex-1 outline-none text-white text-sm font-bold" />
                                <button className="w-12 h-12 bg-indigo-600 rounded-2xl text-white flex items-center justify-center hover:bg-indigo-700 transition-all"><ChevronRight size={24} /></button>
                            </div>
                        </div>
                    </div>

                    <div className="pt-12 border-t border-white/5 flex flex-col md:flex-row justify-between items-center gap-6 text-[13px] font-[900] text-slate-500">
                        <p>© 2026 Smart विद्यालय. Developed with ♥ by Raj Bhusal and Dibash Sharma.</p>
                        <div className="flex gap-8">
                            <span className="hover:text-indigo-500 transition-colors cursor-pointer">Privacy Policy</span>
                            <span className="hover:text-indigo-500 transition-colors cursor-pointer">Terms of Service</span>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    );
};

export default About;
