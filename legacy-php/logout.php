<?php
session_start();
session_unset();
session_destroy();

// Clear session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}
?>
<!DOCTYPE html>
<html>

<body>
    <script>
        // Set a flag to notify other tabs
        localStorage.setItem('logout_event', Date.now());
        // Redirect this tab
        window.location.href = 'index.php';
    </script>
</body>

</html>
<?php
exit();
?>