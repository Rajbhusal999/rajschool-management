import React, { useState, useEffect } from 'react';
import { studentService, examService } from '../services/api';
import { Trophy, Printer, Search, FileText, ChevronRight, Award, UserCheck } from 'lucide-react';

import SecureGateway from '../components/SecureGateway';

const ResultSheets = () => {
    const [loading, setLoading] = useState(false);
    const [year, setYear] = useState('2081');
    const [selectedClass, setSelectedClass] = useState('');
    const [examType, setExamType] = useState('first_terminal');
    
    const [students, setStudents] = useState([]);
    const [subjects, setSubjects] = useState([]);
    const [marks, setMarks] = useState({});
    const [ranks, setRanks] = useState({});
    
    const schoolId = sessionStorage.getItem('institutionId');

    const classes = ['PG', 'NURSERY', 'LKG', 'UKG', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10'];

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

    const handleSearch = async () => {
        if (!selectedClass) return;
        setLoading(true);
        try {
            const group = ['PG', 'NURSERY', 'LKG', 'UKG'].includes(selectedClass) ? selectedClass : (parseInt(selectedClass) >= 4 ? (parseInt(selectedClass) >= 9 ? '9-10' : (parseInt(selectedClass) >= 6 ? '6-8' : '4-5')) : '1-3');
            
            const [stdRes, subRes, markRes] = await Promise.all([
                studentService.getAll({ schoolId, studentClass: selectedClass }),
                examService.getSubjects({ schoolId, classGroup: group }),
                examService.getLedger({ schoolId, examType, year: parseInt(year), studentClass: selectedClass })
            ]);

            setStudents(stdRes.data);
            setSubjects(subRes.data);
            
            const markMap = {};
            markRes.data.forEach(m => {
                if (!markMap[m.studentId]) markMap[m.studentId] = {};
                markMap[m.studentId][m.subject] = m;
            });
            setMarks(markMap);

            // Calculate GPA and Ranks
            const gpaList = {};
            stdRes.data.forEach(student => {
                let totalWGP = 0;
                let totalCredits = 0;
                let hasFailed = false;

                subRes.data.forEach(subject => {
                    const m = markMap[student.id]?.[subject.subjectName];
                    const credit = subject.creditHour || 1;
                    
                    if (m) {
                        let gp = 0;
                        if (group === '1-3') {
                            gp = getGradePoint(m.laObtained, m.laTotal);
                        } else if (['PG', 'NURSERY', 'LKG', 'UKG'].includes(group)) {
                            gp = getGradePoint((m.practical || 0) + (m.terminal || 0), credit);
                        } else {
                            const tot = (m.participation || 0) + (m.practical || 0) + (m.terminal || 0) + (m.external || 0);
                            gp = getGradePoint(tot, examType === 'final' ? 100 : 50);
                        }
                        
                        totalWGP += gp * credit;
                        totalCredits += credit;
                        if (gp === 0) hasFailed = true;
                    } else {
                        hasFailed = true;
                    }
                });

                const gpa = totalCredits > 0 ? (totalWGP / totalCredits) : 0;
                gpaList[student.id] = hasFailed ? 0 : parseFloat(gpa.toFixed(2));
            });

            const sortedIds = Object.keys(gpaList).sort((a, b) => gpaList[b] - gpaList[a]);
            const rankMap = {};
            let currentRank = 1;
            sortedIds.forEach((id, index) => {
                if (index > 0 && gpaList[id] < gpaList[sortedIds[index - 1]]) {
                    currentRank = index + 1;
                }
                rankMap[id] = gpaList[id] > 0 ? currentRank : '-';
            });
            setRanks(rankMap);

        } catch (error) {
            console.error('Error fetching ledger:', error);
        } finally {
            setLoading(false);
        }
    };

    return (
        <SecureGateway>
            <div className="max-w-7xl mx-auto space-y-6">
                {/* Filter Section */}
                <div className="bg-white p-8 rounded-[40px] shadow-sm border border-slate-100 flex flex-col md:flex-row gap-6 items-end">
                    <div className="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4 w-full">
                        <div className="space-y-2">
                            <label className="text-xs font-black uppercase text-slate-400 tracking-wider">Year</label>
                            <input type="number" value={year} onChange={(e) => setYear(e.target.value)} className="w-full px-5 py-3 bg-slate-50 border-none rounded-2xl font-bold" />
                        </div>
                        <div className="space-y-2">
                            <label className="text-xs font-black uppercase text-slate-400 tracking-wider">Class</label>
                            <select value={selectedClass} onChange={(e) => setSelectedClass(e.target.value)} className="w-full px-5 py-3 bg-slate-50 border-none rounded-2xl font-bold">
                                <option value="">Select Class</option>
                                {classes.map(c => <option key={c} value={c}>Class {c}</option>)}
                            </select>
                        </div>
                        <div className="space-y-2">
                            <label className="text-xs font-black uppercase text-slate-400 tracking-wider">Exam Type</label>
                            <select value={examType} onChange={(e) => setExamType(e.target.value)} className="w-full px-5 py-3 bg-slate-50 border-none rounded-2xl font-bold">
                                <option value="first_terminal">First Terminal</option>
                                <option value="second_terminal">Second Terminal</option>
                                <option value="third_terminal">Third Terminal</option>
                                <option value="final">Final Examination</option>
                            </select>
                        </div>
                    </div>
                    <button onClick={handleSearch} className="px-8 py-3 bg-indigo-600 text-white rounded-2xl font-bold flex items-center gap-2 hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
                        <Search size={20} />
                        Generate Ledger
                    </button>
                </div>

                {/* Content Rendering */}
                {students.length > 0 && (
                    <div className="space-y-6">
                        <div className="flex justify-between items-center">
                            <h2 className="text-xl font-black text-slate-800 tracking-tight flex items-center gap-2">
                                <Trophy className="text-amber-500" />
                                Class {selectedClass} Mark Ledger
                            </h2>
                            <button 
                                onClick={() => window.open(`/exams/print?class=${selectedClass}&year=${year}&examType=${examType}`, '_blank')}
                                className="flex items-center gap-2 px-6 py-2 bg-slate-900 text-white rounded-xl font-bold hover:bg-slate-800 transition shadow-lg"
                            >
                                <Printer size={18} />
                                Print Grade Sheets
                            </button>
                        </div>


                        <div className="bg-white rounded-[40px] shadow-sm border border-slate-100 overflow-hidden">
                            <div className="overflow-x-auto">
                                <table className="w-full border-collapse">
                                    <thead className="bg-slate-50">
                                        <tr>
                                            <th className="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest sticky left-0 bg-slate-50 z-10">Student</th>
                                            {subjects.map(s => <th key={s.id} className="px-4 py-4 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest border-l border-slate-100">{s.subjectName}</th>)}
                                            <th className="px-6 py-4 text-center text-[10px] font-black text-indigo-400 uppercase tracking-widest border-l border-slate-100">GPA</th>
                                            <th className="px-6 py-4 text-center text-[10px] font-black text-amber-500 uppercase tracking-widest border-l border-slate-100">Rank</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-slate-50">
                                        {students.map(student => {
                                            let tWGP = 0, tC = 0, fail = false;
                                            return (
                                                <tr key={student.id} className="hover:bg-slate-50/50 transition-colors group">
                                                    <td className="px-6 py-4 sticky left-0 bg-white group-hover:bg-slate-50 transition-colors shadow-[2px_0_5px_rgba(0,0,0,0.05)]">
                                                        <div className="font-bold text-slate-700">{student.fullName}</div>
                                                        <div className="text-[10px] text-slate-400">Roll: {student.rollNo}</div>
                                                    </td>
                                                    {subjects.map(subject => {
                                                        const m = marks[student.id]?.[subject.subjectName];
                                                        let gp = 0;
                                                        if (m) {
                                                            const group = ['PG', 'NURSERY', 'LKG', 'UKG'].includes(selectedClass) ? selectedClass : (parseInt(selectedClass) >= 4 ? (parseInt(selectedClass) >= 9 ? '9-10' : (parseInt(selectedClass) >= 6 ? '6-8' : '4-5')) : '1-3');
                                                            if (group === '1-3') gp = getGradePoint(m.laObtained, m.laTotal);
                                                            else if (['PG', 'NURSERY', 'LKG', 'UKG'].includes(group)) gp = getGradePoint((m.practical || 0) + (m.terminal || 0), subject.creditHour || 50);
                                                            else gp = getGradePoint((m.participation || 0) + (m.practical || 0) + (m.terminal || 0) + (m.external || 0), examType === 'final' ? 100 : 50);
                                                            
                                                            tWGP += gp * (subject.creditHour || 1);
                                                            tC += (subject.creditHour || 1);
                                                            if (gp === 0) fail = true;
                                                        } else {
                                                            fail = true;
                                                        }
                                                        return (
                                                            <td key={subject.id} className="px-4 py-4 text-center border-l border-slate-100">
                                                                <div className={`font-bold ${gp === 0 ? 'text-rose-500' : 'text-slate-600'}`}>{gp.toFixed(1)}</div>
                                                                <div className="text-[10px] font-black text-slate-300">{getLetterGrade(gp)}</div>
                                                            </td>
                                                        );
                                                    })}
                                                    <td className="px-6 py-4 text-center border-l border-slate-100 bg-indigo-50/30">
                                                        <div className="font-black text-indigo-600">{fail ? '0.00' : (tC > 0 ? (tWGP / tC).toFixed(2) : '0.00')}</div>
                                                    </td>
                                                    <td className="px-6 py-4 text-center border-l border-slate-100 bg-amber-50/30">
                                                        <div className="font-black text-amber-600">{ranks[student.id]}</div>
                                                    </td>
                                                </tr>
                                            );
                                        })}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </SecureGateway>
    );
};

export default ResultSheets;
