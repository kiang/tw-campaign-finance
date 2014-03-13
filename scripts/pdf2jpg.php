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
$path = dirname(dirname(__FILE__));
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
            exec("mv {$jpg} {$pathinfo['dirname']}/");
        }
    }
}
