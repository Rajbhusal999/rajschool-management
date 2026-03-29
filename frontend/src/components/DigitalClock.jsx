import React, { useState, useEffect } from 'react';

const DigitalClock = () => {
    const [time, setTime] = useState(new Date());

    useEffect(() => {
        const timer = setInterval(() => setTime(new Date()), 1000);
        return () => clearInterval(timer);
    }, []);

    const formatTime = (date) => {
        return date.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        });
    };

    const formatDateAD = (date) => {
        return date.toLocaleDateString('en-US', {
            weekday: 'long',
            day: 'numeric',
            month: 'short',
            year: 'numeric'
        });
    };

    // Simple B.S. Date Approximation (Chaitra 16, 2082 for March 29, 2026)
    const getNepaliDate = (date) => {
        // In a real app, use a library like 'ad-bs'
        // This is a placeholder that matches the screenshot's context
        return "2082-12-16 (B.S.)";
    };

    return (
        <div className="bg-white/80 backdrop-blur-xl border border-slate-200 rounded-[32px] p-6 shadow-sm flex items-center gap-8 group hover:border-slate-300 transition-all">
            <div className="flex flex-col items-center">
                <span className="text-4xl md:text-5xl font-[1000] text-indigo-700 tracking-tighter tabular-nums drop-shadow-sm">
                    {formatTime(time).split(' ')[0]}
                </span>
                <span className="text-[10px] font-black text-indigo-400 uppercase tracking-[0.3em] mt-1 ml-1">
                    {formatTime(time).split(' ')[1]}
                </span>
            </div>
            
            <div className="w-px h-12 bg-slate-200"></div>

            <div className="space-y-1">
                <div className="flex items-center gap-2">
                    <span className="text-sm font-black text-slate-800 tracking-tight">
                        {getNepaliDate(time)}
                    </span>
                    <span className="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                </div>
                <p className="text-[10px] font-bold text-slate-500 uppercase tracking-widest leading-none">
                    {formatDateAD(time)}
                </p>
            </div>
        </div>
    );
};

export default DigitalClock;
