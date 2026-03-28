package com.rajschool.model;

import jakarta.persistence.*;
import lombok.Data;

@Entity
@Table(name = "exam_attendance")
@Data
public class ExamAttendance {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @Column(name = "school_id")
    private Long schoolId;

    @Column(name = "student_id")
    private Long studentId;

    @Column(name = "exam_type")
    private String examType;

    @Column(name = "year")
    private Integer year;

    @Column(name = "days_present")
    private Integer daysPresent;
}
