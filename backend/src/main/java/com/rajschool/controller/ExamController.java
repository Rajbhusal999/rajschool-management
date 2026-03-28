package com.rajschool.controller;

import com.rajschool.model.Subject;
import com.rajschool.model.ExamMark;
import com.rajschool.model.ExamAttendance;
import com.rajschool.repository.SubjectRepository;
import com.rajschool.service.ExamService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

import java.util.List;

@RestController
@RequestMapping("/api/exams")
@CrossOrigin(origins = "*")
public class ExamController {

    @Autowired
    private ExamService examService;

    @Autowired
    private SubjectRepository subjectRepository;

    // --- Subject Management ---

    @GetMapping("/subjects")
    public List<Subject> getSubjects(
            @RequestParam Long schoolId,
            @RequestParam(required = false) String classGroup) {
        if (classGroup != null && !classGroup.isEmpty()) {
            return examService.getSubjectsByClassGroup(schoolId, classGroup);
        }
        return subjectRepository.findBySchoolId(schoolId);
    }

    @PostMapping("/subjects")
    public Subject createSubject(@RequestBody Subject subject) {
        return subjectRepository.save(subject);
    }

    @DeleteMapping("/subjects/{id}")
    public ResponseEntity<Void> deleteSubject(@PathVariable Long id) {
        subjectRepository.deleteById(id);
        return ResponseEntity.ok().build();
    }

    // --- Marks Entry ---

    @GetMapping("/marks")
    public List<ExamMark> getMarks(
            @RequestParam Long schoolId,
            @RequestParam Long studentId,
            @RequestParam String examType,
            @RequestParam Integer year) {
        return examService.getMarksForStudent(schoolId, studentId, examType, year);
    }

    @PostMapping("/marks/bulk")
    public List<ExamMark> saveMarksBulk(@RequestBody List<ExamMark> marks) {
        return marks.stream()
                .map(examService::saveMark)
                .toList();
    }

    // --- Attendance ---

    @GetMapping("/attendance")
    public ResponseEntity<ExamAttendance> getAttendance(
            @RequestParam Long schoolId,
            @RequestParam Long studentId,
            @RequestParam String examType,
            @RequestParam Integer year) {
        return ResponseEntity.ok(examService.saveAttendance(new ExamAttendance())); // Placeholder logic for now
    }

    @PostMapping("/attendance")
    public ExamAttendance saveAttendance(@RequestBody ExamAttendance attendance) {
        return examService.saveAttendance(attendance);
    }

    // --- Ledger / Results ---

    @GetMapping("/ledger")
    public List<ExamMark> getLedger(
            @RequestParam Long schoolId,
            @RequestParam String examType,
            @RequestParam Integer year,
            @RequestParam String studentClass) {
        return examService.getMarksForClass(schoolId, examType, year, studentClass);
    }
}
