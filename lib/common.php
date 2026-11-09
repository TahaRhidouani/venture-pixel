<?php
// Shared by the PHP endpoints: storage under DATA_DIR, the session and access checks.

define('DATA_DIR', rtrim(getenv('DATA_DIR') ?: '/data', '/'));
define('LANGUAGES', ['en', 'fr', 'sp']);

header('Content-Type: text/plain; charset=utf-8');

function data_path($relative)
{
    return DATA_DIR . '/' . $relative;
}

function read_json($relative)
{
    $raw = @file_get_contents(data_path($relative));
    $value = $raw === false ? null : json_decode($raw, true);
    return is_array($value) ? $value : [];
}

function write_json($relative, $value, $flags = 0)
{
    return file_put_contents(data_path($relative), json_encode($value, $flags), LOCK_EX) !== false;
}

function start_session()
{
    @mkdir(data_path('sessions'), 0770, true);
    session_name('venture_pixel');
    session_save_path(data_path('sessions'));
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https',
    ]);
    session_start();
}

// Booking ids: 13 hex characters from the old uniqid() ids, 20 for new random ones.
function is_access_id($value)
{
    return is_string($value) && preg_match('/^[a-f0-9]{13,32}$/', $value) === 1;
}

function new_access_id()
{
    do {
        $accessid = bin2hex(random_bytes(10));
    } while (file_exists(data_path($accessid)));
    return $accessid;
}

function admin_password_matches($value)
{
    $password = getenv('ADMIN_PASSWORD');
    return is_string($password) && $password !== '' && is_string($value) && hash_equals($password, $value);
}

function is_admin()
{
    return !empty($_SESSION['admin']);
}

function deny()
{
    http_response_code(403);
    echo 'error';
    exit;
}

function require_admin()
{
    if (!is_admin()) {
        deny();
    }
}

// Returns the booking id once the session (admin or the booking's customer) may act on it.
function require_booking($value)
{
    if (!is_access_id($value) || !is_dir(data_path($value))) {
        deny();
    }
    if (!is_admin() && ($_SESSION['accessid'] ?? null) !== $value) {
        deny();
    }
    return $value;
}

function valid_email($value)
{
    return is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
}
