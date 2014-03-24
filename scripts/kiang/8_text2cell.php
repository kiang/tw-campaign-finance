<?php

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

    $fh = fopen($path . '/text-position/' . $oFile[0] . '.csv', 'r');
    /*
     * Array
      (
      [0] => 列         text
      [1] => 699.73004  getXDirAdj()
      [2] => 50.789978  getYDirAdj()
      [3] => 8.85       getFontSize()
      [4] => 8.851325   getXScale()
      [5] => 7.7349005  getHeightDir()
      [6] => 2.2125     getWidthOfSpace()
      [7] => 8.159729   getWidthDirAdj()
      )
     */

//a4 = 595 pt x  842 pt

    $targetWidth = 842; //pt
    $targetHeight = 595; //pt

    $cells = json_decode(file_get_contents($path . '/cells/' . $oFile[0] . '.json'));

    $xRatio = $targetWidth / $cells->width;
    $yRatio = $targetHeight / $cells->height;

    $cellPolygons = array();
    foreach ($cells->cells AS $line) {
        foreach ($line AS $cell) {
            $x1 = $cell->x * $xRatio;
            $y1 = $cell->y * $yRatio;
            $x2 = ($cell->x + $cell->width) * $xRatio;
            $y2 = ($cell->y + $cell->height) * $yRatio;
            $cellPolygons[$cell->id] = compact('x1', 'x2', 'y1', 'y2');
        }
    }
    $cellText = array();
    while ($line = fgetcsv($fh, 512)) {
        $textPushed = false;
        foreach ($cellPolygons AS $cellId => $cellPolygon) {
            if (false === $textPushed && $line[1] >= $cellPolygon['x1'] && $line[1] <= $cellPolygon['x2'] && $line[2] >= $cellPolygon['y1'] && $line[2] <= $cellPolygon['y2']) {
                if (!isset($cellText[$cellId])) {
                    $cellText[$cellId] = '';
                }
                $cellText[$cellId] .= $line[0];
                $textPushed = true;
            }
        }
    }
    fclose($fh);
    file_put_contents($path . '/cells-text/' . $oFile[0] . '.json', json_encode($cellText));
}