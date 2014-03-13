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
$fh = fopen($path . '/pdf/pdf2jpg.csv', 'w');
fputcsv($fh, array('id','檔名','頁數','網址','圖寬','圖高'));
$fileId = 1;
foreach (glob($path . '/pdf/*/*/*.pdf') AS $file) {
    $pathinfo = pathinfo($file);
    $file = addslashes($file);
    $file = str_replace(array(' ', '(', ')'), array('\\ ', '\\(', '\\)'), $file);
    $pathinfo['filename'] = str_replace(array(' ', '(', ')'), array('-', '', ''), $pathinfo['filename']);
    $firstTargetFile = "{$pathinfo['dirname']}/{$pathinfo['filename']}-1.jpg";
    if (!file_exists($firstTargetFile)) {
        exec("gs -dNOPAUSE -sDEVICE=jpeg -sOutputFile={$pathinfo['filename']}-%d.jpg -dJPEGQ=100 -r300x300 -q {$file} -c quit");
        foreach (glob($path . "/{$pathinfo['filename']}-*") AS $jpg) {
            exec("convert {$jpg} -morphology thicken '1x3>:1,0,1' {$jpg}");
            exec("convert {$jpg} -morphology thicken '1x3>:1,0,1' {$jpg}");
            $size = getimagesize($jpg);
            exec("mv {$jpg} {$pathinfo['dirname']}/");
            $dashPos = strrpos($jpg, '-');
            $dotPos = strpos($jpg, '.', $dashPos);
            $pageNumber = substr($jpg, $dashPos + 1, $dotPos - $dashPos - 1);
            fputcsv($fh, array($fileId++,substr($file, 48),$pageNumber,$jpg,$size[0],$size[1]));
        }
    } else {
        foreach (glob("{$pathinfo['dirname']}/{$pathinfo['filename']}-*.jpg") AS $jpg) {
            $size = getimagesize($jpg);
            $dashPos = strrpos($jpg, '-');
            $dotPos = strpos($jpg, '.', $dashPos);
            $pageNumber = substr($jpg, $dashPos + 1, $dotPos - $dashPos - 1);
            fputcsv($fh, array($fileId++,substr($file, 48),$pageNumber,$jpg,$size[0],$size[1]));
        }
    }
}
fclose($fh);