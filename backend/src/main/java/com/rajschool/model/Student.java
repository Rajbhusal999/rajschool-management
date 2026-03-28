package com.rajschool.model;

import jakarta.persistence.*;
import lombok.AllArgsConstructor;
import lombok.Data;
import lombok.NoArgsConstructor;
import java.time.LocalDateTime;

@Entity
@Table(name = "students")
@Data
@NoArgsConstructor
@AllArgsConstructor
public class Student {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @Column(name = "school_id", nullable = false)
    private Long schoolId;

    @Column(name = "full_name", nullable = false)
    private String fullName;

    @Column(name = "roll_no")
    private String rollNo;

    @Column(name = "class")
    private String studentClass; // 'class' is a reserved word in Java

    @Column(name = "parent_contact")
    private String parentContact;

    @Column(name = "address")
    private String address;

    @Column(name = "emis_no")
    private String emisNo;

    @Column(name = "symbol_no", unique = true)
    private String symbolNo;

    @Column(name = "caste")
    private String caste;

    @Column(name = "dob_nepali")
    private String dobNepali;

    @Column(name = "gender")
    private String gender;

    @Column(name = "student_photo")
    private String studentPhoto;

    @Column(name = "father_name")
    private String fatherName;

    @Column(name = "mother_name")
    private String motherName;

    @Column(name = "guardian_name")
    private String guardianName;

    @Column(name = "guardian_contact")
    private String guardianContact;

    @Column(name = "guardian_email")
    private String guardianEmail;

    // Permanent Address
    @Column(name = "perm_province")
    private String permProvince;
    @Column(name = "perm_district")
    private String permDistrict;
    @Column(name = "perm_local_level")
    private String permLocalLevel;
    @Column(name = "perm_ward_no")
    private String permWardNo;
    @Column(name = "perm_tole")
    private String permTole;

    // Temporary Address
    @Column(name = "temp_province")
    private String tempProvince;
    @Column(name = "temp_district")
    private String tempDistrict;
    @Column(name = "temp_local_level")
    private String tempLocalLevel;
    @Column(name = "temp_ward_no")
    private String tempWardNo;
    @Column(name = "temp_tole")
    private String tempTole;

    @Column(name = "scholarship_type")
    private String scholarshipType;

    @Column(name = "disability_type")
    private String disabilityType;

    @Column(name = "created_at", updatable = false)
    private LocalDateTime createdAt = LocalDateTime.now();
}
