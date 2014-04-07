<?php
    include_once("./_config/config.php");

    

    //清單檔案
    $path_list = "./../output2.csv";
    //canvas檔案
    $path_canvas = "./../outputs2/";
    //OCR檔案
    $path_ocr = "./../cells-text/";

    //開啟清單檔案
    $fp = fopen($path_list , "r");
    
    //寫入資料庫
    $i = 0;
    while ($data = fgetcsv($fp, 1000, ",")){
        if($i == 0){ $i++; continue; }
        //定義變數名稱
        list($filSno, $filName, $filPage, $filImage, $filImagewidth, $filImageheight) = $data;

        //讀取 canvas 資料
        $filCanvas = file_get_contents($path_canvas.$filSno.".json");

        //寫入資料庫
        $sql = " insert into ".__table_prefix."filestatus set 
        filSno = '".$filSno."' ,
        filName = '".$filName."' ,
        filPage = '".$filPage."' ,
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



   


?>