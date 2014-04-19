<?php
    set_time_limit(600);

    include_once("./_config/config.php");

    //清單檔案
    //cancel $path_list = "./../output2.csv";
    $path_list = "./../pdf2jpg.csv";
    //圖片基礎路徑
    $path_imgurl = "http://203.69.90.98/tw-campaign-finance/";
    //canvas檔案
    //cancel $path_canvas = "./../outputs2/";
    $path_canvas = "./../lines/";
    //OCR檔案
    $path_ocr = "./../cells-text/";
    //開啟清單檔案
    $fp = fopen($path_list , "r");


/* 
CREATE TABLE IF NOT EXISTS `pocr_filestatus` (
  `filSno` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主鍵',
  `filName` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '檔名',
  `filPage` int(10) unsigned DEFAULT '0' COMMENT '頁數',
  `filRows` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '總列數',
  `filCols` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '單列總欄數',
  `filImage` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '圖檔位置',
  `filImagewidth` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '圖片寬度',
  `filImageheight` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '圖片高度',
  `filCanvas` text COLLATE utf8_unicode_ci COMMENT 'Canvas',
  `filStatusedit` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '編輯狀態{''0'':''禁止編輯'',''1'':''可編輯'',''7'':''編輯中''}',
  `filStatusocr` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '分析狀態{''0'':''待驗證'',''1'':''驗證完成''}',
  `filSessionid` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '編輯中',
  `filTimeedit` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新時間',
  PRIMARY KEY (`filSno`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='檔案清單' AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `pocr_filefields` (
  `fidSno` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主鍵',
  `filSno` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '檔案主鍵',
  `fidRow` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '列',
  `fidCol` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '欄',
  `fidValue` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '欄位值',
  `fidStatus` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '狀態 {''0'':''未校對'',''1'':''已校對'',''7'':''待驗證''}',
  `fidTimeedit` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新時間',
  PRIMARY KEY (`fidSno`),
  KEY `filSno` (`filSno`,`fidRow`,`fidCol`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='欄位值' AUTO_INCREMENT=1 ;
*/

    //寫入資料庫
    $i = 0;
    while ($data = fgetcsv($fp, 1000, ",")){
        //pass line 1
        if($i == 0){ $i++; continue; }
        //定義變數名稱
        list($filSno, $filName, $filPage, $filImage, $filImagewidth, $filImageheight) = $data;
        $filImage = $path_imgurl.$filImage; //絕對路徑

        //讀取 canvas 資料
        $filCanvas = file_get_contents($path_canvas.$filSno.".json");
        $canvas = json_decode($filCanvas);
        $filRows = count($canvas->cross_points[0]);
        $filCols = count($canvas->cross_points);

        //寫入資料庫
        $sql = " insert into ".__table_prefix."filestatus set 
        filSno = '".$filSno."' ,
        filName = '".$filName."' ,
        filPage = '".$filPage."' ,
        filRows = '".$filRows."' ,
        filCols = '".$filCols."' ,        
        filImage = '".$filImage."' ,
        filImagewidth = '".$filImagewidth."' ,
        filImageheight = '".$filImageheight."' ,
        filCanvas = '".$filCanvas."' ";
        myquery($sql);

        //讀取 ocr 檔案
        $ocrs = file_get_contents($path_ocr.$filSno.".json");
        $ocrs = json_decode($ocrs);
        foreach ($ocrs as $key => $ocr) {
            $keys = explode("-", $key);
            list($filSno, $fidRow, $fidCol) = $keys;
            $fidValue = returnpost($ocr);

            //寫入資料庫
            $sql = " insert into ".__table_prefix."filefields set 
            filSno = '".$filSno."' ,
            fidRow = '".$fidRow."' ,
            fidCol = '".$fidCol."' ,
            fidValue = '".$fidValue."' ";
            myquery($sql);

        }
    }

    fclose($fp);



    echo "done";

?>