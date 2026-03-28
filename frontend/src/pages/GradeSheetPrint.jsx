import React, { useEffect, useState } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import { studentService, examService } from '../services/api';
import './GradeSheetPrint.css';

const GradeSheetPrint = () => {
    const location = useLocation();
    const navigate = useNavigate();
    const query = new URLSearchParams(location.search);
    
    const year = query.get('year');
    const studentClass = query.get('class');
    const examType = query.get('examType');
    
    const [data, setData] = useState({ students: [], subjects: [], marks: {}, schoolInfo: {} });
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (!year || !studentClass || !examType) return;
        fetchData();
    }, []);

    const fetchData = async () => {
        setLoading(true);
        try {
            const group = ['PG', 'NURSERY', 'LKG', 'UKG'].includes(studentClass) ? studentClass : (parseInt(studentClass) >= 4 ? (parseInt(studentClass) >= 9 ? '9-10' : (parseInt(studentClass) >= 6 ? '6-8' : '4-5')) : '1-3');
            
            const [stdRes, subRes, markRes] = await Promise.all([
                studentService.getAll({ schoolId: 1, studentClass }),
                examService.getSubjects({ schoolId: 1, classGroup: group }),
                examService.getLedger({ schoolId: 1, examType, year: parseInt(year), studentClass })
            ]);

            const markMap = {};
            markRes.data.forEach(m => {
                if (!markMap[m.studentId]) markMap[m.studentId] = {};
                markMap[m.studentId][m.subject] = m;
            });

            setData({
                students: stdRes.data,
                subjects: subRes.data,
                marks: markMap,
                schoolInfo: { name: 'RAJSCHOOL MODERN ACADEMY', address: 'Simara, Bara, Nepal', estd: '2075' }
            });
            
            // Auto-trigger print after a short delay
            setTimeout(() => window.print(), 1000);
        } catch (error) {
            console.error('Error printing sheets:', error);
        } finally {
            setLoading(false);
        }
    };

    const getGradePoint = (obtained, max) => {
        if (!max || max === 0) return 0;
        const p = obtained / max;
        if (p >= 0.9) return 4.0;
        if (p >= 0.8) return 3.6;
        if (p >= 0.7) return 3.2;
        if (p >= 0.6) return 2.8;
        if (p >= 0.5) return 2.4;
        if (p >= 0.4) return 2.0;
        if (p >= 0.35) return 1.6;
        return 0.0;
    };

    const getLetterGrade = (gp) => {
        if (gp >= 4.0) return 'A+';
        if (gp >= 3.6) return 'A';
        if (gp >= 3.2) return 'B+';
        if (gp >= 2.8) return 'B';
        if (gp >= 2.4) return 'C+';
        if (gp >= 2.0) return 'C';
        if (gp >= 1.6) return 'D';
        return 'NG';
    };

    // Group students in pairs for 2-per-page layout
    const studentPairs = [];
    for (let i = 0; i < data.students.length; i += 2) {
        studentPairs.push(data.students.slice(i, i + 2));
    }

    if (loading) return <div className="p-20 text-center font-bold text-slate-400">Preparing Grade Sheets...</div>;

    return (
        <div className="print-container">
            {studentPairs.map((pair, pageIdx) => (
                <div key={pageIdx} className="print-page landscape">
                    {pair.map((student, idx) => (
                        <div key={student.id} className="gradesheet-item">
                            <div className="gs-header">
                                <div className="gs-school-name">{data.schoolInfo.name}</div>
                                <div className="gs-school-address">{data.schoolInfo.address}</div>
                                <div className="gs-exam-title">{examType.toUpperCase().replace('_', ' ')} REPORT CARD</div>
                            </div>
                            
                            <div className="gs-student-info">
                                <div><strong>Name:</strong> {student.fullName}</div>
                                <div><strong>Class:</strong> {studentClass}</div>
                                <div><strong>Roll:</strong> {student.rollNo}</div>
                                <div><strong>Year:</strong> {year} B.S.</div>
                            </div>

                            <table className="gs-table">
                                <thead>
                                    <tr>
                                        <th>Subject</th>
                                        <th>Credit</th>
                                        <th>Grade Point</th>
                                        <th>Letter Grade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {data.subjects.map(sub => {
                                        const m = data.marks[student.id]?.[sub.subjectName];
                                        let gp = 0;
                                        if (m) {
                                            const group = ['PG', 'NURSERY', 'LKG', 'UKG'].includes(studentClass) ? studentClass : (parseInt(studentClass) >= 4 ? (parseInt(studentClass) >= 9 ? '9-10' : (parseInt(studentClass) >= 6 ? '6-8' : '4-5')) : '1-3');
                                            if (group === '1-3') gp = getGradePoint(m.laObtained, m.laTotal);
                                            else if (['PG', 'NURSERY', 'LKG', 'UKG'].includes(group)) gp = getGradePoint((m.practical || 0) + (m.terminal || 0), sub.creditHour || 50);
                                            else gp = getGradePoint((m.participation || 0) + (m.practical || 0) + (m.terminal || 0) + (m.external || 0), examType === 'final' ? 100 : 50);
                                        }
                                        return (
                                            <tr key={sub.id}>
                                                <td className="text-left">{sub.subjectName}</td>
                                                <td>{sub.creditHour}</td>
                                                <td>{gp.toFixed(2)}</td>
                                                <td>{getLetterGrade(gp)}</td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>

                            <div className="gs-footer">
                                <div className="sig-box">Class Teacher</div>
                                <div className="sig-box">Examination Dept.</div>
                                <div className="sig-box">Principal</div>
                            </div>
                        </div>
                    ))}
                    {pair.length === 1 && <div className="gradesheet-item empty"></div>}
                </div>
            ))}
        </div>
    );
};

export default GradeSheetPrint;
