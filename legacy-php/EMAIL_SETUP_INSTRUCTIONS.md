# Email Setup Instructions for Smart विद्यालय

## 📧 How to Send Real Emails

Your system is now configured to send real emails! Follow these steps:

---

## Option 1: Gmail SMTP (For Testing & Small Scale)

### Step 1: Enable 2-Step Verification

1. Go to your Google Account: https://myaccount.google.com/
2. Click **"Security"** in the left sidebar
3. Scroll down and enable **"2-Step Verification"**
4. Follow the prompts to set it up

### Step 2: Generate App Password

1. After enabling 2-Step Verification, go to: https://myaccount.google.com/apppasswords
2. Select **"Mail"** from the dropdown
3. Select **"Other (Custom name)"** and type: `Smart Vidhyalaya`
4. Click **"Generate"**
5. You'll see a 16-character password like: `abcd efgh ijkl mnop`
6. **Copy this password** (you won't see it again!)

### Step 3: Configure Email Settings

1. Open the file: `includes/email_config.php`
2. Replace these lines:
   ```php
   define('SMTP_USERNAME', 'your-email@gmail.com');  // Your Gmail address
   define('SMTP_PASSWORD', 'your-app-password');     // The 16-char password from Step 2
   define('SMTP_FROM_EMAIL', 'your-email@gmail.com'); // Same as username
   ```

### Step 4: Test It!

1. Go to your admin panel
2. Click on any school's email address
3. The verification code will be sent to their real Gmail!

**Gmail Limits:**

- ✅ Free
- ⚠️ 500 emails per day limit
- ⚠️ May go to spam folder

---

## Option 2: SendGrid (Recommended for Production)

### Why SendGrid?

- ✅ 100 free emails per day (forever)
- ✅ Professional delivery (won't go to spam)
- ✅ Easy to set up
- ✅ Detailed analytics

### Setup Steps:

1. Sign up at: https://sendgrid.com/
2. Verify your email
3. Go to **Settings** → **API Keys**
4. Click **"Create API Key"**
5. Name it "Smart Vidhyalaya" and select **"Full Access"**
6. Copy the API key

### Configure:

Open `includes/email_config.php` and change to:

```php
define('SMTP_HOST', 'smtp.sendgrid.net');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'apikey');  // Literally the word "apikey"
define('SMTP_PASSWORD', 'YOUR_SENDGRID_API_KEY');  // Paste your API key here
define('SMTP_FROM_EMAIL', 'noreply@yourdomain.com'); // Your verified sender email
```

---

## Option 3: Your Web Hosting Email

Most web hosts (Hostinger, Bluehost, etc.) provide email services.

### Setup:

1. Check your hosting control panel (cPanel)
2. Create an email account (e.g., `noreply@yourdomain.com`)
3. Find SMTP settings in your hosting docs

### Configure:

```php
define('SMTP_HOST', 'mail.yourdomain.com');  // From hosting provider
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'noreply@yourdomain.com');
define('SMTP_PASSWORD', 'your-email-password');
define('SMTP_FROM_EMAIL', 'noreply@yourdomain.com');
```

---

## 🧪 Testing

After configuration, test by:

1. Registering a new school with your real email
2. Using "Forgot Password" feature
3. Admin clicking on school email to send verification code

---

## 🚀 For Production Launch

**Recommended Setup:**

1. **Use SendGrid or Amazon SES** (professional, reliable)
2. **Verify your domain** (prevents spam folder)
3. **Set up DKIM/SPF records** (your email provider will guide you)
4. **Monitor email delivery** through your provider's dashboard

---

## 📝 Current Status

- ✅ PHPMailer installed
- ✅ Email functions created
- ✅ Integrated into admin panel & password reset
- ⚠️ **Email config needed** - Edit `includes/email_config.php`

---

## ❓ Need Help?

If emails aren't sending:

1. Check `includes/email_config.php` - credentials correct?
2. Enable debug mode: Set `SMTP_DEBUG` to `2` in config file
3. Check error messages shown on screen
4. Verify 2-Step Verification is enabled (for Gmail)
5. Make sure App Password is correct (for Gmail)

---

## 📧 What Emails Are Sent?

1. **Verification Code Email** - When admin clicks school email
2. **Password Reset Email** - When user requests password reset

Both use professional HTML templates with your branding!

---

**Good luck with your launch! 🎉**
