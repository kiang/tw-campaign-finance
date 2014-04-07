<?php //中

define("_basename" , basename($_SERVER['PHP_SELF']));
define("_today" , getmk());
define("_today_day" , date("Y-m-d" , _today));


//
function print_j($var,$exit=false){
	echo "<pre>";
	print_r($var);
	echo "</pre>";
	if($exit){ exit; }
}
//記錄admin動作 : 動作 , 管理員 , 主鍵
function w_action($logDo,$logWho,$admSno){
	$logWho = (isset($logWho))?$logWho:$_COOKIE['admin_admId'];
	if(isset($admSno)){ }elseif(isset($_COOKIE['admin_admSno'])){ $admSno = $_COOKIE['admin_admSno']; }else{ $admSno = 0; } $logMk = _today;
	$sql = "insert into oj_adminlog set logMk = '".$logMk."' , logIp = '".$_SERVER['REMOTE_ADDR']."' , admSno = '".$admSno."' , logWho = '".$logWho."' , logDo = '".$logDo."' , logTime = '".date("Y-m-d H:i:s" , $logMk)."' ";
	myquery($sql);
}
//記錄一些動作的處理結果
function w_tmp($tmpKey,$tmpName,$tmpValue){
	$sql = " replace into oj_tmp set 
	tmpKey = '".$tmpKey."' , 
	tmpName = '".$tmpName."' , 
	tmpValue = '".$tmpValue."' ";
	myquery($sql);
}
//記錄 排程歷程 動作
function w_crontab($Log){
	//清空 3 個月內資料
	$sql = "delete from oj_crontablog where crtDatetime < '".date("Y-m-d H:i:s" , (_today - 86400 * 30))."' ";
	myquery($sql);

	$sql = "insert into oj_crontablog set crtFiles = '".$_SERVER['REQUEST_URI']."' , crtLog = '".$Log."' ";
	myquery($sql);
}

//存檔用的文字處理
function savetxtact($txt){ 
	$txt = addcslashes($txt , "\\"); $txt = addcslashes($txt , "\\"); $txt = addcslashes($txt , "'"); $txt = addcslashes($txt , "'");
	return $txt;
}
//查看頁面權限
function checkpower($md , $url){
	global $develop_status;
	if(!$develop_status){ 
		$hasmd = (isset($md) and !is_null($md) and $md != "")?true:false; 
		$hasurl = (isset($url) and !is_null($url))?true:false; 
		if(isset($_COOKIE['admin_login']) and isset($_COOKIE['admin_admSno'])){ 
			if($hasmd){ 
				$admin = mysql_fetch_assoc(myquery("select admPower from oj_admin where admSno = '{$_COOKIE['admin_admSno']}'")); 
				$admPower = explode(",", $admin['admPower']); 
				if(in_array($md , $admPower)){ 
					return true; 
				}else{ 
					if($hasurl){ 
						if($url != ""){ 
							header("location:".$url); 
						}else{ 
							header("location:".__murl."nopower.php"); 
						} 
					}else{ 
						return false; 
					} 
				} 
			}else{ 
				/*因為不知道要檢查什麼,所以不做事*/ 
				return true;
			} 
		}else{ 
			header("location:".__murl."index.php?act=godie"); 
		}
	}else{ 
		return true;
	}
}

//校時
function getmk($H=0 , $i=0 , $s=0 , $m=0 , $d=0 , $Y=0){
	//時差 台灣是 8 
	$othercounty = 8; 
	$gmtime = strtotime(gmdate("Y-m-d H:i:s")); $gy = date("Y",$gmtime); $gm = date("m",$gmtime); $gd = date("d",$gmtime); $gh = date("H",$gmtime); $gi = date("i",$gmtime); $gs = date("s",$gmtime); $retime = mktime($gh+$H+$othercounty , $gi+$i , $gs+$s , $gm+$m , $gd+$d , $gy+$Y);
	return $retime;
}
//整理時間
function format_mydatetime($mydatetime){
	//$mydatetime = array("date"=>"" , "datedef"=>"" , "time"=>"" , "timedef"=>"" , "cannull"=>1 , "strtotime"=>0);
	$str = "";
	if($mydatetime['date'] == ""){
		if($mydatetime['datedef'] == ""){
			
		}else{
			$mydatetime['date'] == $mydatetime['datedef'];
		}
	}
	if($mydatetime['time'] == ""){
		if($mydatetime['timedef'] == ""){
			
		}else{
			$mydatetime['time'] == $mydatetime['timedef'];
		}
	}
	if($mydatetime['date'] != "" and $mydatetime['time'] != ""){
		$str = $mydatetime['date']." ".$mydatetime['time'];
		if($mydatetime['strtotime']){
			$str = strtotime($str);
		}
	}else{
		if($mydatetime['cannull']){
			$str = NULL;
		}
	}
	return $str;
}
//取得資料庫選項
function getdboption($table , $field , $json){
	$sql = "SHOW FULL COLUMNS FROM ".$table;
	$res = myquery($sql);
	while($row = mysql_fetch_assoc($res)){
		if($row['Field'] == $field){
			if(preg_match("/{.*}/" , $row['Comment'] , $match)){
				$option = $match[0];
				if(preg_match("/\'/" , $option)){
					$option = str_replace("'","\"",$option);
				}
				$json = json_decode($option,TRUE);
			}else{
				$json = json_decode(array(),TRUE);
			}
		}
	}
	return $json;
}

//去html < > 和處理衝碼字
function returnpost($post , $nohtml , $noaddsla){
	if(is_array($post)){
		foreach($post as $key => $val){ 
			if(!is_array($val) and !in_array($key , $nohtml)){ 
				$post[$key] = htmlspecialchars($val , ENT_QUOTES);
				if(!get_magic_quotes_gpc()){ 
					$post[$key] = addslashes($post[$key]);
				}
			}elseif(!is_array($val) and in_array($key , $nohtml)){
				if(!get_magic_quotes_gpc()){ 
					$post[$key] = addslashes($post[$key]);
				}
			}elseif(is_array($val)){ 
				$post[$key] = returnpost($val , $nohtml , $noaddsla);
			}  
		}
	}else{
		if(empty($nohtml)){
			$post = htmlspecialchars($post , ENT_QUOTES);
		}
		if(empty($nohtml)){
			$post = addslashes($post);
		}
	}
	
	return $post;
	//舊的沒有用遞回 foreach($post as $key => $val){ if(!is_array($val) and !in_array($key , $nohtml)){ $post[$key] = htmlspecialchars($val , ENT_QUOTES); }elseif(is_array($val)){ foreach($val as $key2 => $val2){ if(!is_array($val2) and !in_array($key2 , $nohtml)){ $post[$key][$key2] = htmlspecialchars($val2 , ENT_QUOTES); }elseif(is_array($val2)){ foreach($val2 as $key3 => $val3){ if(!in_array($key3 , $nohtml)){ $post[$key][$key2][$key3] = htmlspecialchars($val3 , ENT_QUOTES); }else{ /*$post[$key][$key2][$key3] = $val3;*/ } } }else{ /*$post[$key][$key2] = $val2;*/ } } }else{ /*$post[$key] = $val;*/ }  }
	//舊的沒有用遞回 if(!get_magic_quotes_gpc()){ foreach($post as $key => $val){ if(!is_array($val) and !in_array($key , $noaddsla)){ $post[$key] = addslashes($val); }elseif(is_array($val)){ foreach($val as $key2 => $val2){ if(!is_array($val) and !in_array($key , $noaddsla)){ $post[$key][$key2] = addslashes($val2); }elseif(is_array($val2)){ foreach($val2 as $key3 => $val3){ if(!in_array($key3 , $noaddsla)){ $post[$key][$key2][$key3] = addslashes($val3); }else{ /*$post[$key][$key2][$key3] = $val3;*/ } } }else{ /*$post[$key][$key2] = $val2;*/ } } }else{ /*$post[$key] = $val;*/ } } }
}
//處理 get 產生新的字串 : 目前的$_GET , 要殺的只代變數名 array("aaa","bbb") , 要加的變數名+值 array("aaa"=>"111"))
function changeget($get = array() , $del = array() , $add = array()){
	$get_a = array(); $arr = array_merge($get , $add);
	foreach($arr as $key => $val){ if(!in_array($key,$del)){ $get_a[] = "{$key}={$val}"; } }
	if(count($get_a) == 0){ $get_s = ""; }else{ $get_s = "?".implode("&", $get_a); }
	return $get_s;
}
//處理 get 只留特定值 : 目前的$_GET , 要留的只代變數名 array("aaa","bbb")
function filterget($get = array() , $need = array()){
	$get_a = array();
	foreach($get as $key => $val){ if(in_array($key,$need)){ $get_a[] = "{$key}={$val}"; } }
	if(count($get_a) == 0){ $get_s = ""; }else{ $get_s = "?".implode("&", $get_a); }
	return $get_s;
}
//刪掉array為 空的值 : 處理陣列 , 是否刪除 null , 是否刪除空值
function cleararraynull($array , $space = true , $null = true){
	$newarray = array(); 
	foreach($array as $key => $val){ if($val == "" and $space){ continue; } if($val == NULL and $null){ continue; } $newarray[$key] = $val; }
	return $newarray;
}
//後台用的 page bar
function managepage($nowpage , $onepage , $restotal){
	global $myl_list_page_first; global $myl_list_page_prev; global $myl_list_page_next; global $myl_list_page_last; global $myl_list_page_info1; global $myl_list_page_info2; global $myl_list_page_info3; global $myl_list_page_info4;
	$page = array(); $getstr = "";
	if(strtolower($onepage) != "all"){ $strlimit = ($nowpage-1) * $onepage; $firstinfo = ($nowpage-1) * $onepage + 1; $totalpage = ceil($restotal / $onepage); $startpage = floor(($nowpage-1) / 10) * 10 + 1; $lastinfo = $firstinfo + $onepage - 1; $lastinfo = ($restotal < $lastinfo)?$restotal:$lastinfo; $page['limit'] = " limit {$strlimit},{$onepage}"; }else{ $firstinfo = 1; $totalpage = 1; $lastinfo = $restotal; $page['limit'] = ""; } foreach($_GET as $key => $val){ if($key == "page"){ continue; } $getstr .= "{$key}={$val}&"; }
	$geturl = "?".$getstr."page=";
	if($restotal > 0){
		$page['pagebar'] = '<ul class="pagination">';
		$page['pagebar'] .= '<li class="info">'.$myl_list_page_info1.' '.$firstinfo.' '.$myl_list_page_info2.' '.$lastinfo.' '.$myl_list_page_info3.' '.$restotal.' '.$myl_list_page_info4.'</li>';
		$page['pagebar'] .= ($nowpage != 1)?'<li><a href="'.$_SERVER['PHP_SELF'].$geturl.'1" class="button white">'.$myl_list_page_first.'</a></li>':'';
		$page['pagebar'] .= ($nowpage > 1)?'<li><a href="'.$_SERVER['PHP_SELF'].$geturl.($nowpage-1).'" class="button white">'.$myl_list_page_prev.'</a></li>':'';
		for($i=$startpage; $i < $startpage + 10; $i++){
			if($i>$totalpage){ break; }
			$page['pagebar'] .= ($nowpage != $i)?'<li><a href="'.$_SERVER['PHP_SELF'].$geturl.$i.'" class="button white">'.$i.'</a></li>':'<li><a href="javascript:;" class="button white active">'.$i.'</a></li>';
		}
		$page['pagebar'] .= ($nowpage < $totalpage)?'<li><a href="'.$_SERVER['PHP_SELF'].$geturl.($nowpage+1).'" class="button white">'.$myl_list_page_next.'</a></li>':'';
		$page['pagebar'] .= ($nowpage != $totalpage)?'<li><a href="'.$_SERVER['PHP_SELF'].$geturl.$totalpage.'" class="button white">'.$myl_list_page_last.'</a></li>':'';
		$page['pagebar'] .= '</ul>';
	}else{ $page['pagebar'] = ''; }
	return $page;
}
//判斷後台清單的正反序樣式名稱 : 判斷欄位
function is_orderme($who){
	global $dta; 
	if($_SESSION['search'][$dta['model']]['Sorder'] == $who){ 
		if($_SESSION['search'][$dta['model']]['Sascdesc'] == "asc"){ 
			return "orderasc"; 
		}else{ 
			return "orderdesc"; 
		} 
	}
}
//判斷後台清單的正反序樣式名稱 : 判斷欄位
function is_orderweb($who){ 
	global $dta; 
	if($_SESSION['search'][$dta['model']]['Sorder'] == ""){ 
		return "ordersameweb"; 
	}else{ 
		return "orderreset"; 
	}
}
//取得檔案所在資料夾
function getFileFolder($url){
	$filename_array = explode(".", $str);
	if(empty($nameORsub)){
		$re = strtolower($filename_array[count($filename_array)-1]);
		return $re;
	}else{
		unset($filename_array[count($filename_array)-1]);
		$re = implode(".", $filename_array);
		return $re;
	}
}
//將陣列拼在一起但不改變索引
function array_join($arra , $arrb){
	foreach($arrb as $key => $val){
		$arra[$key] = $val;
	}
	return $arra;
}
//確認資料有無重覆 : table名稱 , array("要查的欄位名" => "要查的值") , 1任意符合0全符合 , 沒有重覆 ture , 重覆 false
function checktableinfo($table , $info , $anyone = 1){
	$where = array(); $implode = ($anyone)?" or ":" and ";
	foreach($info as $key => $val){ if($val != ""){ $where[] = " {$key} = '{$val}' "; } }
	if( count($where) > 0){ $where_s = implode($implode , $where); $sql = " select count(*) from ".$table." where ".$where_s." "; $count = mysql_result(myquery($sql) , 0); 
	if($count < 1){ return true; }else{ return false; } }else{ return false; }
}
//抓出分類選項的內容 : 資料表 , 前置 , 主鍵(索引) , 需要欄位 , where , order by
function selectsnoopt($table , $bef , $pk , $field , $where , $order ){
	$opt = array();
	$total_field = count($field);
	$field_s = implode(",", $field);
	$where_s = ($where != "")?" where ".$where:"";
	$order_s = ($order != "")?" order by ".$order:"";
	$res = myquery(" select ".$pk.",".$field_s." from ".$table." ".$where_s." ".$order_s." ");
	while($row = mysql_fetch_assoc($res)){ 
		if($total_field == 1){
			$opt[$row[$pk]] = $row[$field_s];
		}else{
			$opt[$row[$pk]] = $row;
		}
	}
	return 	$opt;
}
//找出執行目錄所在位置
function whereiam(){
	$wheredir = strtolower(dirname($_SERVER['PHP_SELF']));
	//echo "dirname : ".$wheredir."<br />";
	$where = array();
	if(preg_match("/manage/" , $wheredir)){
		$where['webset'] = "./../../upload/webset/";
		$where['upload'] = "./../../upload/";
		$where['root'] = "./../../";
	}elseif(preg_match("/backend/" , $wheredir)){
		$where['webset'] = "./../upload/webset/";
		$where['upload'] = "./../upload/";
		$where['root'] = "./../";
	}else{
		$where['webset'] = "./upload/webset/";
		$where['upload'] = "./upload/";
		$where['root'] = "./";
	}
	return $where;
}
//建立資料夾
function createdir($dirname){
	$mkstatus = mkdir($dirname , "0777");
	chmod($dirname, 0777);

	if($mkstatus){
		return true;
	}else{
		return false;
	}
}
//產生上傳檔案的新檔名
function createfilename($rep , $dta){
	if(count($dta['upfiles']) > 0){
		$randnum = array();
		$source['index'] = $rep['createmore'] + 1;
		$source['act'] = $rep['act'];
		foreach($dta['upfiles'] as $field => $no){
			foreach($rep[$dta['bef'].$field] as $key => $file){
				$file_a = explode(".", $file);
				$file_ext = $file_a[count($file_a)-1];
				$source[$field]['source'][] = $file;
				for($i=0;$i<$source['index'];$i++){
					do{
						$contione = true;
						$newrand = rand(10000,99999);
						if(!in_array($newrand , $randnum)){
							$randnum[] = $newrand;
							$contione = false;
						}
					}while($contione);
					$source[$field][$i][] = _today."_".$newrand.".".$file_ext;
				}
			}
		}
	}
	return $source;
}
//移動上傳的檔案
function uploadormovefile($files , $pkarray , $dta , $old){
	$tmpfolder = __uurl."tmp/";
	$modfolder = $dta['folder'];
	$saveasfromfolder = folder($dta,$old[$dta['pk']]);
	if(!is_dir($modfolder)){ createdir($modfolder); }else{ } //建立功能資料夾
	if($files['index'] > 0 and count($dta['upfiles']) > 0){
		for($i=0;$i<$files['index'];$i++){
			$filefolder = folder($dta,$pkarray[$i]);
			if(!is_dir($filefolder)){ createdir($filefolder); }else{ }  //建立資料資料夾
			foreach($dta['upfiles'] as $field => $uptype){
				foreach($files[$field][$i] as $key => $name){
					$source = $files[$field]['source'][$key];
					foreach($uptype as $type){
						if(is_file($tmpfolder.$type.$source)){
							//echo "如果檔案存在 tmp 中 => copy<br />";
							copy($tmpfolder.$type.$source , $filefolder.$type.$name);
						}elseif(is_file($filefolder.$type.$source)){
							//echo "如果檔案存在 其它資料夾 中 => rename<br />";
							copy($filefolder.$type.$source , $tmpfolder.$type.$source); //先copy到tmp等等可能要給別人用
							rename($filefolder.$type.$source , $filefolder.$type.$name);
						}elseif(is_file($saveasfromfolder.$type.$source)){
							//echo "如果檔案存在 其它資料夾 中 => rename<br />";
							copy($saveasfromfolder.$type.$source , $filefolder.$type.$name);
						}else{
							//echo "找沒有<br />";
						}
					}
				}
			}
		}
	}
	if(is_array($old) and $files['act'] != "add"){
		delrowfile($dta , $old);
	}
	DelTmpPic(); //移除過久的tmp檔案
}
//刪除 update/tmp 中超過 x 秒的檔案
function DelTmpPic($sec){
	$sec = (empty($sec))?(60*60*12):$sec;
	$dir = scandir(__uurl."tmp/"); 
	unset($dir[0]); 
	unset($dir[1]); 
	$now = _today - $sec;
	foreach($dir as $val){ 
		preg_match('/\d{10}/', $val , $filetime); 
		if($filetime[0] < $now){ 
			unlink(__uurl."tmp/".$val); 
		} 
	}
}
function folder($dta,$pk){
	if($dta['subfolder']){
		return $dta['folder'].$pk."/";
	}else{
		return $dta['folder'];
	}
}
//刪除舊檔清單
function delrowfile($dta , $row){
	foreach($dta['upfiles'] as $field => $uptype){
		$filse = explode(";;;", $row[$dta['bef'].$field]);
		foreach($filse as $fils){
			foreach($uptype as $type){
				unlink(folder($dta,$row[$dta['pk']]).$type.$fils);
				//echo folder($dta,$row[$dta['pk']]).$type.$fils."<br />";
			}
		}
	}
	if($dta['subfolder']){
		rmdir(folder($dta,$row[$dta['pk']]));
	}
}
//==============================================================================================================================================================================================
//儲存暫存檔 , sql語句 , 資料表名 不帶 oj_ , array(索引,索引) , 需要的欄位(可空) , 不要的欄位(可空) , 檔名關鍵字(可空) , 資料夾(可空)
function saveWebsetTxt( $sql , $dta , $index , $in_filed , $out_filed , $keyw){
	$websettxt = ""; $keyw = (empty($keyw))?"w":$keyw;
	if(is_array($in_filed) and count($in_filed) > 0){ $check_in = true; }else{ $check_in = false; }
	
	$res = myquery($sql);
	while($row = mysql_fetch_assoc($res)){
		$one_row = array();
		$index_row = array();
		foreach($row as $key => $val){
			$val = savetxtact($val);
			if(($check_in and in_array($key , $in_filed)) or (!$check_in and !in_array($key , $out_filed))){
				$one_row[] = "'{$key}'=>'{$val}'";
			}
		}
		foreach($index as $key => $val){
			if($val == ""){
				$index_row[] = "[]";
			}else{
				$index_row[] = "['".$row[$val]."']";
			}
		}
		$websettxt .= "\$".$dta['model']."_".$keyw."_sql".implode("", $index_row)." = array( ".implode(",", $one_row)." ); \n";
	}
	$websettxt = "<?php //中 \n".$websettxt."?>";
	$fp = fopen(__uurl."webset/".$dta['model']."_".$keyw."_sql.php" , "w");
	$websettxt = stripslashes($websettxt);
	fwrite($fp , $websettxt);
	fclose($fp);
}
//==============================================================================================================================================================================================
//前台用的 page bar
function webpagebar($nowpage , $onepage , $restotal , $mode){
	// global $myl_list_page_first; 
	// global $myl_list_page_prev; 
	// global $myl_list_page_next; 
	// global $myl_list_page_last; 
	// global $myl_list_page_info1; 
	// global $myl_list_page_info2; 
	// global $myl_list_page_info3; 
	// global $myl_list_page_info4;

	$page = array(); $getstr = "";

	if(strtolower($onepage) != "all"){ 
		$strlimit = ($nowpage-1) * $onepage; 
		$firstinfo = ($nowpage-1) * $onepage + 1; 
		$totalpage = ceil($restotal / $onepage); 
		$startpage = floor(($nowpage-1) / 10) * 10 + 1; 
		$lastinfo = $firstinfo + $onepage - 1; 
		$lastinfo = ($restotal < $lastinfo)?$restotal:$lastinfo; 
		$page['limit'] = " limit {$strlimit},{$onepage}"; 
	}else{ 
		$firstinfo = 1; 
		$totalpage = 1; 
		$lastinfo = $restotal; 
		$page['limit'] = ""; 
	} 
	foreach($_GET as $key => $val){ if($key == "page"){ continue; } $getstr .= "{$key}={$val}&"; }

	$geturl = "?".$getstr."page=";
	if($mode == "ajax"){
		if($restotal > 0){
			$page['pagebar'] = '<ul>';
			$page['pagebar'] .= ($nowpage != 1)?'<li><a href="javascript:;" class="changepage ajaxpro" data-page="1">««</a></li>':'';
			$page['pagebar'] .= ($nowpage > 1)?'<li><a href="javascript:;" class="changepage ajaxpro" data-page="'.($nowpage-1).'">«</a></li>':'';
			for($i=$startpage; $i < $startpage + 10; $i++){
				if($i>$totalpage){ break; }
				$page['pagebar'] .= ($nowpage != $i)?'<li><a href="javascript:;" class="changepage ajaxpro" data-page="'.$i.'">'.$i.'</a></li>':'<li class="active"><a href="">'.$i.'</a></li>';
			}
			$page['pagebar'] .= ($nowpage < $totalpage)?'<li><a href="javascript:;" class="changepage ajaxpro" data-page="'.($nowpage+1).'">»</a></li>':'';
			$page['pagebar'] .= ($nowpage != $totalpage)?'<li><a href="javascript:;" class="changepage ajaxpro" data-page="'.$totalpage.'">»»</a></li>':'';
			$page['pagebar'] .= '</ul>';
		}else{ 
			$page['pagebar'] = ''; 
		}
	}else{
		if($restotal > 0){
			$page['pagebar'] = '<ul>';
			$page['pagebar'] .= ($nowpage != 1)?'<li><a href="'.$_SERVER['PHP_SELF'].$geturl.'1'.'" class="">««</a></li>':'';
			$page['pagebar'] .= ($nowpage > 1)?'<li><a href="'.$_SERVER['PHP_SELF'].$geturl.($nowpage-1).'" class="" >«</a></li>':'';

			for($i=$startpage; $i < $startpage + 10; $i++){
				if($i>$totalpage){ break; }
				$page['pagebar'] .= ($nowpage != $i)?'<li><a href="'.$_SERVER['PHP_SELF'].$geturl.($i).'" class="" data-page="'.$i.'">'.$i.'</a></li>':'<li class="active"><a href="">'.$i.'</a></li>';
			}

			$page['pagebar'] .= ($nowpage < $totalpage)?'<li><a href="'.$_SERVER['PHP_SELF'].$geturl.($nowpage+1).'" class="" >»</a></li>':'';
			$page['pagebar'] .= ($nowpage != $totalpage)?'<li><a href="'.$_SERVER['PHP_SELF'].$geturl.$totalpage.'" class="" >»»</a></li>':'';
			$page['pagebar'] .= '</ul>';
		}else{ 
			$page['pagebar'] = ''; 
		}
	}
	
	return $page;
}
//前台產品清單
function fc_productlist($fin , $json){
	$pros = array(); //待回傳資料
    $issno = array('kpr' , 'abr' , 'akc' , 'aks' , 'amt' , 'aor' , 'apt' , 'asr' , 'asf' , 'asz' , 'ast' , 'aus');
    $ispass = array('page');
    $unset = array(
        //'proSno' ,
        //'kprSno' ,
        //'proCode' ,
        //'proColorcode' ,
        //'abrSno' ,
        //'ausSno' ,
        //'aptSno' ,
        //'amtSno' ,
        //'astSno' ,
        //'asrSno' ,
        //'aorSno' ,
        //'aksSno' ,
        //'akcSno' ,
        //'proCpic' ,
        //'proCrgb' ,
        //'proCname' ,
        //'proLpic' ,
        //'proHpic' ,
        'proDpic' ,
        'proVpic' ,
        'proOpic' ,
        //'proTitle' ,
        'proIntro' ,
        'proFpic' ,
        'proFeature' ,
        'proSpic' ,
        'proSpec' ,
        //'proNprice' ,
        //'proVprice' ,
        //'proSprice' ,
        //'proSellstart' ,
        //'proSellend' ,
        //'proSellnprice' ,
        //'proSellvprice' ,
        //'proSellsprice' ,
        //'proNewdeadline' ,
        'proSort' ,
        'proStatus' ,
        'proErp' ,
        'proImport' ,
        'proExport' ,
    );
    
    //判斷搜尋條件
    $where = array();
    foreach ($fin as $key => $val) {
        if(!in_array($key , $issno) or empty($val)){ continue; }
        $field = $key;
        if(in_array($key , $issno)){
            $field .= "Sno";
        }
        //如果是多筆複選 或直接搜尋 ?
        if(is_array($val)){
            $farr = array();
            foreach ($val as $k => $v) {
                $farr[] = " ".$field." = '".$v."' ";
            }
            $where[] = " ( ".implode(" or ", $farr)." ) ";
        }else{
            $where[] = " ".$field." = '".$val."' ";
        }
    }
    if($fin['sel'] == 1){
    	$tmp = array();
    	$res = webquery(" select * from oj_productsell where proSellstart <= '"._today."' and proSellend >= '"._today."' and proStatus > 0 ");
    	while($row = mysql_fetch_assoc($res)){
    		$tmp[] = $row['proSno'];
    	}
    	if(count($tmp) > 0){
    		$tmp = array_unique($tmp);
    		$where[] = " proSno in (".implode(",", $tmp).") ";
    	}else{
    		$where[] = " ( proSno = 0 ) ";
    	}
    }elseif($fin['sel'] == 2){
    	$findlimit = _today - 86400 * 30; //定義1個月內訂最多的前100件商品 這邊不限制 分類 靠其它搜尋內容處理
    	$pros = array();
    	$res = webquery(" select proSno,sum(orlQt) qt from oj_orderlist where orlTime >= '".$findlimit."' group by proSno order by qt desc limit 0,50 ");
    	if(mysql_num_rows($res) > 0){
	    	while($row = mysql_fetch_assoc($res)){
	    		$pros[] = $row['proSno'];
	    	}
	    	$where[] = " ( proSno in (".implode(",", $pros).") ) ";
    	}else{
    		$where[] = " ( proSno = 0 ) ";
    	}
    }elseif($fin['sel'] == 3){
    	$where[] = " ( proNewdeadline >= '"._today."' ) ";
    }

    if($_COOKIE['web_login'] and !empty($_COOKIE['web_memSno'])){
		$user = mysql_fetch_assoc(webquery(" select * from oj_member where memSno = '".$_COOKIE['web_memSno']."' "));
		$lv = $user['memLv'];
	}else{
		$lv = 0;
	}
    if($fin['pri'] == 1){
    	$where[] = " ( proNprice <= '499' ) ";
    }elseif($fin['pri'] == 2){
    	$where[] = " ( proNprice >= '500' and proNprice <= '999' ) ";
    }elseif($fin['pri'] == 3){
    	$where[] = " ( proNprice >= '999' and proNprice <= '1499' ) ";
    }elseif($fin['pri'] == 4){
    	$where[] = " ( proNprice >= '1499' and proNprice <= '1999' ) ";
    }elseif($fin['pri'] == 5){
    	$where[] = " ( proNprice >= '2000' ) ";
    }
    $where[] = " proStatus > '0' ";

    //開始弄資料
    $limit = (empty($fin['limit']))?24:$fin['limit'];
    $page = (empty($fin['page']))?1:$fin['page'];

    $sql = " select * from oj_product where ".implode(" and ", $where)." order by proSort , proSno ";
    $pros['sqltotal'] = $sql;
    $res = webquery($sql);
    $restotal = mysql_num_rows($res);
    $webpagebar = webpagebar($page , $limit , $restotal , "ajax");
    $pros['pagebar'] = $webpagebar['pagebar'];

    $sql = $sql.$webpagebar['limit'];
    $pros['sqltotal'] = $sql;
    $res = webquery($sql);
    $rowtotal = mysql_num_rows($res);
	$pros['total'] = $rowtotal;    

    $pros['sql'] = $sql;
    $pros['page'] = $page;
    $i = 1;
    include_once(__uurl."./webset/pattrserie_w_sql.php");
    while($row = mysql_fetch_assoc($res)){
        foreach ($unset as $u) {
            unset($row[$u]); //不要的
        }

        //特別處理的
        $row['row'] = $i%3;
        $row['proLpic'] = (empty($row['proLpic']))?__wurl."img/missing-184x232.png":__uurl."product/".$row['proSno']."/".$row['proLpic'];
        $row['proHpic'] = (empty($row['proHpic']))?__wurl."img/missing-184x232.png":__uurl."product/".$row['proSno']."/".$row['proHpic'];
        $proPrice = findmymoney($row['proSno']);
        //$proPrice = $proPrice['f'];
        $row['proPrice'] = $proPrice;
        $row['asrTitle'] = $pattrserie_w_sql[$row['asrSno']]['asrTitle'];

        //存
        $pros['pros'][] = $row;
        $i++;
    }
	if($json){
		return json_encode($pros);
	}else{
		return $pros;
	}
}
//查看前台應秀商品金額
function findmymoney($proSno , $memSno){
	$price['n'] = "999999";
	$price['v'] = "999999";
	$price['s'] = "999999";
	$price['f'] = "999999";

	if(empty($memSno)){
		$resm = myquery(" select * from oj_member where memSno = '".$_COOKIE['web_memSno']."' ");
		if(mysql_num_rows($resm) > 0){
			$rowm = mysql_fetch_assoc($resm);
			$memLv = $rowm['memLv'];
		}else{
			$memLv = 0;
		}
	}else{
		$rowm = mysql_fetch_assoc(myquery(" select * from oj_member where memSno = '".$memSno."' "));
		$memLv = $rowm['memLv'];
	}

	$pro = mysql_fetch_assoc(myquery(" select * from oj_product where proSno = '".$proSno."' "));
	if($pro['proStatus'] > 0){
		$sql = " select * from oj_productsell 
		where proSno = '".$proSno."' 
		and proSellstart < "._today." 
		and proSellend > "._today." 
		order by proSellstart desc 
		limit 0,1 ";
		$res = myquery($sql);
		if(mysql_num_rows($res) > 0){
			$row = mysql_fetch_assoc($res);
			$price['c'] = $row['proSellcode'];
			$price['n'] = $row['proSellnprice'];
			$price['v'] = $row['proSellvprice'];
			$price['s'] = $row['proSellsprice'];
			if($memLv == 5){
				$price['f'] = $price['v'];
			}else{
				$price['f'] = $price['s'];
			}
		}else{
			$price['c'] = "";
			$price['n'] = $pro['proNprice'];
			$price['v'] = $pro['proVprice'];
			$price['s'] = $pro['proSprice'];
			if($memLv == 5){
				$price['f'] = $price['v'];
			}else{
				$price['f'] = $price['s'];
			}
		}
	}
	return $price;
}


//登入
function fc_memlogin($inf){
	//查帳號
	if($inf['from'] == "webform"){
		$res = myquery(" select * from oj_member where memEmail = '".$inf['email']."' ");
		if(mysql_num_rows($res) > 0){
			$row = mysql_fetch_assoc($res);

			//標準密碼
			$memPw = ($row['memPw'] == md5($inf['pw']))?true:false;

			//暫時密碼
			$memPwtmp = ($row['memPwtmptime'] + 600 > _today and $inf['pw'] == $row['memPwtmp'])?true:false;

			if($memPw or $memPwtmp){
				if($row['memStatus'] == 1){
					
					setcookie("web_login" , true);
					setcookie("web_memSno" , $row['memSno']);
					myquery(" update oj_member set memLogintime = '"._today."' where memSno = '".$row['memSno']."' ");

					if(!empty($inf['after'])){
						header("location:".$inf['after']);
					}
				}else{
					//被停權
					$msg = "您的帳號已經被停用";
				}
			}else{
				//密碼錯
				$msg = "帳號或密碼輸入不正確";
			}
		}else{
			//沒帳號
			$msg = "帳號或密碼輸入不正確";
		}
		return $msg;
	}
}
function fc_fblogin($inf){
	//查帳號
	if($inf['memFbuid'] != ""){
		$res = myquery(" select * from oj_member where memFbuid = '".$inf['memFbuid']."' ");
		if(mysql_num_rows($res) > 0){
			$row = mysql_fetch_assoc($res);
			
				if($row['memStatus'] == 1){
					setcookie("web_login" , true);
					setcookie("web_memSno" , $row['memSno']);
					myquery(" update oj_member set memLogintime = '"._today."' where memSno = '".$row['memSno']."' ");
					if(!empty($inf['after'])){
						header("location:".$inf['after']);
					}
					$re['status'] = 1;
				}else{
					//被停權
					$re['msg'] = "您的帳號已經被停用";
					$re['status'] = 0;
				}
			
		}else{
			//沒帳號
			$re['msg'] = "無此帳號";
			$re['status'] = 0;
		}
		return $re;
	}
}

//EDM
function fc_epaperlist($email , $edm){
	if(!empty($email)){
		$epaEdm = ($edm === false)?0:1;

		$res = myquery(" select * from oj_member where memEmail = '".$email."' ");
		if(mysql_num_rows($res) > 0){
			$row = mysql_fetch_assoc($res);
			$memSno = $row['memSno'];

			myquery(" update oj_member set memAdmail = '".$epaEdm."' , memAdsms = '".$epaEdm."' where memSno = '".$memSno."' ");
		}else{
			$memSno = 0;	
		}

		$res = myquery(" select * from oj_epaperlist where epaEmail = '".$email."' ");
		if(mysql_num_rows($res) > 0){
			$row = mysql_fetch_assoc($res);

			$sql = " update oj_epaperlist set 
			
			memSno = '".$memSno."' ,
			epaEdm = '".$epaEdm."' ,
			epaEdittime = '"._today."' 
			where  epaEmail = '".$email."' ";
			myquery($sql);
		}else{
			$sql = " insert into oj_epaperlist set 
			epaEmail = '".$email."' , 
			memSno = '".$memSno."' ,
			epaEdm = '".$epaEdm."' ,
			epaAddtime = '"._today."' ";
			myquery($sql);
		}
		fc_edit2CampaignMonitor($email);
	}
}

//Campaign Monitor
function fc_edit2CampaignMonitor($email){
	$res = myquery(" select * from oj_epaperlist where epaEmail = '".$email."' ");
	if(mysql_num_rows($res) > 0){
		$row = mysql_fetch_assoc($res);
		if(!empty($row['memSno'])){
			$rowm = mysql_fetch_assoc(myquery(" select * from oj_member where memSno = '".$row['memSno']."' "));
			if($row['epaEdm']){
				$sendurl = "http://3gun.createsend.com/t/i/s/udjjh/";
				$senddata['cm-udjjh-udjjh'] = $row['epaEmail'];
				$senddata['cm-name'] = $rowm['memName'];
			}else{
				$sendurl = "http://3gun.createsend.com/t/i/u/udjjh/";
				$senddata['cm-udjjh-udjjh'] = $row['epaEmail'];
			}
		}else{
			if($row['epaEdm']){
				$sendurl = "http://3gun.createsend.com/t/i/s/udjjd/";
				$senddata['cm-udjjd-udjjd'] = $row['epaEmail'];
			}else{
				$sendurl = "http://3gun.createsend.com/t/i/u/udjjd/";
				$senddata['cm-udjjd-udjjd'] = $row['epaEmail'];
			}
		}
		//處理
		$ch = curl_init();
		$curl_options = array(
			CURLOPT_URL => $sendurl, 
			CURLOPT_PORT => 80, 
			CURLOPT_HEADER => true,
			CURLOPT_POST => true, 
			CURLOPT_POSTFIELDS => http_build_query($senddata), 
			CURLOPT_RETURNTRANSFER=>true
		);
		curl_setopt_array($ch, $curl_options);
		$result = curl_exec($ch);
		curl_close($ch);
	}
}

// oj_memberaddrlist
function fc_get2memberaddrlist($memSno){
	$myaddrs = array();
	$coySno = array();
	$ctySno = array();
	$disSno = array();

    $res = webquery(" select * from oj_memberaddrlist where memSno = '".$memSno."' ");
    while($row = mysql_fetch_assoc($res)){
        $myaddrs[$row['malSno']] = $row;
        $coySno[] = $row['coySno'];
        $ctySno[] = $row['ctySno'];
        $disSno[] = $row['disSno'];
    }
    
    $coySno = array_unique($coySno);
    $ctySno = array_unique($ctySno);
    $disSno = array_unique($disSno);

    $coy = array();
    $res = webquery(" select * from oj_country where coySno in (".implode(",", $coySno).") ");
    while($row = mysql_fetch_assoc($res)){
        $coy[$row['coySno']] = $row;
    }

    $cty = array();
    $res = webquery(" select * from oj_city where ctySno in (".implode(",", $ctySno).") ");
    while($row = mysql_fetch_assoc($res)){
        $cty[$row['ctySno']] = $row;
    }

    $dis = array();
    $res = webquery(" select * from oj_district where disSno in (".implode(",", $disSno).") ");
    while($row = mysql_fetch_assoc($res)){
        $dis[$row['disSno']] = $row;
    }

    foreach ($myaddrs as $key => $myaddr) {
        $myaddrs[$key]['coyTitle'] = $coy[$myaddr['coySno']]['coyTitle'];
        $myaddrs[$key]['ctyTitle'] = $cty[$myaddr['ctySno']]['ctyTitle'];
        $myaddrs[$key]['disTitle'] = $dis[$myaddr['disSno']]['disTitle'];
    }
    return $myaddrs;
}
function fc_get2fulladdr($inf){
	$fulladdr = array();

	if($inf['coySno'] != 0){
		$res = webquery(" select * from oj_country where coySno = '".$inf['coySno']."' ");
		if(mysql_num_rows($res) > 0){
			$row = mysql_fetch_assoc($res);
			$fulladdr[0] = $row['coyTitle'];
		}
	}

	if($inf['ctySno'] != 0){
		$res = webquery(" select * from oj_city where ctySno = '".$inf['ctySno']."' ");
		if(mysql_num_rows($res) > 0){
			$row = mysql_fetch_assoc($res);
			$fulladdr[2] = $row['ctyTitle'];
		}
	}

	if($inf['disSno'] != 0){
		$res = webquery(" select * from oj_district where disSno = '".$inf['disSno']."' ");
		if(mysql_num_rows($res) > 0){
			$row = mysql_fetch_assoc($res);
			$fulladdr[3] = $row['disTitle'];
			$fulladdr[1] = $row['disZip'];
		}
	}
	ksort($fulladdr);
	if(empty($inf['arr'])){
		return implode(" ", $fulladdr);
	}else{
		return $fulladdr;
	}
}


//取得 erp 用訂單編號
function fc_create2ordererpno(){
	$ordErpno = array();
	$ordErpno['ordErpno'] = "";
	$ordErpno['ordErponeday'] = date("Ymd" , _today);
	$ordErpno['ordErponedayno'] = 1;

	$res = myquery(" select * from oj_orderinfo where ordErponeday = '".$ordErpno['ordErponeday']."' order by ordErponedayno desc limit 0,1 ");
	if(mysql_num_rows($res) > 0){
		$row = mysql_fetch_assoc($res);
		$ordErpno['ordErponedayno'] = $row['ordErponedayno']+1;
	}
	$ordErpno['ordErpno'] = $ordErpno['ordErponeday'].str_pad($ordErpno['ordErponedayno'],5,"0",STR_PAD_LEFT);
	return $ordErpno;
}


//殺掉上傳檔案資料夾
function fc_clearupload($inf){

}

// 確認折扣碼
function fc_check2discountcode($code , $memSno , $ordSno , $orrSno){
	$re = array();
    $re['price'] = 0; //折扣金額
    $re['procent'] = 100; //折扣比
    $re['reprice'] = 0; //退貨應還折扣金額
    $re['status'] = 0; //允許折扣

    if(!empty($ordSno)){ $ord = mysql_fetch_assoc(webquery(" select * from oj_orderinfo where ordSno = '".$ordSno."' ")); }
    if(!empty($orrSno)){ $orr = mysql_fetch_assoc(webquery(" select * from oj_orderreturninfo where orrSno = '".$orrSno."' ")); }

    if(!empty($code)){
        $sql = " select * from oj_discountcode where dicCode = '".$code."' ";
        $res = webquery($sql);
        if(mysql_num_rows($res) > 0){
            $row = mysql_fetch_assoc($res);
                
            //判斷折扣碼是否可以使用 , 若有訂單編號 就不查看 表已經用過了
            if(!empty($ordSno)){
            	$re['status'] = 1;
            }else{
            	if($row['dicOnline'] <= _today and _today <= $row['dicOffline']){
	                if($row['dicType'] == "t"){
	                    if($row['dicUse'] < $row['dicMaxtime']){
	                        $re['status'] = 1;
	                    }else{
	                        $re['msg'] = "此折扣碼已超過使用次數";
	                    }
	                }elseif($row['dicType'] == "p"){
	                    if(empty($memSno)){
	                        $re['msg'] = "此折扣碼限單人單次使用 請先登入會員";
	                    }else{
	                        $resl = webquery(" select * from oj_discountcodelog where memSno = '".$memSno."' ");
	                        if(mysql_num_rows($resl) < 1){
	                            $re['status'] = 1;
	                        }else{
	                            $re['msg'] = "您已經使用過此折扣碼";
	                        }
	                    }
	                }
	            }elseif($row['dicOnline'] > _today){
	                $re['msg'] = "此折扣碼未到使用日期";
	            }elseif(_today > $row['dicOffline']){
	                $re['msg'] = "此折扣碼超過使用日期";
	            }
            }

            if($re['status'] == 1){
                //整裡判斷產品內容 , 若ordSno 為空值 , 找 購物車 $_SESSION['cartitem'] , 若 orrSno 為空值 找 $_SESSION['ret_cart']['item']
                $item_total_qt = 0; //總金額
                $item_total_price = 0; //總數量
                $item_onekind_qt = array(); //單一分類金額
                $item_onekind_price = array(); //單一分類數量
                $items_kind = array(); //所有商品 分類 整理
                $kind_field = array('abrSno' , 'ausSno' , 'aptSno' , 'amtSno' , 'akcSno' , 'astSno' , 'asrSno' , 'aorSno' , 'proSno'); //所有分類
                $items = array(); //商品資料
                $matchs = array(); //符合條件的商品

                //找出所有購物商品
                if(!empty($ordSno)){
                	$res = webquery(" select * from oj_orderlist where ordSno = '".$ord['ordSno']."' ");
                	$orls = array();
                	while($orl = mysql_fetch_assoc($res)){
                		$orls[$orl['orlSno']] = $orl;
                		$items[$orl['proSno']]['qt'] += $orl['orlQt']; //數量
                		$items[$orl['proSno']]['price'] = $orl['orlPrice']; //單價
                		$matchs[$orl['proSno']][$orl['aszSno']]['qt'] = $orl['orlQt'];
                		$matchs[$orl['proSno']][$orl['aszSno']]['orlSno'] = $orl['orlSno'];
                	}
                	//如果有訂單 看要不要退貨
                	if(!empty($orrSno)){
	                	$res = webquery(" select * from oj_orderreturnitem where orrSno = '".$orr['orrSno']."' ");
	                	while($ori = mysql_fetch_assoc($res)){
	                		$items[$ori['proSno']]['qt'] -= $ori['oriQt']; //數量
                			$matchs[$ori['proSno']][$ori['aszSno']]['qt'] -= $ori['oriQt'];
	                	}
	                }else{
	                	foreach ($_SESSION['ret_cart']['item'] as $orlSno => $ones) {
	                		$orl = $orls[$orlSno];
	                		$items[$orl['proSno']]['qt'] -= $ones['oriQt']; //數量
	                		$matchs[$orls[$orlSno]['proSno']][$orls[$orlSno]['aszSno']]['qt'] -= $ones['oriQt'];
	                    }
	                }
                }else{
                	foreach ($_SESSION['cartitem'] as $proSno => $onepro) {
                    	foreach ($onepro as $aszSno => $orlQt) {
                    		$items[$proSno]['qt'] += $orlQt; //數量
                    		$items[$proSno]['price'] = 999999; //單價
                    		$matchs[$proSno][$aszSno]['qt'] = $orlQt;
                    	}
                    }
                }
                $re['items'] = $items;
                
                //撈商品資料並整理 擁有分類 與分類總數
                foreach ($items as $proSno => $item) {
                	$res = webquery(" select * from oj_product where proSno = '".$proSno."' ");
                	if(mysql_num_rows($res) > 0){
                		$pro = mysql_fetch_assoc($res);	
	                    $qt = $item['qt'];
	                    if(!empty($ordSno)){
	                    	$price = $item['price'];

	                    }else{
	                    	$price = findmymoney($proSno);
	                    	$price = $price['f'];
	                    	$items[$proSno]['price'] = $price;

	                    }
	                    $items[$proSno]['pro'] = $pro;
	                    $item_total_qt += $qt;
	                    $item_total_price += $price * $qt;
	                    foreach ($kind_field as $field) {
	                        $item_onekind_qt[$field][$pro[$field]] += $qt;
	                        $item_onekind_price[$field][$pro[$field]] += $price * $qt;
	                        $items_kind[$field][] = $pro[$field];
	                    }
                	}
                }
                $re['item_onekind_qt'] = $item_total_qt;
                $re['item_total_price'] = $item_total_price;

                //開始判斷折扣條件 -- 如果無分類與商品限制 就只查看總金額與總件數
                $hasrule = false;
                foreach ($kind_field as $field) {
                    if($row[$field] != ""){
                    	$hasrule = true;
                    }
                }
                if(!$hasrule){
                    $re['rule'] = "只查看總金額與總件數";
                    $re['status'] = 1;
                    if($item_total_price < $row['dicMinprice']){
                        $re['status'] = 0;
                    }
                    if($item_total_qt < $row['dicMinqt']){
                        $re['status'] = 0;
                    }
                }else{
                	$re['rule'] = "有限制商品分類";
                    $item_limit_qt = 0;
                    $item_limit_price = 0;
                    $re['status'] = 1;
                    $row_fields = array();
                    foreach ($kind_field as $field) {
                    	if($row[$field] != ""){
                    		$row_fields[$field] = explode(",", $row[$field]);
                    		 foreach($row_fields[$field] as $sno){
                                if(!in_array($sno , $items_kind[$field])){
                                    $re['status'] = 0;
                                }else{
                                    $item_limit_qt += $item_onekind_qt[$field][$sno];
                                    $item_limit_price += $item_onekind_price[$field][$sno];
                                }
                            }
                    	}
                    }
                    if($item_limit_price < $row['dicMinprice']){
                        $re['status'] = 0;
                    }
                    if($item_limit_qt < $row['dicMinqt']){
                        $re['status'] = 0;
                    }
                    //處理 不 match 的產品
                    foreach ($matchs as $proSno => $onepro) {
                    	foreach ($onepro as $aszSno => $match) {
                    		$matchinf = $items[$proSno]['pro'];
                    		$delme = true;
                    		foreach ($kind_field as $field) {
                    			if(in_array($matchinf[$field],$row_fields[$field])){
                    				$delme = false;
                    			}
                    		}
                    		if($delme){
                    			unset($matchs[$proSno]);
                    		}
                    	}
                    }
                }
                if($re['status'] != 1){
                    $re['msg'] = nl2br($row['dicNote']);
                }else{
                	$re['matchs'] = $matchs;
                }
            }   
        }else{
            $re['msg'] = "無此折扣碼";
        }
    }
    $re['row'] = $row;

    //計算折扣金額 
    if($re['status'] == 1){
    	$matchprice = 0;
    	foreach ($matchs as $proSno => $onepro) {
        	foreach ($onepro as $aszSno => $match) {
        		$matchprice += $match['qt'] * $items[$proSno]['price'];
        	}
        }
        $re['matchprice'] = $matchprice;

    	if(empty($row['dicProcent'])){
    		$re['price'] = $row['dicPrice'];
    		$row['price'] = $re['price'];
    	}else{
    		$re['price'] = round($matchprice  * $row['dicProcent'] / 100);
    		$row['price'] = $re['price'];
    	}
    	$re['reprice'] = ($ord['ordPricediscount'] - $re['price'] < 0)?0:$ord['ordPricediscount'] - $re['price'];
    }else{
    	$re['reprice'] = $ord['ordPricediscount'];
    }
    $re['row']['price'] = $re['price'];
    return $re;
}

//亂弄訂單的折扣金額
function fc_randorderpricediscount($ordSno){
	$re = array();
	$res = myquery(" select * from oj_orderinfo where ordSno = '".$ordSno."' ");
	if(mysql_num_rows($res) > 0){
		$re['status'] = "有訂單";
		$ord = mysql_fetch_assoc($res);
		$chk = fc_check2discountcode($ord['dicCode'] , $ord['memSno'] , $ord['ordSno']);
		$re['matchs'] = $chk['matchs'];
		
		if($chk['status'] == 1){
			$re['status'] = "有折價倦";
			$ordPricediscount = $ord['ordPricediscount'];
			$orls2itemprice = 0;
			$matchscount = 0;
			foreach ($chk['matchs'] as $proSno => $onepro) {
				foreach ($onepro as $aszSno => $match) {
					$orls2itemprice += $match['qt'] * $chk['items'][$proSno]['price'];
					$re['matchs'][$proSno][$aszSno]['orls2itemprice'] = $match['qt'] * $chk['items'][$proSno]['price'];
					$matchscount++;
				}
			}
			$matchscount--; //陣列扣1
			$re['orls2itemprice'] = $orls2itemprice;
			$re['matchscount'] = $matchscount;
			$useprice = 0;
			$i = 0;
			$orlDiscounts = array();
			foreach ($chk['matchs'] as $proSno => $onepro) {
				foreach ($onepro as $aszSno => $match) {
					if($i == $matchscount){
						$re['matchs'][$proSno][$aszSno]['way'] = "撿剩的";
						$re['matchs'][$proSno][$aszSno]['i'] = $i;
						$orlDiscounts[$match['orlSno']] = $ordPricediscount - $useprice;
						$useprice += $orlDiscounts[$match['orlSno']];
					}else{
						$re['matchs'][$proSno][$aszSno]['way'] = "算比例";
						$re['matchs'][$proSno][$aszSno]['i'] = $i;
						$orlDiscounts[$match['orlSno']] = round($ordPricediscount * $chk['items'][$proSno]['price'] * $match['qt']  / $orls2itemprice);
						$useprice += $orlDiscounts[$match['orlSno']];
					}
					$i++;
				}
			}
			$re['i'] = $i;
			if($useprice > 0){
				myquery(" update oj_orderlist set orlDiscount = '0' where ordSno = '".$ord['ordSno']."' ");
				foreach ($orlDiscounts as $orlSno => $orlDiscount) {
					myquery(" update oj_orderlist set orlDiscount = '".$orlDiscount."' where orlSno = '".$orlSno."' ");
				}
			}
		}else{
			$re['status'] = "無折價倦";
		}
	}else{
		$re['status'] = "無訂單";
	}
	return $re;
}
//亂弄退貨單的折扣金額
function fc_randreturnorderpricediscount($orrSno){
	$res = myquery(" select * from oj_orderreturninfo where orrSno = '".$orrSno."' ");
	if(mysql_num_rows($res) > 0){
		$orr = mysql_fetch_assoc($res);
		$ord = mysql_fetch_assoc(myquery(" select * from oj_orderinfo where ordSno = '".$orr['ordSno']."' "));
		$chk = fc_check2discountcode($ord['dicCode'] , $ord['memSno'] , $ord['ordSno'] , $orr['orrSno']);
		myquery(" update oj_orderreturnitem set orlDiscount = '0' where orrSno = '".$orr['orrSno']."' ");
		if($chk['status'] == 1){
			$ordPricediscount = $chk['price'];
			$orls2itemprice = $chk['matchprice'];

			$matchscount = 0;
			foreach ($chk['matchs'] as $proSno => $onepro) {
				foreach ($onepro as $aszSno => $match) {
					$matchscount++;
				}
			}
			$matchscount--; //陣列扣1

			$useprice = 0;
			$i = 0;
			$orlDiscounts = array();
			foreach ($chk['matchs'] as $proSno => $onepro) {
				foreach ($onepro as $aszSno => $match) {
					if($i == $matchscount){
						$orlDiscounts[$match['orlSno']] = $ordPricediscount - $useprice;
						$useprice += $orlDiscounts[$match['orlSno']];
					}else{
						$orlDiscounts[$match['orlSno']] = round($ordPricediscount * $chk['items'][$proSno]['price'] * $match['qt']  / $orls2itemprice);
						$useprice += $orlDiscounts[$match['orlSno']];
					}
					$i++;
				}
			}
			if($useprice > 0){
				foreach ($orlDiscounts as $orlSno => $orlDiscount) {
					myquery(" update oj_orderreturnitem set orlDiscount = '".$orlDiscount."' where orlSno = '".$orlSno."' ");
				}
			}
		}
	}
}


//erp時間民國轉西元
function fc_erptime2decode($t){
	$ret = array();
	$ret['source'] = $t;
	$ret['ts'] = 0;
	$ret['str'] = "";
	$ret['y'] = 0;
	$ret['m'] = 0;
	$ret['d'] = 0;
	$ret['h'] = 0;
	$ret['i'] = 0;
	$ret['s'] = 0;

	if(!is_array($t)){
		$str = $t;
		$t = array();
		$t[0] = substr($str, 0 , 8);
		$t[1] = substr($str, 8);
	}

	if(is_array($t)){
		if(strlen($t[0]) == 7){
			$ret['strlen'] = 7;
			$y = substr($t[0], 0 , 3);
			$m = substr($t[0], 3 , 2);
			$d = substr($t[0], 5 , 2);
			$ret['y'] = $y * 1 + 1911;
			$ret['m'] = $m * 1;
			$ret['d'] = $d * 1;
		}elseif(strlen($t[0]) == 8){
			$ret['strlen'] = 8;
			$y = substr($t[0], 0 , 4);
			$m = substr($t[0], 4 , 2);
			$d = substr($t[0], 6 , 2);
			if($y * 1 < 1911){
				$ret['y'] = $y * 1 + 1911;
			}else{
				$ret['y'] = $y * 1;
			}
			$ret['m'] = $m * 1;
			$ret['d'] = $d * 1;
		}else{
			$ret['y'] = date("Y" , _today);
			$ret['m'] = date("m" , _today);
			$ret['d'] = date("d" , _today);
		}

		if(strlen($t[1]) == 8){
			$time = explode(":", $t[1]);
			$ret['h'] = $time[0] * 1;
			$ret['i'] = $time[1] * 1;
			$ret['s'] = $time[2] * 1;
		}elseif(strlen($t[1]) == 6){
			$h = substr($t[0], 0 , 2);
			$i = substr($t[1], 2 , 2);
			$s = substr($t[2], 4 , 2);
			$ret['h'] = $h * 1;
			$ret['i'] = $i * 1;
			$ret['s'] = $s * 1;
		}elseif(strlen($t[1]) == 4){
			$h = substr($t[1], 0 , 2);
			$i = substr($t[1], 2 , 2);
			$s = 0;
			$ret['h'] = $h * 1;
			$ret['i'] = $i * 1;
			$ret['s'] = 0;
		}else{
			$ret['h'] = date("h" , _today);
			$ret['i'] = date("i" , _today);
			$ret['s'] = date("s" , _today);
		}

		$ret['ts'] = mktime($ret['h'] , $ret['i'] , $ret['s'] , $ret['m'] , $ret['d'] , $ret['y']);
		$ret['str'] = date("Y-m-d H:i:s" , $ret['ts']);
	}else{

	}

	return $ret;
}
function fc_erptime2encode($ts){
	$ret = array();
	$ret['y'] = date("Y",$ts) - 1911;
	$ret['y'] = str_pad($ret['y'],4,"0",STR_PAD_LEFT);
	$ret['m'] = date("m",$ts);
	$ret['d'] = date("d",$ts);
	$ret['h'] = date("H",$ts);
	$ret['i'] = date("i",$ts);
	$ret['s'] = date("s",$ts);

	$ret['date'] = $ret['y'].$ret['m'].$ret['d'];
	$ret['time'] = $ret['h'].":".$ret['i'].":".$ret['s'];

	return $ret;
}

//統一處理訂單狀態的各種判斷
function fc_checkorderstatus($inf){
	$re = array();
	$ret['allow_cancal'] = false;
	$ret['allow_return'] = false;
	$ret['allow_contact'] = false;
	$ret['allow_redetail'] = false;

	if($inf['field'] == "ordSno"){
		$sql = " select * from oj_orderinfo where ".$inf['field']." = '".$inf['val']."' ";
	}

	$res = webquery($sql);
	if(mysql_num_rows($res) > 0){
		$ord = mysql_fetch_assoc($res);
		$shp = mysql_fetch_assoc(webquery(" select * from oj_shipping where shpSno = '".$ord['shpSno']."' "));

		//訂單下一步流程 {'1':'線上金流','3':'三槍門市','7':'7-11流程','0':'完成訂單','4':'暫停訂單'}
        //付款狀態 {'0':'待付款','1':'已付款','4':'付款異常'}
        //物流狀態 {'0':'待付款','1':'已到貨','2':'備貨中','3':'已出貨'}
        //訂單狀態 {'0':'訂單取消','1':'訂單正常','4':'退貨','9':'待處理'}
        //ERP狀態 {'0':'等待訂單流程','1':'已匯出','9':'待匯出'}

		//允許取消訂單
		if( in_array($ord['ordErpstatus'] , array(0,9)) and in_array($ord['ordStatusship'] , array(0)) and in_array($ord['ordStatus'] , array(1,9)) ){
			$ret['allow_cancal'] = true;
		}
		//允許退貨
		if( in_array($ord['ordStatusship'] , array(1)) and $ord['ordTime4redeadline'] > _today and in_array($ord['ordStatus'] , array(1)) ){
			$ret['allow_return'] = true;
		}
		//允許咨詢
		if(1){
			$ret['allow_contact'] = true;
		}
		//前台用的狀態判斷
		if($shp['shpProcess'] == 1){
			if($ord['ordStatusship'] == 0){
				if($ord['ordStatuspay'] == 0){
					$ret['web_txt'] = "訂購成功";
				}elseif($ord['ordStatuspay'] == 1){
					$ret['web_txt'] = "訂購成功";
				}elseif($ord['ordStatuspay'] == 4){
					$ret['web_txt'] = "付款失敗";
				}
			}else{
				if($ord['ordStatusship'] == 2 or $ord['ordErpstatus'] == 1){
					$ret['web_txt'] = "包裝出貨中";
				}
				if($ord['ordStatusship'] == 1){
					$ret['web_txt'] = "已送達";
				}
				if($ord['ordStatusship'] == 3){
					$ret['web_txt'] = "運送中";
				}
			}
		}
		if($shp['shpProcess'] == 3){
			if($ord['ordStatusship'] == 0){
				$ret['web_txt'] = "訂購成功";
			}
			if($ord['ordStatusship'] == 2 and $ord['ordErpstatus'] == 1){
				$ret['web_txt'] = "包裝出貨中";
			}
			if($ord['ordStatusship'] == 3){
				$ret['web_txt'] = "運送中";
			}
			if($ord['ordStatusship'] == 1){
				if($ord['ordStatuspay'] == 1){
					$ret['web_txt'] = "交易完成";
				}else{
					$ret['web_txt'] = "待取貨";
				}
			}
		}
		if($shp['shpProcess'] == 7){
			if($ord['ordStatusship'] == 0){
				$ret['web_txt'] = "訂購成功";
			}
			if($ord['ordStatusship'] == 2 or $ord['ordErpstatus'] == 1){
				$ret['web_txt'] = "包裝出貨中";
			}
			if($ord['ordStatusship'] == 1){
				if($ord['ordStatuspay'] == 1){
					$ret['web_txt'] = "交易完成";
				}else{
					$ret['web_txt'] = "待取貨";
				}
			}
			if($ord['ordStatusship'] == 3){
				$ret['web_txt'] = "運送中";
			}
		}
		if($shp['shpProcess'] == 5){
			if($ord['ordStatusship'] == 0){
				if($ord['ordStatuspay'] == 0){
					$ret['web_txt'] = "訂購成功";
				}elseif($ord['ordStatuspay'] == 1){
					$ret['web_txt'] = "訂購成功";
				}elseif($ord['ordStatuspay'] == 4){
					$ret['web_txt'] = "付款失敗";
				}
			}else{
				if($ord['ordStatusship'] == 2 or $ord['ordErpstatus'] == 1){
					$ret['web_txt'] = "包裝出貨中";
				}
				if($ord['ordStatusship'] == 1){
					$ret['web_txt'] = "已送達";
				}
				if($ord['ordStatusship'] == 3){
					$ret['web_txt'] = "運送中";
				}
			}
		}
		if($shp['shpProcess'] == 0){
			if($ord['ordStatusship'] == 2 or $ord['ordErpstatus'] == 1){
				$ret['web_txt'] = "包裝出貨中";
			}
			if($ord['ordStatusship'] == 0){
				$ret['web_txt'] = "訂購成功";
			}
			if($ord['ordStatusship'] == 1){
				if($ord['ordStatuspay'] == 1){
					$ret['web_txt'] = "交易完成";
				}
			}
			if($ord['ordStatusship'] == 3){
				$ret['web_txt'] = "運送中";
			}
		}
		if($shp['shpProcess'] == 4){
			$ret['web_txt'] = "待管理員處理";
		}

		if($ord['ordStatus'] == 4){
			$ret['web_txt'] = "退貨";
			$orr = mysql_fetch_assoc(webquery(" select * from oj_orderreturninfo where ordSno = '".$ord['ordSno']."' "));
			$ret['orr'] = $orr;
			$ret['allow_redetail'] = true;
		}
		if($ord['ordStatus'] == 0){
			$ret['web_txt'] = "訂單已取消";
		}

	}else{
		//找沒有訂單
	}
	return $ret;
}



//訂單修改作業
function fc_orderinfochange($rep){
	$re['status'] = true;
	if(empty($rep['ordSno'])){
		$re['status'] = false;
	}else{
		$dta['bef'] = "ord";
		include(__lurl."manage_orderinfo.php");
		include(__uurl."webset/payment_cms_sql.php");
		$payment_cms_sql[0] = array('paySno'=>0,'payTitle'=>"非使用線上金流");
		include(__uurl."webset/shipping_cms_sql.php");
		$once = "ordInvoicehow";	${$once."Opt"} = getdboption("oj_orderinfo" , $once);
		$once = "ordInvoicetype";	${$once."Opt"} = getdboption("oj_orderinfo" , $once);
		$once = "ordStatuspay";		${$once."Opt"} = getdboption("oj_orderinfo" , $once);
		$once = "ordStatusship";	${$once."Opt"} = getdboption("oj_orderinfo" , $once);
		$once = "ordStatusstock";	${$once."Opt"} = getdboption("oj_orderinfo" , $once);
		$once = "ordStatus";		${$once."Opt"} = getdboption("oj_orderinfo" , $once);
		$once = "ordErpstatus";		${$once."Opt"} = getdboption("oj_orderinfo" , $once);

		$is_pass = array("act","selecter");
		$is_sno = array("paySno","shpSno");
		$is_opt = array("ordInvoicehow","ordInvoicetype","ordStatuspay","ordStatusship","ordStatusstock","ordStatus","ordErpstatus");
		$is_time = array("ordTime4redeadline");


		$row = mysql_fetch_assoc(myquery(" select * from oj_orderinfo where ordSno = '".$rep['ordSno']."' "));

		$shp = $shipping_cms_sql[$row['shpSno']];
		$hadchange = array();
		//處理資料修改紀錄 和 調值
		foreach ($rep as $key => $val) {
			if(in_array($key , $is_pass)){ continue; }
			if(!is_array($val)){
				if(in_array($key , $is_sno)){
					if($row[$key] != $rep[$key]){
						if($key == "paySno"){
							$hadchange[] = $myl[$key]." : ".$payment_cms_sql[$row[$key]]['payTitle']." &gt; ".$payment_cms_sql[$rep[$key]]['payTitle']."\n";
						}elseif($key == "shpSno"){
							$hadchange[] = $myl[$key]." : ".$shipping_cms_sql[$row[$key]]['shpTitle']." &gt; ".$shipping_cms_sql[$rep[$key]]['shpTitle']."\n";
						}
					}
				}elseif(in_array($key , $is_opt)){
					if($row[$key] != $rep[$key]){
						$hadchange[] = $myl[$key]." : ".${$key."Opt"}[$row[$key]]." &gt; ".${$key."Opt"}[$rep[$key]]."\n";
					}
				}elseif(in_array($key , $is_time)){
					$rep[$key] = (empty($rep[$key]))?0:$rep[$key];
					if($row[$key] != $rep[$key]){
						$hadchange[] = $myl[$key]." : ".${$key."Opt"}[$row[$key]]." &gt; ".${$key."Opt"}[$rep[$key]]."\n";
					}
					${$key} = $rep[$key];
				}else{
					if($row[$key] != $rep[$key]){
						$hadchange[] = $myl[$key]." : ".$row[$key]." &gt; ".$rep[$key]."\n";
					}
				}
			}else{
				if(in_array($key , $is_time)){
					if($rep[$key][0] == "" and $rep[$key][1] == ""){
						$newtime = 0;
					}elseif($rep[$key][0] != "" and $rep[$key][1] == ""){
						$newtime = strtotime($rep[$key][0]." 23:59:59");
					}else{
						$newtime = strtotime($rep[$key][0]." ".$rep[$key][1]);
					}
					${$key} = $newtime;
					if($row[$key] != $newtime){
						$hadchange[] = $myl[$key]." : ".date("Y-m-d H:i:s",$row[$key])." &gt; ".date("Y-m-d H:i:s",$newtime)."\n";
					}	
				}else{

				}
			}
		}
		if(count($hadchange) > 0){
			if(isset($_COOKIE['admin_admNick'])){
				$ordLog = date("Y-m-d H:i:s" , _today)." ".$_COOKIE['admin_admNick']." 修改資料\n";
			}else{
				$ordLog = date("Y-m-d H:i:s" , _today)." 修改資料\n";
			}
			$ordLog .= implode(" ", $hadchange);
			$ordLog .= "\n\n";
			$Log = " ordLog = concat('".$ordLog."' , ordLog) , ";
		}

		if($rep['ordStatus'] == 0 and $row['ordStatus'] != 0){
			$ordCanceltime = " ordCanceltime = '"._today."' , ";
		}
		if($rep['ordStatuspay'] == 1 and $row['ordStatuspay'] != 1){
			$ordTime4paid = " ordTime4paid = '"._today."' , ";
		}
		if($rep['ordStatusship'] == 3 and $row['ordStatusship'] != 3){
			$ordTime4shipment = " ordTime4shipment = '"._today."' , ";
		}
		if($rep['ordStatusship'] == 1 and $row['ordStatusship'] != 1){
			$ordTime4arrival = " ordTime4arrival = '"._today."' , ";
			if(empty($row['ordTime4redeadline'])){
				$ordTime4redeadline = mktime(23,59,59,date("m",_today),date("d",_today)+$shp['shpDeadline'],date("Y",_today));
			}
		}
		if($rep['ordStatusship'] == 3 and $row['ordStatusship'] != 3){
			// if(empty($row['ordTime4redeadline'])){
			// 	$ordTime4redeadline = mktime(23,59,59,date("m",_today),date("d",_today)+$shp['shpDeadline'],date("Y",_today));
			// }
		}

		if($rep['ordStatusstock'] == 1 and $row['ordStatusstock'] != 1){
			fc_orderstockswitch($rep['ordSno'] , "out");
		}
		if($rep['ordStatusstock'] == 0 and $row['ordStatusstock'] != 0){
			fc_orderstockswitch($rep['ordSno'] , "in");
		}

		$i = 0;
		
		$sql = "update oj_orderinfo set 

		ordBname = '".$rep['ordBname']."' ,
		ordBemail = '".$rep['ordBemail']."' ,
		ordBcountry = '".$rep['ordBcountry']."' ,
		ordBzip = '".$rep['ordBzip']."' ,
		ordBcity = '".$rep['ordBcity']."' ,
		ordBdistrict = '".$rep['ordBdistrict']."' ,
		ordBAddress = '".$rep['ordBAddress']."' ,
		ordBtel = '".$rep['ordBtel']."' ,
		ordBmobile = '".$rep['ordBmobile']."' ,

		ordInvoicehow = '".$rep['ordInvoicehow']."' ,
		ordInvoicetype = '".$rep['ordInvoicetype']."' ,
		ordInvoicetitle = '".$rep['ordInvoicetitle']."' ,
		ordInvoicenum = '".$rep['ordInvoicenum']."' ,
		ordInvoice = '".$rep['ordInvoice']."' ,

		ordSname = '".$rep['ordSname']."' ,
		ordSemail = '".$rep['ordSemail']."' ,
		ordScountry = '".$rep['ordScountry']."' ,
		ordSzip = '".$rep['ordSzip']."' ,
		ordScity = '".$rep['ordScity']."' ,
		ordSdistrict = '".$rep['ordSdistrict']."' ,
		ordSAddress = '".$rep['ordSAddress']."' ,
		ordStel = '".$rep['ordStel']."' ,
		ordSmobile = '".$rep['ordSmobile']."' ,

		ordPriceitem = '".$rep['ordPriceitem']."' ,
		ordPriceshipping = '".$rep['ordPriceshipping']."' ,
		ordPricediscount = '".$rep['ordPricediscount']."' ,
		ordPricetotal = '".$rep['ordPricetotal']."' ,
		ordPricepaid = '".$rep['ordPricepaid']."' ,

		paySno = '".$rep['paySno']."' ,

		shpSno = '".$rep['shpSno']."' ,
		ordShpsnotxt = '".$rep['ordShpsnotxt']."' ,
		ordStotype = '".$rep['ordStotype']."' , 
		ordStoid = '".$rep['ordStoid']."' , 
		ordStoname = '".$rep['ordStoname']."' , 
		ordStocoy = '".$rep['ordStocoy']."' , 
		ordStozip = '".$rep['ordStozip']."' , 
		ordStocty = '".$rep['ordStocty']."' , 
		ordStodis = '".$rep['ordStodis']."' , 
		ordStoaddress = '".$rep['ordStoaddress']."' , 

		ordShippingno = '".$rep['ordShippingno']."' ,
		ordStatuspay = '".$rep['ordStatuspay']."' ,
		ordStatusship = '".$rep['ordStatusship']."' ,
		ordStatusstock = '".$rep['ordStatusstock']."' ,
		ordStatus = '".$rep['ordStatus']."' ,
		
		{$ordCanceltime}
		{$ordTime4paid}
		{$ordTime4shipment}
		{$ordTime4arrival}

		ordTime4redeadline = '".$ordTime4redeadline."' ,

		ordUserps = '".$rep['ordUserps']."' ,
		ordAdminps = '".$rep['ordAdminps']."' ,

		{$Log}
		ordErpstatus = '".$rep['ordErpstatus']."' 

		where ordSno = '".$rep['ordSno']."' ";
		myquery($sql);

		if($rep['ordStatuspay'] == 1 and $row['ordStatuspay'] != 1){
			$inf['emtKey'] = "e_m_orderpaid";
	        $inf['ordSno'] = $rep['ordSno'];
	        // fc_sendemail($inf);
	        fc_emailcrontab($inf);
		}
		if($rep['ordStatusship'] == 1 and $row['ordStatusship'] != 1){
			$inf['emtKey'] = "e_m_ordersended";
	        $inf['ordSno'] = $rep['ordSno'];
	        // fc_sendemail($inf);
	        fc_emailcrontab($inf);
		}

		$re['status'] = true;
	}
	return $re;
}

//退貨單修改作業
function fc_orderreturninfochange($rep){
	$re['status'] = true;
	if(empty($rep['orrSno'])){
		$re['status'] = false;
	}else{
		$dta['bef'] = "orr";
		include(__lurl."manage_orderreturninfo.php");
		include(__uurl."webset/shippingreturn_cms_sql.php");
		include(__uurl."webset/orderreturnwhy_cms_sql.php");

		$once = "orrInvoicehow";	${$once."Opt"} = getdboption("oj_orderreturninfo" , $once);
		$once = "orrStatuspay";		${$once."Opt"} = getdboption("oj_orderreturninfo" , $once);
		$once = "orrStatusship";	${$once."Opt"} = getdboption("oj_orderreturninfo" , $once);
		$once = "orrErpstatus";		${$once."Opt"} = getdboption("oj_orderreturninfo" , $once);

		$is_pass = array("act","selecter","oriQt","orwSno");
		$is_sno = array("shrSno");
		$is_opt = array("orrInvoicehow","orrStatuspay","orrStatusship","orrErpstatus");
		$is_time = array();

		$row = mysql_fetch_assoc(myquery(" select * from oj_orderreturninfo where orrSno = '".$rep['orrSno']."' ")); //原資料
		$shr = $shippingreturn_cms_sql[$row['shrSno']];

		$hadchange = array();
		//處理資料修改紀錄 和 調值
		foreach ($rep as $key => $val) {
			if(in_array($key , $is_pass)){ continue; }
			if(!is_array($val)){
				if(in_array($key , $is_sno)){
					if($row[$key] != $rep[$key]){
						if($key == "shrSno"){
							$hadchange[] = $myl[$key]." : ".$shippingreturn_cms_sql[$row[$key]]['shrTitle']." &gt; ".$shippingreturn_cms_sql[$rep[$key]]['shrTitle']."\n";
						}
					}
				}elseif(in_array($key , $is_opt)){
					if($row[$key] != $rep[$key]){
						$hadchange[] = $myl[$key]." : ".${$key."Opt"}[$row[$key]]." &gt; ".${$key."Opt"}[$rep[$key]]."\n";
					}
				}elseif(in_array($key , $is_time)){
					$rep[$key] = (empty($rep[$key]))?0:$rep[$key];
					if($row[$key] != $rep[$key]){
						$hadchange[] = $myl[$key]." : ".$row[$key]." &gt; ".$rep[$key]."\n";
					}
					${$key} = $rep[$key];
				}else{
					if($row[$key] != $rep[$key]){
						$hadchange[] = $myl[$key]." : ".$row[$key]." &gt; ".$rep[$key]."\n";
					}
				}
			}else{
				if(in_array($key , $is_time)){
					if($rep[$key][0] == "" and $rep[$key][1] == ""){
						$newtime = 0;
					}elseif($rep[$key][0] != "" and $rep[$key][1] == ""){
						$newtime = strtotime($rep[$key][0]." 23:59:59");
					}else{
						$newtime = strtotime($rep[$key][0]." ".$rep[$key][1]);
					}
					${$key} = $newtime;
					if($row[$key] != $newtime){
						$hadchange[] = $myl[$key]." : ".date("Y-m-d H:i:s",$row[$key])." &gt; ".date("Y-m-d H:i:s",$newtime)."\n";
					}	
				}else{

				}
			}
		}
		if(count($hadchange) > 0){
			if(isset($_COOKIE['admin_admNick'])){
				$orrLog = date("Y-m-d H:i:s" , _today)." ".$_COOKIE['admin_admNick']." 修改資料\n";
			}else{
				$orrLog = date("Y-m-d H:i:s" , _today)." 修改資料\n";
			}
			$orrLog .= implode(" ", $hadchange);
			$orrLog .= "\n\n";
			$Log = " orrLog = concat('".$orrLog."' , orrLog) , ";
		}

		if($rep['orrStatuspay'] == 1 and $row['orrStatuspay'] != 1){
			$orrTime4paid = " orrTime4paid = '"._today."' , ";
		}else{
			$orrTime4paid = " orrTime4paid = '0' , ";
		}
		if($rep['orrStatusship'] == 2 and $row['orrStatusship'] != 2){
			if(empty($rep['orrTime4pickup'])){
				$orrTime4shipment = " orrTime4pickup = '"._today."' , ";
			}else{
				$orrTime4shipment = " orrTime4pickup = '".$rep['orrTime4pickup']."' , ";
			}
		}
		if($rep['orrStatusship'] == 1 and $row['orrStatusship'] != 1){
			$orrTime4arrival = " orrTime4arrival = '"._today."' , ";
		}
		//商品清單
		foreach ($rep['oriQt'] as $oriSno => $no) {
			if($rep['oriQt'][$oriSno] == 0){
				$rep['orwSno'][$oriSno] = 0;
			}
			$sql = " update oj_orderreturnitem set 
			oriQt = '".$rep['oriQt'][$oriSno]."' , 
			orwSno = '".$rep['orwSno'][$oriSno]."' 
			where oriSno = '".$oriSno."' ";
			myquery($sql);
		}

		$sql = "update oj_orderreturninfo set 
		shrSno = '".$rep['shrSno']."' ,
		orrShpsnotxt = '".$rep['orrShpsnotxt']."' ,
		orrStotype = '".$rep['orrStotype']."' ,
		orrStoid = '".$rep['orrStoid']."' ,
		ordStoname = '".$rep['ordStoname']."' ,
		orrStocoy = '".$rep['orrStocoy']."' ,
		orrStozip = '".$rep['orrStozip']."' ,
		orrStocty = '".$rep['orrStocty']."' ,
		orrStodis = '".$rep['orrStodis']."' ,
		orrStoaddress = '".$rep['orrStoaddress']."' ,
		orrShippingno = '".$rep['orrShippingno']."' ,
		orrShippingvno = '".$rep['orrShippingvno']."' ,
		orrBname = '".$rep['orrBname']."' ,
		orrBbankcode = '".$rep['orrBbankcode']."' ,
		orrBbankid = '".$rep['orrBbankid']."' ,
		orrInvoicehow = '".$rep['orrInvoicehow']."' ,
		orrIcountry = '".$rep['orrIcountry']."' ,
		orrIzip = '".$rep['orrIzip']."' ,
		orrIcity = '".$rep['orrIcity']."' ,
		orrIdistrict = '".$rep['orrIdistrict']."' ,
		orrIAddress = '".$rep['orrIAddress']."' ,
		orrStatuspay = '".$rep['orrStatuspay']."' ,
		orrStatusship = '".$rep['orrStatusship']."' ,
		{$orrTime4paid}
		{$orrTime4shipment}
		{$orrTime4arrival}
		{$Log}
		orrErpstatus = '".$rep['orrErpstatus']."' 
		where orrSno = '".$rep['orrSno']."' ";
		myquery($sql);
		$re['status'] = true;

		//重算退貨單金額
		$orr = mysql_fetch_assoc(myquery(" select * from oj_orderreturninfo where orrSno = '".$rep['orrSno']."' "));
		$ord = mysql_fetch_assoc(myquery(" select * from oj_orderinfo where ordSno = '".$orr['ordSno']."' "));
		$shr = $shippingreturn_cms_sql[$orr['shrSno']];
		$shr['orwSno'] = explode(",", $shr['orwSno']);

		$checkcode = fc_check2discountcode($ord['dicCode'] , $ord['memSno'] , $ord['ordSno'] , $orr['orrSno']);
		
		$ordPricediscount = 0;
		$orrPriceitem = 0;
		$orrPriceshipping = $shr['shrPrice'];
		$orrPricediscount = 0;
		$orrPricebank = 0;
		$orrPricetotal = 0;

		$res = myquery(" select * from oj_orderreturnitem where orrSno='".$row['orrSno']."'");
		while($ori = mysql_fetch_assoc($res)){
			$orrPriceitem += $ori['orlPrice'] * $ori['oriQt'];
			if(in_array($ori['orwSno'] , $shr['orwSno'])){
				$orrPriceshipping = 0;
			}
		}

		$ordPricediscount = $checkcode['price'];
		$orrPricediscount = $checkcode['reprice'];

		$orrPricetotal = $orrPriceitem - $orrPriceshipping - $orrPricediscount;
		$sql = "update oj_orderreturninfo set 
		ordPricediscount = '".$ordPricediscount."' ,
		orrPriceitem = '".$orrPriceitem."' ,
		orrPriceshipping = '".$orrPriceshipping."' ,
		orrPricediscount = '".$orrPricediscount."' ,
		orrPricebank = '".$orrPricebank."' ,
		orrPricetotal = '".$orrPricetotal."' 
		where orrSno = '".$rep['orrSno']."' ";
		myquery($sql);

		//更新折扣碼使用金額
        if($ord['dicSno'] > 0 and $ord['dicCode'] != ""){
            $sql = " update oj_discountcodelog set 
            orrSno = '".$orr['orrSno']."' , 
            orrPricediscount = '".$ordPricediscount."' 
            where dicSno = '".$ord['dicSno']."' and ordSno = '".$ord['ordSno']."' ";
            myquery($sql);
        }

		fc_randreturnorderpricediscount($rep['orrSno']);

		if($rep['orrStatuspay'] == 1 and $row['orrStatuspay'] != 1){
			$inf['emtKey'] = "e_m_orderreturnpaid";
	        $inf['orrSno'] = $rep['orrSno'];
	        // fc_sendemail($inf);
	        fc_emailcrontab($inf);
		}
	}
	return $re;
}
function fc_orderstockswitch($ordSno , $io){
	$res = webquery(" select * from oj_orderinfo where ordSno = '".$ordSno."' ");
	if(mysql_num_rows($res) > 0){
		$ord = mysql_fetch_assoc($res);
		$res = webquery(" select * from oj_orderlist where ordSno = '".$ordSno."' ");
		while($orl = mysql_fetch_assoc($res)){
			$sql = " update oj_productstock set ";
			if($io == "in"){
				$sql .= " pstStock = ( pstStock + ".$orl['orlQt']." ) ";
			}elseif($io == "out"){
				$sql .= " pstStock = ( pstStock - ".$orl['orlQt']." ) ";
			}
			$sql .= "where proSno = '".$orl['proSno']."' and proColorcode = '".$orl['orlColorcode']."' and proSizecode = '".$orl['orlSizecode']."' ";
			webquery($sql);
		}
	}
}



//檢查統一編號
function fc_check2companyid($num){
	$ckn = $num;
	$rule = array(1,2,1,2,1,2,4,1);
	$re['status'] = true;
	if(strlen($ckn) == 8){
        $ckn = str_split($ckn);
        foreach ($ckn as $key => $val) {
        	if($val * $rule[$key] >= 10){
        		$again = str_split($val * $rule[$key]);
        		$ckn[$key] = $again[0] + $again[1];
        	}else{
        		$ckn[$key] = $val * $rule[$key];
        	}
        }
        $sum = array_sum($ckn);
        if($sum % 10){
        	$re['status'] = false;
        }
    }else{
        $re['status'] = false;
    }
	return $re;
}


// 7-11 退貨 $inf =>  act : add , del , print ; code , vcode; way:f,b
function fc_711returnorder($inf){
	$re['status'] = 0;
	$res = webquery(" select * from oj_orderreturninfo where orrSno = '".$inf['orrSno']."' ");
	if(mysql_num_rows($res) > 0){
		$orr = mysql_fetch_assoc($res);
		$re['status'] = 1;

		$url = ""; 
		$send['account'] = str_pad($orr['orrPriceitem'],5,"0",STR_PAD_LEFT); //商品總金額，不足左補0
		$send['deadlinedate'] = date("Ymd" , _today + 86400 * _ship7r_deadline); //繳款截止期限
		$send['deadlinetime'] = "2359"; //繳款截止時間 固定2359
		$send['eshopid'] = _ship7r_uid; //母廠商代號
		$send['eshopsonid'] = _ship7r_eshopid; //子廠商代號
		$send['orderno'] = $orr['orrErpno']; //顧客訂單編號，一年內不可重複
		$send['paymentno'] = ""; //退貨編號(第一次列印不傳，但重複列印時必傳)
		$send['validationno'] = ""; //驗證碼, 消費者由ibon輸入時確認paymentno是否正確
		$send['service_type'] = "5"; //單據類別 4：退貨付款 5：退貨不付款
		$send['tempvar'] = ""; //eShop自行運用，identify 資料用
		$send['url'] = _meta_weburl._ship7r_reSystem."?id=".md5("ordSno,ordTime"); //7-11根據此URL回傳 eShop，以http:// 開頭
		$send['online_url'] = _meta_weburl._ship7r_reReture."?id=".md5("ordSno,ordTime"); //消費者選擇下次列印功能時，根據此URL回傳eShop
		$send['trade_describe'] = "THREEGUN"; //商品簡述20個中文字
		$send['payment_cpname'] = "THREEGUN"; //付款廠商 最長10個中文字
		$send['cp_remark01'] = ""; //eShop備註1 最長20個中文字
		$send['cp_remark02'] = ""; //eShop備註2 最長20個中文字
		$send['cp_remark03'] = ""; //eShop備註3 最長20個中文字
		$send['show_type'] = ""; //網頁顯示類型 前景要號請帶入 11 背景要號請帶入 21
		$send['trade_no'] = 0; //1:要號, 2:訂單取消, 3:二次列印)
		$send['daishou_account'] = "00000"; //代收金額，不足左補0
		$send['shopper_name'] = substr($orr['orrBname'], 0 , 6); //退貨人姓名 個資不代值?
		$send['member_pwd'] = ""; //廠商密碼 客服說用空值
		
		if($inf['way'] == "f"){
			$url = _ship7r_furl;
			$send['show_type'] = "11";

		}elseif($inf['way'] == "b"){
			$url = _ship7r_burl;
			$send['show_type'] = "21";
		}
		$re['url'] = $url;

		if($inf['act'] == "add"){
			$send['trade_no'] = 1;

		}elseif($inf['act'] == "del"){
			$send['trade_no'] = 2;
			$send['paymentno'] = $orr['orrShippingno'];
			$send['validationno'] = $orr['orrShippingvno'];

		}elseif($inf['act'] == "print"){
			$send['trade_no'] = 3;
			$send['paymentno'] = $orr['orrShippingno'];
			$send['validationno'] = $orr['orrShippingvno'];
		}

		if($inf['way'] == "f"){
			$html = '<form name="print711returnorder" action="'.$url.'" method="post" target="_blank">
			<input type="hidden" name="account" value="'.$send['account'].'" />
			<input type="hidden" name="deadlinedate" value="'.$send['deadlinedate'].'" />
			<input type="hidden" name="deadlinetime" value="'.$send['deadlinetime'].'" />
			<input type="hidden" name="eshopid" value="'.$send['eshopid'].'" />
			<input type="hidden" name="eshopsonid" value="'.$send['eshopsonid'].'" />
			<input type="hidden" name="orderno" value="'.$send['orderno'].'" />
			<input type="hidden" name="paymentno" value="'.$send['paymentno'].'" />
			<input type="hidden" name="validationno" value="'.$send['validationno'].'" />
			<input type="hidden" name="service_type" value="'.$send['service_type'].'" />
			<input type="hidden" name="tempvar" value="'.$send['tempvar'].'" />
			<input type="hidden" name="url" value="'.$send['url'].'" />
			<input type="hidden" name="online_url" value="'.$send['online_url'].'" />
			<input type="hidden" name="trade_describe" value="'.$send['trade_describe'].'" />
			<input type="hidden" name="payment_cpname" value="'.$send['payment_cpname'].'" />
			<input type="hidden" name="cp_remark01" value="'.$send['cp_remark01'].'" />
			<input type="hidden" name="cp_remark02" value="'.$send['cp_remark02'].'" />
			<input type="hidden" name="cp_remark03" value="'.$send['cp_remark03'].'" />
			<input type="hidden" name="show_type" value="'.$send['show_type'].'" />
			<input type="hidden" name="trade_no" value="'.$send['trade_no'].'" />
			<input type="hidden" name="daishou_account" value="'.$send['daishou_account'].'" />
			<input type="hidden" name="shopper_name" value="'.$send['shopper_name'].'" />
			</form>';
			$re['html'] = $html;
		}elseif($inf['way'] == "b"){
			//殺掉不要的參數後 直接送值
			$dels = array('paymentno','validationno','tempvar','url','online_url');
			foreach ($dels as $del) {
				unset($send[$del]);
			}

	        $ch = curl_init();
	        $curl_options = array(
	            CURLOPT_URL => $url, 
	            CURLOPT_PORT => 80, 
	            CURLOPT_HEADER => false,
	            CURLOPT_POST => true, 
	            CURLOPT_POSTFIELDS => http_build_query($send), 
	            CURLOPT_RETURNTRANSFER=>true
	        );
	        curl_setopt_array($ch, $curl_options);
	        $result = curl_exec($ch);
	        curl_close($ch);

	        $xml = parse_xml($result); /*<c2b xmlns="http://7-11.com.tw/online/C2B/OrderInfo/RS">;<status>S</status>;<description></description>;<paymentno>A0111098</paymentno>;<validationno>9165</validationno>;<eshopid>907</eshopid>;<eshopsonid>453</eshopsonid>;<orderno>R13820047470011</orderno>;<payamount>490</payamount>;<daishou_account>00000</daishou_account>;<shopper_name>黃果凍</shopper_name>;</c2b>;*/
			$re['res'] = $result;
			$re['xml'] = $xml;
			$re['code'] = $xml['paymentno'];
			$re['vcode'] = $xml['eshopid'];
		}
		$re['send'] = $send;
	}
	return $re;
}



//解 xml 成 array
function parse_xml($xml){
	$xml_parser = xml_parser_create();
	xml_parse_into_struct($xml_parser, $xml, $xmlvals, $index);
	xml_parser_free($xml_parser);
	$params = array();
	$level = array();
	foreach ($xmlvals as $xml_elem) {
		if ($xml_elem['type'] == 'open') {
			if (array_key_exists('attributes',$xml_elem)) {
				list($level[$xml_elem['level']],$extra) = array_values($xml_elem['attributes']);
			}else{
				$level[$xml_elem['level']] = $xml_elem['tag'];
			}
		}
		if ($xml_elem['type'] == 'complete') {
			$start_level = 1;
			while($start_level < $xml_elem['level']) {
				$params[strtolower($xml_elem['tag'])] = $xml_elem['value'];
				$start_level++;
			}
		}
	}
	return $params;
}








?>
