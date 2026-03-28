package com.rajschool.repository;

import com.rajschool.model.ExamAttendance;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import java.util.Optional;

@Repository
public interface ExamAttendanceRepository extends JpaRepository<ExamAttendance, Long> {
    Optional<ExamAttendance> findBySchoolIdAndStudentIdAndExamTypeAndYear(Long schoolId, Long studentId,
            String examType, Integer year);
}
