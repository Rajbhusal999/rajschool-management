import React, { useEffect, useState } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import { studentService, examService, institutionService } from '../services/api';
import './MarkSlipPrint.css';

const MarkSlipPrint = () => {
    const location = useLocation();
    const navigate = useNavigate();
    const query = new URLSearchParams(location.search);
    
    const year = query.get('year');
    const studentClass = query.get('class');
    const examType = query.get('examType');
    const subject = query.get('subject');
    
    const [data, setData] = useState({ 
        students: [], 
        marks: {}, 
        attendance: {}, 
        schoolInfo: null 
    });
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (!year || !studentClass || !examType || !subject) {
            console.error('Missing required parameters:', { year, studentClass, examType, subject });
            return;
        }
        fetchData();
    }, []);

    const fetchData = async () => {
        setLoading(true);
        try {
            const [stdRes, markRes, attRes, schoolRes] = await Promise.all([
                studentService.getAll({ studentClass }),
                examService.getMarks({ examType, year, studentClass, subject }),
                examService.getExamAttendance({ examType, year, studentClass }),
                institutionService.get()
            ]);

            // Map marks by studentId
            const markMap = {};
            markRes.data.forEach(m => {
                markMap[m.studentId] = m;
            });

            // Map attendance by studentId
            const attMap = {};
            attRes.data.forEach(a => {
                attMap[a.studentId] = a;
            });

            // Sort students by symbol number or name safely
            const sortedStudents = (stdRes.data || []).sort((a, b) => {
                if (a.symbolNo && b.symbolNo) {
                    return String(a.symbolNo).localeCompare(String(b.symbolNo));
                }
                const nameA = a.fullName || '';
                const nameB = b.fullName || '';
                return nameA.localeCompare(nameB);
            });

            setData({
                students: sortedStudents,
                marks: markMap,
                attendance: attMap,
                schoolInfo: schoolRes.data
            });
            /* Removed auto-trigger print to allow user manual control via button */
        } catch (error) {
            console.error('Error fetching mark slip data:', error);
        } finally {
            setLoading(false);
        }
    };

    if (loading) {
        return (
            <div className="flex flex-col items-center justify-center min-h-screen bg-slate-50">
                <div className="w-12 h-12 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                <div className="text-emerald-700 font-bold animate-pulse text-lg">Generating Mark Slip...</div>
                <div className="text-slate-400 text-sm mt-2">Preparing A4 Portrait Layout</div>
            </div>
        );
    }

    const { schoolInfo, students, marks, attendance } = data;
    const isBasicLevel = ['4', '5', '6', '7', '8'].includes(studentClass);

    const translateExamType = (type) => {
        if (!type) return '';
        if (type.includes('First')) return 'प्रथम';
        if (type.includes('Second')) return 'द्वितीय';
        if (type.includes('Third')) return 'तृतीय';
        if (type.includes('Final')) return 'वार्षिक';
        return type;
    };

    return (
        <div className="markslip-print-wrapper pb-20">
            {/* Top Toolbar - Hidden during print */}
            <div className="ms-top-toolbar no-print">
                <div className="toolbar-content">
                    <button 
                        onClick={() => navigate(-1)} 
                        className="ms-btn-secondary"
                    >
                        ← Back to Config
                    </button>
                    <div className="toolbar-info">
                        Print Preview: {subject} - {studentClass}
                    </div>
                    <button 
                        onClick={() => window.print()} 
                        className="ms-btn-primary"
                    >
                        Print Mark Slip
                    </button>
                </div>
            </div>

            <div className="print-page portrait A4">
                {/* Watermark Logo */}
                {schoolInfo?.logo && (
                    <div className="ms-watermark">
                        <img src={schoolInfo.logo} alt="watermark" />
                    </div>
                )}

                {isBasicLevel ? (
                    <table className="ms-table border-[3px] border-black border-collapse mt-2 w-full">
                        <thead>
                            <tr>
                                <th colSpan="10" className="text-center font-black text-xl py-3 border-black border-2 border-b-0 text-gray-900 tracking-wide">
                                    {schoolInfo?.schoolName || 'श्री हिमालय आधारभूत विद्यालय (१-८)'}
                                </th>
                            </tr>
                            <tr>
                                <th colSpan="8" className="text-left font-bold text-sm border-black border-2 border-r-0 border-t-0 p-2">
                                    विषय : {subject}
                                </th>
                                <th colSpan="2" className="text-right font-bold text-sm border-black border-2 border-l-0 border-t-0 p-2 pr-4">
                                    कक्षा : {studentClass}
                                </th>
                            </tr>
                            <tr>
                                <th rowSpan="2" className="w-[60px] text-center border-black border-2 font-black py-2">क.सं.</th>
                                <th rowSpan="2" className="w-[30%] text-center border-black border-2 font-black py-2">विद्यार्थीको नाम</th>
                                <th colSpan="3" className="text-center border-black border-2 font-black py-1">सहभागिता</th>
                                <th colSpan="3" className="text-center border-black border-2 font-black py-1">परियोजना / प्रयोगात्मक</th>
                                <th rowSpan="2" className="w-[90px] text-center border-black border-2 font-black leading-tight py-2">{translateExamType(examType)} त्रैमासिक<br/>परीक्षा (१०)</th>
                                <th rowSpan="2" className="w-[70px] text-center border-black border-2 font-black py-2">जम्मा<br/>५०</th>
                            </tr>
                            <tr>
                                <th className="w-[60px] text-center border-black border-2 font-bold leading-tight py-1">हाजिरी<br/>(२)</th>
                                <th className="w-[60px] text-center border-black border-2 font-bold leading-tight py-1">सक्रियता<br/>(२)</th>
                                <th className="w-[60px] text-center border-black border-2 font-bold leading-tight py-1">जम्मा<br/>(४)</th>
                                <th className="w-[50px] text-center border-black border-2 font-bold py-1">१६</th>
                                <th className="w-[50px] text-center border-black border-2 font-bold py-1">२०</th>
                                <th className="w-[60px] text-center border-black border-2 font-bold leading-tight py-1">जम्मा<br/>(३६)</th>
                            </tr>
                        </thead>
                        <tbody>
                            {students.map((student, index) => {
                                const sn = student.symbolNo || index + 1;
                                return (
                                    <tr key={student.id}>
                                        <td className="text-center border-black border-2 font-medium">{sn}</td>
                                        <td className="border-black border-2 font-medium px-2">{student.fullName}</td>
                                        <td className="border-black border-2"></td>
                                        <td className="border-black border-2"></td>
                                        <td className="border-black border-2"></td>
                                        <td className="border-black border-2"></td>
                                        <td className="border-black border-2"></td>
                                        <td className="border-black border-2"></td>
                                        <td className="border-black border-2"></td>
                                        <td className="border-black border-2"></td>
                                    </tr>
                                );
                            })}
                            {students.length < 25 && Array.from({ length: 25 - students.length }).map((_, i) => (
                                <tr key={`empty-${i}`}>
                                    <td className="h-[26px] border-black border-2"></td>
                                    <td className="border-black border-2"></td>
                                    <td className="border-black border-2"></td>
                                    <td className="border-black border-2"></td>
                                    <td className="border-black border-2"></td>
                                    <td className="border-black border-2"></td>
                                    <td className="border-black border-2"></td>
                                    <td className="border-black border-2"></td>
                                    <td className="border-black border-2"></td>
                                    <td className="border-black border-2"></td>
                                </tr>
                            ))}
                            {/* Empty space below rows */}
                            <tr>
                                <td colSpan="10" className="border-black border-2 h-16 bg-white border-b-0"></td>
                            </tr>
                            <tr>
                                <th colSpan="3" className="text-left font-bold text-sm border-black border-2 border-r-0 border-t-0 p-3 pb-6">
                                    <span className="ml-12">विषय शिक्षक :</span>
                                </th>
                                <th colSpan="7" className="border-black border-2 border-l-0 border-t-0 p-3 pb-6">
                                </th>
                            </tr>
                        </tbody>
                    </table>
                ) : (
                    <>
                        {/* Header Section */}
                        <div className="ms-header text-center">
                            <h1 className="ms-school-name uppercase bold">{schoolInfo?.schoolName || 'SCHOOL NAME'}</h1>
                            <p className="ms-school-address">{schoolInfo?.address || 'Address'}</p>
                            <div className="ms-title-wrapper">
                                <h2 className="ms-title uppercase bold">Markslip</h2>
                            </div>
                            <h3 className="ms-exam-type uppercase">{examType?.replace('_', ' ')} Exam - {year}</h3>
                        </div>

                        {/* Info Section */}
                        <div className="ms-info-grid">
                            <div className="ms-info-item">
                                <span className="label text-left">Class:-</span>
                                <span className="value border-b-dotted w-32">{studentClass}</span>
                            </div>
                            <div className="ms-info-item text-right justify-end ml-auto">
                                <span className="label">Subject:-</span>
                                <span className="value border-b-dotted flex-grow min-w-[200px]">{subject}</span>
                            </div>
                        </div>

                        {/* Marks Table */}
                        <table className="ms-table">
                            <thead>
                                <tr>
                                    <th rowSpan="2" className="w-24">Symbol Number</th>
                                    <th rowSpan="2">Student Name</th>
                                    <th colSpan="2" className="text-center">Obtained Marks</th>
                                    <th rowSpan="2" className="w-28">Total Marks</th>
                                    <th rowSpan="2" className="w-28">Attendance</th>
                                </tr>
                                <tr>
                                    <th className="w-16 text-center">WR</th>
                                    <th className="w-16 text-center">OR</th>
                                </tr>
                            </thead>
                            <tbody>
                                {students.map((student) => {
                                    const m = marks[student.id];
                                    const att = attendance[student.id];
                                    const wr = m?.practical || 0;
                                    const oral = m?.terminal || 0;
                                    const total = Number(wr) + Number(oral);
                                    
                                    return (
                                        <tr key={student.id}>
                                            <td className="text-center">{student.symbolNo || '-'}</td>
                                            <td>{student.fullName}</td>
                                            <td className="text-center">{m ? wr : ''}</td>
                                            <td className="text-center">{m ? oral : ''}</td>
                                            <td className="text-center font-bold">{m ? total : ''}</td>
                                            <td className="text-center">{att?.presentDays || ''}</td>
                                        </tr>
                                    );
                                })}
                                {students.length < 25 && Array.from({ length: 25 - students.length }).map((_, i) => (
                                    <tr key={`empty-${i}`}>
                                        <td className="h-8"></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>

                        {/* Footer Section */}
                        <div className="ms-footer mt-12 flex justify-start">
                            <div className="ms-signature-box">
                                <span className="label">Subject Teacher:</span>
                                <span className="signature-line w-64 border-b-dotted"></span>
                            </div>
                        </div>
                    </>
                )}
            </div>
        </div>
    );
};

export default MarkSlipPrint;
