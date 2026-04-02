import React from 'react';
import { Link } from 'react-router-dom';
import { 
  ArrowLeft, Globe, ExternalLink, BookOpen, GraduationCap, 
  FileText, Shield, Award, ClipboardCheck
} from 'lucide-react';

const EduResources = () => {
    const resources = [
        {
            title: "Ministry of Education (MOEST)",
            description: "The official portal of the Ministry of Education, Science and Technology, Nepal. Access policies, news, and organizational structures.",
            url: "https://moe.gov.np/",
            icon: <Shield className="text-white" size={24} />,
            color: "bg-indigo-600",
            borderColor: "border-indigo-500/20"
        },
        {
            title: "CEHRD Portal",
            description: "Center for Education and Human Resource Development. Access teacher data, educational statistics, and school-level resources.",
            url: "https://cehrd.gov.np/",
            icon: <Users className="text-white" size={24} />,
            color: "bg-emerald-600",
            borderColor: "border-emerald-500/20"
        },
        {
            title: "National Examination Board (NEB)",
            description: "Official results and information for SEE, Class 11, and Class 12 examinations. Download exam schedules and notices.",
            url: "https://www.neb.gov.np/",
            icon: <FileText className="text-white" size={24} />,
            color: "bg-blue-600",
            borderColor: "border-blue-500/20"
        },
        {
            title: "Curriculum Development Centre (CDC)",
            description: "Download curriculum, textbooks, and teacher guides for all school levels in Nepal. Access the digital library of resources.",
            url: "https://moecdc.gov.np/",
            icon: <BookOpen className="text-white" size={24} />,
            color: "bg-orange-600",
            borderColor: "border-orange-500/20"
        },
        {
            title: "Teachers Service Commission (TSC)",
            description: "Information on teacher recruitment, licensing, and permanent positions for community schools in Nepal.",
            url: "https://www.tsc.gov.np/",
            icon: <Award className="text-white" size={24} />,
            color: "bg-purple-600",
            borderColor: "border-purple-500/20"
        },
        {
            title: "UGC Nepal",
            description: "University Grants Commission. Resources for higher education, university statistics, and research grants in Nepal.",
            url: "https://www.ugcnepal.edu.np/",
            icon: <GraduationCap className="text-white" size={24} />,
            color: "bg-rose-600",
            borderColor: "border-rose-500/20"
        },
        {
            title: "CTEVT Nepal",
            description: "Council for Technical Education and Vocational Training. Explore technical courses, certifications, and vocational opportunities.",
            url: "https://www.ctevt.org.np/",
            icon: <ClipboardCheck className="text-white" size={24} />,
            color: "bg-cyan-600",
            borderColor: "border-cyan-500/20"
        }
    ];

    return (
        <div className="min-h-screen bg-[#020617] relative py-24 px-8 font-['Outfit',sans-serif] overflow-x-hidden">
            {/* Background Effects */}
            <div className="fixed inset-0 pointer-events-none">
                <div className="absolute top-[-10%] right-[-5%] w-[600px] h-[600px] bg-indigo-500/10 blur-[150px] rounded-full"></div>
                <div className="absolute bottom-[-10%] left-[-5%] w-[500px] h-[500px] bg-emerald-500/5 blur-[120px] rounded-full"></div>
            </div>

            <div className="max-w-6xl mx-auto relative z-10">
                {/* Header Section */}
                <div className="mb-20 space-y-6 text-center">
                    <Link to="/" className="inline-flex items-center gap-2 text-indigo-400 hover:text-white font-black text-xs uppercase tracking-[0.2em] transition-all bg-indigo-500/10 px-4 py-2 rounded-full border border-indigo-500/20 group">
                        <ArrowLeft size={16} className="group-hover:-translate-x-1 transition-transform" /> Back to Home Control
                    </Link>
                    <h1 className="text-5xl md:text-7xl font-[1000] text-white tracking-tighter leading-tight drop-shadow-2xl">
                        Educational <span className="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-emerald-400">Hub Nepal</span>
                    </h1>
                    <p className="text-slate-400 font-bold text-lg max-w-2xl mx-auto leading-relaxed">
                        Access official portals for Nepalese education system, curriculum, examinations, and institutional intelligence.
                    </p>
                </div>

                {/* Grid of Resources */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    {resources.map((res, index) => (
                        <a 
                            key={index}
                            href={res.url}
                            target="_blank"
                            rel="noopener noreferrer"
                            className={`group relative p-10 rounded-[40px] border ${res.borderColor} bg-white/5 backdrop-blur-3xl hover:bg-white/10 transition-all duration-500 hover:scale-[1.02] hover:-translate-y-2 flex flex-col items-start text-left`}
                        >
                            <div className={`w-16 h-16 ${res.color} rounded-2xl flex items-center justify-center mb-8 shadow-2xl shadow-indigo-600/20 group-hover:-rotate-6 transition-transform`}>
                                {res.icon}
                            </div>
                            <h3 className="text-2xl font-black text-white mb-4 flex items-center gap-3">
                                {res.title}
                                <ExternalLink size={18} className="text-slate-600 group-hover:text-indigo-400 transition-colors" />
                            </h3>
                            <p className="text-slate-400 font-bold text-sm leading-relaxed mb-8 flex-1">
                                {res.description}
                            </p>
                            <div className="flex items-center gap-2 text-[10px] font-black text-indigo-400 uppercase tracking-widest bg-indigo-500/10 px-4 py-2 rounded-full border border-indigo-500/20">
                                Verify Official Status <Globe size={12} />
                            </div>
                        </a>
                    ))}
                </div>

                {/* Footer Section */}
                <div className="mt-20 pt-12 border-t border-white/5 flex flex-col md:flex-row items-center justify-between gap-8 opacity-60">
                    <div className="flex items-center gap-3">
                        <div className="w-8 h-8 bg-slate-800 rounded-lg flex items-center justify-center text-white">
                            <Shield size={16} />
                        </div>
                        <span className="text-[10px] font-black text-slate-400 uppercase tracking-widest">Verified Educational Links • 2082 B.S.</span>
                    </div>
                    <div className="text-[10px] font-black text-slate-600 uppercase tracking-widest text-center">
                        Information provided is for official institutional reference only.
                    </div>
                </div>
            </div>
        </div>
    );
};

// Mock Users icon since it wasn't imported in my head but I used it
const Users = (props) => <svg {...props} xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>;

export default EduResources;
