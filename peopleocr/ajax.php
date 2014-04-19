<?php
    include_once("./_config/config.php");
    $rep = returnpost($_POST);

    if($_POST['act'] == "save_field"){
        $re = array();
        $fileds = $rep['data']['data'][0];
        foreach ($fileds as $key => $filed) {
            //print_r($filed);
            $filSno = $rep['id'];
            $fidRow = $filed[0] * 1 + 1;
            $fidCol = $filed[1] * 1 + 1;
            $fidValue = $filed[3];

            //先判段有沒有值
            $sql = "select count(fidSno) from ".__table_prefix."filefields 
            where 
            filSno = '".$filSno."' and 
            fidRow = '".$fidRow."' and 
            fidCol = '".$fidCol."' ";
            $has = mysql_result (myquery($sql), 0);

            if($has){
                $sql = "update ".__table_prefix."filefields set 
                fidValue = '".$fidValue."' 
                where 
                filSno = '".$filSno."' and 
                fidRow = '".$fidRow."' and 
                fidCol = '".$fidCol."' ";
            }else{  
                $sql = "insert into ".__table_prefix."filefields set 
                filSno = '".$filSno."' , 
                fidRow = '".$fidRow."' , 
                fidCol = '".$fidCol."' , 
                fidValue = '".$fidValue."' ";
            }

            $re['sql'][] = $sql;

            myquery($sql);

        }
        echo json_encode($re);
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