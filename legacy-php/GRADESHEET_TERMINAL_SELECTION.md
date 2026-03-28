# Grade Sheet Terminal Selection Feature - Update Summary

## ✅ Feature Added: Terminal Exam Selection

### What Changed:

The grade sheet system now allows users to **select which terminal exam** to generate grade sheets for, instead of always generating both 1st and 2nd terminal exams together.

---

## 📋 Changes Made

### 1. **gradesheet_selector.php** - Added Terminal Selector

**Location**: Between Academic Year and Class selection

**New Field**:

```html
<select name="terminal" class="form-select" required>
  <option value="">Select Terminal Exam</option>
  <option value="first_terminal">First Terminal Exam</option>
  <option value="second_terminal">Second Terminal Exam</option>
  <option value="both" selected>Both (1st & 2nd Terminal)</option>
</select>
```

**Features**:

- ✅ Dropdown to select terminal exam type
- ✅ Options: First Terminal, Second Terminal, or Both
- ✅ "Both" is selected by default (maintains backward compatibility)
- ✅ Updated page header and info box text

---

### 2. **gradesheet_print.php** - Conditional Grade Sheet Generation

#### A. Parameter Handling

```php
$terminal = isset($_GET['terminal']) ? $_GET['terminal'] : 'both';

// Validate terminal parameter
if (!in_array($terminal, ['first_terminal', 'second_terminal', 'both'])) {
    $terminal = 'both';
}
```

#### B. Conditional Data Fetching

- **First Terminal Marks**: Only fetched if `$terminal === 'first_terminal'` or `$terminal === 'both'`
- **Second Terminal Marks**: Only fetched if `$terminal === 'second_terminal'` or `$terminal === 'both'`
- **Attendance**: Only fetched for selected terminal(s)

#### C. Conditional Rank Calculation

```php
if ($terminal === 'first_terminal' || $terminal === 'both') {
    $first_terminal_ranks = calculateRanks(...);
}

if ($terminal === 'second_terminal' || $terminal === 'both') {
    $second_terminal_ranks = calculateRanks(...);
}
```

#### D. Conditional HTML Output

- **First Terminal Pages**: Only rendered if selected
- **Second Terminal Pages**: Only rendered if selected
- Each section wrapped with `if ($terminal === '...' || $terminal === 'both'):`

---

## 🎯 How It Works Now

### User Flow:

1. **Navigate**: Dashboard → Exams → Grade Sheet
2. **Select Academic Year**: e.g., 2081
3. **Select Terminal Exam**:
   - First Terminal Exam only
   - Second Terminal Exam only
   - Both (default)
4. **Select Class**: e.g., Class 5
5. **Choose Students**: All or specific students
6. **Print**: Generate grade sheets for selected terminal(s)

### Output Scenarios:

#### Scenario 1: First Terminal Only

- ✅ Generates grade sheets for 1st terminal exam only
- ✅ 2 students per A4 landscape page
- ✅ Shows: 1st terminal marks, GPA, rank, attendance

#### Scenario 2: Second Terminal Only

- ✅ Generates grade sheets for 2nd terminal exam only
- ✅ 2 students per A4 landscape page
- ✅ Shows: 2nd terminal marks, GPA, rank, attendance

#### Scenario 3: Both Terminals (Default)

- ✅ Generates grade sheets for both terminals
- ✅ First: All students' 1st terminal grade sheets
- ✅ Then: All students' 2nd terminal grade sheets
- ✅ 2 students per page for each terminal

---

## 💡 Benefits

### 1. **Flexibility**

- Schools can print grade sheets for individual terminals as needed
- No need to print both if only one is required

### 2. **Efficiency**

- Reduces paper usage when only one terminal is needed
- Faster printing for single terminal exams

### 3. **Database Performance**

- Only fetches data for selected terminal(s)
- Reduces unnecessary database queries

### 4. **User Experience**

- Clear selection with dropdown
- Default "Both" option maintains familiar behavior
- Intuitive interface

---

## 🔧 Technical Details

### Database Queries Optimized:

```php
// OLD: Always fetched both terminals
SELECT ... WHERE exam_type = 'first_terminal' ...
SELECT ... WHERE exam_type = 'second_terminal' ...

// NEW: Conditionally fetch based on selection
if ($terminal === 'first_terminal' || $terminal === 'both') {
    SELECT ... WHERE exam_type = 'first_terminal' ...
}
if ($terminal === 'second_terminal' || $terminal === 'both') {
    SELECT ... WHERE exam_type = 'second_terminal' ...
}
```

### Performance Impact:

- **First Terminal Only**: ~50% fewer queries
- **Second Terminal Only**: ~50% fewer queries
- **Both Terminals**: Same as before (backward compatible)

---

## 📊 Example Use Cases

### Use Case 1: Mid-Year Progress Reports

**Scenario**: School wants to print 1st terminal grade sheets in November
**Action**: Select "First Terminal Exam" → Print
**Result**: Only 1st terminal grade sheets generated

### Use Case 2: Final Year Reports

**Scenario**: School wants to print 2nd terminal grade sheets in March
**Action**: Select "Second Terminal Exam" → Print
**Result**: Only 2nd terminal grade sheets generated

### Use Case 3: Complete Academic Record

**Scenario**: School wants complete record for student files
**Action**: Select "Both (1st & 2nd Terminal)" → Print
**Result**: Complete grade sheets for both terminals

---

## ✅ Testing Checklist

- [x] Terminal selector displays correctly
- [x] All three options work (1st, 2nd, Both)
- [x] Default "Both" option selected
- [x] Data fetches correctly for each option
- [x] Ranks calculate correctly for selected terminal(s)
- [x] Grade sheets render correctly
- [x] Print layout maintains 2 students per page
- [x] Backward compatibility maintained (default = both)
- [x] No syntax errors
- [x] Database queries optimized

---

## 🎨 UI Updates

### Updated Text:

- **Page Header**: "Generate Terminal Examination Grade Sheets" (was: "Generate combined 1st & 2nd...")
- **Info Box**: "Select which terminal exam grade sheets to generate (1st, 2nd, or both)"

### New Dropdown:

- Icon: 📋 (clipboard-list)
- Label: "Terminal Exam"
- Required field
- Default: "Both (1st & 2nd Terminal)"

---

## 🚀 Ready to Use!

The grade sheet system now provides complete flexibility for terminal exam selection while maintaining full backward compatibility. Users can generate grade sheets for:

- ✅ First Terminal only
- ✅ Second Terminal only
- ✅ Both Terminals (default)

All with the same professional 2-students-per-page A4 landscape format!

---

**Implementation Date**: February 9, 2026  
**Version**: 1.1  
**Status**: ✅ Complete and Tested
