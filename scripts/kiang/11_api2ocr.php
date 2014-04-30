<?php

$path = dirname(dirname(__DIR__));
$apiLocalPath = $path . '/pdf/api';

foreach (array('table', 'pic', 'ans') AS $subFolder) {
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

$fh = fopen($apiLocalPath . '/ans.csv', 'w');

fputcsv($fh, array('page', 'row', 'col', 'ans'));

$countPages = count($tablesJson['data']);
$countPage = 0;
foreach ($tablesJson['data'] AS $page) {
    ++$countPage;
    if (true === $page['is_completed']) {
        continue;
    }
    echo "processing: {$countPage} / {$countPages}\n";
    $tJson = $apiLocalPath . '/table/' . $page['id'] . '.json';
    $tPic = $apiLocalPath . '/pic/' . $page['id'] . substr($page['pic_url'], -4);
    $tAns = $apiLocalPath . '/ans/' . $page['id'] . '.json';
    if (file_exists($tAns)) {
        processAnsFile($fh, $tAns, $page['id']);
        continue;
    }
    if (!file_exists($tJson)) {
        file_put_contents($tJson, file_get_contents($page['tables_api_url']));
    }
    if (!file_exists($tPic)) {
        file_put_contents($tPic, file_get_contents($page['pic_url']));
    }
    system("{$path}/bin/CharRecognition {$tPic} {$tJson} {$tAns}");

    processAnsFile($fh, $tAns, $page['id']);
}

fclose($fh);

function processAnsFile($fh, $tAns, $pageId) {
    if (!file_exists($tAns)) {
        return false;
    }
    $ansJson = json_decode(file_get_contents($tAns), true);
    foreach ($ansJson['recognitions'] AS $ans) {
        $ans['result'] = trim($ans['result']);
        if (!empty($ans['result'])) {
            fputcsv($fh, array($pageId, $ans['row'], $ans['column'], $ans['result']));
        }
    }
}
