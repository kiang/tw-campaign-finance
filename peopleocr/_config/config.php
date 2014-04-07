<?php
	session_start(); //中
	header('Content-Type: text/html; charset=utf-8');
	
	//設定路徑
	$developIP = array("127.0.0.1","192.168.7.3","192.168.7.1");
	
	$pathkey = 'manage/';
	$pathurl = explode($pathkey, $_SERVER['REQUEST_URI']);
	$pathbef = "./";
	$webfilepath = "";

	if(isset($_SERVER['HOME'])){
		if(preg_match("/blimp/",$_SERVER['argv'][0])){
			define("__murl",$_SERVER['HOME']."/public_html/_subdomains/blimp/manage/");
			define("__curl",$_SERVER['HOME']."/public_html/_subdomains/blimp/manage/aalibojelly/configs/");
			define("__furl",$_SERVER['HOME']."/public_html/_subdomains/blimp/manage/aalibojelly/function/");
			define("__lurl",$_SERVER['HOME']."/public_html/_subdomains/blimp/manage/aalibojelly/language/");
			define("__uurl",$_SERVER['HOME']."/public_html/_subdomains/blimp/manage/upload/");
			define("__wurl",$_SERVER['HOME']."/public_html/_subdomains/blimp/");
		}else{
			define("__murl",$_SERVER['HOME']."/public_html/manage/");
			define("__curl",$_SERVER['HOME']."/public_html/manage/aalibojelly/configs/");
			define("__furl",$_SERVER['HOME']."/public_html/manage/aalibojelly/function/");
			define("__lurl",$_SERVER['HOME']."/public_html/manage/aalibojelly/language/");
			define("__uurl",$_SERVER['HOME']."/public_html/manage/upload/");
			define("__wurl",$_SERVER['HOME']."/public_html/");
		}
		$webfilepath = "cron";
	}elseif(preg_match("/manage/",$_SERVER['REQUEST_URI'])){
		$pathlevel = preg_match_all("/\//",$pathurl[1] , $no);
		for($i=0;$i<$pathlevel;$i++){ $pathbef .= "../"; }
		define("__murl",$pathbef);
		define("__curl",__murl."aalibojelly/configs/");
		define("__furl",__murl."aalibojelly/function/");
		define("__lurl",__murl."aalibojelly/language/");
		define("__uurl",__murl."upload/");	
		define("__wurl","./../../../");
		$webfilepath = "manage";
	}elseif(preg_match("/welcome/",$_SERVER['REQUEST_URI'])){
		$pathbef .= "../manage/";
		define("__murl",$pathbef);
		define("__curl",__murl."aalibojelly/configs/");
		define("__furl",__murl."aalibojelly/function/");
		define("__lurl",__murl."aalibojelly/language/");
		define("__uurl",__murl."upload/");	
		$webfilepath = "welcome";
	}else{
		$pathbef .= "./manage/";
		define("__murl",$pathbef);
		define("__curl",__murl."aalibojelly/configs/");
		define("__furl",__murl."aalibojelly/function/");
		define("__lurl",__murl."aalibojelly/language/");
		define("__uurl",__murl."upload/");	
		define("__rurl","./");
		define("__wurl","./");
		$webfilepath = "frontend";
	}

	//資料庫
	if($_SERVER['DOCUMENT_ROOT'] == "W:/htdocs" or $_SERVER['DOCUMENT_ROOT'] == "D:/htdocs"){
		$db_hostname="localhost"; $db_username="jellyhttp"; $db_password="19820115"; $db_database="twcampaignfinance";
	}else{
		//$db_hostname="localhost"; $db_username="gun3com_main2013"; $db_password="e2JOsQQTmMNd"; $db_database="gun3com_main2013blimp";	
		$db_hostname="localhost"; $db_username="twcampaignfinance"; $db_password="twcampaignfinance"; $db_database="twcampaignfinance";		
	}
	
	//測試 ip 略過權限管理 正式主機需移除 , 設定略過權限判斷的測試頁面或介接系統頁面
	$ojellyIP = array("114.34.79.88");
	$developIP = array_merge($developIP , $ojellyIP);
	$developPage = array("order-return-test.php" , "savepost.php" , "paid-system.php");
	$develop_status = (in_array($_SERVER['REMOTE_ADDR'] , $developIP))?true:false;
	
	//載入其它須要的檔案
	require_once("mysqlquery.php");
	require_once("phperror.php");
	require_once("function.php");
	// require_once(__furl."function.php");
	// require_once(__furl."emailcrontab.php");
	
	// require_once(__uurl."webset/webset_sql.php");

	// if($webfilepath == "manage"){
	// 	require_once(__curl."manage_defaul.php");
	// 	require_once(__lurl."lang.php");
	// }elseif($webfilepath == "welcome"){

	// }elseif(in_array($webfilepath , array("frontend"))){
	// 	require_once(__curl."web_defaul.php");
	// }
	


	//資料庫前置
    define("__table_prefix", "pocr_");








	
?>
