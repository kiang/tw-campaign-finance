<?php

/*
 * download all pdf files into ../pdf/ and then execute this command,
 * then you should expect:
 * 
 * # each pdf file will be converted to multiple jpg file divided based on page
 * # the generated jpg file will have some noise removal effect, not that perfect currently
 * 
 * The way to remove noise is refered to
 * http://www.imagemagick.org/discourse-server/viewtopic.php?f=1&t=18707
 */
$path = dirname(dirname(__DIR__));
$pathLength = strlen($path);
$pdfImgPath = $path . '/pdf/img_orig';
if (!file_exists($pdfImgPath)) {
    mkdir($pdfImgPath, 0777, true);
}

$rotateMap = array();
$rotateMapFile = $path . '/pdf/rotateMap.csv';
if (file_exists($rotateMapFile)) {
    $list = explode("\n", file_get_contents($rotateMapFile));
    foreach ($list AS $item) {
        $item = trim($item);
        if(empty($item)) continue;
        $rotateMap[$item] = true;
    }
}

$fh = fopen($path . '/pdf/pdf2jpg.csv', 'w');
fputcsv($fh, array('id', '檔名', '頁數', '網址', '圖寬', '圖高'));
$fileId = 0;
$pageNumbers = array();
foreach (glob($path . '/pdf/src/*/*.pdf') AS $file) {
    $pdfPath = substr($file, $pathLength + 5);
    $fileToken = md5($pdfPath);
    $file = str_replace(array(' ', '(', ')'), array('\\ ', '\\(', '\\)'), $file);
    if (!file_exists("{$pdfImgPath}/{$fileToken}-0001.jpg")) {
        error_log("Extracting images from {$file}");
        exec("gs -dNOPAUSE -dNumRenderingThreads=4 -sDEVICE=jpeg -sOutputFile={$fileToken}-%04d.jpg -dJPEGQ=90 -r300x300 -q {$file} -c quit");
        foreach (glob("{$path}/{$fileToken}-*") AS $jpg) {
            $dashPos = strrpos($jpg, '-');
            $dotPos = strpos($jpg, '.', $dashPos);
            $pageNumber = substr($jpg, $dashPos + 1, $dotPos - $dashPos - 1);
            //copy($jpg, "{$pdfImgPath}/{$fileToken}-{$pageNumber}.jpg");
            exec("convert -morphology thicken '1x3>:1,0,1' -threshold 70% {$jpg} {$jpg}");
            $size = getimagesize($jpg);
            if ($size[0] < $size[1]) {
                $source = imagecreatefromjpeg($jpg);
                $rotate = imagerotate($source, 270, 0);
                imagejpeg($rotate, $jpg);
                $tmp = $size[1];
                $size[1] = $size[0];
                $size[0] = $tmp;
            }
            exec("mv {$jpg} {$pdfImgPath}/{$fileToken}-{$pageNumber}.jpg");
            fputcsv($fh, array(++$fileId, $pdfPath, $pageNumber, "img_orig/{$fileToken}-{$pageNumber}.jpg", $size[0], $size[1]));
        }
        error_log("Finished extracting images from {$file}");
    } else {
        foreach (glob("{$pdfImgPath}/{$fileToken}-*") AS $jpg) {
            $dashPos = strrpos($jpg, '-');
            $dotPos = strpos($jpg, '.', $dashPos);
            $pageNumber = substr($jpg, $dashPos + 1, $dotPos - $dashPos - 1);
            $size = getimagesize($jpg);
            if ($size[0] < $size[1]) {
                $source = imagecreatefromjpeg($jpg);
                $rotate = imagerotate($source, 270, 0);
                imagejpeg($rotate, $jpg);
                $tmp = $size[1];
                $size[1] = $size[0];
                $size[0] = $tmp;
            }
            fputcsv($fh, array(++$fileId, substr($file, 48), $pageNumber, "img_orig/{$fileToken}-{$pageNumber}.jpg", $size[0], $size[1]));
        }
    }
}
//width = 3525
foreach (glob($path . '/pdf/src/*/*.tif') AS $file) {
    $csvFilePath = substr($file, 48);
    $pageIndex = dirname($file);
    $fileToken = md5($pageIndex);
    if (!isset($pageNumbers[$pageIndex])) {
        $pageNumbers[$pageIndex] = 1;
    } else {
        ++$pageNumbers[$pageIndex];
    }
    $pageNumber = str_pad($pageNumbers[$pageIndex], 4, '0', STR_PAD_LEFT);
    $jpg = "{$path}/pdf/img_orig/{$fileToken}-{$pageNumber}.jpg";
    exec("convert {$file} -resize 3525x3525 -quiet -morphology thicken '1x3>:1,0,1' -threshold 70% {$jpg}");
    $size = getimagesize($jpg);
    if ($size[0] < $size[1]) {
        $source = imagecreatefromjpeg($jpg);
        if (isset($rotateMap[$csvFilePath])) {
            $rotate = imagerotate($source, 90, 0);
        } else {
            $rotate = imagerotate($source, 270, 0);
        }
        imagejpeg($rotate, $jpg);
        $tmp = $size[1];
        $size[1] = $size[0];
        $size[0] = $tmp;
    }
    fputcsv($fh, array(++$fileId, $csvFilePath, $pageNumber, "img_orig/{$fileToken}-{$pageNumber}.jpg", $size[0], $size[1]));
}
//width = 3525
foreach (glob($path . '/pdf/src/*/*.jpg') AS $file) {
    $csvFilePath = substr($file, 48);
    $pageIndex = dirname($file);
    $fileToken = md5($pageIndex);
    if (!isset($pageNumbers[$pageIndex])) {
        $pageNumbers[$pageIndex] = 1;
    } else {
        ++$pageNumbers[$pageIndex];
    }
    $pageNumber = str_pad($pageNumbers[$pageIndex], 4, '0', STR_PAD_LEFT);
    $jpg = "{$path}/pdf/img_orig/{$fileToken}-{$pageNumber}.jpg";
    exec("convert -resize 3525x3525 -quiet -morphology thicken '1x3>:1,0,1' -threshold 70% {$file} {$jpg}");
    $size = getimagesize($jpg);
    if ($size[0] < $size[1]) {
        $source = imagecreatefromjpeg($jpg);
        if (isset($rotateMap[$csvFilePath])) {
            $rotate = imagerotate($source, 90, 0);
        } else {
            $rotate = imagerotate($source, 270, 0);
        }
        imagejpeg($rotate, $jpg);
        $tmp = $size[1];
        $size[1] = $size[0];
        $size[0] = $tmp;
    }
    fputcsv($fh, array(++$fileId, $csvFilePath, $pageNumber, "img_orig/{$fileToken}-{$pageNumber}.jpg", $size[0], $size[1]));
}

fclose($fh);