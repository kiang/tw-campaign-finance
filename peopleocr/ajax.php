<?php
    include_once("./_config/config.php");
    $rep = returnpost($_POST);

   if($_POST['act'] == "save_field"){
        $fileds = $rep['data']['data'][0];
        foreach ($fileds as $key => $filed) {
            print_r($filed);
            $filSno = $rep['id'];
            $fidRow = $filed[0] * 1 + 1;
            $fidCol = $filed[1] * 1 + 1;
            $fidValue = $filed[3];

            $sql = "update ".__table_prefix."filefields set 
            fidValue = '".$fidValue."' 
            where 
            filSno = '".$filSno."' and 
            fidRow = '".$fidRow."' and 
            fidCol = '".$fidCol."' ";

            myquery($sql);
        }
    }










?>