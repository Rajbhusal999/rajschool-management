# Admin Panel - Visual Layout

## Table Structure

```
┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│                                                              COMMAND CENTER                                                                                                 │
│                                                                                                                                                      [Logout]              │
└─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│ ID │ School Name        │ EMIS Code │ Email              │ Contact      │ Status  │ Joining Date │ Expiry Date │ Days Left      │ Verif. Code │ Actions                │
├────┼────────────────────┼───────────┼────────────────────┼──────────────┼─────────┼──────────────┼─────────────┼────────────────┼─────────────┼────────────────────────┤
│ #1 │ ABC High School    │ 12345     │ abc@school.com     │ 9841234567   │ 🟢ACTIVE│ 2026-01-15   │ 2026-02-13  │ 🔴 2 days      │ 123456      │ [Generate Code]        │
│    │                    │           │                    │              │         │              │             │                │             │ [🟠 Send Notice]       │
│    │                    │           │                    │              │         │              │             │                │             │ [Delete]               │
├────┼────────────────────┼───────────┼────────────────────┼──────────────┼─────────┼──────────────┼─────────────┼────────────────┼─────────────┼────────────────────────┤
│ #2 │ XYZ Academy        │ 67890     │ xyz@school.com     │ 9851234567   │ 🟢ACTIVE│ 2026-01-10   │ 2026-02-16  │ 🟡 5 days      │ 789012      │ [Generate Code]        │
│    │                    │           │                    │              │         │              │             │                │             │ [Delete]               │
├────┼────────────────────┼───────────┼────────────────────┼──────────────┼─────────┼──────────────┼─────────────┼────────────────┼─────────────┼────────────────────────┤
│ #3 │ PQR School         │ 11223     │ pqr@school.com     │ 9861234567   │ 🟢ACTIVE│ 2026-01-01   │ 2026-03-13  │ 🟢 30 days     │ 345678      │ [Generate Code]        │
│    │                    │           │                    │              │         │              │             │                │             │ [Delete]               │
├────┼────────────────────┼───────────┼────────────────────┼──────────────┼─────────┼──────────────┼─────────────┼────────────────┼─────────────┼────────────────────────┤
│ #4 │ LMN College        │ 44556     │ lmn@school.com     │ 9871234567   │ 🔴EXPIRED│ 2025-12-01  │ 2026-02-10  │ 🔴 EXPIRED     │ 901234      │ [Generate Code]        │
│    │                    │           │                    │              │         │              │             │                │             │ [Delete]               │
└────┴────────────────────┴───────────┴────────────────────┴──────────────┴─────────┴──────────────┴─────────────┴────────────────┴─────────────┴────────────────────────┘
```

## Color Legend

### Days Left Column:

- 🔴 **Red Badge** (0-2 days): Critical - Immediate action required
  - Background: rgba(239, 68, 68, 0.2)
  - Text Color: #ef4444
  - Shows "Send Notice" button

- 🟡 **Yellow Badge** (3-7 days): Warning - Renewal needed soon
  - Background: rgba(250, 204, 21, 0.2)
  - Text Color: #facc15
  - No "Send Notice" button

- 🟢 **Green Badge** (8+ days): Safe - Subscription active
  - Background: rgba(16, 185, 129, 0.1)
  - Text Color: #34d399
  - No "Send Notice" button

### Status Column:

- 🟢 **ACTIVE**: Green badge with border
- 🔴 **EXPIRED**: Red badge with border

### Action Buttons:

- **Generate Code**: Blue button (#0ea5e9)
- **Send Notice**: Orange button (#f59e0b) - Only visible when days left ≤ 2
- **Delete**: Red button (#ef4444)

## Key Features

### 1. Days Left Column (NEW)

- Automatically calculates days remaining
- Color-coded for quick visual identification
- Updates in real-time based on current date

### 2. Send Notice Button (NEW)

- Appears only when subscription expires in ≤ 2 days
- Orange color to indicate urgency
- Sends professional email notification
- Includes hover effect for better UX

### 3. Email Notification

When "Send Notice" is clicked:

```
┌────────────────────────────────────────────────┐
│  ⚠️ Subscription Expiry Notice - Action Required │
├────────────────────────────────────────────────┤
│                                                │
│  Dear ABC High School,                         │
│                                                │
│  ⚠️ Your subscription is expiring soon!        │
│                                                │
│  Your subscription will expire in 2 days       │
│  on February 13, 2026.                         │
│                                                │
│  To continue enjoying uninterrupted access     │
│  to all features of Smart विद्यालय, please    │
│  renew your subscription before it expires.    │
│                                                │
│         ┌──────────────────┐                   │
│         │   Renew Now      │                   │
│         └──────────────────┘                   │
│                                                │
│  Thank you for choosing Smart विद्यालय!       │
│                                                │
└────────────────────────────────────────────────┘
```

## Workflow

```
┌─────────────────┐
│  Admin Views    │
│  Dashboard      │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Checks "Days   │
│  Left" Column   │
└────────┬────────┘
         │
         ▼
┌─────────────────┐      NO      ┌─────────────────┐
│  Days ≤ 2?      │─────────────▶│  Monitor Only   │
└────────┬────────┘              └─────────────────┘
         │ YES
         ▼
┌─────────────────┐
│  "Send Notice"  │
│  Button Visible │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Admin Clicks   │
│  "Send Notice"  │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Email Sent to  │
│  School         │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Success        │
│  Message        │
└─────────────────┘
```

## Responsive Design

The table is wrapped in a scrollable container for smaller screens:

```html
<div style="overflow-x: auto;">
  <table class="admin-table">
    <!-- Table content -->
  </table>
</div>
```

## Browser Compatibility

- ✅ Chrome/Edge (Latest)
- ✅ Firefox (Latest)
- ✅ Safari (Latest)
- ✅ Mobile browsers (Responsive)

## Accessibility Features

- Clear color contrast for readability
- Descriptive button titles
- Semantic HTML structure
- Keyboard navigation support
