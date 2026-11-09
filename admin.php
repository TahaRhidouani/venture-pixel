<?php
    require __DIR__ . '/lib/common.php';
    start_session();

    if (is_admin()) {
        if(isSet($_POST['status']) && $_POST['status'] == 'confirm_booking'){
            $accessid = require_booking($_POST['accessid'] ?? null);

            $index = read_json('bookings.json');
            $index[$accessid]["Status"] = 2;
            write_json('bookings.json', $index);

            $index = read_json($accessid.'/info.json');
            $index["Status"] = 2;
            write_json($accessid.'/info.json', $index);

            $text_data = json_decode(file_get_contents('texts/'.$language.'.json'), true);

            $subject = $text_data["booking_available"];
            $message = '<!DOCTYPE html> <html lang="en"> <head> <meta charset="UTF-8"> <meta http-equiv="X-UA-Compatible" content="IE=edge"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title></title> <style> @font-face { font-family: Circular; src: url("../assets/CircularStd.ttf") format("truetype"), url("../assets/CircularStd.woff") format("woff"); } body { background-color: #d8d8d8; } h1 { font-family: "Circular"; color: #2d2d2d; } h2 { font-family: "Circular"; font-weight: lighter; font-size: 1em; color: #2d2d2d; } h3 { font-family: "Circular"; font-weight: lighter; font-size: 1em; color: #2d2d2d; width: 100%; text-align: center; } #main { position: relative; width: 500px; max-width: 80%; height: auto; padding: 50px 25px; margin: 100px auto; background-color: white; border-radius: 10px; box-shadow: 0px 5px 30px rgba(0, 0, 0, 0.15); } #circle_logo { position: absolute; top: -40px; left: 30px; } #logo { width: 200px; display: block; margin: 40px auto; } </style> </head> <body> <div id="main"> <img id="circle_logo" src="assets/icons/circular_logo.png"> <h1>'.$text_data["booking_available"].'</h1><h2>'.$text_data["booking_available"].'</h2> </div> <img id="logo" src="assets/icons/logo.png"> <h3>© Copyright 2022 Venture Pixel</h3> </body> </html>';
        
            mail($email, $subject, $message);

        } else if (isSet($_POST['status']) && ($_POST['status'] == 'reject_booking' || $_POST['status'] == 'delete_booking')) {
            $accessid = require_booking($_POST['accessid'] ?? null);

            $index = read_json('bookings.json');
            unset($index[$accessid]);
            write_json('bookings.json', $index);

            $text_data = json_decode(file_get_contents('texts/'.$language.'.json'), true);
            Delete(data_path($accessid));

            $subject = $text_data["booking_unavailable"];
            $message = '<!DOCTYPE html> <html lang="en"> <head> <meta charset="UTF-8"> <meta http-equiv="X-UA-Compatible" content="IE=edge"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title></title> <style> @font-face { font-family: Circular; src: url("../assets/CircularStd.ttf") format("truetype"), url("../assets/CircularStd.woff") format("woff"); } body { background-color: #d8d8d8; } h1 { font-family: "Circular"; color: #2d2d2d; } h2 { font-family: "Circular"; font-weight: lighter; font-size: 1em; color: #2d2d2d; } h3 { font-family: "Circular"; font-weight: lighter; font-size: 1em; color: #2d2d2d; width: 100%; text-align: center; } #main { position: relative; width: 500px; max-width: 80%; height: auto; padding: 50px 25px; margin: 100px auto; background-color: white; border-radius: 10px; box-shadow: 0px 5px 30px rgba(0, 0, 0, 0.15); } #circle_logo { position: absolute; top: -40px; left: 30px; } #logo { width: 200px; display: block; margin: 40px auto; } </style> </head> <body> <div id="main"> <img id="circle_logo" src="assets/icons/circular_logo.png"> <h1>'.$text_data["booking_unavailable"].'</h1><h2>'.$text_data["booking_unavailable_desc"].'</h2> </div> <img id="logo" src="assets/icons/logo.png"> <h3>© Copyright 2022 Venture Pixel</h3> </body> </html>';
        
            mail($email, $subject, $message);

        } else if (isSet($_POST['status']) && $_POST['status'] == 'confirm_paid') {
            $accessid = require_booking($_POST['accessid'] ?? null);

            $index = read_json('bookings.json');
            $index[$accessid]["Status"] = 3;
            write_json('bookings.json', $index);

            $index = read_json($accessid.'/info.json');
            $index["Status"] = 3;
            write_json($accessid.'/info.json', $index);

            
            $text_data = json_decode(file_get_contents('texts/'.$language.'.json'), true);

            $subject = $text_data["booking_paid"];
            $message = '<!DOCTYPE html> <html lang="en"> <head> <meta charset="UTF-8"> <meta http-equiv="X-UA-Compatible" content="IE=edge"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title></title> <style> @font-face { font-family: Circular; src: url("../assets/CircularStd.ttf") format("truetype"), url("../assets/CircularStd.woff") format("woff"); } body { background-color: #d8d8d8; } h1 { font-family: "Circular"; color: #2d2d2d; } h2 { font-family: "Circular"; font-weight: lighter; font-size: 1em; color: #2d2d2d; } h3 { font-family: "Circular"; font-weight: lighter; font-size: 1em; color: #2d2d2d; width: 100%; text-align: center; } #main { position: relative; width: 500px; max-width: 80%; height: auto; padding: 50px 25px; margin: 100px auto; background-color: white; border-radius: 10px; box-shadow: 0px 5px 30px rgba(0, 0, 0, 0.15); } #circle_logo { position: absolute; top: -40px; left: 30px; } #logo { width: 200px; display: block; margin: 40px auto; } </style> </head> <body> <div id="main"> <img id="circle_logo" src="assets/icons/circular_logo.png"> <h1>'.$text_data["booking_paid"].'</h1><h2>'.$text_data["booking_paid_desc"].'</h2> </div> <img id="logo" src="assets/icons/logo.png"> <h3>© Copyright 2022 Venture Pixel</h3> </body> </html>';
        
            mail($email, $subject, $message);
        } else if (isSet($_POST['status']) && $_POST['status'] == 'pictures_ready') {
            $accessid = require_booking($_POST['accessid'] ?? null);

            $index = read_json('bookings.json');
            $index[$accessid]["Status"] = 4;
            write_json('bookings.json', $index);

            $index = read_json($accessid.'/info.json');
            $index["Status"] = 4;
            write_json($accessid.'/info.json', $index);

            echo json_encode($index);

            
            $text_data = json_decode(file_get_contents('texts/'.$language.'.json'), true);

            $subject = $text_data["images_ready"];
            $message = '<!DOCTYPE html> <html lang="en"> <head> <meta charset="UTF-8"> <meta http-equiv="X-UA-Compatible" content="IE=edge"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title></title> <style> @font-face { font-family: Circular; src: url("../assets/CircularStd.ttf") format("truetype"), url("../assets/CircularStd.woff") format("woff"); } body { background-color: #d8d8d8; } h1 { font-family: "Circular"; color: #2d2d2d; } h2 { font-family: "Circular"; font-weight: lighter; font-size: 1em; color: #2d2d2d; } h3 { font-family: "Circular"; font-weight: lighter; font-size: 1em; color: #2d2d2d; width: 100%; text-align: center; } #main { position: relative; width: 500px; max-width: 80%; height: auto; padding: 50px 25px; margin: 100px auto; background-color: white; border-radius: 10px; box-shadow: 0px 5px 30px rgba(0, 0, 0, 0.15); } #circle_logo { position: absolute; top: -40px; left: 30px; } #logo { width: 200px; display: block; margin: 40px auto; } </style> </head> <body> <div id="main"> <img id="circle_logo" src="assets/icons/circular_logo.png"> <h1>'.$text_data["images_ready"].'</h1><h2>'.$text_data["images_ready_desc_digital"].'</h2> </div> <img id="logo" src="assets/icons/logo.png"> <h3>© Copyright 2022 Venture Pixel</h3> </body> </html>';
        
            mail($email, $subject, $message);
        } else if (isSet($_POST['status']) && $_POST['status'] == 'edited_pictures_ready') {
            $accessid = require_booking($_POST['accessid'] ?? null);

            $index = read_json('bookings.json');
            $index[$accessid]["Status"] = 6;
            write_json('bookings.json', $index);

            $index = read_json($accessid.'/info.json');
            $index["Status"] = 6;
            write_json($accessid.'/info.json', $index);

            echo json_encode($index);

            
            $text_data = json_decode(file_get_contents('texts/'.$language.'.json'), true);

            $subject = $text_data["images_ready"];
            $message = '<!DOCTYPE html> <html lang="en"> <head> <meta charset="UTF-8"> <meta http-equiv="X-UA-Compatible" content="IE=edge"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title></title> <style> @font-face { font-family: Circular; src: url("../assets/CircularStd.ttf") format("truetype"), url("../assets/CircularStd.woff") format("woff"); } body { background-color: #d8d8d8; } h1 { font-family: "Circular"; color: #2d2d2d; } h2 { font-family: "Circular"; font-weight: lighter; font-size: 1em; color: #2d2d2d; } h3 { font-family: "Circular"; font-weight: lighter; font-size: 1em; color: #2d2d2d; width: 100%; text-align: center; } #main { position: relative; width: 500px; max-width: 80%; height: auto; padding: 50px 25px; margin: 100px auto; background-color: white; border-radius: 10px; box-shadow: 0px 5px 30px rgba(0, 0, 0, 0.15); } #circle_logo { position: absolute; top: -40px; left: 30px; } #logo { width: 200px; display: block; margin: 40px auto; } </style> </head> <body> <div id="main"> <img id="circle_logo" src="assets/icons/circular_logo.png"> <h1>'.$text_data["images_ready"].'</h1><h2>'.$text_data["images_ready_desc_digital"].'</h2> </div> <img id="logo" src="assets/icons/logo.png"> <h3>© Copyright 2022 Venture Pixel</h3> </body> </html>';
        
            mail($email, $subject, $message);
        } else if (isSet($_POST['status']) && $_POST['status'] == 'not_new') {
            $orderid = filter_var($_POST['orderid'], FILTER_SANITIZE_STRING);

            $order = read_json('order.json');

            $order[$orderid]["new"] = false;
            write_json('order.json', $order);
        } else if (isSet($_POST['status']) && $_POST['status'] == 'confirm_order_paid') {
            $orderid = filter_var($_POST['orderid'], FILTER_SANITIZE_STRING);

            $order = read_json('order.json');

            $order[$orderid]["paid"] = true;
            write_json('order.json', $order);

            echo json_encode($order);
        } else if (isSet($_POST['status']) && $_POST['status'] == 'fulfill_order') {
            $orderid = filter_var($_POST['orderid'], FILTER_SANITIZE_STRING);

            $order = read_json('order.json');

            unset($order[$orderid]);

            write_json('order.json', $order);
        }
    } else {
        deny();
    }

    function Delete($path) {
        if (is_dir($path) === true) {
            $files = array_diff(scandir($path), array('.', '..'));

            foreach ($files as $file) {
                Delete(realpath($path) . '/' . $file);
            }

            return rmdir($path);
        }

        else if (is_file($path) === true) {
            return unlink($path);
        }

        return false;
    }
?>