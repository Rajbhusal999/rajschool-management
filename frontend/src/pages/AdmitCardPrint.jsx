import React, { useState, useEffect } from 'react';
import { useSearchParams, useNavigate } from 'react-router-dom';
import { 
  studentService, 
  examService,
  institutionService 
} from '../services/api';
import { 
  Printer, 
  ChevronLeft, 
  Loader2
} from 'lucide-react';

const AdmitCardPrint = () => {
    const [searchParams] = useSearchParams();
    const navigate = useNavigate();
    
    // Config from URL
    const selectedClass = searchParams.get('class');
    const examType = searchParams.get('exam');
    const year = searchParams.get('year');
    const studentIds = searchParams.get('ids')?.split(',') || [];

    // UI/Data State
    const [loading, setLoading] = useState(true);
    const [students, setStudents] = useState([]);
    const [schedule, setSchedule] = useState(null);
    const [institution, setInstitution] = useState(null);

    useEffect(() => {
        fetchData();
        // Force print background graphics for the watermark
        const style = document.createElement('style');
        style.innerHTML = `
            @media print {
                body { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            }
        `;
        document.head.appendChild(style);
    }, []);

    const fetchData = async () => {
        try {
            const [instRes, schRes, stdRes] = await Promise.all([
                institutionService.get(),
                examService.getSchedule({ class: selectedClass, examType, year }),
                studentService.getAll({ studentClass: selectedClass })
            ]);
            
            setInstitution(instRes.data);
            setInstitution(instRes.data);
            setSchedule(schRes.data);
            
            // Filter only selected students from the class
            // Robust check using both s.id and s.ID or s.Id
            const items = Array.isArray(stdRes.data) ? stdRes.data : [];
            const selectedStudents = items.filter(s => {
                const sid = (s.id || s.ID || s.Id || "").toString();
                return studentIds.includes(sid);
            });
            
            // Sort by roll number if available - handling both snake and camel
            selectedStudents.sort((a, b) => {
                const rollA = parseInt(a.rollNo || a.roll_no || 0);
                const rollB = parseInt(b.rollNo || b.roll_no || 0);
                return rollA - rollB;
            });
            setStudents(selectedStudents);
        } catch (error) {
            console.error('Error fetching data:', error);
        } finally {
            setLoading(false);
        }
    };

    const handlePrint = () => window.print();

    if (loading) return (
        <div className="h-screen flex flex-col items-center justify-center gap-4 bg-slate-50">
             <Loader2 className="w-12 h-12 text-rose-500 animate-spin" />
             <p className="font-bold text-slate-500">Preparing Print Layout...</p>
        </div>
    );

    // Split students into pairs for A4 distribution
    const studentPairs = [];
    for (let i = 0; i < students.length; i += 2) {
        studentPairs.push(students.slice(i, i + 2));
    }

    return (
        <div className="min-h-screen bg-slate-900 print:bg-white overflow-x-hidden font-outfit">
            {/* Toolbar - Hidden in Print */}
            <div className="print:hidden bg-slate-800 border-b border-white/10 p-5 sticky top-0 z-[100] shadow-2xl">
                <div className="max-w-6xl mx-auto flex items-center justify-between">
                    <button 
                        onClick={() => navigate(-1)}
                        className="flex items-center gap-3 text-white/70 hover:text-white transition-all font-black text-xs uppercase tracking-widest"
                    >
                        <ChevronLeft size={20} strokeWidth={3} />
                        Back to Workspace
                    </button>
                    
                    <div className="flex items-center gap-8">
                        <div className="text-right">
                             <p className="text-[10px] font-black uppercase text-rose-400 tracking-[0.2em]">Print Preview Mode</p>
                             <p className="text-white font-black text-lg leading-tight">{students.length} Cards Generated</p>
                        </div>
                        <button 
                            onClick={handlePrint}
                            className="bg-emerald-500 hover:bg-emerald-600 text-white px-10 py-4 rounded-2xl font-black text-lg flex items-center gap-3 shadow-xl shadow-emerald-950/20 transition-all active:scale-95 group"
                        >
                            <Printer size={24} className="group-hover:scale-110 transition-transform" />
                            PRINT DOCUMENTS
                        </button>
                    </div>
                </div>
            </div>

            {/* Print Layout Container */}
            <div className="p-0 md:p-10 print:p-0">
                <div className="max-w-[210mm] mx-auto space-y-0 print:space-y-0">
                    {studentPairs.map((pair, pageIdx) => (
                        <div key={pageIdx} className="admit-card-page bg-white p-0 print:p-0 min-h-[297mm] print:min-h-[297mm] flex flex-col shadow-2xl print:shadow-none mb-12 print:mb-0 relative overflow-hidden">
                            {/* Visual Divider in UI, Hidden in Print */}
                            <div className="absolute top-[50%] left-0 right-0 border-t border-dashed border-slate-200 z-50 print:hidden pointer-events-none"></div>

                            {pair.map((student, idx) => (
                                <AdmitCard 
                                    key={student.id} 
                                    student={student} 
                                    institution={institution}
                                    schedule={schedule}
                                    examType={examType}
                                    year={year}
                                    isLastInPage={idx === 1 || pair.length === 1}
                                />
                            ))}
                        </div>
                    ))}
                </div>
            </div>

            <style dangerouslySetInnerHTML={{ __html: `
                @media print {
                    @page { 
                        size: A4 portrait; 
                        margin: 5mm; 
                    }
                    /* Force black text and visible elements */
                    * {
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                        color: black !important;
                    }
                    .text-rose-600 { color: #e11d48 !important; } /* Keep exam red if possible */
                    
                    /* Ultra-aggressive hide all site layout elements */
                    nav, header, footer, aside, .top-nav, .nav-container, .sidebar, 
                    .back-button, button.back-button, .print-hidden, [class*="TopNav"], [class*="Sidebar"] {
                        display: none !important;
                        opacity: 0 !important;
                    }
                }
                .admit-card-container {
                    height: 138mm;
                    width: 200mm;
                    margin: 0 auto;
                    padding: 5mm;
                    position: relative;
                    overflow: hidden;
                    box-sizing: border-box;
                    display: flex;
                    flex-direction: column;
                }
                .card-border-outer {
                    border: 1px solid #000;
                    height: 100%;
                    width: 100%;
                    padding: 2px;
                    box-sizing: border-box;
                }
                .card-border-inner {
                    border: 2px solid #000;
                    outline: 1px solid #000;
                    outline-offset: 2px;
                    border-radius: 4px;
                    height: 100%;
                    width: 100%;
                    padding: 6mm;
                    position: relative;
                    box-sizing: border-box;
                    display: flex;
                    flex-direction: column;
                    background: white;
                }
                .watermark-box {
                    position: absolute;
                    top: 55%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    width: 250px;
                    height: 250px;
                    opacity: 0.08;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 0;
                    pointer-events: none;
                }
                .saraswati-top-left {
                    width: 70px;
                    height: 70px;
                    object-fit: contain;
                }
                .institution-logo-top {
                    width: 65px;
                    height: 65px;
                    object-fit: contain;
                }
            `}} />
        </div>
    );
};

const AdmitCard = ({ student, institution, schedule, examType, year, isLastInPage }) => {
    // Subject grid construction
    const scheduleData = schedule?.[0] || schedule || {};
    const subjectList = scheduleData.subjectData || scheduleData.subject_data || [];
    const tableRows = [];
    for (let i = 0; i < 5; i++) {
        tableRows.push({
            left: subjectList[i] || null,
            right: subjectList[i + 5] || null
        });
    }

    return (
        <div className="admit-card-container">
            <div className="card-border-outer">
                <div className="card-border-inner">
                    {/* Background Watermark */}
                    {institution?.logoUrl && (
                        <div className="watermark-box">
                            <img src={institution.logoUrl} alt="" className="w-full h-full object-contain" />
                        </div>
                    )}

                    {/* Header: Logo | Institution Info | Saraswati */}
                    <div className="flex items-center justify-between border-b-2 border-black pb-3 relative z-10">
                        <img 
                            src={institution?.logoUrl || 'https://cdn-icons-png.flaticon.com/512/5327/5327041.png'} 
                            alt="Logo" 
                            className="institution-logo-top" 
                            onError={(e) => { e.target.onerror = null; e.target.src="https://cdn-icons-png.flaticon.com/512/5327/5327041.png"}}
                        />
                        
                        <div className="text-center flex-1 mx-4">
                            <h1 className="text-3xl font-black uppercase leading-tight tracking-tighter">{institution?.schoolName || 'YOUR SCHOOL NAME'}</h1>
                            <p className="text-[10px] font-bold text-slate-700 uppercase tracking-wide">{institution?.address || 'School Address Line'}</p>
                            <h2 className="text-md font-black text-rose-600 mt-2 border-t border-slate-200 pt-1 inline-block uppercase tracking-widest leading-none">
                                {examType?.replace(/_/g, ' ')} EXAMINATION - {year}
                            </h2>
                        </div>

                        <img 
                            src="/saraswati.png" 
                            alt="Saraswati" 
                            className="saraswati-top-left"
                            onError={(e) => { e.target.onerror = null; e.target.src="https://upload.wikimedia.org/wikipedia/commons/thumb/d/d4/Saraswati_veena.svg/512px-Saraswati_veena.svg.png"}}
                        />
                    </div>

                    {/* Admit Card Badge */}
                    <div className="flex justify-center -mt-3.5 relative z-20">
                        <div className="bg-black text-white px-8 py-1.5 rounded-[4px] text-[11px] font-black uppercase tracking-[0.4em] shadow-sm">
                            ADMIT CARD
                        </div>
                    </div>

                    {/* Info Section */}
                    <div className="grid grid-cols-12 gap-4 mt-6 border border-black rounded p-3 bg-white/40 backdrop-blur-sm relative z-10 font-bold text-xs">
                        <div className="col-span-8 space-y-2">
                            <div className="flex items-end border-b border-dotted border-black/30 pb-0.5">
                                <span className="text-[9px] uppercase tracking-wider text-slate-500 w-32 font-black">Student Name:</span>
                                <span className="flex-1 uppercase font-black text-black">{student.fullName || student.full_name}</span>
                            </div>
                            <div className="flex items-end border-b border-dotted border-black/30 pb-0.5">
                                <span className="text-[9px] uppercase tracking-wider text-slate-500 w-32 font-black">Symbol / Roll:</span>
                                <span className="flex-1 font-black text-black">{student.symbolNo || student.symbol_no || student.rollNo || student.roll_no}</span>
                            </div>
                        </div>
                        <div className="col-span-4 border-l border-black/20 pl-4 space-y-2">
                            <div className="flex justify-between items-end border-b border-dotted border-black/30 pb-0.5">
                                <span className="text-[9px] uppercase tracking-wider text-slate-500 font-black">Class:</span>
                                <span className="text-black font-bold">{student.class || student.studentClass || "7"}</span>
                            </div>
                            <div className="flex justify-between items-end border-b border-dotted border-black/30 pb-0.5">
                                <span className="text-[9px] uppercase tracking-wider text-slate-500 font-black">Shift:</span>
                                <span className="uppercase text-black font-bold">{scheduleData.shift || "DAY"}</span>
                            </div>
                            <div className="flex justify-between items-end border-b border-dotted border-black/30 pb-0.5">
                                <span className="text-[9px] uppercase tracking-wider text-slate-500 font-black">Time:</span>
                                <span className="whitespace-nowrap text-black font-bold">{scheduleData.examTime || scheduleData.exam_time || '10:00 - 01:00'}</span>
                            </div>
                        </div>
                    </div>

                    {/* Subjects Table */}
                    <div className="mt-5 flex-1 relative z-10">
                        <table className="w-full border-collapse border-2 border-black text-[10px]">
                            <thead>
                                <tr className="bg-slate-100 uppercase font-black">
                                    <th className="border border-black p-1 w-[18%]">Date</th>
                                    <th className="border border-black p-1 w-[22%]">Subject</th>
                                    <th className="border border-black p-1 w-[10%]">Sign</th>
                                    <th className="border border-black p-1 w-[18%]">Date</th>
                                    <th className="border border-black p-1 w-[22%]">Subject</th>
                                    <th className="border border-black p-1 w-[10%]">Sign</th>
                                </tr>
                            </thead>
                            <tbody>
                                {tableRows.map((row, idx) => (
                                    <tr key={idx} className="h-7">
                                        <td className="border border-black px-2 text-center font-bold">{row.left?.date || '/ /'}</td>
                                        <td className="border border-black px-2 uppercase font-black">{row.left?.subject || ''}</td>
                                        <td className="border border-black"></td>
                                        <td className="border border-black px-2 text-center font-bold">{row.right?.date || '/ /'}</td>
                                        <td className="border border-black px-2 uppercase font-black">{row.right?.subject || ''}</td>
                                        <td className="border border-black"></td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {/* Footer: Signatures */}
                    <div className="grid grid-cols-3 gap-12 mt-10 relative z-10 mb-2">
                        <div className="text-center">
                            <div className="border-t border-slate-400 mb-1 mx-4"></div>
                            <p className="text-[9px] font-black uppercase tracking-widest">Class Teacher</p>
                        </div>
                        <div className="text-center">
                            <div className="border-t border-slate-400 mb-1 mx-4"></div>
                            <p className="text-[9px] font-black uppercase tracking-widest">Exam Co-ordinator</p>
                        </div>
                        <div className="text-center">
                            <div className="border-t border-slate-400 mb-1 mx-4"></div>
                            <p className="text-[9px] font-black uppercase tracking-widest">Principal</p>
                        </div>
                    </div>

                    {/* Bottom Perforation Hint in UI */}
                    {!isLastInPage && (
                        <div className="absolute bottom-[-10px] left-0 right-0 flex items-center justify-center print:hidden">
                            <span className="bg-white px-4 text-[9px] font-black text-slate-300 uppercase tracking-[0.5em]">CUT LINE</span>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
};

export default AdmitCardPrint;
