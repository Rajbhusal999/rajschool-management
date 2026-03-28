# Complete Subscription Control Flow - User Guide

## Overview

This document explains the complete flow of how subscription control works from both admin and user perspectives.

---

## Complete User Journey

### Scenario: Admin Suspends an Active User

```
┌─────────────────────────────────────────────────────────────┐
│                    COMPLETE FLOW                            │
└─────────────────────────────────────────────────────────────┘

Step 1: User is Working
├─ User logged in with EMIS Code + Password
├─ Using dashboard normally
└─ Subscription Status: ACTIVE ✅

Step 2: Admin Takes Action
├─ Admin opens super admin panel
├─ Finds the school in the list
├─ Clicks green "ACTIVE" badge (Stop button)
├─ Confirms: "Stop this subscription?"
└─ Status changes to: SUSPENDED ⚠️

Step 3: User Gets Logged Out
├─ User clicks any menu item OR refreshes page
├─ System checks database status
├─ Detects: subscription_status = 'suspended'
├─ Destroys user session
└─ Redirects to: login.php

Step 4: User Tries to Login
├─ User enters EMIS Code: 356030006
├─ User enters Password: ••••••••
├─ Clicks "Sign In"
├─ System validates credentials ✅
├─ System checks subscription status
└─ Detects: SUSPENDED or EXPIRED

Step 5: Verification Code Required
├─ Form changes to show verification code input
├─ Message: "Your subscription has been suspended by admin"
├─ Input field: "Enter 6-digit code"
└─ Button: "Verify & Continue"

Step 6: Admin Generates Code
├─ Admin opens super admin panel
├─ Finds the school
├─ Clicks "Generate Code" button
├─ New code generated: 735786
└─ Admin shares code with user (email/phone)

Step 7: User Enters Code
├─ User enters code: 735786
├─ Clicks "Verify & Continue"
├─ System validates code
└─ If correct: Redirects to subscription_plans.php

Step 8: Resolution
Option A: User Renews Subscription
├─ User pays for renewal
├─ Admin activates subscription
└─ User can login normally

Option B: Admin Activates Without Payment
├─ Admin clicks "ACTIVATE" button
├─ Status changes to: ACTIVE
└─ User can login without verification code
```

---

## Detailed Step-by-Step Guide

### For Users

#### When Your Subscription is Suspended

**What You'll See:**

1. **Automatic Logout**
   - You're working on dashboard
   - You click a menu item or refresh
   - Suddenly redirected to login page
   - **Why?** Admin suspended your subscription

2. **Login Page**
   - Enter your EMIS Code
   - Enter your Password
   - Click "Sign In"

3. **Verification Code Form Appears**

   ```
   ┌────────────────────────────────────┐
   │  School Portal                     │
   ├────────────────────────────────────┤
   │  ⚠️ Your subscription has been     │
   │  suspended by admin. Please enter  │
   │  verification code.                │
   ├────────────────────────────────────┤
   │  Verification Code                 │
   │  ┌──────────────────────────────┐  │
   │  │     _ _ _ _ _ _              │  │
   │  └──────────────────────────────┘  │
   │  ℹ️ Contact admin for verification │
   │     code                           │
   ├────────────────────────────────────┤
   │  [ Verify & Continue ]             │
   └────────────────────────────────────┘
   ```

4. **Contact Admin**
   - Call or email your system administrator
   - Request verification code
   - Admin will provide 6-digit code

5. **Enter Code**
   - Type the 6-digit code (e.g., 735786)
   - Click "Verify & Continue"

6. **Access Granted**
   - Redirected to subscription plans page
   - Renew your subscription
   - Or wait for admin to activate

---

### For Administrators

#### How to Suspend a Subscription

**Step-by-Step:**

1. **Login to Admin Panel**
   - Go to: `admin.php`
   - Enter admin credentials
   - Click "Login"

2. **Find the School**
   - Scroll through the schools list
   - Or use browser search (Ctrl+F)
   - Locate the school to suspend

3. **Click Stop Button**
   - Find the "STATUS" column
   - Click the green "⏹ ACTIVE" badge
   - Confirmation dialog appears

4. **Confirm Action**
   - Dialog: "Stop this subscription? User will need verification code to login."
   - Click "OK" to confirm
   - Status changes to "▶ SUSPENDED"

5. **Success Message**
   - Green notification appears
   - "⚠️ Subscription suspended - User will need verification code to login"

6. **User is Logged Out**
   - User automatically logged out on next page load
   - User redirected to login page

---

#### How to Generate Verification Code

**Step-by-Step:**

1. **Locate School in Admin Panel**
   - Find the suspended school
   - Look at "Verif. Code" column

2. **Click "Generate Code"**
   - Click the "Generate Code" button
   - New 6-digit code is created
   - Example: 735786

3. **Success Message**
   - "Code generated: 735786"
   - Code is displayed in the table

4. **Share Code with User**
   - Email the code to school
   - Or call and provide code
   - User needs this to login

---

#### How to Activate Subscription

**Step-by-Step:**

1. **Locate School in Admin Panel**
   - Find the suspended school
   - Look at "STATUS" column

2. **Click Activate Button**
   - Click the red "▶ SUSPENDED" badge
   - Confirmation dialog appears

3. **Confirm Action**
   - Dialog: "Activate this subscription? User can login without verification code."
   - Click "OK" to confirm
   - Status changes to "⏹ ACTIVE"

4. **Success Message**
   - "✓ Subscription activated - User can login normally"

5. **User Can Login**
   - User can now login with just EMIS + Password
   - No verification code needed
   - Direct access to dashboard

---

## Login Flow Diagrams

### Flow 1: Active User Login (Normal)

```
User enters EMIS + Password
         ↓
    Credentials Valid?
         ↓ YES
  Check Subscription Status
         ↓
    Status = ACTIVE ✅
         ↓
  Login to Dashboard
```

### Flow 2: Suspended User Login

```
User enters EMIS + Password
         ↓
    Credentials Valid?
         ↓ YES
  Check Subscription Status
         ↓
    Status = SUSPENDED ⚠️
         ↓
  Show Verification Form
         ↓
  User Enters Code
         ↓
    Code Valid?
    ├─ YES → Subscription Plans
    └─ NO → Error Message
```

### Flow 3: Expired User Login

```
User enters EMIS + Password
         ↓
    Credentials Valid?
         ↓ YES
  Check Subscription Status
         ↓
  Check Expiry Date
         ↓
    Expired? ❌
         ↓
  Show Verification Form
         ↓
  User Enters Code
         ↓
    Code Valid?
    ├─ YES → Subscription Plans
    └─ NO → Error Message
```

---

## Admin Panel Interface

### Status Column

**Active Subscription:**

```
┌─────────────────────┐
│  ⏹ ACTIVE          │  ← Click to SUSPEND
└─────────────────────┘
   Green badge
   Stop icon
   Clickable
```

**Suspended Subscription:**

```
┌─────────────────────┐
│  ▶ SUSPENDED       │  ← Click to ACTIVATE
└─────────────────────┘
   Red badge
   Play icon
   Clickable
```

**Expired Subscription:**

```
┌─────────────────────┐
│  ▶ EXPIRED         │  ← Click to ACTIVATE
└─────────────────────┘
   Red badge
   Play icon
   Clickable
```

### Verification Code Column

**Code Generated:**

```
┌─────────────────────┐
│     735786         │  ← Current code
└─────────────────────┘
   Yellow text
   Monospace font
   Letter-spaced
```

**No Code:**

```
┌─────────────────────┐
│        -           │  ← No code yet
└─────────────────────┘
```

### Actions Column

**Generate Code Button:**

```
┌─────────────────────┐
│  Generate Code     │  ← Click to create new code
└─────────────────────┘
   Blue button
   Creates 6-digit code
```

---

## Common Scenarios

### Scenario 1: Non-Payment

**Situation:** School hasn't paid invoice

**Admin Actions:**

1. Click "STOP" on ACTIVE status
2. Generate verification code
3. Contact school about payment
4. Share verification code
5. After payment, click "ACTIVATE"

**User Experience:**

1. Logged out automatically
2. Tries to login
3. Sees verification code form
4. Contacts admin
5. Receives code
6. Enters code
7. Renews subscription

---

### Scenario 2: Subscription Expired

**Situation:** Subscription period ended

**System Actions:**

1. Automatically detects expiry
2. User logged out on next page load
3. Login requires verification code

**Admin Actions:**

1. Generate verification code
2. Share with school
3. After renewal, click "ACTIVATE"

**User Experience:**

1. Logged out automatically
2. Sees: "Your subscription has expired"
3. Contacts admin
4. Receives code
5. Enters code
6. Renews subscription

---

### Scenario 3: Account Review

**Situation:** Suspicious activity detected

**Admin Actions:**

1. Click "STOP" immediately
2. Review account activity
3. Contact school for verification
4. If legitimate, click "ACTIVATE"
5. If not, keep suspended

**User Experience:**

1. Logged out immediately
2. Sees suspension message
3. Contacts admin
4. Explains situation
5. Waits for resolution

---

## Error Messages

### User-Facing Messages

| Situation               | Message                                                                          |
| ----------------------- | -------------------------------------------------------------------------------- |
| Wrong EMIS/Password     | "Invalid EMIS Code or Password."                                                 |
| Suspended Account       | "Your subscription has been suspended by admin. Please enter verification code." |
| Expired Subscription    | "Your subscription has expired. Please enter verification code to renew."        |
| Wrong Verification Code | "Invalid verification code. Please contact admin."                               |

### Admin Messages

| Action           | Message                                                                 |
| ---------------- | ----------------------------------------------------------------------- |
| Suspend Success  | "⚠️ Subscription suspended - User will need verification code to login" |
| Activate Success | "✓ Subscription activated - User can login normally"                    |
| Code Generated   | "Code generated: 735786"                                                |

---

## Verification Code Details

### Code Format

- **Length**: 6 digits
- **Type**: Numeric only (0-9)
- **Example**: 735786, 346924, 123456

### Code Generation

- **Method**: Random number generation
- **Range**: 100000 to 999999
- **Storage**: `payment_verification_code` column
- **Validity**: Until new code generated

### Code Usage

- **One-time**: No (can be reused)
- **Expiry**: No expiry (until replaced)
- **Sharing**: Via email, phone, or in-person

---

## Security Considerations

### For Administrators

✅ **Generate New Codes Regularly**

- Don't reuse old codes
- Generate fresh code for each suspension
- Keep codes confidential

✅ **Verify User Identity**

- Confirm user identity before sharing code
- Use secure communication channels
- Don't share codes publicly

✅ **Document Actions**

- Keep record of suspensions
- Note reasons for suspension
- Track code generation

### For Users

✅ **Keep Codes Secure**

- Don't share verification codes
- Delete codes after use
- Request new code if compromised

✅ **Contact Admin Promptly**

- Don't delay when suspended
- Resolve issues quickly
- Keep contact info updated

---

## Troubleshooting

### User: "I can't login"

**Check:**

1. Is subscription suspended? → Contact admin for code
2. Is subscription expired? → Contact admin for code
3. Wrong EMIS/Password? → Reset password
4. Wrong verification code? → Request new code from admin

### Admin: "User still accessing after suspension"

**Check:**

1. Did you click "STOP" button? → Click it
2. Did user refresh page? → Ask them to refresh
3. Is subscription_check.php included? → Verify code
4. Database updated? → Check database

### User: "Verification code not working"

**Check:**

1. Correct code? → Ask admin for current code
2. Typing correctly? → Check all 6 digits
3. Code expired? → Request new code
4. Database issue? → Contact admin

---

## Quick Reference

### Admin Quick Actions

| Task          | Steps                                 |
| ------------- | ------------------------------------- |
| Suspend       | Click green "ACTIVE" badge → Confirm  |
| Activate      | Click red "SUSPENDED" badge → Confirm |
| Generate Code | Click "Generate Code" button          |
| Share Code    | Copy from "Verif. Code" column        |

### User Quick Actions

| Task              | Steps                                 |
| ----------------- | ------------------------------------- |
| Login (Active)    | EMIS + Password → Dashboard           |
| Login (Suspended) | EMIS + Password → Code → Plans        |
| Get Code          | Contact admin via email/phone         |
| Renew             | Enter code → Subscription Plans → Pay |

---

## Summary

### Complete Flow in 8 Steps

1. **Admin suspends** → Click "STOP" button
2. **User logged out** → Automatic on next page load
3. **User tries login** → Enters EMIS + Password
4. **System detects** → Suspended or expired status
5. **Verification required** → Form appears
6. **Admin generates code** → 6-digit number
7. **User enters code** → Verification
8. **Access granted** → Subscription plans page

### Key Points

✅ **Immediate Effect** - Suspension works instantly  
✅ **Automatic Logout** - No manual action needed  
✅ **Verification Required** - Code from admin mandatory  
✅ **Admin Control** - Full control over access  
✅ **User Guidance** - Clear messages at each step

---

**Document Version**: 1.0  
**Last Updated**: February 11, 2026  
**Status**: ✅ Complete and Tested
