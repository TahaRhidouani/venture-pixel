<?php
    require __DIR__ . '/lib/common.php';
    include(__DIR__ . '/lib/stripe-php-7.92.0/init.php');
    start_session();

    if (!is_admin() && empty($_SESSION['accessid'])) {
      deny();
    }

    \Stripe\Stripe::setApiKey(getenv('STRIPE_SECRET_KEY') ?: '');

    $status = $_POST["status"] ?? null;
    $price_data = json_decode(file_get_contents('texts/en.json'), true);

    // Prices come from texts/en.json; the posted cart only supplies sizes and quantities.
    function cart_total($cart, $price_data) {
      $sizes = ["4x6" => 0, "5x7" => 1, "8x10" => 2, "8.5x11" => 3];
      $total = 0;

      foreach (is_array($cart) ? $cart : [] as $value) {
        $size = is_array($value) ? ($value["size"] ?? null) : null;
        $quantity = is_array($value) ? (int) ($value["quantity"] ?? 0) : 0;
        if (is_string($size) && isset($sizes[$size]) && $quantity > 0) {
          $total += $price_data["photography_prices"]["printing"][$sizes[$size]] * $quantity;
        }
      }

      return $total;
    }

    function cents($total) {
      return (int) round($total * 100);
    }

    function payment_intent($clientid) {
      if (!is_string($clientid) || !preg_match('/^pi_\w+$/', $clientid)) {
        deny();
      }
      return \Stripe\PaymentIntent::retrieve($clientid);
    }

    if ($status == "cart_intent_request") {
      $total = cart_total($_POST["cart"] ?? [], $price_data);

      $intent = \Stripe\PaymentIntent::create([
        'amount' => cents($total),
        'currency' => 'cad',
      ]);

      echo json_encode($intent);
    } else if ($status == "booking_intent_request") {
      $accessid = require_booking($_POST["accessid"] ?? null);

      $booking_data = read_json($accessid.'/info.json');

      $total = floatval($booking_data["Total"]);
      $total += ($booking_data["Photo_editing"] == "true" ? $price_data["photography_prices"]["addon"][0] : 0);

      $intent = \Stripe\PaymentIntent::create([
        'amount' => cents($total),
        'currency' => 'cad',
        'metadata' => ['accessid' => $accessid],
      ]);

      echo json_encode($intent);
    } else if ($status == "editing_intent_request") {
      $accessid = require_booking($_POST["accessid"] ?? null);

      $total = floatval($price_data["photography_prices"]["addon"][0]);

      $intent = \Stripe\PaymentIntent::create([
        'amount' => cents($total),
        'currency' => 'cad',
        'metadata' => ['accessid' => $accessid],
      ]);

      echo json_encode($intent);
    } else if ($status == "cart_payment_complete"){
      $name = $_POST["name"];
      $email = $_POST["email"];
      $address = $_POST["address"];
      $accessid = require_booking($_POST["accessid"] ?? null);
      $payment_type = $_POST["payment_type"] ?? null;
      $cart = $_POST["cart"] ?? [];

      if (!in_array($payment_type, ["card", "e-transfer", "cash"], true)) {
        deny();
      }

      if ($payment_type == "card" && $status == "cart_payment_complete") {
        $intent = payment_intent($_POST["client_id"] ?? null);
      }

      $total = cart_total($cart, $price_data);

      if ($payment_type != "card" || ($intent -> status == "succeeded" && $intent -> amount == cents($total))) {
        $order = read_json('order.json');

        do {
          $order_number = abs(crc32(uniqid()));
        } while (array_key_exists($order_number, $order));

        $order[$order_number] = array("access_id" => $accessid, "name" => $name, "email" => $email, "address" => $address, "cart" => $cart, "paid" => ($payment_type == "card" ? true : false), "payment_type" => $payment_type, "new" => true);
        write_json('order.json', $order);
        
        $text_data = json_decode(file_get_contents('texts/'.$language.'.json'), true);

        $subject = $text_data["order_sent"];
        $message = '<!DOCTYPE html> <html lang="en"> <head> <meta charset="UTF-8"> <meta http-equiv="X-UA-Compatible" content="IE=edge"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title></title> <style> @font-face { font-family: Circular; src: url("../assets/CircularStd.ttf") format("truetype"), url("../assets/CircularStd.woff") format("woff"); } body { background-color: #d8d8d8; } h1 { font-family: "Circular"; color: #2d2d2d; } h2 { font-family: "Circular"; font-weight: lighter; font-size: 1em; color: #2d2d2d; } h3 { font-family: "Circular"; font-weight: lighter; font-size: 1em; color: #2d2d2d; width: 100%; text-align: center; } #main { position: relative; width: 500px; max-width: 80%; height: auto; padding: 50px 25px; margin: 100px auto; background-color: white; border-radius: 10px; box-shadow: 0px 5px 30px rgba(0, 0, 0, 0.15); } #circle_logo { position: absolute; top: -40px; left: 30px; } #logo { width: 200px; display: block; margin: 40px auto; } </style> </head> <body> <div id="main"> <img id="circle_logo" src="assets/icons/circular_logo.png"> <h1>'.$text_data["order_sent"].'</h1><h2>'.$text_data["expect_shipping"].'</h2> </div> <img id="logo" src="assets/icons/logo.png"> <h3>© Copyright 2022 Venture Pixel</h3> </body> </html>';
    
        if (valid_email($email)) {
          mail($email, $subject, $message);
        }


        echo $order_number;

      } else if ($intent -> amount != cents($total)) {
        echo "error";
      }
    } else if ($status == "booking_payment_complete"){
      $accessid = require_booking($_POST["accessid"] ?? null);

      $intent = payment_intent($_POST["client_id"] ?? null);
      $booking_data = read_json($accessid.'/info.json');
      $total = floatval($booking_data["Total"]);
      $total += ($booking_data["Photo_editing"] == "true" ? $price_data["photography_prices"]["addon"][0] : 0);

      // The intent must be one created for this booking, for its full price.
      if ($intent -> status == "succeeded" && $intent -> amount == cents($total) && ($intent -> metadata["accessid"] ?? null) === $accessid) {

        $index = read_json($accessid.'/info.json');
        $index["Status"] = 3;
        write_json($accessid.'/info.json', $index);

        echo "success";
      } else {
        echo "error";
      }
    } else if ($status == "editing_payment_complete"){
      $accessid = require_booking($_POST["accessid"] ?? null);

      $intent = payment_intent($_POST["client_id"] ?? null);
      $total = floatval($price_data["photography_prices"]["addon"][0]);

      if ($intent -> status == "succeeded" && $intent -> amount == cents($total) && ($intent -> metadata["accessid"] ?? null) === $accessid) {

        $index = read_json('bookings.json');
        $index[$accessid]["Status"] = 5;
        write_json('bookings.json', $index);

        $index = read_json($accessid.'/info.json');
        $index["Status"] = 5;
        $index["Photo_editing"] = "true";
        write_json($accessid.'/info.json', $index);

        echo "success";
      } else {
        echo "error";
      }
    }

?>