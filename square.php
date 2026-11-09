<?php
require __DIR__ . '/lib/common.php';

// Square signs each webhook: base64 HMAC-SHA256 of the notification URL followed by the raw body.
$body = file_get_contents("php://input");
$signature_key = getenv('SQUARE_WEBHOOK_SIGNATURE_KEY');
$notification_url = getenv('SQUARE_WEBHOOK_URL');
$signature = $_SERVER['HTTP_X_SQUARE_HMACSHA256_SIGNATURE'] ?? '';

if (!$signature_key || !$notification_url || !hash_equals(base64_encode(hash_hmac('sha256', $notification_url . $body, $signature_key, true)), $signature)) {
    deny();
}

$data = json_decode($body);

if (is_object($data) && ($data->type ?? null) === "booking.created") {
    
    $text_data = json_decode(file_get_contents('texts/'.$language.'.json'), true);
    
    // TODO
    $id = $data->data->object->booking->customer_id;
    $plan = ($data->data->object->booking->appointment_segments->service_variation_id == "" ? 0 : ($data->data->object->booking->appointment_segments->service_variation_id == "KMDGTRR7Q7UL2GKTQC6GHIFW" ? 1 : 2)); // GET IDs
    $total = $text_data["photography_prices"]["packages"][$plan];
    $pictures = $plan == 0 ? 15 : ($plan == 1 ? 20 : 35);
    $location = "";
    $date = "";
    $time = "";
    $comment = $data->data->object->booking->customer_note;
    $customMessage = $language_data["default_message"][0].$language_data["default_message"][1];
    
    $language = "en";
    $event = "";
    
    
    if (!is_string($id) || !preg_match('/^[A-Za-z0-9]+$/', $id)) {
        exit;
    }

    $url = "https://connect.squareup.com/v2/customers/".$id;
    
    $curl = curl_init($url);
    
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
    $headers = array(
       "Square-Version: 2021-10-20",
       "Authorization: Bearer ".getenv('SQUARE_ACCESS_TOKEN'),
       "Content-Type: application/json",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    
    $resp = json_decode(curl_exec($curl));
    curl_close($curl);
    
    $name = $resp->customer->given_name." ".$resp->customer->family_name;
    $email = $resp->customer->email_address;
    $phone = $resp->customer->phone_number;
    
    $accessid = new_access_id();

    mkdir(data_path($accessid.'/pictures'), 0770, true);
    mkdir(data_path($accessid.'/preview'), 0770, true);

    $info = array("accessid" => $accessid, "Name" => $name, "Email" => $email, "Phone" => $phone, "Total" => $total, "Pictures" => $pictures, "Current_pictures" => 0, "Photo_editing" => false, "Language" => $language, "Message" => $customMessage, "Event" => $event, "Location" => $location, "Date" => $date, "Time" => $time, "Comment" => $comment, "Status" => 1, "payment_type" => "", "cart" => "[]");

    write_json($accessid.'/info.json', $info, JSON_FORCE_OBJECT);

    $index = read_json('bookings.json');
    $index[$accessid] = array("Name" => $name, "Status" => 1);
    write_json('bookings.json', $index);
    
    $subject = $text_data["booking_received"];
    $message = '<!DOCTYPE html> <html lang="en"> <head> <meta charset="UTF-8"> <meta http-equiv="X-UA-Compatible" content="IE=edge"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title></title> <style> @font-face { font-family: Circular; src: url("../assets/CircularStd.ttf") format("truetype"), url("../assets/CircularStd.woff") format("woff"); } body { background-color: #d8d8d8; } h1 { font-family: "Circular"; color: #2d2d2d; } h2 { font-family: "Circular"; font-weight: lighter; font-size: 1em; color: #2d2d2d; } h3 { font-family: "Circular"; font-weight: lighter; font-size: 1em; color: #2d2d2d; width: 100%; text-align: center; } #main { position: relative; width: 500px; max-width: 80%; height: auto; padding: 50px 25px; margin: 100px auto; background-color: white; border-radius: 10px; box-shadow: 0px 5px 30px rgba(0, 0, 0, 0.15); } #circle_logo { position: absolute; top: -40px; left: 30px; } #logo { width: 200px; display: block; margin: 40px auto; } </style> </head> <body> <div id="main"> <img id="circle_logo" src="assets/icons/circular_logo.png"> <h1>'.$text_data["booking_received"].'</h1><h2>'.$text_data["booking_processed"].$accessid.'</h2> </div> <img id="logo" src="assets/icons/logo.png"> <h3>© Copyright 2022 Venture Pixel</h3> </body> </html>';
    
    if (valid_email($email)) {
        mail($email, $subject, $message);
    }
}

?>

