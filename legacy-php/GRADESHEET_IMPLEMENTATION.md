# Grade Sheet System - Implementation Summary

## ✅ What Has Been Created

### 1. Grade Sheet Selector Page (`gradesheet_selector.php`)

**Purpose**: Main interface for configuring and selecting students for grade sheet generation

**Features**:

- Academic year selection (Nepali B.S. calendar)
- Class selection dropdown
- Dynamic student loading via AJAX
- Individual student selection with checkboxes
- "Select All" functionality
- Two print options:
  - Print All Students
  - Print Selected Students
- Beautiful gradient UI with purple theme
- Responsive design

**Access**: Dashboard → Exams → Grade Sheet (orange card)

### 2. AJAX Student Loader (`get_students_ajax.php`)

**Purpose**: Backend endpoint to fetch students by class

**Features**:

- JSON API response
- Fetches students with ID, full name, and symbol number
- Ordered alphabetically by name
- Session-based authentication

### 3. Grade Sheet Print Page (`gradesheet_print.php`)

**Purpose**: Generates printable grade sheets with combined 1st & 2nd terminal marks

**Features**:

- **Layout**: 2 students per A4 landscape page
- **Dual Terminal Display**: Separate pages for 1st and 2nd terminal exams
- **Complete Information**:
  - School logo, name, address, establishment date
  - Student name, roll number, class
  - Subject-wise marks with credit hours
  - Grade points and letter grades (A+, A, B+, B, C+, C, D, NG)
  - GPA calculation
  - Class rank
  - Attendance
  - Signature lines for Class Teacher, Exam Coordinator, Head Teacher

**Print Settings**:

- Paper: A4 Landscape
- Optimized for professional printing
- Black borders for clarity
- Print-ready CSS with @media print rules

### 4. Integration with Exams Page

**Updated**: `exams.php`

- Added new "Grade Sheet" card (orange/yellow gradient)
- Direct link to `gradesheet_selector.php`
- Positioned between "Mark Slip" and "Subject Management"

### 5. Documentation

**Files Created**:

- `GRADESHEET_GUIDE.md` - Comprehensive user guide
- This implementation summary

## 📋 How It Works

### Data Flow:

1. User selects academic year and class in `gradesheet_selector.php`
2. AJAX call to `get_students_ajax.php` loads students dynamically
3. User selects print option (all or selected students)
4. Form submits to `gradesheet_print.php` with parameters:
   - `year`: Academic year
   - `class`: Selected class
   - `print_type`: "all" or "selected"
   - `students[]`: Array of selected student IDs (if print_type = "selected")
5. `gradesheet_print.php` fetches:
   - Student data
   - Subjects for the class group
   - 1st terminal marks from `exam_marks` table
   - 2nd terminal marks from `exam_marks` table
   - Attendance data for both terminals
6. Calculates GPA and ranks for both terminals
7. Generates HTML pages with 2 students per page
8. Displays separate pages for 1st and 2nd terminal exams
9. User clicks "Print Grade Sheets" to print

### Database Tables Used:

- `users` - School information (name, location, photo, establishment_date)
- `students` - Student data (id, full_name, symbol_no, class)
- `subjects` - Subject list with credit hours
- `exam_marks` - Marks data (participation, practical, terminal) for both exam types
- `exam_attendance` - Attendance records for both terminals

## 🎨 Design Features

### Visual Design:

- **Selector Page**: Modern gradient design with purple theme
- **Grade Sheets**: Professional, formal layout matching the sample image
- **Typography**: Times New Roman for grade sheets (formal), Sans-serif for UI
- **Colors**: School-appropriate with clear hierarchy
- **Icons**: Font Awesome 6.0 icons throughout

### User Experience:

- Intuitive step-by-step process
- Real-time student loading
- Clear visual feedback
- Informative help text
- Responsive layout
- Print-optimized output

## 🔧 Technical Implementation

### Technologies:

- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB via PDO
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **AJAX**: Fetch API for dynamic loading
- **Icons**: Font Awesome 6.0
- **Print**: CSS @media print rules

### Key Functions:

```php
getClassGroup($class)           // Determines class group (1-3, 4-5, 6-8, 9-10)
getGradePoint($marks, $max)     // Calculates grade point from marks
getGrade($gp)                   // Converts grade point to letter grade
getRemarks($gpa)                // Generates remarks based on GPA
calculateRanks(...)             // Calculates student ranks by GPA
```

### Security:

- Session-based authentication
- SQL injection prevention (PDO prepared statements)
- XSS prevention (htmlspecialchars)
- Input validation
- Subscription status check

## 📊 Grade Sheet Format

### Page Layout:

```
┌─────────────────────────────────────────────────────────┐
│  [Student 1 Grade Sheet]  │  [Student 2 Grade Sheet]   │
│                            │                             │
│  School Header             │  School Header              │
│  Student Info              │  Student Info               │
│  Marks Table               │  Marks Table                │
│  GPA, Rank, Attendance     │  GPA, Rank, Attendance      │
│  Signatures                │  Signatures                 │
└─────────────────────────────────────────────────────────┘
```

### Marks Table Structure:

| S.N. | SUBJECTS | CREDIT HOUR | GRADE POINT | GRADE | ... | GRADE | REMARKS |
| ---- | -------- | ----------- | ----------- | ----- | --- | ----- | ------- |
| 1    | NEPALI   | 2.5         | 3.2         | B+    | ... | B+    |         |
| 2    | ENGLISH  | 2.5         | 2.8         | B     | ... | B     |         |

## 🚀 Testing Instructions

### Step 1: Verify Prerequisites

1. Ensure marks are entered for 1st and 2nd terminal exams
2. Check subjects are configured with credit hours
3. Verify students have symbol numbers assigned
4. Upload school logo if not already done

### Step 2: Access the System

1. Login to your school account
2. Navigate to Dashboard
3. Click on "Exams"
4. Click on "Grade Sheet" card (orange/yellow)

### Step 3: Generate Grade Sheets

1. Select academic year (e.g., 2081)
2. Select a class (e.g., Class 5)
3. Wait for students to load
4. Choose print option:
   - Click "Print All Students" for entire class
   - OR select specific students and click "Print Selected Students"

### Step 4: Print

1. Review the generated grade sheets
2. Click "Print Grade Sheets" button
3. In print dialog:
   - Select printer
   - Set orientation to **Landscape**
   - Set paper size to **A4**
   - Adjust margins if needed
4. Click Print

## 📝 Sample Output

Each student gets TWO grade sheets:

1. **First Terminal Examination Grade Sheet**
2. **Second Terminal Examination Grade Sheet**

Both displayed side-by-side (2 students per page) in landscape format.

## 🔍 Troubleshooting

### Common Issues:

**1. Students not loading**

- Check if students exist in the selected class
- Verify database connection
- Check browser console for AJAX errors

**2. Missing marks**

- Ensure marks are entered in Mark Entry
- Verify exam_type is 'first_terminal' or 'second_terminal'
- Check year matches selected year

**3. Incorrect GPA/Rank**

- Verify all subject marks are entered
- Check credit hours are set for subjects
- Ensure calculation functions are working

**4. Print layout issues**

- Use A4 paper size
- Set orientation to Landscape
- Use Chrome/Firefox for best results
- Check print preview before printing

## 📁 File Locations

```
student management/
├── gradesheet_selector.php      # Main selector page
├── gradesheet_print.php         # Print generation page
├── get_students_ajax.php        # AJAX endpoint
├── exams.php                    # Updated with grade sheet link
├── GRADESHEET_GUIDE.md          # User guide
└── GRADESHEET_IMPLEMENTATION.md # This file
```

## ✨ Features Summary

✅ Combined 1st & 2nd terminal marks display
✅ 2 students per A4 landscape page
✅ Print all or selected students
✅ Professional grade sheet format
✅ School branding (logo, name, address)
✅ GPA calculation and class ranking
✅ Attendance tracking
✅ Subject-wise grade display
✅ Credit hour integration
✅ Signature lines for authorities
✅ Print-optimized CSS
✅ Responsive UI
✅ AJAX-powered student loading
✅ Session-based security

## 🎯 Next Steps (Optional Enhancements)

1. **PDF Export**: Add PDF generation using libraries like TCPDF or mPDF
2. **Email Distribution**: Send grade sheets to guardian emails
3. **Bulk Download**: ZIP file download of all grade sheets
4. **Custom Templates**: Allow schools to customize grade sheet layout
5. **Historical Data**: View grade sheets from previous years
6. **Performance Analytics**: Add charts and graphs
7. **Multi-language**: Support for Nepali language
8. **Digital Signatures**: Add digital signature support

## 📞 Support

For issues or questions:

- Check GRADESHEET_GUIDE.md for user instructions
- Review database schema for data requirements
- Verify all prerequisite data is entered
- Contact system administrator for technical support

---

**Implementation Date**: February 9, 2026
**Version**: 1.0
**Status**: ✅ Complete and Ready to Use
