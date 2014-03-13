<?php

/*
 * As we have had cross points, use this script to convert them to unique cells
 * 
 * Then we could deal with each cell indenpently.
 * 
 * Each cell should has following fields:
 * id -> concat(image-id, x number, y number)
 * x -> x of begin point
 * y -> y of begin point
 * width -> width of the cell
 * height -> height of the cell
 */

$path = dirname(dirname(__DIR__));

$oh = fopen($path . '/output2.csv', 'r');
$firstLineSkipped = false;
while ($oFile = fgetcsv($oh, 512)) {
    /*
     * $oFile -> id,檔名,頁數,網址,圖寬,圖高
     */
    if (false === $firstLineSkipped) {
        $firstLineSkipped = true;
        continue;
    }
    $oJsonFile = "{$path}/outputs2/{$oFile[0]}.json";
    if (!file_exists($oJsonFile)) {
        die("{$oJsonFile} not exist!\n");
    }
    $oJson = json_decode(file_get_contents($oJsonFile));
    $imageObj = new stdClass();
    $imageObj->image_id = $oFile[0];
    $imageObj->document = $oFile[1];
    $imageObj->page_no = $oFile[2];
    $imageObj->url = $oFile[3];
    $imageObj->width = $oFile[4];
    $imageObj->height = $oFile[5];
    $imageObj->cells = array();
    $previousLine = array();
    $numberX = 0;
    foreach ($oJson->cross_points AS $line) {
        if (empty($previousLine)) {
            $previousLine = $line;
            continue;
        }
        ++$numberX;
        $numberY = 0;
        $firstPointSkipped = false;
        foreach ($line AS $key => $point) {
            if (false === $firstPointSkipped) {
                $firstPointSkipped = true;
                continue;
            }
            ++$numberY;
            if (!isset($imageObj->cells[$numberX])) {
                $imageObj->cells[$numberX] = array();
            }
            $imageObj->cells[$numberX][$numberY] = array(
                'id' => "{$oFile[0]}-{$numberX}-{$numberY}",
                'x' => $previousLine[$key - 1][0],
                'y' => $previousLine[$key - 1][1],
                'width' => ($line[$key][0] - $previousLine[$key - 1][0]),
                'height' => ($line[$key][1] - $previousLine[$key - 1][1]),
            );
        }
        $previousLine = $line;
    }
    file_put_contents("{$path}/cells/{$oFile[0]}.json", json_encode($imageObj));
    continue;
    /*
     * to test cropped image
     */
    $img = imagecreatefrompng($imageObj->url);
    if (false !== $img) {
        foreach ($imageObj->cells AS $x => $line) {
            foreach ($line AS $y => $cell) {
                $croppedImg = imagecrop($img, $cell);
                if (false !== $croppedImg) {
                    imagepng($croppedImg, "{$path}/cells/{$cell['id']}.png");
                    unset($croppedImg);
                }
            }
        }
    }
}