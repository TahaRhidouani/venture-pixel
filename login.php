<?php
require __DIR__ . '/lib/common.php';
start_session();

if(isSet($_POST['status']) && $_POST['status'] == 'submit'){
    $accessid = is_string($_POST['accessid'] ?? null) ? $_POST['accessid'] : '';
    if ($accessid != "") {
        // The admin page keeps the marker "admin" instead of the password once signed in.
        if (admin_password_matches($accessid) || ($accessid === 'admin' && is_admin())) {
            if (!is_admin()) {
                session_regenerate_id(true);
            }
            $_SESSION = ['admin' => true];
            echo "admin";
        } else if (is_access_id($accessid) && is_dir(data_path($accessid))) {
            if (($_SESSION['accessid'] ?? null) !== $accessid) {
                session_regenerate_id(true);
            }
            $_SESSION = ['accessid' => $accessid];
            echo "true";
        } else {
            echo "false";
        }
    } else {
        $_SESSION = [];
        session_destroy();
        echo "logout";
    }
}
?>