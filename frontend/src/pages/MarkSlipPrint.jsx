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

            // Sort students by symbol number or name
            const sortedStudents = stdRes.data.sort((a, b) => {
                if (a.symbolNo && b.symbolNo) return a.symbolNo.localeCompare(b.symbolNo);
                return a.fullName.localeCompare(b.fullName);
            });

            setData({
                students: sortedStudents,
                marks: markMap,
                attendance: attMap,
                schoolInfo: schoolRes.data
            });
            
            // Auto-trigger print after data is loaded and rendered
            setTimeout(() => {
                window.print();
            }, 1500);
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

    return (
        <div className="markslip-print-wrapper">
            <div className="print-page portrait A4">
                {/* Watermark Logo */}
                {schoolInfo?.logo && (
                    <div className="ms-watermark">
                        <img src={schoolInfo.logo} alt="watermark" />
                    </div>
                )}

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
                        <span className="label">Subject:-</span>
                        <span className="value border-b-dotted flex-grow">{subject}</span>
                    </div>
                    <div className="ms-info-item text-right">
                        <span className="label">Class:-</span>
                        <span className="value border-b-dotted w-24">{studentClass}</span>
                    </div>
                </div>

                {/* Marks Table */}
                <table className="ms-table">
                    <thead>
                        <tr>
                            <th rowSpan="2" className="w-16">Symbol no.</th>
                            <th rowSpan="2">Student's Name</th>
                            <th colSpan="2" className="text-center">Obtained marks</th>
                            <th rowSpan="2" className="w-20">Total mark</th>
                            <th rowSpan="2" className="w-20">Attendance</th>
                        </tr>
                        <tr>
                            <th className="w-16 text-center">RW</th>
                            <th className="w-16 text-center">LS</th>
                        </tr>
                    </thead>
                    <tbody>
                        {students.map((student) => {
                            const m = marks[student.id];
                            const att = attendance[student.id];
                            const rw = m?.terminal || 0;
                            const ls = m?.practical || 0;
                            const total = rw + ls;
                            
                            return (
                                <tr key={student.id}>
                                    <td className="text-center">{student.symbolNo || '-'}</td>
                                    <td>{student.fullName}</td>
                                    <td className="text-center">{m ? rw : ''}</td>
                                    <td className="text-center">{m ? ls : ''}</td>
                                    <td className="text-center font-bold">{m ? total : ''}</td>
                                    <td className="text-center">{att?.presentDays || ''}</td>
                                </tr>
                            );
                        })}
                        {/* Fill empty rows to make it look professional if student list is short */}
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
                        <span className="label">Subject teacher:</span>
                        <span className="signature-line w-64 border-b"></span>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default MarkSlipPrint;
