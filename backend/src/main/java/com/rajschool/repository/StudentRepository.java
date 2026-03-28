package com.rajschool.repository;

import com.rajschool.model.Student;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.stereotype.Repository;

import java.util.List;
import java.util.Optional;

@Repository
public interface StudentRepository extends JpaRepository<Student, Long> {
    List<Student> findBySchoolIdOrderByFullNameAsc(Long schoolId);

    @Query("SELECT MAX(CAST(SUBSTRING(s.symbolNo, :prefixLenPlusOne) AS integer)) FROM Student s WHERE s.schoolId = :schoolId AND s.symbolNo LIKE :prefixPattern")
    Integer findMaxSymbolSequence(Long schoolId, int prefixLenPlusOne, String prefixPattern);

    List<Student> findBySchoolIdAndStudentClass(Long schoolId, String studentClass);

    List<Student> findBySchoolIdAndFullNameContainingIgnoreCaseOrSymbolNoContainingIgnoreCaseOrParentContactContainingIgnoreCase(
            Long schoolId, String name, String symbolNo, String contact);
}
