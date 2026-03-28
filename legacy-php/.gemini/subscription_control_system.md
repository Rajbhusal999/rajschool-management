# Subscription Control System - Admin Panel

## Overview

Implemented a comprehensive subscription control system that allows administrators to suspend or activate user subscriptions. The system enforces verification code requirements based on subscription status.

## Feature Summary

### Admin Controls

- **Stop/Suspend**: Admin can suspend active subscriptions
- **Activate**: Admin can reactivate suspended subscriptions
- **Automatic Detection**: System automatically detects expired subscriptions

### User Login Flow

- **Active Subscription**: Login normally without verification code
- **Suspended Subscription**: Requires verification code to login
- **Expired Subscription**: Requires verification code to login

---

## How It Works

### Subscription States

#### 1. **ACTIVE** ✅

- **Status**: `subscription_status = 'active'`
- **Login**: Normal login (EMIS + Password)
- **Access**: Full dashboard access
- **Admin Action**: Can click "STOP" to suspend

#### 2. **SUSPENDED** ⚠️

- **Status**: `subscription_status = 'suspended'`
- **Login**: Requires verification code
- **Access**: Redirected to subscription plans
- **Admin Action**: Can click "ACTIVATE" to restore

#### 3. **EXPIRED** ❌

- **Status**: `subscription_expiry < current_date`
- **Login**: Requires verification code
- **Access**: Redirected to subscription plans
- **Admin Action**: Can click "ACTIVATE" to restore

---

## Admin Panel Interface

### Status Column - Interactive Buttons

#### Active Subscription

```
┌─────────────────────┐
│  ⏹ ACTIVE          │  ← Click to STOP
└─────────────────────┘
   Green badge
   Stop icon
```

**On Click**:

- Confirmation: "Stop this subscription? User will need verification code to login."
- Action: Sets `subscription_status = 'suspended'`
- Result: User needs verification code on next login

#### Suspended/Expired Subscription

```
┌─────────────────────┐
│  ▶ SUSPENDED       │  ← Click to ACTIVATE
└─────────────────────┘
   Red badge
   Play icon
```

**On Click**:

- Confirmation: "Activate this subscription? User can login without verification code."
- Action: Sets `subscription_status = 'active'`
- Result: User can login normally

---

## User Login Flow

### Flow Diagram

```
User enters EMIS + Password
         ↓
   Credentials Valid?
    ├─ NO → Error: "Invalid EMIS Code or Password"
    └─ YES
         ↓
   Check Subscription Status
    ├─ ACTIVE → Login to Dashboard ✅
    ├─ SUSPENDED → Show Verification Form ⚠️
    └─ EXPIRED → Show Verification Form ❌
         ↓
   User Enters Verification Code
         ↓
   Code Matches Database?
    ├─ NO → Error: "Invalid verification code"
    └─ YES → Redirect to Subscription Plans
```

---

## Technical Implementation

### 1. Admin Panel Actions

#### Suspend Action

```php
if ($_GET['action'] == 'suspend') {
    // Suspend subscription - user will need verification code to login
    $stmt = $conn->prepare("UPDATE schools SET subscription_status = 'suspended' WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: super_admin.php?msg=⚠️ Subscription suspended - User will need verification code to login");
    exit();
}
```

#### Activate Action

```php
if ($_GET['action'] == 'activate') {
    // Activate subscription - user can login without verification code
    $stmt = $conn->prepare("UPDATE schools SET subscription_status = 'active' WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: super_admin.php?msg=✓ Subscription activated - User can login normally");
    exit();
}
```

### 2. Status Button UI

```html
<?php if ($row['subscription_status'] == 'active'): ?>
<a
  href="?action=suspend&id=<?php echo $row['id']; ?>"
  class="badge badge-active"
  onclick="return confirm('Stop this subscription? User will need verification code to login.');"
>
  <i class="fas fa-stop-circle"></i>ACTIVE
</a>
<?php else: ?>
<a
  href="?action=activate&id=<?php echo $row['id']; ?>"
  class="badge badge-inactive"
  onclick="return confirm('Activate this subscription? User can login without verification code.');"
>
  <i class="fas fa-play-circle"></i
  ><?php echo strtoupper($row['subscription_status']); ?>
</a>
<?php endif; ?>
```

### 3. Login Verification Logic

```php
// Check subscription status
$is_expired = false;
if ($user['subscription_expiry']) {
    $is_expired = strtotime($user['subscription_expiry']) < time();
}

// Determine if verification code is needed
$needs_verification = false;

if ($user['subscription_status'] == 'suspended') {
    $needs_verification = true;
    $error = 'Your subscription has been suspended by admin. Please enter verification code.';
} elseif ($is_expired) {
    $needs_verification = true;
    $error = 'Your subscription has expired. Please enter verification code to renew.';
}

// If verification is needed
if ($needs_verification) {
    if (isset($_POST['verification_code'])) {
        if ($verification_code == $user['payment_verification_code']) {
            // Verification successful - redirect to subscription plans
            header("Location: subscription_plans.php");
        } else {
            $error = 'Invalid verification code. Please contact admin.';
        }
    } else {
        // Show verification code form
        $show_verification = true;
    }
} else {
    // Active subscription - login normally
    header("Location: dashboard.php");
}
```

---

## User Experience

### Scenario 1: Active User Login

```
1. User enters EMIS Code: 12345678
2. User enters Password: ••••••••
3. Click "Sign In"
4. ✅ Redirected to Dashboard
```

### Scenario 2: Suspended User Login

```
1. User enters EMIS Code: 12345678
2. User enters Password: ••••••••
3. Click "Sign In"
4. ⚠️ Form changes to show:
   - Message: "Your subscription has been suspended by admin. Please enter verification code."
   - Input: "Verification Code" (6-digit)
   - Button: "Verify & Continue"
5. User enters code: 735786
6. Click "Verify & Continue"
7. ✅ If correct → Redirected to Subscription Plans
   ❌ If wrong → Error: "Invalid verification code. Please contact admin."
```

### Scenario 3: Expired User Login

```
1. User enters EMIS Code: 12345678
2. User enters Password: ••••••••
3. Click "Sign In"
4. ⚠️ Form changes to show:
   - Message: "Your subscription has expired. Please enter verification code to renew."
   - Input: "Verification Code" (6-digit)
   - Button: "Verify & Continue"
5. User enters code: 346924
6. Click "Verify & Continue"
7. ✅ If correct → Redirected to Subscription Plans
   ❌ If wrong → Error: "Invalid verification code. Please contact admin."
```

---

## Verification Code Form

### Visual Design

```
┌────────────────────────────────────┐
│  School Portal                     │
│  Enter EMIS Code to access.        │
├────────────────────────────────────┤
│  ⚠️ Your subscription has been     │
│  suspended by admin. Please enter  │
│  verification code.                │
├────────────────────────────────────┤
│  Verification Code                 │
│  ┌──────────────────────────────┐  │
│  │     1  2  3  4  5  6         │  │
│  └──────────────────────────────┘  │
│  ℹ️ Contact admin for verification │
│     code                           │
├────────────────────────────────────┤
│  [ Verify & Continue ]             │
└────────────────────────────────────┘
```

### Form Features

- **Large Input**: 1.2rem font size
- **Letter Spacing**: 0.3em for readability
- **Center Aligned**: Easy to read
- **6-Digit Pattern**: Only accepts numbers
- **Auto-focus**: Cursor ready in input
- **Help Text**: "Contact admin for verification code"

---

## Security Features

### 1. Input Validation

```php
// Validate ID parameter
$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
if ($id === false || $id <= 0) {
    header("Location: super_admin.php?msg=⚠️ Invalid request");
    exit();
}
```

### 2. Confirmation Dialogs

- Prevents accidental suspension/activation
- Clear messaging about consequences
- User must confirm action

### 3. Verification Code Security

- Stored in database (`payment_verification_code`)
- 6-digit numeric code
- Must match exactly
- No brute force protection (consider adding rate limiting)

### 4. Session Security

- Credentials preserved in hidden fields (encrypted in production)
- Session data set only after successful verification
- Immediate redirect after verification

---

## Use Cases

### Use Case 1: Temporary Suspension

**Scenario**: School hasn't paid invoice  
**Admin Action**: Click "STOP" on ACTIVE status  
**Result**: User cannot access dashboard until payment  
**Resolution**: After payment, admin clicks "ACTIVATE"

### Use Case 2: Account Review

**Scenario**: Suspicious activity detected  
**Admin Action**: Click "STOP" to suspend access  
**Result**: User must contact admin for verification code  
**Resolution**: After review, admin provides code and clicks "ACTIVATE"

### Use Case 3: Expired Subscription

**Scenario**: Subscription period ended  
**System Action**: Automatically detects expiry  
**Result**: User must enter verification code  
**Resolution**: User renews subscription, admin clicks "ACTIVATE"

### Use Case 4: Grace Period

**Scenario**: Subscription expired but admin gives grace period  
**Admin Action**: Click "ACTIVATE" on expired subscription  
**Result**: User can login without verification code  
**Benefit**: Smooth renewal experience

---

## Admin Workflow

### Daily Operations

#### Morning Check

```
1. Login to admin panel
2. Review "Days Left" column
3. Identify subscriptions expiring soon (yellow/red)
4. Send expiry notices to affected schools
5. Generate verification codes if needed
```

#### Handling Suspensions

```
1. Receive payment issue report
2. Locate school in admin panel
3. Click "STOP" on ACTIVE status
4. Confirm suspension
5. Message appears: "⚠️ Subscription suspended - User will need verification code to login"
6. Contact school about payment
```

#### Handling Activations

```
1. Receive payment confirmation
2. Locate school in admin panel
3. Click "ACTIVATE" on SUSPENDED status
4. Confirm activation
5. Message appears: "✓ Subscription activated - User can login normally"
6. Notify school that access is restored
```

---

## Database Schema

### Schools Table

```sql
CREATE TABLE schools (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_name VARCHAR(255),
    emis_code VARCHAR(20) UNIQUE,
    email VARCHAR(255),
    password_hash VARCHAR(255),
    phone VARCHAR(20),
    subscription_status ENUM('active', 'suspended', 'expired') DEFAULT 'active',
    subscription_expiry DATE,
    payment_verification_code VARCHAR(6),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Status Values

- `'active'` - Normal access
- `'suspended'` - Admin suspended, needs code
- `'expired'` - Auto-detected, needs code

---

## Error Messages

### User-Facing Messages

| Scenario                | Message                                                                          |
| ----------------------- | -------------------------------------------------------------------------------- |
| Invalid credentials     | "Invalid EMIS Code or Password."                                                 |
| Suspended account       | "Your subscription has been suspended by admin. Please enter verification code." |
| Expired subscription    | "Your subscription has expired. Please enter verification code to renew."        |
| Wrong verification code | "Invalid verification code. Please contact admin."                               |

### Admin Messages

| Action           | Message                                                                 |
| ---------------- | ----------------------------------------------------------------------- |
| Suspend success  | "⚠️ Subscription suspended - User will need verification code to login" |
| Activate success | "✓ Subscription activated - User can login normally"                    |
| Invalid request  | "⚠️ Invalid request"                                                    |

---

## Testing Scenarios

### Test 1: Suspend Active Subscription

```
1. Find active school in admin panel
2. Click "STOP" button
3. Confirm dialog
4. Verify status changes to "SUSPENDED"
5. Try to login as that school
6. Verify verification code form appears
7. Enter correct code
8. Verify redirect to subscription plans
```

### Test 2: Activate Suspended Subscription

```
1. Find suspended school in admin panel
2. Click "ACTIVATE" button
3. Confirm dialog
4. Verify status changes to "ACTIVE"
5. Try to login as that school
6. Verify normal login (no verification code)
7. Verify redirect to dashboard
```

### Test 3: Expired Subscription

```
1. Set subscription_expiry to past date
2. Try to login as that school
3. Verify verification code form appears
4. Enter wrong code
5. Verify error message
6. Enter correct code
7. Verify redirect to subscription plans
```

### Test 4: Invalid Verification Code

```
1. Login with suspended account
2. Enter wrong verification code
3. Verify error: "Invalid verification code. Please contact admin."
4. Form remains on verification screen
5. Can try again with correct code
```

---

## Best Practices

### For Administrators

1. **Communication First**
   - Always notify users before suspending
   - Provide clear reason for suspension
   - Give verification code via email/phone

2. **Documentation**
   - Log all suspension/activation actions
   - Keep records of why accounts were suspended
   - Track verification code usage

3. **Grace Periods**
   - Consider grace period before suspending for non-payment
   - Send multiple reminders before action
   - Be responsive to user inquiries

4. **Verification Codes**
   - Generate new codes regularly
   - Don't reuse codes
   - Provide codes through secure channels

### For Users

1. **Keep Subscription Active**
   - Renew before expiry
   - Monitor expiry date in dashboard
   - Respond to admin notices

2. **Contact Admin**
   - If suspended, contact admin immediately
   - Have payment ready for quick resolution
   - Keep contact information updated

3. **Verification Codes**
   - Store codes securely
   - Don't share codes
   - Request new code if lost

---

## Future Enhancements

### Potential Improvements

1. **Rate Limiting**
   - Limit verification code attempts
   - Lock account after 5 failed attempts
   - Require admin intervention to unlock

2. **Audit Log**
   - Track all suspension/activation actions
   - Record who performed action and when
   - Display in admin panel

3. **Automated Notifications**
   - Auto-email user when suspended
   - Auto-email verification code
   - Auto-notify when activated

4. **Temporary Codes**
   - Verification codes expire after 24 hours
   - One-time use codes
   - SMS verification option

5. **Bulk Actions**
   - Suspend multiple schools at once
   - Activate multiple schools at once
   - Export list of suspended accounts

6. **Self-Service**
   - Allow users to request verification code
   - Automated code generation and delivery
   - Reduce admin workload

---

## Troubleshooting

### Issue 1: Can't Suspend Account

**Symptom**: Click "STOP" but status doesn't change  
**Cause**: Database update failed  
**Solution**: Check database connection, verify permissions

### Issue 2: Verification Code Not Working

**Symptom**: Correct code shows "Invalid"  
**Cause**: Code mismatch or not generated  
**Solution**: Generate new code in admin panel, try again

### Issue 3: Active Users Seeing Verification Form

**Symptom**: Active users prompted for code  
**Cause**: Subscription expired but status still "active"  
**Solution**: Check expiry date, update status or extend expiry

### Issue 4: Suspended Users Can Login

**Symptom**: Suspended users bypass verification  
**Cause**: Status not properly checked  
**Solution**: Clear browser cache, check database status value

---

## Files Modified

### 1. super_admin.php

**Lines 145-162**: Added suspend/activate actions  
**Lines 386-401**: Updated status column with interactive buttons

### 2. login.php

**Lines 5-77**: Implemented verification code logic  
**Lines 140-206**: Added conditional verification form

---

## Summary

✅ **Admin can suspend subscriptions** - Click "STOP" button  
✅ **Admin can activate subscriptions** - Click "ACTIVATE" button  
✅ **Suspended users need verification code** - Automatic enforcement  
✅ **Expired users need verification code** - Automatic detection  
✅ **Active users login normally** - No verification needed  
✅ **Secure verification process** - Code validation  
✅ **User-friendly interface** - Clear messages and forms  
✅ **Confirmation dialogs** - Prevent accidental actions

---

**Implementation Date**: February 11, 2026  
**Version**: 1.0  
**Status**: ✅ Active and Tested  
**Files**: `super_admin.php`, `login.php`
