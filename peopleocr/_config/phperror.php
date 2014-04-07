<?php
	//錯誤訊息設定
	$showerror = array(
		0=>array(0,"",""),
		1=>array(0,"E_ERROR","致命:"),
		4096=>array(0,"E_RECOVERABLE_ERROR","E_RECOVERABLE_ERROR"),
		2=>array(0,"E_WARNING","警告:"),
		4=>array(0,"E_PARSE","E_PARSE"),
		8=>array(0,"E_NOTICE","注意:"),
		2048=>array(0,"E_STRICT","E_STRICT"),
		16=>array(0,"E_CORE_ERROR","E_CORE_ERROR"),
		32=>array(0,"E_CORE_WARNING","E_CORE_WARNING"),
		64=>array(0,"E_COMPILE_ERROR","E_COMPILE_ERROR"),
		128=>array(0,"E_COMPILE_WARNING","E_COMPILE_WARNING"),
		256=>array(0,"E_USER_ERROR","E_USER_ERROR"),
		512=>array(0,"E_USER_WARNING","E_USER_WARNING"),
		1024=>array(0,"E_USER_NOTICE","E_USER_NOTICE")
	);
	//error_reporting(0); 
	set_error_handler("jelly_error");
	function jelly_error($errno , $errstr , $errfile , $errline , $errcontext){
		global $showerror;
		if($showerror[$errno][0]){
			$jellysay = "";
			$jellysay .= $errno."<br />";
			$jellysay .= $showerror[$errno][2]."<br />";
			$jellysay .= "檔案 : ".$errfile."<br />第 ".$errline." 行 <br />";
			$jellysay .= "錯誤訊息 : ".$errstr."<br />";
			//$jellysay .= print_r($errcontext);
			$jellysay .= "<br />";
			echo $jellysay;
			//error_log($jellysay , 3 , "configs/errors.log");
		}
	}
?>