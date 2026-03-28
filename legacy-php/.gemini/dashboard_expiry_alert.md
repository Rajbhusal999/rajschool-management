# User Dashboard Subscription Expiry Alert

## Overview

Added a dynamic subscription expiry alert system to the user dashboard that automatically displays a warning banner when a school's subscription is expiring within 1 week (7 days).

## Feature Details

### Alert Trigger Conditions

The alert appears when:

- Subscription is **active** (not already expired)
- Days remaining ≤ **7 days** (1 week)
- User is logged into their dashboard

### Visual Design

#### Two Alert Levels:

1. **Critical Alert (0-2 days remaining)**
   - **Color**: Red (#fee2e2 background, #dc2626 border)
   - **Icon**: ⚠️ Exclamation Triangle
   - **Animation**: Pulsing effect (opacity fades in/out every 2 seconds)
   - **Title**: "Critical: Subscription Expiring Soon!"
   - **Button**: Red "Renew Now" button (#dc2626)
   - **Purpose**: Urgent attention required

2. **Warning Alert (3-7 days remaining)**
   - **Color**: Orange (#fff7ed background, #f97316 border)
   - **Icon**: 🕐 Clock
   - **Animation**: None (static)
   - **Title**: "Subscription Expiring Soon"
   - **Button**: Orange "Renew Now" button (#f97316)
   - **Purpose**: Advance warning

### Alert Content

The alert displays:

- **Subscription Plan Name**: e.g., "Premium Plan", "Basic Plan"
- **Days Remaining**: Large, bold countdown (e.g., "2 days")
- **Exact Expiry Date**: Formatted as "February 18, 2026"
- **Action Message**: "Please renew to avoid service interruption"
- **Renew Button**: Direct link to subscription plans page

### Example Alert Messages

#### Critical (2 days):

```
⚠️ Critical: Subscription Expiring Soon!

Your Premium Plan subscription will expire in 2 days
on February 13, 2026. Please renew to avoid service interruption.

[Renew Now]
```

#### Warning (5 days):

```
🕐 Subscription Expiring Soon

Your Basic Plan subscription will expire in 5 days
on February 16, 2026. Please renew to avoid service interruption.

[Renew Now]
```

## Technical Implementation

### Database Query

```php
// Fetch subscription expiry information
$stmt = $conn->prepare("SELECT subscription_expiry, subscription_plan FROM schools WHERE id = ?");
$stmt->execute([$school_id]);
$subscription_info = $stmt->fetch(PDO::FETCH_ASSOC);
```

### Days Calculation

```php
$days_left = ceil((strtotime($subscription_expiry) - time()) / (60 * 60 * 24));

// Show alert if expiring within 7 days (1 week)
if ($days_left > 0 && $days_left <= 7) {
    $show_expiry_alert = true;
}
```

### Conditional Styling

```php
if ($days_left <= 2) {
    // Critical - Red alert with pulse animation
} else {
    // Warning - Orange alert (static)
}
```

## User Experience Flow

```
┌─────────────────────┐
│  User Logs In       │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  Dashboard Loads    │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐      NO      ┌─────────────────────┐
│  Days Left ≤ 7?     │─────────────▶│  No Alert Shown     │
└──────────┬──────────┘              └─────────────────────┘
           │ YES
           ▼
┌─────────────────────┐
│  Check Days Left    │
└──────────┬──────────┘
           │
           ├─── ≤2 days ───▶ Red Critical Alert (Pulsing)
           │
           └─── 3-7 days ──▶ Orange Warning Alert (Static)
```

## Responsive Design

### Desktop View

- Alert spans full width of content area
- Icon, text, and button displayed in single row
- Optimal spacing and padding

### Mobile View

- Alert remains visible
- Content may wrap for smaller screens
- Button stays accessible
- Icon size adjusts appropriately

## Alert Positioning

The alert is positioned:

1. **After**: Navigation bar
2. **Before**: Demo mode alert (if applicable)
3. **Before**: Dashboard content (stats, charts, activities)

This ensures maximum visibility when users first land on the dashboard.

## CSS Animation

### Pulse Effect (Critical Alerts Only)

```css
@keyframes pulse {
  0%,
  100% {
    opacity: 1;
  }
  50% {
    opacity: 0.7;
  }
}
```

- **Duration**: 2 seconds per cycle
- **Infinite**: Loops continuously
- **Effect**: Gentle fade in/out to draw attention

## User Actions

### "Renew Now" Button

- **Action**: Redirects to `subscription_plans.php`
- **Style**: Prominent, color-coded button
- **Icon**: ↑ Arrow up icon
- **Hover**: Subtle shadow effect

### Dismissal

- Alert is **not dismissible** (intentional)
- Remains visible until subscription is renewed
- Ensures users cannot ignore critical warnings

## Testing Scenarios

### Test Case 1: 1 Day Remaining

- **Expected**: Red critical alert with pulse animation
- **Message**: "1 day" (singular)
- **Button**: Red "Renew Now"

### Test Case 2: 2 Days Remaining

- **Expected**: Red critical alert with pulse animation
- **Message**: "2 days" (plural)
- **Button**: Red "Renew Now"

### Test Case 3: 5 Days Remaining

- **Expected**: Orange warning alert (no animation)
- **Message**: "5 days"
- **Button**: Orange "Renew Now"

### Test Case 4: 7 Days Remaining

- **Expected**: Orange warning alert (no animation)
- **Message**: "7 days"
- **Button**: Orange "Renew Now"

### Test Case 5: 8 Days Remaining

- **Expected**: No alert shown
- **Reason**: Beyond 7-day threshold

### Test Case 6: Expired Subscription

- **Expected**: No alert shown (user redirected to login)
- **Reason**: Subscription status check prevents dashboard access

## Database Requirements

### Required Fields in `schools` Table:

- `subscription_expiry` (DATE): Expiry date of subscription
- `subscription_plan` (VARCHAR): Plan name (e.g., 'premium', 'basic')
- `subscription_status` (VARCHAR): Status ('active', 'expired')

### Sample Data:

```sql
-- Critical alert (2 days)
UPDATE schools SET subscription_expiry = DATE_ADD(CURDATE(), INTERVAL 2 DAY) WHERE id = 1;

-- Warning alert (5 days)
UPDATE schools SET subscription_expiry = DATE_ADD(CURDATE(), INTERVAL 5 DAY) WHERE id = 2;

-- No alert (10 days)
UPDATE schools SET subscription_expiry = DATE_ADD(CURDATE(), INTERVAL 10 DAY) WHERE id = 3;
```

## Integration with Admin Panel

This feature complements the admin panel's "Send Notice" functionality:

### Admin Side:

- Admin sees "Days Left" column
- Admin can send email notices
- Admin monitors all schools

### User Side:

- User sees dashboard alert
- User receives email notification (if admin sends)
- User can renew directly from dashboard

## Benefits

### For Schools:

✅ **Proactive Notification**: See expiry warning before it's too late
✅ **Visual Urgency**: Color-coded alerts indicate severity
✅ **Easy Action**: One-click access to renewal page
✅ **No Surprises**: Clear countdown and exact date

### For Administrators:

✅ **Reduced Support**: Users self-serve renewal
✅ **Better Retention**: Timely reminders prevent lapses
✅ **Clear Communication**: Consistent messaging
✅ **Automated System**: No manual intervention needed

## Customization Options

### Adjustable Thresholds:

```php
// Change alert threshold from 7 to 14 days
if ($days_left > 0 && $days_left <= 14) {
    $show_expiry_alert = true;
}

// Change critical threshold from 2 to 3 days
if ($days_left <= 3) {
    // Critical alert
}
```

### Custom Messages:

```php
// Personalize alert message
$alert_message = "Dear {$school_name}, your subscription expires soon!";
```

## Accessibility

- **Color Contrast**: High contrast text for readability
- **Icon Support**: Visual icons supplement text
- **Clear Language**: Simple, direct messaging
- **Keyboard Navigation**: Button is keyboard accessible
- **Screen Readers**: Semantic HTML structure

## Performance

- **Single Query**: One database query for subscription info
- **Minimal Overhead**: Simple date calculation
- **No External Calls**: All processing server-side
- **Fast Rendering**: Inline styles for immediate display

## Future Enhancements

### Potential Improvements:

1. **Dismissible Alerts**: Allow users to hide for 24 hours
2. **Countdown Timer**: Live JavaScript countdown
3. **Multiple Reminders**: Show at 14, 7, 3, and 1 day
4. **Email Integration**: Auto-send email when alert appears
5. **SMS Alerts**: Send SMS for critical (≤2 days)
6. **Grace Period**: Show different message for expired subscriptions
7. **Auto-Renewal**: Offer automatic renewal option

---

**Implementation Date**: February 11, 2026
**Version**: 1.0
**Status**: ✅ Complete and Active
**Location**: `dashboard.php` (lines 14-30, 160-226)
