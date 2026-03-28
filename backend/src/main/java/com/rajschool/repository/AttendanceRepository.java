package com.rajschool.repository;

import com.rajschool.model.Attendance;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import java.util.List;
import java.util.Optional;

@Repository
public interface AttendanceRepository extends JpaRepository<Attendance, Long> {

    List<Attendance> findBySchoolIdAndStudentClassAndAttendanceDateAndSession(
            Long schoolId, String studentClass, String attendanceDate, String session);

    Optional<Attendance> findBySchoolIdAndStudentIdAndAttendanceDateAndSession(
            Long schoolId, Long studentId, String attendanceDate, String session);

    List<Attendance> findBySchoolIdAndStudentClassAndAttendanceDateStartingWith(
            Long schoolId, String studentClass, String datePrefix);
}
