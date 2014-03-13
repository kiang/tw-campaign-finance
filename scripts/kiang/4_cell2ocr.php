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
    $oJsonFile = "{$path}/cells/{$oFile[0]}.json";
    if (!file_exists($oJsonFile)) {
        die("{$oJsonFile} not exist!\n");
    }
    $oJson = json_decode(file_get_contents($oJsonFile));
    if (file_exists("{$path}/text/{$oJson->image_id}.json")) {
        continue;
    }
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

    if (false !== $img) {
        $translated = array();
        foreach ($oJson->cells AS $x => $line) {
            foreach ($line AS $y => $cell) {
                $cell = (array) $cell;
                $croppedImg = imagecrop($img, $cell);
                switch ($y) {
                    case 1:
                    case 2:
                    case 5:
                    case 6:
                    case 7:
                        $lang = 'eng';
                        break;
                    default:
                        $lang = 'chi_tra';
                }


                if (false !== $croppedImg) {
                    switch ($fileType) {
                        case 'jpg':
                            imagejpeg($croppedImg, "{$path}/scripts/good.jpg");
                            exec("/usr/bin/tesseract {$path}/scripts/good.jpg /tmp/u -l {$lang}");
                            break;
                        case 'png':
                            imagepng($croppedImg, "{$path}/scripts/good.png");
                            exec("/usr/bin/tesseract {$path}/scripts/good.png /tmp/u -l {$lang}");
                            break;
                    }
                    if (filesize("/tmp/u.txt") > 0) {
                        $text = trim(str_replace(array("\n", ' '), array('', ''), file_get_contents("/tmp/u.txt")));
                        if (!empty($text)) {
                            $translated[$cell['id']] = $text;
                            //file_put_contents("{$path}/text/{$cell['id']}.txt", $text);
                            //copy("{$path}/scripts/good.png", "{$path}/text/{$cell['id']}.png");
                        }
                    }
                    unlink('/tmp/u.txt');
                    unset($croppedImg);
                }
            }
        }
        if (!empty($translated)) {
            file_put_contents("{$path}/text/{$oJson->image_id}.json", json_encode($translated));
        }
    }
}