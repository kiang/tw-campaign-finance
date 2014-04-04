<?php

$path = dirname(dirname(__DIR__));
$imgPath = $path . '/pdf/img_review';

$oh = fopen($path . '/pdf/pdf2jpg.csv', 'r');
if (!file_exists($imgPath)) {
    mkdir($imgPath, 0777, true);
}
fgetcsv($oh, 512); //skip first line
while ($oFile = fgetcsv($oh, 512)) {
    /*
     * $oFile -> id,檔名,頁數,網址,圖寬,圖高
     */
    $oJsonFile = "{$path}/pdf/cells/{$oFile[0]}.json";
    if (!file_exists($oJsonFile)) {
        copy($oFile[3], $imgPath . '/' . $oFile[0] . '.jpg');
    } else {
        $oJson = json_decode(file_get_contents($oJsonFile));

        $img = imagecreatefromjpeg($path . '/pdf/' . str_replace('.jpg', '_l.jpg', $oJson->url));
        $color = imagecolorallocatealpha($img, 255, 0, 0, 70);

        foreach ($oJson->cells AS $line) {
            foreach ($line AS $cell) {
                imagefilledrectangle($img, $cell->x, $cell->y, $cell->x + $cell->width, $cell->y + $cell->height, $color);
            }
        }

        imagejpeg($img, $imgPath . '/' . $oFile[0] . '.jpg');
    }
}