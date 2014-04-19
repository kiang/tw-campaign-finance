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
    if($_GET['act'] == "getdata"){
        $data = array();
        $rep = returnpost($_GET);
        if(!empty($rep['id'])){
            $data['sql'] = " select fidRow,fidCol,fidValue from ".__table_prefix."filefields where filSno = '".$rep['id']."' order by fidRow , fidCol ";
            $res = myquery($data['sql']);
            while($row = mysql_fetch_assoc($res)){
                $data[$row['fidRow']][$row['fidCol']] = $row['fidValue'];
            }
        }
        unset($data['sql']);
        echo json_encode($data);
    }










?>