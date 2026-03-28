package com.rajschool.controller;

import com.rajschool.model.Attendance;
import com.rajschool.repository.AttendanceRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

import java.time.LocalDateTime;
import java.util.List;
import java.util.Optional;

@RestController
@RequestMapping("/api/attendance")
@CrossOrigin(origins = "http://localhost:5173")
public class AttendanceController {

    @Autowired
    private AttendanceRepository attendanceRepository;

    @GetMapping
    public List<Attendance> getAttendance(
            @RequestParam Long schoolId,
            @RequestParam(required = false) String studentClass,
            @RequestParam(required = false) String attendanceDate,
            @RequestParam(required = false) String session,
            @RequestParam(required = false) String datePrefix) {

        if (datePrefix != null && studentClass != null) {
            return attendanceRepository.findBySchoolIdAndStudentClassAndAttendanceDateStartingWith(schoolId,
                    studentClass, datePrefix);
        }

        if (studentClass != null && attendanceDate != null && session != null) {
            return attendanceRepository.findBySchoolIdAndStudentClassAndAttendanceDateAndSession(schoolId, studentClass,
                    attendanceDate, session);
        }

        return attendanceRepository.findAll(); // Simplified for general listing
    }

    @PostMapping("/bulk")
    public ResponseEntity<List<Attendance>> saveBulkAttendance(@RequestBody List<Attendance> attendanceList) {
        for (Attendance attendance : attendanceList) {
            // Upsert logic: check if record exists for this student/date/session
            Optional<Attendance> existing = attendanceRepository.findBySchoolIdAndStudentIdAndAttendanceDateAndSession(
                    attendance.getSchoolId(), attendance.getStudentId(), attendance.getAttendanceDate(),
                    attendance.getSession());

            if (existing.isPresent()) {
                Attendance update = existing.get();
                update.setStatus(attendance.getStatus());
                attendanceRepository.save(update);
            } else {
                attendance.setCreatedAt(LocalDateTime.now());
                attendanceRepository.save(attendance);
            }
        }
        return ResponseEntity.ok(attendanceList);
    }
}
