# Grade Sheet System - User Guide

## Overview

The Grade Sheet system generates combined 1st and 2nd Terminal Examination grade sheets with 2 students displayed per A4 landscape page.

## Features

✅ **Combined Terminal Marks**: Displays both 1st and 2nd terminal exam results
✅ **Optimized Layout**: 2 students per A4 landscape page for efficient printing
✅ **Student Selection**: Print all students or select specific students
✅ **Professional Format**: Includes school logo, name, address, and establishment date
✅ **Complete Information**: Shows GPA, rank, attendance, and subject-wise grades
✅ **Print-Ready**: Optimized for A4 landscape printing

## How to Use

### Step 1: Access Grade Sheet Generator

1. Navigate to **Dashboard** → **Exams**
2. Click on the **"Grade Sheet"** card (orange/yellow colored)

### Step 2: Configure Settings

1. **Select Academic Year**: Choose the Nepali year (B.S.) - defaults to current year
2. **Select Class**: Choose the class for which you want to generate grade sheets
3. Wait for students to load automatically

### Step 3: Select Students (Optional)

- **Print All Students**: Click "Print All Students" button to generate for entire class
- **Print Selected Students**:
  - Check individual student checkboxes
  - Or use "Select All Students" checkbox
  - Click "Print Selected Students" button

### Step 4: Print Grade Sheets

1. Review the generated grade sheets on screen
2. Click the **"Print Grade Sheets"** button
3. Select your printer and print settings
4. Ensure **A4 Landscape** orientation is selected
5. Print the documents

## Grade Sheet Format

Each grade sheet contains:

- **School Header**: Logo, name, address, establishment date
- **Exam Title**: "FIRST TERMINAL EXAMINATION-[Year]" or "SECOND TERMINAL EXAMINATION-[Year]"
- **Student Information**: Roll number, class, student name
- **Marks Table**:
  - Subject names
  - Credit hours
  - Grade points
  - Grades (A+, A, B+, B, C+, C, D, NG)
- **Footer Information**:
  - GPA (Grade Point Average)
  - Rank in class
  - Attendance
- **Signature Lines**: Class Teacher, Exam Coordinator, Head Teacher

## Important Notes

### Prerequisites

1. **Marks Entry**: Ensure marks are entered for both 1st and 2nd terminal exams
2. **Subjects Setup**: Configure subjects with proper credit hours in Subject Management
3. **Student Data**: Verify student information is complete (name, symbol number, class)
4. **School Info**: Update school logo and details in settings

### Grading System

- **A+**: 90% and above (GP: 4.0)
- **A**: 80-89% (GP: 3.6)
- **B+**: 70-79% (GP: 3.2)
- **B**: 60-69% (GP: 2.8)
- **C+**: 50-59% (GP: 2.4)
- **C**: 40-49% (GP: 2.0)
- **D**: 35-39% (GP: 1.6)
- **NG**: Below 35% (GP: 0.0)

### Printing Tips

1. **Paper Size**: Use A4 size paper
2. **Orientation**: Set to **Landscape** mode
3. **Margins**: Use default or minimal margins
4. **Color**: Can be printed in black & white or color
5. **Quality**: Use high quality for professional appearance

### Troubleshooting

**No students showing?**

- Verify students are added to the selected class
- Check if class is correctly assigned to students

**Missing marks?**

- Ensure marks are entered in Mark Entry for both terminals
- Verify exam type is set correctly (first_terminal, second_terminal)

**Incorrect GPA/Rank?**

- Check if all subject marks are entered
- Verify credit hours are set for all subjects

**School logo not showing?**

- Upload school logo in settings
- Ensure image path is correct

## File Structure

- `gradesheet_selector.php` - Main selection page
- `gradesheet_print.php` - Print view with grade sheets
- `get_students_ajax.php` - AJAX endpoint for loading students

## Access Path

Dashboard → Exams → Grade Sheet

## Support

For any issues or questions, contact your system administrator.
