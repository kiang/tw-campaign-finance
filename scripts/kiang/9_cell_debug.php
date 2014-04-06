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

/*
 * keys in cellCountStack
 * 
 * 0, no, 9, 10, 16, 18, 27, 36, 40, 45, 54, 63, 64, 72, 81, 84, 90, 96, 99, 105, 108, 117, 119, 126, 135, 136, 144, 147, 153, 162, 168, 171, 180, 189
 * 
 * % 9 !== 0
 * 
 * 10, 16, 40, 64, 84, 96, 105, 119, 136, 147, 168
 * 
 * % 9 === 0
 * 
 * 0, no, 9, 18, 27, 36, 45, 54, 63, 72, 81, 90, 99, 108, 117, 126, 135, 144, 153, 162, 171, 180, 189
 * 
  $keys = array_keys($cellCountStack);
  sort($keys);
  echo implode(', ', $keys) . "\n\n";
  foreach($keys AS $k => $v) {
  if($v % 9 === 0) {
  unset($keys[$k]);
  }
  }
  echo implode(', ', $keys) . "\n\n";
 */

/*
 * no: 1961:189, 1962:189, 1963:189, 1964:189
 * 0: 264:45, 279:189, 304:189, 308:36, 363:189, 468:189, 2283:189
 * 10: 334:189
 * 16: 297:99
 * 40: 2062:189
 * 64: 360:189, 2005:189
 * 84: 2967:189, 309:189, 322:144, 331:189, 610:189, 648:189, 655:189, 943:189, 965:189, 972:189
 * 96: 398:108
 * 105: 814:189
 * 119: 411:189
 * 136: 294:153
 * 147: 1997:189, 2036:189, 2042:189, 2044:189, 2066:189, 2084:189, 786:189, 837:189, 886:189, 917:189
 * 168: 1965:189, 1966:189, 1968:189, 1971:189, 1974:189, 1976:189, 1979:189, 1981:189, 1989:189, 1994:189, 1995:189, 1999:189, 2003:189, 2009:189, 2019:189, 2020:189, 2022:189, 2026:189, 2035:189, 2046:189, 2052:189, 2053:189, 2063:189, 2065:189, 2067:189, 2072:189, 2073:189, 2083:189, 2196:189, 2667:189, 278:189, 298:189, 3240:189, 381:189, 633:189, 634:189
 * 
 * rotate 180: 152, 2554
 * 
 * 9: 346:189
 * 18: 43:27, 401:189, 402:189, 405:189
 * 27: 283:189, 323:36, 327:189
 * 36: 350:189, 400:45
 * 45: 303:189, 353:189, 412:189, 436:189
 * 63: 413:189, 2079:189
 * 72: 82:189, 301:189, 332:189, 1985:189, 2159:189, 2241:189
 * 81: 157:189, 273:189, 328:189, 2177:189, 2189:189, 2217:189, 2295:189, 2299:189
 * 90: 319:99, 321:189, 325:189, 2031:189, 2193:189, 2247:189
 * 99: 347:189, 351:189, 409:189
 * 108: 302:189
 * 117: 335:189, 354:189
 * 126: 850:189, 893:189, 902:189, 924:189, 942:189, 949:189, 1015:189, 3244:189
 * 171: 284:189, 356:189, 407:189
 * 189: 1:189, 34:189, 231:189, 257:189, 285:189, 291:189, 299:189, 306:189, 349:189, 357:189, 365:189, 404:189, 410:189, 477:189, 545:189, 711:189, 866:189, 1028:189, 2027:189, 2245:189, 2569:189, 2938:189, 3026:189, 3076:189
 * 
 * 
  foreach ($cellCountStack[168] AS $pageId => $pageJsonFile) {
  copy($path . '/pdf/' . $oFileStack[$pageId][3], $imgPath . '/' . $pageId . '.jpg');
  }
 * 
 */

foreach ($cellCountStack AS $cellCount => $pages) {
    if ($cellCount % 9 !== 0)
        continue;
    switch ($cellCount) {
        case 'no':
        case 0:
        case 189:
            break;
        default:
            foreach ($pages AS $pageId => $pageJsonFile) {
                echo "processing - {$pageId}\n";
                $oJson = json_decode(file_get_contents($pageJsonFile));

                if (!file_exists($path . '/pdf/' . $oJson->url)) {
                    echo $path . '/pdf/' . $oJson->url;
                    exit();
                }

                $img = imagecreatefromjpeg($path . '/pdf/' . str_replace('.jpg', '_l.jpg', $oJson->url));
                $color = imagecolorallocatealpha($img, 255, 0, 0, 70);

                foreach ($oJson->cells AS $line) {
                    foreach ($line AS $cell) {
                        imagefilledrectangle($img, $cell->x, $cell->y, $cell->x + $cell->width, $cell->y + $cell->height, $color);
                    }
                }

                imagejpeg($img, $imgPath . '/' . $cellCount . '_' . $pageId . '.jpg');
            }
    }
}