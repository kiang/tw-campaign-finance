<?php

$path = dirname(dirname(__DIR__));

$oh = fopen($path . '/pdf2jpg.csv', 'r');
fgetcsv($oh, 512); // skip first line
/*
 * 
  Array
  (
  [0] => id
  [1] => 檔名
  [2] => 頁數
  [3] => 網址
  [4] => 圖寬
  [5] => 圖高
  )
 */
$pages = array();
while ($line = fgetcsv($oh, 1024)) {
    if (!isset($pages[$line[1]])) {
        $pages[$line[1]] = array();
    }
    $pages[$line[1]][$line[2]] = $line[0];
}
fclose($oh);

$oh = fopen($path . '/g0v-tw-cf.csv', 'r');
fgetcsv($oh, 512); // skip first line
/*
 * 
  Array
  (
  [0] => 文件名稱
  [1] => 頁碼
  [2] => 序號 , row - 1
  [3] => 交易日期 , row - 2
  [4] => 收支科目 , row - 3
  [5] => 捐贈者/支出對象 , row - 4
  [6] => 身份證/統一編 , row - 5
  [7] => 收入金額 , row - 6
  [8] => 支出金額 , row - 7
  [9] => 金錢類 , row - 8
  [10] => 地址 , row - 9
  [11] => id
  [12] => row
  )
 */
$cellText = array();
$toRemoved = array();
while ($line = fgetcsv($oh, 1024)) {
    $pageNumber = str_pad($line[1] + 1, 4, '0', STR_PAD_LEFT);
    if (!isset($pages[$line[0]][$pageNumber])) {
        print_r($line);
        exit();
    } else {
        $toRemoved[$line[0]] = 1;
        $pageId = $pages[$line[0]][$pageNumber];
        if(!isset($line[12])) {
            print_r($line); exit();
        }
        if(!isset($cellText[$pageId])) {
            $cellText[$pageId] = array();
        }
        $cellText[$pageId]["{$pageId}-{$line[12]}-1"] = $line[2];
        $cellText[$pageId]["{$pageId}-{$line[12]}-2"] = $line[3];
        $cellText[$pageId]["{$pageId}-{$line[12]}-3"] = $line[4];
        $cellText[$pageId]["{$pageId}-{$line[12]}-4"] = $line[5];
        $cellText[$pageId]["{$pageId}-{$line[12]}-5"] = $line[6];
        $cellText[$pageId]["{$pageId}-{$line[12]}-6"] = $line[7];
        $cellText[$pageId]["{$pageId}-{$line[12]}-7"] = $line[8];
        $cellText[$pageId]["{$pageId}-{$line[12]}-8"] = $line[9];
        $cellText[$pageId]["{$pageId}-{$line[12]}-9"] = $line[10];
    }
}
fclose($oh);

foreach($cellText AS $pageId => $pageData) {
    file_put_contents($path . '/pdf/cells-text/' . $pageId . '.json', json_encode($pageData));
}

foreach(array_keys($toRemoved) AS $doc) {
    unset($pages[$doc]);
}

print_r(array_keys($pages));