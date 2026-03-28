package com.rajschool.repository;

import com.rajschool.model.Teacher;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;
import org.springframework.stereotype.Repository;
import java.util.List;

@Repository
public interface TeacherRepository extends JpaRepository<Teacher, Long> {

    @Query("SELECT t FROM Teacher t WHERE t.schoolId = :schoolId AND " +
            "(:search IS NULL OR LOWER(t.fullName) LIKE LOWER(CONCAT('%', :search, '%')) OR " +
            "LOWER(t.contact) LIKE LOWER(CONCAT('%', :search, '%')) OR " +
            "LOWER(t.subject) LIKE LOWER(CONCAT('%', :search, '%')))")
    List<Teacher> findBySchoolIdAndSearch(@Param("schoolId") Long schoolId, @Param("search") String search);

    List<Teacher> findBySchoolId(Long schoolId);
}
