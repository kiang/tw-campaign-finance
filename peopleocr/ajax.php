<?php
    include_once("./_config/config.php");
    $rep = returnpost($_POST);

   if($_POST['act'] == "save_field"){
        $data = $rep['data']['data'][0];
        if(isset($data[0]) and $data[1]){
            $filSno = $rep['id'];
            $fidRow = $data[0] * 1 + 1;
            $fidCol = $data[1] * 1 + 1;
            $fidValue = $data[3];

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