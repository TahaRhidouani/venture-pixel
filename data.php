<?php
// Serves stored booking files (requests to data/...) to the admin or to the booking's customer.
require __DIR__ . '/lib/common.php';
start_session();
session_write_close();

$path = is_string($_GET['path'] ?? null) ? $_GET['path'] : '';

function send_file($file, $type)
{
    if (!is_file($file)) {
        http_response_code(404);
        exit;
    }
    header('Content-Type: ' . $type);
    header('Cache-Control: private, no-cache');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
}

if (preg_match('#^/data/(bookings|order)\.json$#', $path, $match)) {
    require_admin();
    header('Content-Type: application/json');
    header('Cache-Control: private, no-cache');
    echo json_encode(read_json($match[1] . '.json') ?: new stdClass());
    exit;
}

if (preg_match('#^/data/([a-f0-9]{13,32})/info\.json$#', $path, $match)) {
    $accessid = require_booking($match[1]);
    send_file(data_path($accessid . '/info.json'), 'application/json');
}

if (preg_match('#^/data/([a-f0-9]{13,32})/(preview|pictures)/([0-9]{1,3})\.jpg$#', $path, $match)) {
    $accessid = require_booking($match[1]);
    // Customers get the full pictures only once they are released (status 6).
    if ($match[2] === 'pictures' && !is_admin() && (int) (read_json($accessid . '/info.json')['Status'] ?? 0) < 6) {
        deny();
    }
    $file = data_path($accessid . '/' . $match[2] . '/' . $match[3] . '.jpg');
    send_file($file, @getimagesize($file)['mime'] ?? 'image/jpeg');
}

http_response_code(404);
