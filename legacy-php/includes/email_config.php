<?php
/**
 * Email Configuration File
 * 
 * INSTRUCTIONS FOR GMAIL SETUP:
 * 1. Go to your Google Account: https://myaccount.google.com/
 * 2. Click "Security" in the left sidebar
 * 3. Enable "2-Step Verification" if not already enabled
 * 4. Search for "App passwords" or go to: https://myaccount.google.com/apppasswords
 * 5. Select "Mail" and "Other (Custom name)" - name it "Smart Vidhyalaya"
 * 6. Click "Generate" - you'll get a 16-character password
 * 7. Copy that password and paste it in SMTP_PASSWORD below
 * 
 * FOR PRODUCTION (When launching publicly):
 * Switch to SendGrid, Mailgun, or Amazon SES by changing these settings
 */

// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com');           // Gmail SMTP server
define('SMTP_PORT', 587);                         // Port for TLS
define('SMTP_USERNAME', 'bhusalraaz2@gmail.com'); // Your Gmail address
define('SMTP_PASSWORD', 'your-app-password');    // 16-character app password from Gmail
define('SMTP_FROM_EMAIL', 'bhusalraaz2@gmail.com'); // From email (same as username)
define('SMTP_FROM_NAME', 'Smart विद्यालय');      // From name

// Email Settings
define('SMTP_ENCRYPTION', 'tls');                 // tls or ssl
define('SMTP_DEBUG', 0);                          // 0=off, 1=client, 2=server, 3=connection

/**
 * ALTERNATIVE CONFIGURATIONS (For when you switch to production):
 * 
 * SENDGRID:
 * define('SMTP_HOST', 'smtp.sendgrid.net');
 * define('SMTP_PORT', 587);
 * define('SMTP_USERNAME', 'apikey');
 * define('SMTP_PASSWORD', 'your-sendgrid-api-key');
 * 
 * MAILGUN:
 * define('SMTP_HOST', 'smtp.mailgun.org');
 * define('SMTP_PORT', 587);
 * define('SMTP_USERNAME', 'postmaster@your-domain.mailgun.org');
 * define('SMTP_PASSWORD', 'your-mailgun-password');
 * 
 * AMAZON SES:
 * define('SMTP_HOST', 'email-smtp.us-east-1.amazonaws.com');
 * define('SMTP_PORT', 587);
 * define('SMTP_USERNAME', 'your-ses-smtp-username');
 * define('SMTP_PASSWORD', 'your-ses-smtp-password');
 */
?>