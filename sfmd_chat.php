<?php
header('Content-type: text/html; charset=utf-8');
//mb_internal_encoding("UTF-8");
require 'sfmd_chat_config.php';//$time

function reg($text,$time,$phpself,$logfile){
	//$ymdhis = gmdate("ymd",$time)."_".gmdate("His",$time);
	//$tim = $time.substr(microtime(),2,3);
	$ymdhis=date('y/m/d H:i:s',$time);
	$name=substr(crypt(md5($_SERVER["REMOTE_ADDR"].'ㄎㄎ'.gmdate("ymd", $time)),'id'),-8);
	$maxlen=strlen($text);//計算字數
	$maxlen_limit=1500;
		if($maxlen>$maxlen_limit){die("字數".$maxlen.">".$maxlen_limit."");}
		//$text=substr($text,0,2);//移除規定字數之後的部份
	$tmp=array();
	$tmp=explode("\n",$text);
		$maxline=count($tmp);//計算行數
		$maxline_limit=15;
		if($maxline>$maxline_limit){die("行數".$maxline.">".$maxline_limit."");}
		//array_splice($tmp,15);//移除陣列第15項之後的部份
	$text=implode("\n",$tmp);
	$text=chra_fix($text);//[自訂函數]轉換成安全字元
	//
	//$logfile="sfmd_chat.log";//req
	$cp = fopen($logfile, "a+") or die('failed');// 讀寫模式, 指標於最後, 找不到會嘗試建立檔案
	rewind($cp); //從頭讀取
	$buf=fread($cp,1000000); //讀取至暫存
	ftruncate($cp, 0); //砍資料至0
	$buf=$ymdhis.",".$name.",".$text."\n".$buf;
	$cellarr=array();
	$cellarr=explode("\n",$buf);
	foreach($cellarr as $key => $value) {
		$cellarr2=explode(",",$cellarr[$key]);
		if($cellarr2[0]==""){unset($cellarr[$key]);}//空白行去除
	}
	array_splice($cellarr,1000);//移除陣列第1000項之後的部份
	$buf=implode("\n",$cellarr);
	fputs($cp, $buf);
	fclose($cp);
	$t_url=$phpself."?".$time;//網址
	die("<html><head><META HTTP-EQUIV=\"refresh\" content=\"2;URL=".$t_url."\"></head>
	<body>成功發文 <a href='".$t_url."'>".$t_url."</a><br>行數:".$maxline."<br>字數:".$maxlen."</body></html>");
}
function view($text,$time,$p2,$phpself,$logfile){
	//$logfile="sfmd_chat.log";
	//chmod($logfile,0666);
	$cp = fopen($logfile, "a+") or die('log讀取錯誤');// 讀寫模式, 指標於最後, 找不到會嘗試建立檔案
	rewind($cp); //從頭讀取
	$buf=fread($cp,1000000); //讀取至暫存
	fclose($cp);
	
	$cellarr=array();
	$cellarr=explode("\n",$buf);
	$page_echo='';$tmp_arr=array();
	$page_echo.="<dl>\n";
	$maxline=count($cellarr);
	$showline=10;//一頁顯示幾筆
	$allpage=ceil($maxline/$showline)+1;
	if($p2==''){$p2=1;}//排除不符合的p2值
	if($p2>$allpage||$p2<0||preg_match("/[^0-9]/",$p2)){$p2=1;}//排除不符合的p2值
	$page_start=($p2-1)*$showline;//起始資料
	$page_end=($p2)*$showline;//尾端資料
	for($i=0;$i<$maxline;$i++){//利用迴圈列所有資料
		if($i>=$page_start && $i<$page_end){
			$tmp_arr=explode(",",$cellarr[$i]);
			//$page_echo.="$i/$p2";
			$page_echo.="<dt>[".$tmp_arr[0]."] ".$tmp_arr[1]."</dt>\n"."<dd><pre>".$tmp_arr[2]."</pre></dd>\n";
		}
	}
	$page_echo.="</dl>\n";
	$page_echo2='';
	$page_echo2.='<hr>';
	$page_echo2.=$maxline."筆".$showline."見";
	for($i=1;$i<$allpage;$i++){//利用迴圈列所有頁數
		if($i==$p2){
			$page_echo2.="<span style='border-radius: 22px; border:1px solid red;background-color:#0ff;'>[<a href='$phpself?p2=$i'>$i</a>]</span>";
		}else{
			$page_echo2.="[<a href='$phpself?p2=$i'>$i</a>]";
		}
		//
	}
	$page_echo2.='<hr>';
	$page_echo=$page_echo2.$page_echo.$page_echo2;
	return $page_echo;
}
$form=<<<EOT
<form action="$phpself" method="post" id='form1' onsubmit="return check2();">
<input type="hidden" name="mode" value="reg">
內文<textarea name="text" cols="48" rows="4" wrap=soft></textarea>
<br/>
<span style="display:none;">
<input type="checkbox" id="chk" name="chk">
<input type="text" name="na" id="na" size="8" value="??">
</span>
<input type="submit" id='send' name="send" value="送出"/><br/>
</form>
<script language="Javascript">
document.getElementById("na").value="3";
document.getElementById("chk").checked=true;
function check(){document.getElementById("send").value="稍後";}
function check2(){
	document.getElementById("send").disabled=true;
	document.getElementById("send").style.backgroundColor="#ff0000";
}
</script>
EOT;
	
switch($mode){
	case 'reg':
		if($na!='3'){die('xna');}
		if(!$chk){die('!chk');}
		if($text==""){die("無內文");}
		reg($text,$time,$phpself,$logfile);
	break;
	default:
		echo htmlstart_parameter(0,$ver);
		echo $form;
		echo view($text,$time,$p2,$phpself,$logfile);
		echo $htmlend;
	break;
}

/*
		//bbcode()
		$string = $tmp_arr[2]; //bbcode目前只使用連結功能
		$string = preg_replace("/(^|[^=\]])(http|https)(:\/\/[\!-;\=\?-\~]+)/si", "\\1<a href=\"\\2\\3\" target=_blank>\\2\\3</a>", $string);
		$string = preg_replace("/\n/si", "<br/>", $string);
		$tmp_arr[2] = $string;
		//bbcode(/)
*/
?>
	
