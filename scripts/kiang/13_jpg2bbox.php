<?php

$path = dirname(dirname(__DIR__));
$bboxPath = "{$path}/pdf/bbox";
$imgBasePath = "{$path}/pdf/";

if (!file_exists($bboxPath)) {
    mkdir($bboxPath, 0777, true);
}

$bboxKey = "<span class='ocr_line'";

$oh = fopen($path . '/pdf/pdf2jpg.csv', 'r');
fgetcsv($oh, 512); //skip first line
while ($oFile = fgetcsv($oh, 512)) {
    /*
     * $oFile -> id,檔名,頁數,網址,圖寬,圖高
     */
    $imgPath = "{$imgBasePath}{$oFile[3]}";
    exec("/usr/bin/tesseract {$imgPath} {$bboxPath}/{$oFile[0]} -l chi_tra hocr");
    if (file_exists("{$bboxPath}/{$oFile[0]}.hocr")) {

        $content = file_get_contents("{$bboxPath}/{$oFile[0]}.hocr");
        $bboxStack = array();

        $lineOffset = strpos($content, $bboxKey);
        $lx = $oFile[4];
        $rx = 0;
        while (false !== $lineOffset) {
            $bboxOffset = strpos($content, 'bbox ', $lineOffset) + 5;
            $bboxEnd = strpos($content, '"', $bboxOffset);
            //bbox x0 y0 x1 y1 , ref http://en.wikipedia.org/wiki/HOCR
            $bbox = explode(' ', substr($content, $bboxOffset, $bboxEnd - $bboxOffset));
            $bbox[3] = intval($bbox[3]);
            $bboxStack[] = $bbox;
            if($lx > $bbox[0]) {
                $lx = $bbox[0];
            }
            if($rx < $bbox[2]) {
                $rx = $bbox[2];
            }
            $lineOffset = strpos($content, $bboxKey, $bboxEnd);
        }
        
        foreach ($bboxStack AS $key => $bbox) {
            //$bboxStack[$key][0] = $lx;
            //$bboxStack[$key][2] = $rx;
        }

        $img = imagecreatefromjpeg($imgPath);
        $colorA = imagecolorallocatealpha($img, 155, 0, 0, 70);
        $colorB = imagecolorallocatealpha($img, 0, 0, 155, 70);
        foreach ($bboxStack AS $bbox) {
            if (false !== $img) {
                imagefilledrectangle($img, $bbox[0], $bbox[1], $bbox[2], $bbox[3], $colorB);
                imagefilledrectangle($img, $bbox[0] + 5, $bbox[1] + 5, $bbox[2] - 5, $bbox[3] - 5, $colorA);
            }
        }
        imagejpeg($img, "{$bboxPath}/{$oFile[0]}.jpg");
    }
}