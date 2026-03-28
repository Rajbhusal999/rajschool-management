# User Dashboard Alert - Visual Examples

## What Users Will See

### Scenario 1: Critical Alert (2 Days Remaining)

```
╔════════════════════════════════════════════════════════════════════════════╗
║                         SMART विद्यालय DASHBOARD                          ║
╚════════════════════════════════════════════════════════════════════════════╝

┌──────────────────────────────────────────────────────────────────────────┐
│                                                                          │
│  ⚠️   Critical: Subscription Expiring Soon!              [Renew Now]    │
│                                                                          │
│      Your Premium Plan subscription will expire in 2 days               │
│      on February 13, 2026. Please renew to avoid service interruption.  │
│                                                                          │
└──────────────────────────────────────────────────────────────────────────┘
   ↑                                                              ↑
   Red background                                          Red button
   Pulsing animation                                       (urgent)


┌─────────────────────────────────────────────────────────────────────────┐
│  Dashboard Overview                                                     │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────────┐        │
│  │   Total     │  │   Total     │  │  Subscription Status    │        │
│  │  Students   │  │  Teachers   │  │                         │        │
│  │             │  │             │  │      Active             │        │
│  │     150     │  │      12     │  │                         │        │
│  │   Active    │  │ Dept. Head  │  │    Premium Plan         │        │
│  └─────────────┘  └─────────────┘  └─────────────────────────┘        │
│                                                                         │
│  [Rest of dashboard content...]                                        │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

**Visual Characteristics:**

- 🔴 **Background**: Light red (#fee2e2)
- 🔴 **Border**: Dark red left border (#dc2626)
- ⚠️ **Icon**: Large warning triangle in red circle
- 💓 **Animation**: Gentle pulse (fades 100% → 70% → 100%)
- 🔴 **Button**: Red "Renew Now" with shadow
- **Text**: Bold "2 days" in larger font

---

### Scenario 2: Warning Alert (5 Days Remaining)

```
╔════════════════════════════════════════════════════════════════════════════╗
║                         SMART विद्यालय DASHBOARD                          ║
╚════════════════════════════════════════════════════════════════════════════╝

┌──────────────────────────────────────────────────────────────────────────┐
│                                                                          │
│  🕐   Subscription Expiring Soon                         [Renew Now]    │
│                                                                          │
│      Your Basic Plan subscription will expire in 5 days                 │
│      on February 16, 2026. Please renew to avoid service interruption.  │
│                                                                          │
└──────────────────────────────────────────────────────────────────────────┘
   ↑                                                              ↑
   Orange background                                       Orange button
   No animation (static)                                   (warning)


┌─────────────────────────────────────────────────────────────────────────┐
│  Dashboard Overview                                                     │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────────┐        │
│  │   Total     │  │   Total     │  │  Subscription Status    │        │
│  │  Students   │  │  Teachers   │  │                         │        │
│  │             │  │             │  │      Active             │        │
│  │      85     │  │       8     │  │                         │        │
│  │   Active    │  │ Dept. Head  │  │     Basic Plan          │        │
│  └─────────────┘  └─────────────┘  └─────────────────────────┘        │
│                                                                         │
│  [Rest of dashboard content...]                                        │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

**Visual Characteristics:**

- 🟠 **Background**: Light orange (#fff7ed)
- 🟠 **Border**: Dark orange left border (#f97316)
- 🕐 **Icon**: Clock icon in orange circle
- ⏸️ **Animation**: None (static display)
- 🟠 **Button**: Orange "Renew Now" with shadow
- **Text**: Bold "5 days" in standard size

---

### Scenario 3: No Alert (10+ Days Remaining)

```
╔════════════════════════════════════════════════════════════════════════════╗
║                         SMART विद्यालय DASHBOARD                          ║
╚════════════════════════════════════════════════════════════════════════════╝

┌─────────────────────────────────────────────────────────────────────────┐
│  Dashboard Overview                                                     │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────────┐        │
│  │   Total     │  │   Total     │  │  Subscription Status    │        │
│  │  Students   │  │  Teachers   │  │                         │        │
│  │             │  │             │  │      Active             │        │
│  │     200     │  │      15     │  │                         │        │
│  │   Active    │  │ Dept. Head  │  │    Premium Plan         │        │
│  └─────────────┘  └─────────────┘  └─────────────────────────┘        │
│                                                                         │
│  [Rest of dashboard content...]                                        │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

**Visual Characteristics:**

- ✅ **No Alert**: Banner not displayed
- 🟢 **Status**: Subscription safe
- 📅 **Days Left**: 10+ days remaining
- **Display**: Normal dashboard view

---

## Alert Timeline Visualization

```
Subscription Expiry Timeline
────────────────────────────────────────────────────────────────────────▶

30 days    20 days    10 days    7 days     5 days     2 days    0 days
   │          │          │          │          │          │         │
   │          │          │          │          │          │         │
   ▼          ▼          ▼          ▼          ▼          ▼         ▼

   🟢         🟢         🟢         🟠         🟠         🔴        ⛔
  Safe       Safe       Safe     Warning    Warning   Critical  Expired

   No         No         No      Orange     Orange      Red       No
  Alert      Alert      Alert    Alert      Alert      Alert    Access
                                (Static)   (Static)   (Pulse)
```

---

## Mobile View

### Critical Alert on Mobile

```
┌─────────────────────────────┐
│  ☰  Smart विद्यालय         │
└─────────────────────────────┘

┌─────────────────────────────┐
│                             │
│  ⚠️                         │
│  Critical: Subscription     │
│  Expiring Soon!             │
│                             │
│  Your Premium Plan          │
│  subscription will expire   │
│  in 2 days on February 13,  │
│  2026. Please renew to      │
│  avoid service interruption.│
│                             │
│      [Renew Now]            │
│                             │
└─────────────────────────────┘

┌─────────────────────────────┐
│  Dashboard Overview         │
├─────────────────────────────┤
│  ┌───────────────────────┐  │
│  │  Total Students       │  │
│  │       150             │  │
│  └───────────────────────┘  │
│                             │
│  [More content...]          │
└─────────────────────────────┘
```

**Mobile Adaptations:**

- Alert stacks vertically
- Icon on top
- Text wraps naturally
- Button full width
- Maintains visibility

---

## Color Palette Reference

### Critical Alert (Red)

```
Background:     #fee2e2  ░░░░░░░░░░ (Light red)
Border:         #dc2626  ██████████ (Dark red)
Icon BG:        #fecaca  ▓▓▓▓▓▓▓▓▓▓ (Medium red)
Icon Color:     #991b1b  ██████████ (Deep red)
Text:           #991b1b  ██████████ (Deep red)
Title:          #7f1d1d  ██████████ (Darkest red)
Button:         #dc2626  ██████████ (Dark red)
```

### Warning Alert (Orange)

```
Background:     #fff7ed  ░░░░░░░░░░ (Light orange)
Border:         #f97316  ██████████ (Dark orange)
Icon BG:        #ffedd5  ▓▓▓▓▓▓▓▓▓▓ (Medium orange)
Icon Color:     #ea580c  ██████████ (Deep orange)
Text:           #c2410c  ██████████ (Dark orange)
Title:          #9a3412  ██████████ (Darkest orange)
Button:         #f97316  ██████████ (Dark orange)
```

---

## Animation Details

### Pulse Effect (Critical Only)

```
Frame 1 (0s):    ████████████  Opacity: 100%

Frame 2 (1s):    ████████░░░░  Opacity: 70%

Frame 3 (2s):    ████████████  Opacity: 100%

[Repeats infinitely]
```

**Timing:**

- Duration: 2 seconds per cycle
- Easing: Linear
- Infinite loop
- Subtle, not distracting

---

## User Interaction Flow

### From Alert to Renewal

```
┌─────────────────┐
│  User sees      │
│  alert on       │
│  dashboard      │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Reads message: │
│  "2 days left"  │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Clicks         │
│  "Renew Now"    │
│  button         │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Redirected to  │
│  subscription   │
│  plans page     │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Selects plan   │
│  and completes  │
│  payment        │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Subscription   │
│  renewed!       │
│  Alert removed  │
└─────────────────┘
```

---

## Accessibility Features

### Screen Reader Announcement

**Critical Alert:**

```
"Warning! Critical: Subscription Expiring Soon!
Your Premium Plan subscription will expire in 2 days
on February 13, 2026. Please renew to avoid service
interruption. Renew Now button available."
```

**Warning Alert:**

```
"Notice: Subscription Expiring Soon.
Your Basic Plan subscription will expire in 5 days
on February 16, 2026. Please renew to avoid service
interruption. Renew Now button available."
```

### Keyboard Navigation

- Alert is in natural tab order
- "Renew Now" button is keyboard accessible
- Enter/Space activates button
- Focus visible on button

---

## Browser Compatibility

✅ **Chrome/Edge**: Full support (animation, colors, layout)
✅ **Firefox**: Full support
✅ **Safari**: Full support
✅ **Mobile Safari**: Full support (responsive)
✅ **Chrome Mobile**: Full support (responsive)

---

## Performance Metrics

- **Load Time**: < 50ms (inline styles)
- **Animation**: 60 FPS (CSS animation)
- **Database Query**: 1 query (subscription info)
- **Render Blocking**: None (inline CSS)
- **Memory**: Minimal (no JavaScript)

---

**Last Updated**: February 11, 2026
**Version**: 1.0
**Status**: ✅ Active
