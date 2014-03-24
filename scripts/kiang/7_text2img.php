<?php
$path = dirname(dirname(__DIR__));

$fh = fopen($path . '/text-position/2.csv', 'r');
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

$cells = json_decode(file_get_contents($path . '/cells/2.json'));

$xRatio = $targetWidth / $cells->width;
$yRatio = $targetHeight / $cells->height;

$im = new Imagick();
$im->newImage($targetWidth, $targetHeight, 'white', 'png');

$draw = new ImagickDraw();

$draw->setStrokeColor('black');
$draw->setFillColor('white');
$draw->setStrokeWidth(1);
foreach ($cells->cells AS $line) {
    foreach ($line AS $cell) {
        $x1 = $cell->x * $xRatio;
        $y1 = $cell->y * $yRatio;
        $x2 = ($cell->x + $cell->width) * $xRatio;
        $y2 = ($cell->y + $cell->height) * $yRatio;
        $draw->polyline(array(
            array(
                'x' => $x1,
                'y' => $y1,
            ), array(
                'x' => $x2,
                'y' => $y1,
            ), array(
                'x' => $x2,
                'y' => $y2,
            ), array(
                'x' => $x1,
                'y' => $y2,
            ),
        ));
    }
}

$draw->setStrokeColor('red');
$draw->setStrokeWidth(2);
rewind($fh);
while ($line = fgetcsv($fh, 512)) {
    $draw->line($line[1], $line[2], $line[1] + 2, $line[2]);
}

$im->drawImage($draw);

$im->writeImage('test.png');
fclose($fh);
