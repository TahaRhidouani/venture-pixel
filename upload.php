<?php
require __DIR__ . '/lib/common.php';
start_session();
require_admin();

// The admin page posts the pictures under the booking id as the field name.
$accessid = require_booking(key($_FILES));
$files = $_FILES[$accessid];
if (!is_array($files['name'])) {
    echo json_encode("error");
    exit;
}

@mkdir(data_path($accessid.'/preview'), 0770, true);
@mkdir(data_path($accessid.'/pictures'), 0770, true);

for($i = 0; $i < count($files['name']); $i++){
    if($files['error'][$i] === UPLOAD_ERR_OK && is_uploaded_file($files['tmp_name'][$i])){

        $previewfilePath = data_path($accessid."/preview/".$i.".jpg");
        $filePath = data_path($accessid."/pictures/".$i.".jpg");
        $imageType = @getimagesize($files['tmp_name'][$i])[2] ?? null;

        if($imageType === IMAGETYPE_JPEG || $imageType === IMAGETYPE_PNG){
            if(copy($files["tmp_name"][$i], $previewfilePath)){
                copy($files['tmp_name'][$i], $filePath);

                $im = $imageType === IMAGETYPE_PNG ? imagecreatefrompng($previewfilePath) : imagecreatefromjpeg($previewfilePath);
                if ($im === false) {
                    continue;
                }


                $font = dirname(__FILE__).'/assets/CircularStd.ttf';
                $text = 'Venture Pixel';

                $white = imagecolorallocatealpha($im, 255, 255, 255, 25);
                $shadow = imagecolorallocatealpha($im, 0, 0, 0, 95);

                $width = imagesx($im);
                $font_size = $width / 10;

                $height = imagesy($im);
                $centerX = $width / 2;
                $centerY = $height / 2;

                list($left, $bottom, $right, , , $top) = imageftbbox($font_size, 45, $font, $text);

                $left_offset = ($right - $left) / 2;
                $top_offset = ($bottom - $top) / 2;

                $x = $centerX - $left_offset;
                $y = $centerY + $top_offset;

                imagettftext($im, $font_size, 45, $x + 3, $y + 3, $shadow, $font, $text);
                imagettftext($im, $font_size, 45, $x, $y, $white, $font, $text);
            
                imagejpeg($im, $previewfilePath);
                imagedestroy($im);
            }
        }
    }
}

// for($i = 0; $i < count($_FILES[key($_FILES)]['name']); $i++){
//     if(isset($_FILES[key($_FILES)]['name'][$i]) && $_FILES[key($_FILES)]['name'][$i] != ''){

//         $filePath = "data/".key($_FILES)."/pictures/".$i.".jpg"; 
//         $fileType = end(explode(".", $_FILES[key($_FILES)]["name"][$i]));

//         $allowTypes = array('jpg','png','jpeg'); 

//         if(in_array($fileType, $allowTypes)){
//             move_uploaded_file($_FILES[key($_FILES)]['tmp_name'][$i], $filePath);
//         }
//     }
// }

$index = read_json($accessid.'/info.json');
$index["Current_pictures"] = count($files['name']);
write_json($accessid.'/info.json', $index);

echo json_encode($index);

?>