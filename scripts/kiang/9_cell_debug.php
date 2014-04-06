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

$matches = array(
    0 => array(
        -10 => array(
            '349' => '4', '351' => '4', '648' => '8',
        ),
        0 => array(
            '279' => '2', '283' => '2', '291' => '2', '303' => '2', '347' => '2', '353' => '2', '357' => '2', '363' => '2', '365' => '2', '381' => '2', '468' => '2', '610' => '2', '786' => '2', '814' => '2', '2967' => '2', '264' => '42',
            '1' => '3', '34' => '3', '231' => '3', '302' => '3', '304' => '3', '309' => '3', '354' => '3', '356' => '3', '360' => '3', '405' => '3', '413' => '3', '477' => '3', '398' => '444',
            '299' => '4', '301' => '4',
            '298' => '5',
        ),
        10 => array(
            '278' => '5', '350' => '5', '3244' => '5', '297' => '150', '319' => '150', '633' => '3',
        ),
        20 => array(
            '323' => '49',
        ),
    ),
    10 => array(
        -10 => array(
            '711' => '4',
        ),
        0 => array(
            '409' => '5', '411' => '5', '545' => '5', '257' => '2', '2046' => '2', '404' => '2', '346' => '3', '285' => '4',
        ),
        10 => array(
            '2062' => '2', '2066' => '2', '2022' => '4', '2044' => '4', '2052' => '4', '2072' => '4', '2084' => '4', '2196' => '4', '401' => '5', '407' => '5', '308' => '225', '294' => '2875',
        ),
    ),
    20 => array(
        -20 => array(
            '634' => '2', '837' => '4', '2938' => '4',
        ),
        -10 => array(
            '157' => '2',
        ),
        10 => array(
            '273' => '2',
        ),
        20 => array(
            '322' => '121',
        ),
        30 => array(
            '410' => '2',
        ),
        50 => array(
            '43' => '240',
        ),
    ),
    30 => array(
        -20 => array(
            '655' => '2',
        ),
        -10 => array(
            '82' => '2', '436' => '2', '3026' => '2',
        ),
        0 => array(
            '402' => '2', '893' => '2', '917' => '2', '943' => '2',
        ),
        10 => array(
            '412' => '2', '1994' => '2', '1964' => '2', '1966' => '2', '1968' => '2', '1974' => '2', '1976' => '2',
        ),
        20 => array(
            '2020' => '2',
        ),
        40 => array(
            '327' => '2',
        ),
    ),
    40 => array(
        -20 => array(
            '306' => '2', '321' => '2', '2569' => '2', '2667' => '2', '3076' => '2', '3240' => '2',
        ),
        -10 => array(
            '1015' => '2',
        ),
        10 => array(
            '1962' => '2',
        ),
        20 => array(
            '2026' => '2',
        ),
        50 => array(
            '400' => '42',
        ),
    ),
    50 => array(
        -20 => array(
            '284' => '2',
        ),
        -10 => array(
            '949' => '2', '965' => '2',
        ),
        20 => array(
            '1961' => '2', '2035' => '2', '2036' => '2', '2042' => '2', '2053' => '2', '2063' => '2', '2065' => '2', '2217' => '2', '1028' => '2',
        ),
        30 => array(
            '1963' => '2', '1965' => '2', '1971' => '2', '1979' => '2', '1981' => '2', '1985' => '2', '1989' => '2', '1995' => '2', '1997' => '2', '1999' => '2', '2003' => '2', '2005' => '2', '2009' => '2', '2019' => '2', '2027' => '2', '2031' => '2', '2067' => '2', '2073' => '2', '2079' => '2', '2083' => '2', '2159' => '2', '2177' => '2', '2189' => '2', '2193' => '2', '2241' => '2', '2245' => '2', '2247' => '2', '2283' => '2', '2295' => '2', '2299' => '2', '902' => '2', '924' => '2', '942' => '2',
        ),
        40 => array(
            '335' => '2',
        ),
    ),
    60 => array(
        20 => array(
            '328' => '2', '866' => '2', '886' => '2', '972' => '2',
        ),
        30 => array(
            '850' => '2',
        ),
        40 => array(
            '325' => '2',
        ),
    ),
    70 => array(
        -10 => array(
            '332' => '2', '334' => '2',
        ),
        40 => array(
            '331' => '2',
        ),
    ),
);

foreach ($matches AS $xMod => $xStack) {
    foreach ($xStack AS $yMod => $yStack) {
        foreach ($yStack AS $imageId => $cellReference) {
            $oJson = json_decode(file_get_contents($path . '/pdf/cells/' . $cellReference . '.json'));
            foreach ($oJson->cells AS $line) {
                foreach ($line AS $cell) {
                    $cell->id = (string) $cell->id;
                    $cell->id = $imageId . substr($cell->id, strpos($cell->id, '-'));
                    $cell->x += $xMod;
                    $cell->y += $yMod;
                }
            }

            if (file_exists($path . '/pdf/cells/' . $imageId . '.json')) {
                $imageObj = json_decode(file_get_contents($path . '/pdf/cells/' . $imageId . '.json'));
                $imageObj->cells = $oJson->cells;
            } else {
                $imageObj = new stdClass();
                $oFile = $oFileStack[$imageId];
                $imageObj->image_id = $imageId;
                $imageObj->document = $oFile[1];
                $imageObj->page_no = $oFile[2];
                $imageObj->url = $oFile[3];
                $imageObj->width = $oFile[4];
                $imageObj->height = $oFile[5];
                $imageObj->cells = $oJson->cells;
                $imageObj->cellCount = 189;
            }
            file_put_contents($path . '/pdf/cells/' . $imageId . '.json', json_encode($imageObj));
        }
    }
}

exit();

foreach ($matches AS $xMod => $xStack) {
    foreach ($xStack AS $yMod => $yStack) {
        foreach ($yStack AS $imageId => $cellReference) {
            echo "processing - {$imageId}\n";
            $oJson = json_decode(file_get_contents($path . '/pdf/cells/' . $cellReference . '.json'));

            $img = imagecreatefromjpeg($path . '/pdf/' . str_replace('.jpg', '_l.jpg', $oFileStack[$imageId][3]));
            $colorA = imagecolorallocatealpha($img, 155, 0, 0, 70);
            $colorB = imagecolorallocatealpha($img, 0, 155, 0, 70);
            $colorC = imagecolorallocatealpha($img, 0, 0, 155, 70);
            $cellCount = 0;

            foreach ($oJson->cells AS $line) {
                foreach ($line AS $cell) {
                    $cell->x += $xMod;
                    $cell->y += $yMod;
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

            imagejpeg($img, $imgPath . '/' . $cellReference . '_' . $imageId . '.jpg');
        }
    }
}

/*
 * 
 */

$bugList = '1961:189,1962:189,1963:189,1964:189,264:45,279:189,304:189,308:36,363:189,468:189,2283:189,334:189,297:99,2062:189,360:189,2005:189,2967:189,309:189,322:144,331:189,610:189,648:189,655:189,943:189,965:189,972:189,398:108,814:189,411:189,294:153,1997:189,2036:189,2042:189,2044:189,2066:189,2084:189,786:189,837:189,886:189,917:189,1965:189,1966:189,1968:189,1971:189,1974:189,1976:189,1979:189,1981:189,1989:189,1994:189,1995:189,1999:189,2003:189,2009:189,2019:189,2020:189,2022:189,2026:189,2035:189,2046:189,2052:189,2053:189,2063:189,2065:189,2067:189,2072:189,2073:189,2083:189,2196:189,2667:189,278:189,298:189,3240:189,381:189,633:189,634:189,346:189,43:27,401:189,402:189,405:189,283:189,323:36,327:189,350:189,400:45,303:189,353:189,412:189,436:189,413:189,2079:189,82:189,301:189,332:189,1985:189,2159:189,2241:189,157:189,273:189,328:189,2177:189,2189:189,2217:189,2295:189,2299:189,319:99,321:189,325:189,2031:189,2193:189,2247:189,347:189,351:189,409:189,302:189,335:189,354:189,850:189,893:189,902:189,924:189,942:189,949:189,1015:189,3244:189,284:189,356:189,407:189,1:189,34:189,231:189,257:189,285:189,291:189,299:189,306:189,349:189,357:189,365:189,404:189,410:189,477:189,545:189,711:189,866:189,1028:189,2027:189,2245:189,2569:189,2938:189,3026:189,3076:189';
$bugs = explode(',', $bugList);
$testingCells = array();
$currentIndex = 0;
$matches = array();
foreach ($bugs AS $bug) {
    // [0] = image id, [1] = cell count
    $bug = explode(':', $bug);

    if (isset($matches[$bug[0]]))
        continue;

    if (!isset($testingCells[$bug[1]])) {
        $cellPack = $cellCountStack[$bug[1]];
        if ($currentIndex > 0) {
            for ($i = 0; $i < $currentIndex; $i++) {
                next($cellPack);
            }
        }
        $id = key($cellPack);
        if (empty($id))
            continue;
        $testingCells[$bug[1]] = array(
            'id' => $id,
//            'oFile' => $oFileStack[$id],
            'oJsonFile' => $cellPack[$id],
        );
    }

    echo "processing - {$bug[0]}\n";
    $oJson = json_decode(file_get_contents($testingCells[$bug[1]]['oJsonFile']));

    if (!file_exists($path . '/pdf/' . $oJson->url)) {
        echo $path . '/pdf/' . $oJson->url;
        exit();
    }

    $img = imagecreatefromjpeg($path . '/pdf/' . str_replace('.jpg', '_l.jpg', $oFileStack[$bug[0]][3]));
    $colorA = imagecolorallocatealpha($img, 155, 0, 0, 70);
    $colorB = imagecolorallocatealpha($img, 0, 155, 0, 70);
    $colorC = imagecolorallocatealpha($img, 0, 0, 155, 70);
    $cellCount = 0;

    foreach ($oJson->cells AS $line) {
        foreach ($line AS $cell) {
            $cell->x += 30;
            $cell->y += 20;
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

    imagejpeg($img, $imgPath . '/' . $testingCells[$bug[1]]['id'] . '_' . $bug[0] . '.jpg');
}

exit();

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
 * 
 * 
 * 
 */

foreach ($cellCountStack[168] AS $pageId => $pageJsonFile) {
    copy($path . '/pdf/' . $oFileStack[$pageId][3], $imgPath . '/' . $pageId . '.jpg');
}


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
 */

$keys = array_keys($cellCountStack);
sort($keys);
echo implode(', ', $keys) . "\n\n";
foreach ($keys AS $k => $v) {
    if ($v % 9 === 0) {
        unset($keys[$k]);
    }
}
echo implode(', ', $keys) . "\n\n";
