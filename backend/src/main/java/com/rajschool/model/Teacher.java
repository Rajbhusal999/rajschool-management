package com.rajschool.model;

import jakarta.persistence.*;
import lombok.Data;
import java.time.LocalDateTime;

@Entity
@Table(name = "teachers")
@Data
public class Teacher {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @Column(name = "school_id", nullable = false)
    private Long schoolId;

    @Column(name = "full_name", nullable = false)
    private String fullName;

    @Column(name = "staff_role")
    private String staffRole; // Teacher, Staff

    private String subject;
    private String contact;

    @Column(name = "teacher_type")
    private String teacherType; // Permanent, Temporary, etc.

    @Column(name = "attendance_date_nepali")
    private String attendanceDateNepali;

    @Column(columnDefinition = "TEXT")
    private String address;

    private String tah; // Level (प्रा.वि, नि.मा.वि, मा.वि)

    @Column(name = "pan_no")
    private String panNo;

    @Column(name = "blood_group")
    private String bloodGroup;

    @Column(name = "citizenship_no")
    private String citizenshipNo;

    @Column(name = "teacher_photo")
    private String teacherPhoto;

    @Column(name = "teacher_password")
    private String teacherPassword;

    @Column(name = "bank_name")
    private String bankName;

    @Column(name = "account_number")
    private String accountNumber;

    @Column(name = "created_at", updatable = false)
    private LocalDateTime createdAt = LocalDateTime.now();
}
