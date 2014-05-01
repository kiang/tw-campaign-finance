<?php

$path = dirname(dirname(__DIR__));
$apiLocalPath = $path . '/pdf/api';

foreach (array('table', 'pic', 'ans', 'ctiml') AS $subFolder) {
    if (!file_exists($apiLocalPath . '/' . $subFolder)) {
        mkdir($apiLocalPath . '/' . $subFolder, 0777, true);
    }
}

$tablesJsonFile = $apiLocalPath . '/tables.json';
$ctimlTablesJsonFile = $apiLocalPath . '/ctiml.json';

if (!file_exists($tablesJsonFile)) {
    file_put_contents($tablesJsonFile, file_get_contents('http://campaign-finance.g0v.ronny.tw/api/gettables'));
}

if (!file_exists($ctimlTablesJsonFile)) {
    file_put_contents($ctimlTablesJsonFile, file_get_contents('http://campaign-finance.g0v.ctiml.tw/api/getdonepages'));
}

$tablesJson = json_decode(file_get_contents($tablesJsonFile), true);
$ctimlJson = json_decode(file_get_contents($ctimlTablesJsonFile), true);

$tableCompleted = array();
foreach ($ctimlJson AS $page) {
    $tableCompleted[$page['id']] = true;
}

foreach ($tablesJson['data'] AS $key => $page) {
    if (isset($tableCompleted[$page['id']])) {
        $tablesJson['data'][$key]['is_completed'] = true;
    } else {
        $tablesJson['data'][$key]['is_completed'] = false;
    }
}

//page,row,col,ans

$fh = fopen($apiLocalPath . '/ocr.csv', 'w');

fputcsv($fh, array('page', 'cells', 'ocr_results', 'hits'));

$countPages = count($tablesJson['data']);
$countPage = 0;
foreach ($tablesJson['data'] AS $page) {
    ++$countPage;
    echo "processing: {$countPage} / {$countPages}\n";
    $qPos = strpos($page['pic_url'], '?');
    if (false !== $qPos) {
        $page['pic_url'] = substr($page['pic_url'], 0, $qPos);
    }
    $tJson = $apiLocalPath . '/table/' . $page['id'] . '.json';
    $tPic = $apiLocalPath . '/pic/' . $page['id'] . substr($page['pic_url'], -4);
    $tAns = $apiLocalPath . '/ans/' . $page['id'] . '.json';
    $ctimlAns = $apiLocalPath . '/ctiml/' . $page['id'] . '.json';
    if (!file_exists($ctimlAns)) {
        file_put_contents($ctimlAns, file_get_contents('http://campaign-finance.g0v.ctiml.tw/api/getcells/' . $page['id']));
    }
    $ctimlAnsArr = json_decode(file_get_contents($ctimlAns), true);

    if (!file_exists($tAns)) {
        if (!file_exists($tJson)) {
            file_put_contents($tJson, file_get_contents($page['tables_api_url']));
        }
        if (!file_exists($tPic)) {
            file_put_contents($tPic, file_get_contents($page['pic_url']));
        }
        if(!file_exists($tPic)) {
            // can't download the pic, just skip it
            continue;
        }
        system("{$path}/bin/CharRecognition {$tPic} {$tJson} {$tAns}");
    }
    
    if(!file_exists($tAns)) {
        // can't get the ans, just skip it
        continue;
    }

    $ansJson = json_decode(file_get_contents($tAns), true);
    $ansStack = array();
    $ocr_results = 0;
    $hits = 0;
    foreach ($ansJson['recognitions'] AS $ans) {
        $ans['result'] = trim($ans['result']);
        if (!empty($ans['result'])) {
            if(!isset($ansStack[$ans['row']])) {
                $ansStack[$ans['row']] = array();
            }
            ++$ocr_results;
            $ansStack[$ans['row']][$ans['column']] = $ans['result'];
        }
    }
    
    foreach($ctimlAnsArr AS $ans) {
        if(isset($ansStack[$ans['row']][$ans['col']]) && $ansStack[$ans['row']][$ans['col']] === $ans['ans']) {
            ++$hits;
        }
    }
    fputcsv($fh, array($page['id'], count($ctimlAnsArr), $ocr_results, $hits));
}

fclose($fh);
