<?php

$path = dirname(dirname(__DIR__));

/*
 * get id from outputs.csv
 * 
 * $fileRefs[ pdf file name ][ page number ] = record id
 */
$lastIdNo = 0;
$fh = fopen($path . '/output.csv', 'r');
$fileRefs = array();
while ($line = fgetcsv($fh, 512)) {
    if (!isset($fileRefs[$line[1]])) {
        $fileRefs[$line[1]] = array();
    }
    $line[2] = intval($line[2]);
    $fileRefs[$line[1]][$line[2]] = $line[0];
    $lastIdNo = $line[0];
}
fclose($fh);

foreach (glob($path . '/pdf/documents-export-2014-03-18/*/*/*.pdf') AS $file) {
    $fileName = substr($file, 76);
    $execFile = str_replace(array('(', ')', ' '), array('\\(', '\\)', '\\ '), $file);
    $info = array();
    exec("/usr/bin/pdfinfo {$execFile}", $info);
    $pageCount = false;
    foreach ($info AS $meta) {
        if (false === $pageCount && false !== strpos($meta, 'Pages:')) {
            $pageCount = intval(substr($meta, 6));
        }
    }
    if (!isset($fileRefs[$fileName])) {
        $fileRefs[$fileName] = array();
        $fh = fopen($path . '/output.csv', 'a');
        for ($page = 1; $page <= $pageCount; $page++) {
            fputcsv($fh, array(
                ++$lastIdNo,
                $fileName,
                $page - 1,
                'none',
                0,
                0
            ));
            $fileRefs[$fileName][$page - 1] = $lastIdNo;
        }
        fclose($fh);
    }
    for ($page = 1; $page <= $pageCount; $page++) {
        $pageInFile = $page - 1;
        if (!isset($fileRefs[$fileName][$pageInFile])) {
            $fh = fopen($path . '/output.csv', 'a');
            fputcsv($fh, array(
                ++$lastIdNo,
                $fileName,
                $pageInFile,
                'none',
                0,
                0
            ));
            $fileRefs[$fileName][$pageInFile] = $lastIdNo;
            fclose($fh);
        }
        $endPage = $page + 1;
        exec("java -cp /usr/share/java/commons-logging.jar:/usr/share/java/fontbox.jar:/usr/share/java/pdfbox.jar org.apache.pdfbox.PDFBox ExtractText -startPage {$page} -endPage {$endPage} {$file} tmp.txt");
        copy('tmp.txt', $path . "/text/{$fileRefs[$fileName][$pageInFile]}.txt");
    }
}