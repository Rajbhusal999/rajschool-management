package com.rajschool.model;

import jakarta.persistence.*;
import lombok.Data;

@Entity
@Table(name = "exam_marks")
@Data
public class ExamMark {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @Column(name = "school_id")
    private Long schoolId;

    @Column(name = "student_id")
    private Long studentId;

    @Column(name = "exam_type")
    private String examType; // e.g., "first_terminal", "final", "monthly"

    @Column(name = "year")
    private Integer year; // Nepali Year (B.S.)

    @Column(name = "class")
    private String studentClass;

    @Column(name = "subject")
    private String subject;

    // Standard Terminal Marks (Class 4-10)
    private Double participation; // max 4
    private Double practical; // max 36 (also used for RW in PG-KG)
    private Double terminal; // max 10 (also used for LS in PG-KG)
    private Double external; // max 50 (Final exam)

    // Monthly/Total Marks
    private Double total;

    // Learning Achievement (Class 1-3)
    @Column(name = "la_total")
    private Double laTotal;

    @Column(name = "la_obtained")
    private Double laObtained;

    private String remarks;
}
