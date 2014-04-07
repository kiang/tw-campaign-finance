<?php //中
	if(!($dblink=mysql_connect($db_hostname,$db_username,$db_password))){ echo("mysql connect false"); exit(); }
	
	@ mysql_query("SET NAMES 'UTF8'");
	@ mysql_query("SET CHARACTER_SET_CLIENT=utf8");
	@ mysql_query("SET CHARACTER_SET_RESULTS=utf8");
	
	if (!mysql_select_db($db_database,$dblink)){ echo("select database false"); exit(); }
	
	function myquery($sql){
		if(_jellydev){
			$res = mysql_query($sql)or die($sql."<br /><br />".mysql_error());
		}else{
			$res = mysql_query($sql);
		}
		return $res;
	}

	function webquery($sql){
		if(preg_match('/union/i',$sql)){
			mysql_query("insert into oj_sqlinjection set fuip = '".$_SERVER['REMOTE_ADDR']."' , fuurl = '".mysql_real_escape_string($_SERVER['REQUEST_URI'])."' , fuget = '".mysql_real_escape_string($_SERVER['QUERY_STRING'])."' , fusql = '".mysql_real_escape_string($sql)."' ");
			return NULL;
		}
		$match_arr = array();
		$match_arr[] = "\\sselect";
		//$match_arr[] = "select\\s";
		$match_arr[] = "\\sselect\\s";
		$match_arr[] = "\\supdate";
		//$match_arr[] = "update\\s";
		$match_arr[] = "\\supdate\\s";
		$match_arr[] = "\\sinsert";
		//$match_arr[] = "insert\\s";
		$match_arr[] = "\\sinsert\\s";
		$match_arr[] = "\\sdelete";
		//$match_arr[] = "delete\\s";
		$match_arr[] = "\\sdelete\\s";
		$match_arr[] = "\\sreplace";
		//$match_arr[] = "replace\\s";
		$match_arr[] = "\\sreplace\\s";
		$match_str = implode("|", $match_arr);
		preg_match_all('/'.$match_str.'/i',$sql,$match);
		$sqlnum = count($match[0]);
		if($sqlnum <= 1){
			$res = mysql_query($sql);
			return $res;
		}else{
			mysql_query("insert into oj_sqlinjection set wsiIp = '".$_SERVER['REMOTE_ADDR']."' , wsiUrl = '".mysql_real_escape_string($_SERVER['REQUEST_URI'])."' , wsiGet = '".mysql_real_escape_string($_SERVER['QUERY_STRING'])."' , wsiSql = '".mysql_real_escape_string($sql)."' ");
			return NULL;
		}
	}
?>
