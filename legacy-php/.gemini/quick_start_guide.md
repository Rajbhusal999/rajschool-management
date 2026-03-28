# Quick Start Guide: Subscription Expiry Notices

## For Administrators

### How to Send Subscription Expiry Notices

#### Step 1: Access Admin Panel

1. Navigate to: `http://localhost/student management/admin.php`
2. Login with your admin credentials
3. You'll be redirected to the Command Center (Super Admin Panel)

#### Step 2: Identify Schools Needing Notices

Look at the **"Days Left"** column in the table:

- 🔴 **Red badges** = Critical (0-2 days left)
- 🟡 **Yellow badges** = Warning (3-7 days left)
- 🟢 **Green badges** = Safe (8+ days left)

#### Step 3: Send Notice

For schools with **2 days or less** remaining:

1. Locate the orange **"Send Notice"** button in the Actions column
2. Click the button
3. Wait for confirmation message
4. The school will receive an email notification

#### Step 4: Verify Email Sent

After clicking "Send Notice":

- ✅ **Success**: You'll see "✓ Expiry notice sent to [email]"
- ⚠️ **Warning**: If email configuration is not set up, you'll see a configuration message

---

## Email Configuration (One-Time Setup)

### Prerequisites

Before sending emails, ensure:

1. PHPMailer is installed (via Composer)
2. Email settings are configured in `includes/email_config.php`

### Configuration File Location

```
student management/
└── includes/
    └── email_config.php
```

### Sample Configuration

```php
<?php
// SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_ENCRYPTION', 'tls');
define('SMTP_FROM_EMAIL', 'your-email@gmail.com');
define('SMTP_FROM_NAME', 'Smart विद्यालय');
define('SMTP_DEBUG', 0); // Set to 2 for debugging
?>
```

### For Gmail Users

1. Enable 2-Factor Authentication
2. Generate an App Password
3. Use the App Password in `SMTP_PASSWORD`

### For Production

Consider using:

- **SendGrid**: Professional email service
- **Amazon SES**: AWS email service
- **Mailgun**: Email API service

---

## What Schools Receive

### Email Subject

```
⚠️ Subscription Expiry Notice - Action Required
```

### Email Content

- Professional branded header
- Personalized greeting with school name
- Clear warning about expiry
- Days remaining (highlighted)
- Exact expiry date
- "Renew Now" button
- Support information

### Email Preview

```
┌──────────────────────────────────────┐
│   Smart विद्यालय                     │
│   School Management System           │
├──────────────────────────────────────┤
│                                      │
│ Dear ABC High School,                │
│                                      │
│ ⚠️ Your subscription is expiring     │
│    soon!                             │
│                                      │
│ Your subscription will expire in     │
│ 2 days on February 13, 2026.         │
│                                      │
│ To continue enjoying uninterrupted   │
│ access to all features, please       │
│ renew your subscription.             │
│                                      │
│      [ Renew Now ]                   │
│                                      │
│ Thank you for choosing               │
│ Smart विद्यालय!                      │
│                                      │
├──────────────────────────────────────┤
│ © 2026 Smart विद्यालय               │
└──────────────────────────────────────┘
```

---

## Troubleshooting

### Problem: "Send Notice" button not appearing

**Solution**:

- Check if subscription has more than 2 days left
- Button only appears when days left ≤ 2

### Problem: Email not sending

**Solution**:

1. Check `includes/email_config.php` exists
2. Verify SMTP credentials are correct
3. Check PHP error logs
4. Test with a known working email address
5. Set `SMTP_DEBUG` to 2 for detailed error messages

### Problem: Email goes to spam

**Solution**:

1. Use a professional email service (SendGrid, SES)
2. Configure SPF and DKIM records
3. Use a verified domain email address
4. Avoid spam trigger words in subject/body

### Problem: Wrong expiry date shown

**Solution**:

- Verify server timezone is correct
- Check database `subscription_expiry` field format
- Ensure date is stored as YYYY-MM-DD

---

## Best Practices

### When to Send Notices

✅ **Do**:

- Send notices 2 days before expiry
- Send during business hours
- Send to verified email addresses
- Follow up if no response

❌ **Don't**:

- Send multiple notices in one day
- Send to invalid email addresses
- Send after subscription has expired (use different message)

### Monitoring Tips

1. **Daily Check**: Review admin panel daily
2. **Color Coding**: Use the color badges for quick assessment
3. **Proactive**: Send notices as soon as they appear
4. **Follow-up**: Call schools if they don't respond to email

### Communication Tips

- Be professional and courteous
- Provide clear renewal instructions
- Offer support if needed
- Give adequate notice period

---

## Advanced Features (Future)

### Planned Enhancements

- ⏰ **Automated Notices**: Cron job to send automatically
- 📊 **Notice History**: Track when notices were sent
- 📱 **SMS Alerts**: Send SMS in addition to email
- 🔔 **Multiple Reminders**: 7 days, 3 days, 1 day
- 📧 **Bulk Actions**: Send to all expiring schools at once

---

## Support

### For Technical Issues

1. Check documentation in `.gemini/subscription_notice_implementation.md`
2. Review email configuration
3. Check PHP error logs
4. Contact system administrator

### For Feature Requests

Submit requests to development team with:

- Feature description
- Use case
- Priority level
- Expected behavior

---

## Quick Reference

| Days Left   | Badge Color | Send Notice Button | Action Required     |
| ----------- | ----------- | ------------------ | ------------------- |
| 0 (Expired) | 🔴 Red      | No                 | Contact immediately |
| 1-2 days    | 🔴 Red      | ✅ Yes             | Send notice now     |
| 3-7 days    | 🟡 Yellow   | No                 | Monitor closely     |
| 8+ days     | 🟢 Green    | No                 | No action needed    |

---

**Last Updated**: February 11, 2026  
**Version**: 1.0  
**Contact**: khatapana@gmail.com
