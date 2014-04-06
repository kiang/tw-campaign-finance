<?php

$path = dirname(dirname(__DIR__));

$imgPath = $path . '/pdf/img_review';

$oh = fopen($path . '/pdf/pdf2jpg.csv', 'r');
fgetcsv($oh, 512); // skip first line
$oFileStack = array();
while ($oFile = fgetcsv($oh, 512)) {
    /*
     * $oFile -> id,檔名,頁數,網址,圖寬,圖高
     */
    $oFileStack[$oFile[0]] = $oFile;
}
fclose($oh);

if (!file_exists($path . '/pdf/t/9_cellCount_189')) {
    $cellCountStack = array(
        'no' => array(),
    );
    foreach ($oFileStack AS $oFile) {
        /*
         * $oFile -> id,檔名,頁數,網址,圖寬,圖高
         */
        if (!file_exists($path . '/pdf/cells/' . $oFile[0] . '.json')) {
            $cellCountStack['no'][$oFile[0]] = $path . '/pdf/cells/' . $oFile[0] . '.json';
            continue;
        }
        $oJson = json_decode(file_get_contents($path . '/pdf/cells/' . $oFile[0] . '.json'));
        if (!isset($cellCountStack[$oJson->cellCount])) {
            $cellCountStack[$oJson->cellCount] = array(
                $oFile[0] => $path . '/pdf/cells/' . $oFile[0] . '.json',
            );
        } else {
            $cellCountStack[$oJson->cellCount][$oFile[0]] = $path . '/pdf/cells/' . $oFile[0] . '.json';
        }
    }

    foreach ($cellCountStack AS $cellCount => $oJson) {
        $fh = fopen($path . '/pdf/t/9_cellCount_' . $cellCount, 'w');
        foreach ($oJson AS $id => $jsonPath) {
            fputs($fh, "{$id}\t{$jsonPath}\n");
        }
        fclose($fh);
    }
} else {
    $cellCountStack = array();
    foreach (glob($path . '/pdf/t/9_cellCount_*') AS $file) {
        $fh = fopen($file, 'r');
        $cellCount = substr($file, strrpos($file, '_') + 1);
        $cellCountStack[$cellCount] = array();
        while ($line = fgetcsv($fh, 1024, "\t")) {
            $cellCountStack[$cellCount][$line[0]] = $line[1];
        }
        fclose($fh);
    }
}