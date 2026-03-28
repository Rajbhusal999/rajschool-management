package com.rajschool.repository;

import com.rajschool.model.ExamMark;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import java.util.List;
import java.util.Optional;

@Repository
public interface ExamMarkRepository extends JpaRepository<ExamMark, Long> {
    List<Subject> findBySchoolId(Long schoolId);

    List<ExamMark> findBySchoolIdAndStudentIdAndExamTypeAndYear(Long schoolId, Long studentId, String examType,
            Integer year);

    Optional<ExamMark> findBySchoolIdAndStudentIdAndExamTypeAndYearAndSubject(Long schoolId, Long studentId,
            String examType, Integer year, String subject);

    List<ExamMark> findBySchoolIdAndExamTypeAndYearAndStudentClass(Long schoolId, String examType, Integer year,
            String studentClass);
}
