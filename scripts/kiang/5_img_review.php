<?php

$path = dirname(dirname(__DIR__));
$imgPath = $path . '/tmp/img_review';

$oh = fopen($path . '/output2.csv', 'r');
$firstLineSkipped = false;
if (!file_exists($imgPath)) {
    mkdir($imgPath, 0777, true);
}
while ($oFile = fgetcsv($oh, 512)) {
    /*
     * $oFile -> id,檔名,頁數,網址,圖寬,圖高
     */
    if (false === $firstLineSkipped) {
        $firstLineSkipped = true;
        continue;
    }
    $oJsonFile = "{$path}/cells/{$oFile[0]}.json";
    if (!file_exists($oJsonFile)) {
        die("{$oJsonFile} not exist!\n");
    }
    $oJson = json_decode(file_get_contents($oJsonFile));
    $qPos = strrpos($oJson->url, '.');
    $fileType = substr($oJson->url, $qPos + 1, 3);
    $fileCache = $path . '/tmp/' . md5($oJson->url) . '.' . $fileType;
    $fileCacheFolder = substr($fileCache, 0, -32);
    if (!file_exists($fileCacheFolder)) {
        mkdir($fileCacheFolder, 0777, true);
    }
    $fileCache = $fileCacheFolder . '/' . substr($fileCache, -32);
    if (!file_exists($fileCache)) {
        file_put_contents($fileCache, file_get_contents($oJson->url));
        exec("convert {$fileCache} -morphology thicken '1x3>:1,0,1' {$fileCache}");
        exec("convert {$fileCache} -morphology thicken '1x3>:1,0,1' {$fileCache}");
    }
    switch ($fileType) {
        case 'jpg':
            $img = imagecreatefromjpeg($fileCache);
            break;
        case 'png':
            $img = imagecreatefrompng($fileCache);
            break;
        default:
            die(">>> {$oJson->url} <<<;");
    }
    //$imgPath

    $percent = 600 / $oFile[4];
    $newwidth = $oFile[4] * $percent;
    $newheight = $oFile[5] * $percent;

    $thumb = imagecreatetruecolor($newwidth, $newheight);

    imagecopyresized($thumb, $img, 0, 0, 0, 0, $newwidth, $newheight, $oFile[4], $oFile[5]);

    imagejpeg($thumb, $imgPath . '/' . $oFile[0] . '.jpg');
}