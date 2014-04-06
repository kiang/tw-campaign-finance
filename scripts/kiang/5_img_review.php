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
        echo "{$oJsonFile}\n";
    } else {
        $oJson = json_decode(file_get_contents($oJsonFile));

        $img = imagecreatefromjpeg($path . '/pdf/' . str_replace('.jpg', '_l.jpg', $oJson->url));
        
        /*
         * 
         * create thumbnails
         * 
        $newwidth = 600;
        $newheight = round($oJson->height * 600 / $oJson->width);
        $thumb = imagecreatetruecolor($newwidth, $newheight);
        imagecopyresized($thumb, $img, 0, 0, 0, 0, $newwidth, $newheight, $oJson->width, $oJson->height);
        
        imagejpeg($thumb, $imgPath . '/' . $oFile[0] . '.jpg');
        continue;
         * 
         */

        $colorA = imagecolorallocatealpha($img, 155, 0, 0, 70);
        $colorB = imagecolorallocatealpha($img, 0, 155, 0, 70);
        $colorC = imagecolorallocatealpha($img, 0, 0, 155, 70);
        $cellCount = 0;

        foreach ($oJson->cells AS $line) {
            foreach ($line AS $cell) {
                switch (++$cellCount % 2) {
                    case 1:
                        imagefilledrectangle($img, $cell->x, $cell->y, $cell->x + $cell->width, $cell->y + $cell->height, $colorA);
                        break;
                    case 2:
                        imagefilledrectangle($img, $cell->x, $cell->y, $cell->x + $cell->width, $cell->y + $cell->height, $colorB);
                        break;
                    case 0:
                        imagefilledrectangle($img, $cell->x, $cell->y, $cell->x + $cell->width, $cell->y + $cell->height, $colorC);
                        break;
                }
            }
        }
        imagejpeg($img, $imgPath . '/' . $oFile[0] . '.jpg');
    }
}