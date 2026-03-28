package com.rajschool.model;

import jakarta.persistence.*;
import lombok.Data;
import java.time.LocalDateTime;

@Entity
@Table(name = "student_attendance", uniqueConstraints = {
        @UniqueConstraint(name = "unique_attendance", columnNames = { "school_id", "student_id", "attendance_date",
                "session" })
})
@Data
public class Attendance {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @Column(name = "school_id", nullable = false)
    private Long schoolId;

    @Column(name = "student_id", nullable = false)
    private Long studentId;

    @Column(name = "student_class", nullable = false)
    private String studentClass;

    @Column(name = "attendance_date", nullable = false)
    private String attendanceDate; // Nepali Date YYYY-MM-DD or YYYY/MM/DD

    @Column(nullable = false)
    private String session; // Morning, Evening

    @Column(nullable = false)
    private String status; // Present, Absent, Leave, Extra Class

    @Column(name = "created_at", updatable = false)
    private LocalDateTime createdAt = LocalDateTime.now();
}
