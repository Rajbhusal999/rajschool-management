# Backup Users Data Feature - Admin Panel

## Overview

Added a backup functionality to the admin panel that allows administrators to export all school/user data to a CSV file for data recovery, migration, or archival purposes.

## Feature Details

### Location

**Admin Panel**: `super_admin.php`  
**Button Position**: Top-right header, next to Logout link

### Visual Design

```
┌────────────────────────────────────────────────────────────┐
│  Command Center              [Backup Users Data]  Logout   │
└────────────────────────────────────────────────────────────┘
```

**Button Styling**:

- **Color**: Green (#10b981)
- **Hover**: Darker green (#059669)
- **Icon**: Download icon (📥)
- **Text**: "Backup Users Data"

---

## How It Works

### User Flow

```
1. Admin clicks "Backup Users Data" button
   ↓
2. System fetches all school records from database
   ↓
3. Data is formatted as CSV
   ↓
4. File downloads automatically to admin's computer
   ↓
5. Filename: schools_backup_2026-02-11_07-59-26.csv
```

### Technical Implementation

#### 1. Backup Action Handler

```php
if (isset($_GET['action']) && $_GET['action'] == 'backup') {
    // Fetch all schools data
    $stmt = $conn->query("SELECT * FROM schools ORDER BY id ASC");
    $schools_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set headers for CSV download
    $filename = "schools_backup_" . date('Y-m-d_H-i-s') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);

    // Create output stream
    $output = fopen('php://output', 'w');

    // Add BOM for UTF-8 (helps Excel recognize UTF-8)
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Add CSV headers
    if (count($schools_data) > 0) {
        fputcsv($output, array_keys($schools_data[0]));
    }

    // Add data rows
    foreach ($schools_data as $row) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit();
}
```

#### 2. UI Button

```html
<a
  href="?action=backup"
  style="background: #10b981; color: white; padding: 0.75rem 1.5rem; 
          text-decoration: none; border-radius: 8px; font-weight: 600; 
          display: flex; align-items: center; gap: 0.5rem;"
>
  <i class="fas fa-download"></i>
  Backup Users Data
</a>
```

---

## CSV File Format

### Filename Convention

```
schools_backup_YYYY-MM-DD_HH-MM-SS.csv
```

**Examples**:

- `schools_backup_2026-02-11_07-59-26.csv`
- `schools_backup_2026-02-11_14-30-45.csv`

### File Structure

#### CSV Headers (First Row)

```csv
id,school_name,email,password,contact,address,established_date,subscription_plan,subscription_status,subscription_expiry,payment_verification_code,email_verification_code,is_verified
```

#### Sample Data Rows

```csv
1,"Smart Demo School","demo@smartvidhyalaya.com","$2y$10$hash...","9800000000","Kathmandu","2020-01-01","premium","active","2026-02-09","735786","","1"
3,"Shree Himalaya Basic School (1-8)","school@gmail.com","$2y$10$hash...","9845065451","Pokhara","2015-05-15","basic","active","2027-02-06","346924","","1"
```

### Data Fields Included

| Field                       | Description         | Example             |
| --------------------------- | ------------------- | ------------------- |
| `id`                        | Unique school ID    | 1, 2, 3             |
| `school_name`               | School name         | "Smart Demo School" |
| `email`                     | School email        | "demo@example.com"  |
| `password`                  | Hashed password     | "$2y$10$hash..."    |
| `contact`                   | Phone number        | "9800000000"        |
| `address`                   | School address      | "Kathmandu"         |
| `established_date`          | Founding date       | "2020-01-01"        |
| `subscription_plan`         | Plan type           | "premium", "basic"  |
| `subscription_status`       | Status              | "active", "expired" |
| `subscription_expiry`       | Expiry date         | "2026-02-09"        |
| `payment_verification_code` | Payment code        | "735786"            |
| `email_verification_code`   | Email code          | ""                  |
| `is_verified`               | Verification status | "1", "0"            |

---

## Use Cases

### 1. Data Backup

**Purpose**: Regular backups for disaster recovery  
**Frequency**: Daily, weekly, or monthly  
**Storage**: Keep backups in secure location

### 2. Data Migration

**Purpose**: Move data to new server or system  
**Process**: Export → Transfer → Import

### 3. Data Analysis

**Purpose**: Analyze user trends and statistics  
**Tools**: Excel, Google Sheets, Python, R

### 4. Compliance

**Purpose**: Meet data retention requirements  
**Benefit**: Audit trail and record keeping

### 5. Reporting

**Purpose**: Generate reports for stakeholders  
**Output**: Charts, graphs, summaries

---

## Security Considerations

### ✅ Security Features

1. **Admin-Only Access**
   - Only logged-in admins can access
   - Session check prevents unauthorized access

2. **No User Interaction Required**
   - Direct download, no intermediate pages
   - Reduces exposure time

3. **Timestamped Filenames**
   - Unique filenames prevent overwrites
   - Easy to track when backup was created

### ⚠️ Security Warnings

1. **Sensitive Data**
   - CSV contains hashed passwords
   - Contains email addresses and contact info
   - Store backups securely

2. **Access Control**
   - Keep backup files in secure location
   - Don't share publicly
   - Encrypt if storing in cloud

3. **Data Privacy**
   - Comply with data protection laws (GDPR, etc.)
   - Delete old backups when no longer needed
   - Inform users about data backup practices

---

## File Encoding

### UTF-8 with BOM

```php
// Add BOM for UTF-8 (helps Excel recognize UTF-8)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
```

**Why BOM?**

- Helps Microsoft Excel recognize UTF-8 encoding
- Prevents character encoding issues
- Ensures proper display of special characters (विद्यालय, etc.)

**Character Support**:
✅ English characters  
✅ Nepali/Devanagari characters (विद्यालय)  
✅ Special symbols (©, ®, ™)  
✅ Emojis and unicode

---

## Opening the CSV File

### Microsoft Excel

1. Double-click the CSV file
2. Excel opens with proper formatting
3. UTF-8 BOM ensures correct character display

### Google Sheets

1. Go to Google Sheets
2. File → Import
3. Upload the CSV file
4. Select "Comma" as delimiter
5. Click "Import data"

### LibreOffice Calc

1. Open LibreOffice Calc
2. File → Open
3. Select the CSV file
4. Choose "UTF-8" encoding
5. Click OK

### Text Editor

1. Open with Notepad++, VS Code, etc.
2. View raw CSV data
3. Useful for quick inspection

---

## Restoring from Backup

### Manual Restoration

```sql
-- Import CSV data back to database
LOAD DATA INFILE 'schools_backup_2026-02-11_07-59-26.csv'
INTO TABLE schools
FIELDS TERMINATED BY ','
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 ROWS;
```

### Using phpMyAdmin

1. Open phpMyAdmin
2. Select `schools` table
3. Click "Import" tab
4. Choose CSV file
5. Select "CSV using LOAD DATA"
6. Click "Go"

### Using MySQL Workbench

1. Open MySQL Workbench
2. Right-click on `schools` table
3. Select "Table Data Import Wizard"
4. Choose CSV file
5. Follow wizard steps

---

## Backup Best Practices

### 1. Regular Backups

- **Daily**: For active systems
- **Weekly**: For moderate activity
- **Monthly**: For archival

### 2. Multiple Locations

- **Local**: Computer hard drive
- **Cloud**: Google Drive, Dropbox
- **External**: USB drive, external HDD

### 3. Naming Convention

- Use timestamps (already implemented)
- Add version numbers if needed
- Include environment (prod, test)

### 4. Retention Policy

- Keep last 7 daily backups
- Keep last 4 weekly backups
- Keep last 12 monthly backups
- Delete older backups

### 5. Testing

- Periodically test restoration
- Verify data integrity
- Ensure backups are not corrupted

---

## Troubleshooting

### Issue 1: Download Doesn't Start

**Cause**: Browser blocking download  
**Solution**: Check browser settings, allow downloads

### Issue 2: File Opens as Text

**Cause**: No CSV association  
**Solution**: Right-click → Open with → Excel

### Issue 3: Special Characters Broken

**Cause**: Wrong encoding  
**Solution**: Open with UTF-8 encoding explicitly

### Issue 4: Empty File

**Cause**: No data in database  
**Solution**: Check if schools table has records

### Issue 5: Permission Denied

**Cause**: File system permissions  
**Solution**: Check PHP write permissions

---

## Performance Considerations

### Current Implementation

- Fetches all records at once
- Suitable for small to medium databases (< 10,000 records)

### For Large Databases (> 10,000 records)

Consider implementing:

1. **Chunked Processing**: Process in batches
2. **Background Jobs**: Use queue system
3. **Compression**: Zip the CSV file
4. **Streaming**: Stream data instead of loading all

### Example: Chunked Processing

```php
// Process 1000 records at a time
$offset = 0;
$limit = 1000;

while (true) {
    $stmt = $conn->prepare("SELECT * FROM schools LIMIT ? OFFSET ?");
    $stmt->execute([$limit, $offset]);
    $batch = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($batch)) break;

    foreach ($batch as $row) {
        fputcsv($output, $row);
    }

    $offset += $limit;
}
```

---

## Future Enhancements

### Potential Improvements

1. **Selective Backup**
   - Choose specific fields to export
   - Filter by date range
   - Filter by status (active/expired)

2. **Multiple Formats**
   - JSON export
   - XML export
   - Excel (.xlsx) format

3. **Scheduled Backups**
   - Automatic daily backups
   - Email backup file to admin
   - Store in cloud automatically

4. **Compression**
   - Zip CSV file automatically
   - Reduces file size
   - Easier to transfer

5. **Encryption**
   - Encrypt backup files
   - Password-protected
   - Enhanced security

6. **Backup History**
   - Track all backups created
   - Show backup size and date
   - Quick restore from history

---

## Statistics

### Backup Information

- **Format**: CSV (Comma-Separated Values)
- **Encoding**: UTF-8 with BOM
- **Delimiter**: Comma (,)
- **Quote Character**: Double quote (")
- **Line Ending**: \n (Unix style)

### Typical File Sizes

- **10 schools**: ~2 KB
- **100 schools**: ~20 KB
- **1,000 schools**: ~200 KB
- **10,000 schools**: ~2 MB

---

## Example Workflow

### Daily Backup Routine

**Morning (9:00 AM)**:

```
1. Admin logs into super admin panel
2. Clicks "Backup Users Data"
3. File downloads: schools_backup_2026-02-11_09-00-00.csv
4. Admin uploads to Google Drive
5. Admin verifies file integrity
```

**Weekly (Sunday)**:

```
1. Download weekly backup
2. Rename: schools_backup_weekly_2026-W06.csv
3. Store in separate folder
4. Delete daily backups older than 7 days
```

**Monthly (1st of month)**:

```
1. Download monthly backup
2. Rename: schools_backup_monthly_2026-02.csv
3. Archive in long-term storage
4. Delete weekly backups older than 4 weeks
```

---

## Compliance & Legal

### Data Protection

- **GDPR**: Ensure backups comply with EU regulations
- **Privacy**: Inform users about data backup
- **Retention**: Follow data retention policies
- **Security**: Encrypt sensitive backups

### Audit Trail

- Log all backup operations
- Track who downloaded backups
- Monitor access to backup files

---

**Implementation Date**: February 11, 2026  
**Version**: 1.0  
**Status**: ✅ Active  
**Location**: `super_admin.php` (lines 154-183, 288-296)
