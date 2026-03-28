package com.rajschool.service;

import com.rajschool.model.ExamMark;
import com.rajschool.model.ExamAttendance;
import com.rajschool.model.Subject;
import com.rajschool.repository.ExamMarkRepository;
import com.rajschool.repository.ExamAttendanceRepository;
import com.rajschool.repository.SubjectRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.util.*;
import java.util.stream.Collectors;

@Service
public class ExamService {

    @Autowired
    private ExamMarkRepository markRepository;

    @Autowired
    private ExamAttendanceRepository attendanceRepository;

    @Autowired
    private SubjectRepository subjectRepository;

    @Transactional
    public ExamMark saveMark(ExamMark mark) {
        Optional<ExamMark> existing = markRepository.findBySchoolIdAndStudentIdAndExamTypeAndYearAndSubject(
                mark.getSchoolId(), mark.getStudentId(), mark.getExamType(), mark.getYear(), mark.getSubject());

        if (existing.isPresent()) {
            ExamMark updated = existing.get();
            // Update fields manually to avoid overwriting ID
            updated.setParticipation(mark.getParticipation());
            updated.setPractical(mark.getPractical());
            updated.setTerminal(mark.getTerminal());
            updated.setExternal(mark.getExternal());
            updated.setTotal(mark.getTotal());
            updated.setLaTotal(mark.getLaTotal());
            updated.setLaObtained(mark.getLaObtained());
            updated.setRemarks(mark.getRemarks());
            updated.setStudentClass(mark.getStudentClass());
            return markRepository.save(updated);
        } else {
            return markRepository.save(mark);
        }
    }

    @Transactional
    public ExamAttendance saveAttendance(ExamAttendance attendance) {
        Optional<ExamAttendance> existing = attendanceRepository.findBySchoolIdAndStudentIdAndExamTypeAndYear(
                attendance.getSchoolId(), attendance.getStudentId(), attendance.getExamType(), attendance.getYear());

        if (existing.isPresent()) {
            ExamAttendance updated = existing.get();
            updated.setDaysPresent(attendance.getDaysPresent());
            return attendanceRepository.save(updated);
        } else {
            return attendanceRepository.save(attendance);
        }
    }

    public List<ExamMark> getMarksForStudent(Long schoolId, Long studentId, String examType, Integer year) {
        return markRepository.findBySchoolIdAndStudentIdAndExamTypeAndYear(schoolId, studentId, examType, year);
    }

    public List<ExamMark> getMarksForClass(Long schoolId, String examType, Integer year, String studentClass) {
        return markRepository.findBySchoolIdAndExamTypeAndYearAndStudentClass(schoolId, examType, year, studentClass);
    }

    public List<Subject> getSubjectsByClassGroup(Long schoolId, String classGroup) {
        return subjectRepository.findBySchoolIdAndClassGroup(schoolId, classGroup);
    }

    // Grading Logic
    public static String getGradePoint(Double obtained, Double max) {
        if (max == null || max == 0)
            return "0.0";
        double percentage = obtained / max;
        if (percentage >= 0.9)
            return "4.0";
        if (percentage >= 0.8)
            return "3.6";
        if (percentage >= 0.7)
            return "3.2";
        if (percentage >= 0.6)
            return "2.8";
        if (percentage >= 0.5)
            return "2.4";
        if (percentage >= 0.4)
            return "2.0";
        if (percentage >= 0.35)
            return "1.6";
        return "0.0";
    }

    public static String getLetterGrade(String gp) {
        switch (gp) {
            case "4.0":
                return "A+";
            case "3.6":
                return "A";
            case "3.2":
                return "B+";
            case "2.8":
                return "B";
            case "2.4":
                return "C+";
            case "2.0":
                return "C";
            case "1.6":
                return "D";
            case "0.0":
                return "NG";
            default:
                return "";
        }
    }

    public static String getRemarks(Double gpa) {
        if (gpa == null)
            return "N/A";
        if (gpa > 3.6)
            return "OUTSTANDING";
        if (gpa > 3.2)
            return "EXCELLENT";
        if (gpa > 2.8)
            return "VERY GOOD";
        if (gpa > 2.4)
            return "GOOD";
        if (gpa > 2.0)
            return "SATISFACTORY";
        if (gpa > 1.6)
            return "ACCEPTABLE";
        if (gpa == 1.6)
            return "BASIC";
        return "NOT GRADED";
    }
}
