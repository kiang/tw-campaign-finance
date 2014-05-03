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
$fh = fopen($path . '/pdf/pdf2jpg.csv', 'w');
fputcsv($fh, array('id', '檔名', '頁數', '網址', '圖寬', '圖高'));
$fileId = 0;
foreach (glob($path . '/pdf/*/*/*.pdf') AS $file) {
    $pdfPath = substr($file, $pathLength + 5);
    $fileToken = md5($pdfPath);
    $file = addslashes($file);
    $file = str_replace(array(' ', '(', ')'), array('\\ ', '\\(', '\\)'), $file);
    if (!file_exists("{$pdfImgPath}/{$fileToken}-0001.jpg")) {
        error_log("Extracting images from {$file}");
        exec("gs -dNOPAUSE -dNumRenderingThreads=4 -sDEVICE=jpeg -sOutputFile={$fileToken}-%04d.jpg -dJPEGQ=90 -r300x300 -q {$file} -c quit");
        foreach (glob("{$path}/{$fileToken}-*") AS $jpg) {
            $dashPos = strrpos($jpg, '-');
            $dotPos = strpos($jpg, '.', $dashPos);
            $pageNumber = substr($jpg, $dashPos + 1, $dotPos - $dashPos - 1);
            //copy($jpg, "{$pdfImgPath}/{$fileToken}-{$pageNumber}.jpg");
            exec("convert -morphology thicken '1x3>:1,0,1' -normalize -gaussian-blur 1x3 -threshold 60% {$jpg} {$jpg}");
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
            if(substr($jpg, -6) === '_l.jpg') continue;
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
fclose($fh);
