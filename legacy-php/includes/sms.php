<?php
// SMS Helper Function for Nepal
// To send real SMS, you need an account with a Nepali SMS Provider like Sparrow SMS or Aakash SMS.

function send_sms_message($phone, $message)
{
    // =================================================================
    // CHOOSE YOUR SMS PROVIDER (Uncomment ONE section below)
    // =================================================================

    // -----------------------------------------------------------------
    // OPTION 1: SPARROW SMS (Recommended / Most Common)
    // Register at: https://sparrowsms.com/
    // -----------------------------------------------------------------
    /*
    $api_url = "http://api.sparrowsms.com/v2/sms/";
    $token = "YOUR_SPARROW_API_TOKEN";  // <-- REPLACE THIS with your Token
    $from  = "InfoSMS";                 // <-- REPLACE THIS with your approved Identity

    $args = http_build_query([
        'token' => $token,
        'from'  => $from,
        'to'    => $phone,
        'text'  => $message
    ]);

    $url = $api_url . "?" . $args;

    // Use CURL to send request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Initial check (200 = Success request, but check response text for errors like "Insufficient Credits")
    if ($status_code == 200) {
        return true; 
    }
    */

    // -----------------------------------------------------------------
    // OPTION 2: AAKASH SMS
    // Register at: https://aakashsms.com/
    // -----------------------------------------------------------------
    /*
    $auth_token = "YOUR_AAKASH_AUTH_TOKEN"; // <-- REPLACE THIS
    $args = http_build_query([
        'auth_token' => $auth_token,
        'to'    => $phone,
        'text'  => $message
    ]);
    $url = "https://sms.aakashsms.com/sms/v3/send/?" . $args;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    // You can parse $response to check success
    */

    // =================================================================
    // 3. FALLBACK (SIMULATION MODE - For Testing without paying)
    // =================================================================
    // Currently, since no API is configured, we save to a file.
    // REMOVE THIS BLOCK when you enable the real API above.

    $log_entry = "--------------------------------------------------\n";
    $log_entry .= "DATE    : " . date('Y-m-d H:i:s') . "\n";
    $log_entry .= "TO      : " . $phone . "\n";
    $log_entry .= "MESSAGE : " . $message . "\n";
    $log_entry .= "STATUS  : SIMULATED (Check includes/sms.php to configure Real SMS)\n";
    $log_entry .= "--------------------------------------------------\n\n";

    file_put_contents(__DIR__ . '/../sms_log.txt', $log_entry, FILE_APPEND);

    return true;
}
?>