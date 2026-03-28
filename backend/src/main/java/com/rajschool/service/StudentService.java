package com.rajschool.service;

import com.rajschool.model.Student;
import com.rajschool.repository.StudentRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.util.List;
import java.util.Optional;

@Service
public class StudentService {

    @Autowired
    private StudentRepository studentRepository;

    public List<Student> getAllStudents(Long schoolId) {
        return studentRepository.findBySchoolIdOrderByFullNameAsc(schoolId);
    }

    public List<Student> searchStudents(Long schoolId, String query) {
        return studentRepository.findBySchoolIdAndFullNameContainingIgnoreCaseOrSymbolNoContainingIgnoreCaseOrParentContactContainingIgnoreCase(
                schoolId, query, query, query);
    }

    public List<Student> getStudentsByClass(Long schoolId, String studentClass) {
        return studentRepository.findBySchoolIdAndStudentClass(schoolId, studentClass);
    }

    @Transactional
    public Student saveStudent(Student student) {
        if (student.getSymbolNo() == null || student.getSymbolNo().isEmpty()) {
            student.setSymbolNo(generateSymbolNo(student.getSchoolId(), student.getStudentClass()));
        }
        Student savedStudent = studentRepository.save(student);
        resequenceClass(student.getSchoolId(), student.getStudentClass());
        return savedStudent;
    }

    public Optional<Student> getStudentById(Long id) {
        return studentRepository.findById(id);
    }

    @Transactional
    public void deleteStudent(Long id) {
        studentRepository.findById(id).ifPresent(student -> {
            studentRepository.delete(student);
            resequenceClass(student.getSchoolId(), student.getStudentClass());
        });
    }

    private String generateSymbolNo(Long schoolId, String studentClass) {
        String prefix = "HI" + getShortClassCode(studentClass);
        Integer maxSeq = studentRepository.findMaxSymbolSequence(schoolId, prefix.length() + 1, prefix + "%");
        int nextSeq = (maxSeq != null && maxSeq >= 101) ? maxSeq + 1 : 101;
        return prefix + nextSeq;
    }

    private String getShortClassCode(String studentClass) {
        if (studentClass == null) return "X";
        if (studentClass.equalsIgnoreCase("Nursery")) return "N";
        if (studentClass.equalsIgnoreCase("LKG")) return "L";
        if (studentClass.equalsIgnoreCase("UKG")) return "U";
        return studentClass.replaceAll("[^0-9]", ""); // Keep only numbers for Class 1, Class 2, etc.
    }

    @Transactional
    public void resequenceClass(Long schoolId, String studentClass) {
        List<Student> students = studentRepository.findBySchoolIdAndStudentClass(schoolId, studentClass);
        // Sort by full name is already done by JPA if we use a specific query, but let's be explicit
        students.sort((s1, s2) -> s1.getFullName().compareToIgnoreCase(s2.getFullName()));

        String prefix = "HI" + getShortClassCode(studentClass);
        int seq = 101;
        for (Student s : students) {
            s.setSymbolNo(prefix + seq++);
            studentRepository.save(s);
        }
    }
}
