package com.rajschool.controller;

import com.rajschool.model.Student;
import com.rajschool.service.StudentService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

import java.util.List;

@RestController
@RequestMapping("/api/students")
@CrossOrigin(origins = "*") // Allow React to connect
public class StudentController {

    @Autowired
    private StudentService studentService;

    @GetMapping
    public List<Student> getStudents(
            @RequestParam Long schoolId,
            @RequestParam(required = false) String query,
            @RequestParam(required = false) String studentClass) {

        if (query != null && !query.isEmpty()) {
            return studentService.searchStudents(schoolId, query);
        } else if (studentClass != null && !studentClass.isEmpty()) {
            return studentService.getStudentsByClass(schoolId, studentClass);
        }
        return studentService.getAllStudents(schoolId);
    }

    @GetMapping("/{id}")
    public ResponseEntity<Student> getStudentById(@PathVariable Long id) {
        return studentService.getStudentById(id)
                .map(ResponseEntity::ok)
                .orElse(ResponseEntity.notFound().build());
    }

    @PostMapping
    public Student createStudent(@RequestBody Student student) {
        return studentService.saveStudent(student);
    }

    @PutMapping("/{id}")
    public ResponseEntity<Student> updateStudent(@PathVariable Long id, @RequestBody Student studentDetails) {
        return studentService.getStudentById(id).map(student -> {
            student.setFullName(studentDetails.getFullName());
            student.setRollNo(studentDetails.getRollNo());
            student.setStudentClass(studentDetails.getStudentClass());
            student.setParentContact(studentDetails.getParentContact());
            student.setAddress(studentDetails.getAddress());
            student.setEmisNo(studentDetails.getEmisNo());
            student.setCaste(studentDetails.getCaste());
            student.setDobNepali(studentDetails.getDobNepali());
            student.setGender(studentDetails.getGender());
            student.setStudentPhoto(studentDetails.getStudentPhoto());
            student.setFatherName(studentDetails.getFatherName());
            student.setMotherName(studentDetails.getMotherName());
            student.setGuardianName(studentDetails.getGuardianName());
            student.setGuardianContact(studentDetails.getGuardianContact());
            student.setGuardianEmail(studentDetails.getGuardianEmail());

            // Permanent
            student.setPermProvince(studentDetails.getPermProvince());
            student.setPermDistrict(studentDetails.getPermDistrict());
            student.setPermLocalLevel(studentDetails.getPermLocalLevel());
            student.setPermWardNo(studentDetails.getPermWardNo());
            student.setPermTole(studentDetails.getPermTole());

            // Temporary
            student.setTempProvince(studentDetails.getTempProvince());
            student.setTempDistrict(studentDetails.getTempDistrict());
            student.setTempLocalLevel(studentDetails.getTempLocalLevel());
            student.setTempWardNo(studentDetails.getTempWardNo());
            student.setTempTole(studentDetails.getTempTole());

            student.setScholarshipType(studentDetails.getScholarshipType());
            student.setDisabilityType(studentDetails.getDisabilityType());

            return ResponseEntity.ok(studentService.saveStudent(student));
        }).orElse(ResponseEntity.notFound().build());
    }

    @DeleteMapping("/{id}")
    public ResponseEntity<Void> deleteStudent(@PathVariable Long id) {
        studentService.deleteStudent(id);
        return ResponseEntity.ok().build();
    }
}
