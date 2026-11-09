<?php
    require __DIR__ . '/lib/common.php';
    start_session();

    $status = $_POST["status"] ?? null;
    $accessid = require_booking($_POST["accessid"] ?? null);

    if ($status == "update_payment_option") {
        $option = $_POST["option"] ?? "";

        $index = read_json($accessid.'/info.json');
        $index["payment_type"] = is_string($option) ? $option : "";
        write_json($accessid.'/info.json', $index);
    } else if ($status == "update_cart") {
        $cart = $_POST["cart"] ?? [];

        $index = read_json($accessid.'/info.json');
        $index["cart"] = json_encode($cart);
        write_json($accessid.'/info.json', $index);

        echo $index["cart"];
    } else if ($status == "get_cart") {
        $index = read_json($accessid.'/info.json');

        echo json_encode($index["cart"] ?? "[]");
    } else if ($status == 'no_editing' || $status == 'etransfer_editing') {
        $index = read_json($accessid.'/info.json');

        // Both choices are offered only once the preview pictures are ready (status 4).
        if (($index["Status"] ?? null) != 4) {
            deny();
        }

        if ($status == 'no_editing') {
            $bookings = read_json('bookings.json');
            $bookings[$accessid]["Status"] = 6;
            write_json('bookings.json', $bookings);

            $index["Status"] = 6;
            $index["Photo_editing"] = "false";
        } else {
            $index["Photo_editing"] = "true";
        }
        write_json($accessid.'/info.json', $index);

        echo "success";
    }

?>