package com.rajschool.controller;

import com.rajschool.model.Teacher;
import com.rajschool.repository.TeacherRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

import java.util.List;
import java.util.Optional;

@RestController
@RequestMapping("/api/teachers")
@CrossOrigin(origins = "http://localhost:5173")
public class TeacherController {

    @Autowired
    private TeacherRepository teacherRepository;

    @GetMapping
    public List<Teacher> getAllTeachers(
            @RequestParam(required = false) String search,
            @RequestParam(defaultValue = "1") Long schoolId) {
        if (search != null && !search.isEmpty()) {
            return teacherRepository.findBySchoolIdAndSearch(schoolId, search);
        }
        return teacherRepository.findBySchoolId(schoolId);
    }

    @PostMapping
    public Teacher createTeacher(@RequestBody Teacher teacher) {
        return teacherRepository.save(teacher);
    }

    @GetMapping("/{id}")
    public ResponseEntity<Teacher> getTeacherById(@PathVariable Long id) {
        Optional<Teacher> teacher = teacherRepository.findById(id);
        return teacher.map(ResponseEntity::ok).orElseGet(() -> ResponseEntity.notFound().build());
    }

    @PutMapping("/{id}")
    public ResponseEntity<Teacher> updateTeacher(@PathVariable Long id, @RequestBody Teacher teacherDetails) {
        return teacherRepository.findById(id)
                .map(teacher -> {
                    teacher.setFullName(teacherDetails.getFullName());
                    teacher.setStaffRole(teacherDetails.getStaffRole());
                    teacher.setSubject(teacherDetails.getSubject());
                    teacher.setContact(teacherDetails.getContact());
                    teacher.setTeacherType(teacherDetails.getTeacherType());
                    teacher.setAttendanceDateNepali(teacherDetails.getAttendanceDateNepali());
                    teacher.setAddress(teacherDetails.getAddress());
                    teacher.setTah(teacherDetails.getTah());
                    teacher.setPanNo(teacherDetails.getPanNo());
                    teacher.setBloodGroup(teacherDetails.getBloodGroup());
                    teacher.setCitizenshipNo(teacherDetails.getCitizenshipNo());
                    teacher.setTeacherPhoto(teacherDetails.getTeacherPhoto());
                    teacher.setTeacherPassword(teacherDetails.getTeacherPassword());
                    teacher.setBankName(teacherDetails.getBankName());
                    teacher.setAccountNumber(teacherDetails.getAccountNumber());
                    return ResponseEntity.ok(teacherRepository.save(teacher));
                })
                .orElseGet(() -> ResponseEntity.notFound().build());
    }

    @DeleteMapping("/{id}")
    public ResponseEntity<Void> deleteTeacher(@PathVariable Long id) {
        return teacherRepository.findById(id)
                .map(teacher -> {
                    teacherRepository.delete(teacher);
                    return ResponseEntity.ok().<Void>build();
                })
                .orElseGet(() -> ResponseEntity.notFound().build());
    }
}
