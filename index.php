<?php
//
// by james@ustc.edu.cn 2015
// https://github.com/bg6cq/noc
//
include("db.php");

$q = "select info from sysinfo where name='version'";
$r = mysql_fetch_row(mysql_query($q));
$sysversion = $r[0];
$q = "select info from sysinfo where name='title'";
$r = mysql_fetch_row(mysql_query($q));
$systitle = $r[0];
$q = "select info from sysinfo where name='lxr'";
$r = mysql_fetch_row(mysql_query($q));
$syslxr = $r[0];

session_start();

function checkvalue($str) {
	for ($i = 0; $i<strlen($str); $i++) {
        	if (ctype_alnum($str[$i]))  continue;
		if (strchr("@-_ ./:", $str[$i])) continue;
        	echo "$str中第 $i 非法字符 $str[$i]";
		exit(0);
	}
}

function safe_get($str) {
	@$x = $_REQUEST[$str];
	checkvalue($x);
	return $x;
}

function safe_get2($str) {
	@$x = $_REQUEST[$str];
	return mysql_escape_string($x);
}

function changehist ($q) {
	echo "修改日志<p><table border=1 cellspacing=0>";
        echo "<tr><th>时间</th><th>修改内容</th></tr>\n";
        $rr = mysql_query($q);
        while ($r=mysql_fetch_row($rr)) 
                echo "<tr><td>$r[1]</td><td>$r[3]<br>$r[4]</td></tr>\n";
        echo "</table>";
}

function lxr_select($lxrid) {
	echo "<select name=lxr>";
	if ($lxrid=="")
		echo "<option value=\"\" selected=\"selected\"></option>";
	else
		echo "<option value=\"\"></option>";
	$q = "select * from lxr order by dept, name";
	$rr = mysql_query($q);
	while ($r=mysql_fetch_row($rr)) {
		echo "<option value=\"$r[0]\"";
		if ($lxrid ==$r[0]) echo " selected=\"selected\"";
		echo ">$r[1]/$r[2]</option>";
	}       
	echo "</select>";
}

function lxr_display($lxrid) {
	if ($lxrid=="") 
		echo "";
	else {
		$q = "select * from lxr where id='$lxrid'";
		$r = mysql_fetch_row(mysql_query($q));
                if ($r) {
			echo "<a class =\"lxrtips\" href=index.php?cmd=lxr_detail&id=$r[0] target=_blank>$r[1]/$r[2]";
			echo "<span>部门:$r[1]<br>姓名:$r[2]<br>电话:$r[3]<br>手机:$r[4]<br>邮箱:$r[5]<br>Q Q :$r[6]<br>备注:$r[7]</span></a>";
		} else 
			echo $lxrid.":未知联系人";
        } 
}

function ticket_system_select($systemid) {
	echo "故障系统: <select name=system>";
	if ($systemid=="")
		echo "<option value=\"\" selected=\"selected\"></option>";
	else
		echo "<option value=\"\"></option>";
	$q = "select id,`desc` from ticket_system order by sortid";
	$rr = mysql_query($q);
	while ($r=mysql_fetch_row($rr)) {
		echo "<option value=\"$r[0]\"";
		if ($systemid==$r[0]) echo " selected=\"selected\"";
		echo ">$r[1]</option>";
	}       
	echo "</select><br>";
}

function ticket_system_display($systemid) {
	if ($systemid=="") 
		echo "";
	else {
		$q = "select `desc` from ticket_system where id='$systemid'";
		$r = mysql_fetch_row(mysql_query($q));
                if ($r) {
			echo $r[0];
		} else 
			echo $systemid.":未知系统";
        } 
}

function ticket_reason_select($reasonid) {
	echo "故障类型: <select name=reason>";
	if ($reasonid=="")
		echo "<option value=\"\" selected=\"selected\"></option>";
	else
		echo "<option value=\"\"></option>";
	$q = "select id,`desc` from ticket_reason order by sortid";
	$rr = mysql_query($q);
	while ($r=mysql_fetch_row($rr)) {
		echo "<option value=\"$r[0]\"";
		if ($reasonid==$r[0]) echo " selected=\"selected\"";
		echo ">$r[1]</option>";
	}       
	echo "</select><br>";
}

function ticket_reason_display($reasonid) {
	if ($reasonid=="") 
		echo "";
	else {
		$q = "select `desc` from ticket_reason where id='$reasonid'";
		$r = mysql_fetch_row(mysql_query($q));
                if ($r) {
			echo $r[0];
		} else 
			echo $reasonid.":未知类型";
        }
}

function ticket_level_select($levelid) {
	echo "故障级别: <select name=level>";
	if ($levelid=="")
		echo "<option value=\"\" selected=\"selected\"></option>";
	else
		echo "<option value=\"\"></option>";
	$q = "select id,`desc` from ticket_level";
	$rr = mysql_query($q);
	while ($r=mysql_fetch_row($rr)) {
		echo "<option value=\"$r[0]\"";
		if ($levelid==$r[0]) echo " selected=\"selected\"";
		echo ">$r[1]</option>";
	}       
	echo "</select><br>";
}

function ticket_level_display($levelid) {
	if($levelid=="")
		echo "";
	else {
		$q = "select `desc` from ticket_level where id='$levelid'";
		$r = mysql_fetch_row(mysql_query($q));
                if ($r) {
			if ($levelid==2) 
				echo "<font color=green>$r[0]</font>";
			else if ($levelid>=3) 
				echo "<font color=red>$r[0]</font>";
			else 
				echo $r[0];
		} else 
			echo $levelid.":未知";
        }
}

function op_display($op) {
	if ($op=="") 
		echo "";
	else {
		$q = "select truename from user where email='$op'";
		$r = mysql_fetch_row(mysql_query($q));
                if ($r) {
			echo $r[0];
		} else 
			echo $op.":未知管理员";
        }
}

// 0 no right
// 1 readonly right
// 2 add right
// 3 full right 

function getuserright($module) {
	$user = $_SESSION["user"];
	$q = "select isadmin from user where email='$user'";
	$r = mysql_fetch_row(mysql_query($q));
	if ($r[0]=="1") // super user
		return 3;  // full right
	$q = "select max(`right`) from userright where user='$user' and ( module='ALL' or module='$module')";
	$r = mysql_fetch_row(mysql_query($q));
	return intval($r[0]);
}

function checkright($module, $right) {
	if (getuserright($module)<$right) {
		echo "无权限";
		exit(0);
	}
}

// "0" or "1"
function getticketdisplaymode(){
	$user = $_SESSION["user"];
        $q = "select value from userpref where user='$user' and name='ticketdisplaymode'";
	$r = mysql_fetch_row(mysql_query($q));
	if( $r[0]=="1" ) 
                	return "1";  
       	return "0";  
}


// "0" or "1"
function getdisplayopip(){
        $q = "select info from sysinfo where name='displayopip'";
	$r = mysql_fetch_row(mysql_query($q));
	if( $r[0]=="1" ) 
               	return "1";  
       	return "0";  
}

$cmd = safe_get("cmd");

if ($cmd=="file_down") {
	$login = $_SESSION["login"];
	if ($login<>1) {   // 用户没有登录
		echo "请登录后下载";
		exit(0);
	}
	if (getuserright("info")<1) {
		echo "无权限下载";
		exit(0);
	}
	$fid = safe_get("fid");
	$q = "select * from file where id=".$fid;
	$r = mysql_fetch_row(mysql_query($q));
	$file = $uploaddir."/".$fid;
	if (file_exists($file)) {
    		header("Content-Description: File Transfer");
    		header("Content-Type: application/octet-stream");
		$fn = iconv("gb2312","utf-8",$r[2]);
    		header("Content-Disposition: attachment; filename=$fn");
    		header("Expires: 0");
    		header("Cache-Control: must-revalidate");
    		header("Pragma: public");
    		header("Content-Length: $r[3]");
    		readfile($file);
    		exit(0);
	}else  {
		echo "文件不存在<p>";
		exit(0);
	}
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" /> 
<link href="table.css" type="text/css" rel="stylesheet" /> 
<title><?php echo $systitle; ?></title>
</head>
<body>

<?php

if ($cmd=="logout") {
	$_SESSION["login"] = 0;
	$_SESSION["isadmin"] = 0;
	$_SESSION["ipfilter"] = "";
	$_SESSION["serverfilter"] = "";
	echo "<p>已经退出登录";
}

if ($cmd=="login") {
	$id = safe_get("id");
	$pass = $_REQUEST["pass"];
	if ($id<>"") {
		$q = "select isadmin,truename,pop3server from user where email='$id'";
		$r = mysql_fetch_row(mysql_query($q));
		if($r) {
			$_SESSION["isadmin"] = $r[0];
			$_SESSION["truename"] = $r[1];
			$_SESSION["ipfilter"] = "";
			$_SESSION["serverfilter"] = "";
			$r = imap_open("{".$r[2].":110/pop3/novalidate-cert}INBOX",$id,$pass,0,1);
			if ($r) {
				$_SESSION["login"] = 1;
				$_SESSION["user"] = $id;
				echo "登录正常,请选择上面的各项菜单";
				echo "<script language=JavaScript>parent.location='index.php?cmd=jifang';</script>";
				exit(0);
			}
			echo "<font color=red>密码错误，请检查</font>";
		} else
			echo "<font color=red>用户名不存在，请联系管理员</font>";
	}
} // end cmd==login

@$login = $_SESSION["login"];
@$isadmin = $_SESSION["isadmin"];
if ($login<>1) {   // 用户没有登录
	$login = 0;
	$_SESSION["login"] = 0;
	echo "<p>系统版本: $sysversion";
	echo "<p>有任何问题请联系 $syslxr";
	echo "<p>";
	echo "请输入邮箱和密码登录<p>";
	echo "系统会连接到邮件POP3服务器登录验证密码";
	echo "<form action=index.php method=post>";
	echo "<input name=cmd type=hidden value=login>";
	echo "用户邮箱:<input name=id><br>";
	echo "邮箱密码:<input name=pass type=password><p>";
	echo "<input type=submit value=\"登 录\"></form>\n";
	exit(0);
} // login <> 1

echo "<ul class=\"nav\">\n";

if (getuserright("jifang")>0)  {
	echo "<li><dl>";
	echo "<dt><a href=index.php?cmd=jifang>机房巡检</a></dt>";
	echo "<dd><a href=index.php?cmd=jifang&all=yes>所有记录</a></dd>";
	if (getuserright("jifang")>=2) 
		echo "<dd><a href=index.php?cmd=jifang_add>新增记录</a></dd>";
	echo "</dl></li>\n";
}
if (getuserright("ticket")>0) {
	echo "<li><dl>";
	echo "<dt><a href=index.php?cmd=ticket>故障处理</a></dt>";
	echo "<dd><a href=index.php?cmd=ticket&all=yes>所有记录</a></dd>";
	echo "<dd><a href=index.php?cmd=ticket_stat>故障统计</a></dd>";
	if (getuserright("ticket")>=2) 
	  	echo "<dd><a href=index.php?cmd=ticket_add>新增记录</a></dd>";
	echo "</dl></li>\n";
}
if (getuserright("server")>0) {
	echo "<li><dl>";
	echo "<dt><a href=index.php?cmd=cab_list>服务器管理</a></dt>";
	echo "<dd><a href=index.php?cmd=cab_server_list>服务器</a></dd>";
	echo "<dd><a href=index.php?cmd=server_filter>服务器显示筛选</a></dd>";
	if (getuserright("server")>=2) {
		echo "<dd><a href=index.php?cmd=cab_add>新增机柜</a></dd>";
		if ($cmd=='cabinfo_list')  {
			echo "<dd><a href=index.php?cmd=server_add&cabid=";
			echo safe_get("cabid");
			echo ">新增服务器</a></dd>";
		}
	}
	echo "</dl></li>\n";
}
if (getuserright("odf")>0) {
	echo "<li><dl>";
	echo "<dt><a href=index.php?cmd=odf_list>ODF管理</a></dt>";
	if (getuserright("odf")>=2) 
		echo "<dd><a href=index.php?cmd=odf_add>新增ODF</a></dd>";
	echo "</dl></li>\n";
}
if (getuserright("ip")>0) {
	echo "<li><dl>";
	echo "<dt><a href=index.php?cmd=ip>IP管理</a></dt>";
	echo "<dd><a href=index.php?cmd=ip_filter>IP显示筛选</a></dd>";
	if (getuserright("ip")>=2) 
	echo "<dd><a href=index.php?cmd=ip_add>新增IP地址</a></dd>";
	echo "</dl></li>\n";
}
if (getuserright("vm")>0) {
	echo "<li><dl>";
	echo "<dt><a href=index.php?cmd=vm>VM管理</a></dt>";
	if (getuserright("vm")>=2)
		echo "<dd><a href=index.php?cmd=vm_host_modi>新增VM</a></dd>";
	echo "<dd><a href=index.php?cmd=vm_c>VM集群管理</a></dd>";
	if (getuserright("vm")>=2) 
		echo "<dd><a href=index.php?cmd=vm_c_add>新增VM集群</a></dd>";
	echo "</dl></li>\n";
}
if (getuserright("lxr")>0){
	echo "<li><dl>";
	echo "<dt><a href=index.php?cmd=lxr>联系人</a></dt>";
	if (getuserright("lxr")>=2) 
		echo "<dd><a href=index.php?cmd=lxr_add>新增联系人</a></dd>";
	echo "</dl></li>\n";
}
if (getuserright("info")>0){
	echo "<li><dl>";
	echo "<dt><a href=index.php?cmd=info>常用信息</a></dt>";
	if (getuserright("info")>=2) 
		echo "<dd><a href=index.php?cmd=info_add>新增常用信息</a></dd>";
	echo "</dl></li>\n";
}
if (getuserright("user")>0){
	echo "<li><dl>";
	echo "<dt><a href=index.php?cmd=user>用户管理</a></dt>";
	if (getuserright("user")>=2) 
		echo "<dd><a href=index.php?cmd=user_add>新增用户</a></dd>";
	echo "</dl></li>\n";
}
if (getuserright("sysinfo")>0) {
	echo "<li><dl>";
	echo "<dt><a href=index.php?cmd=sysinfo>系统管理</a></dt>";
	echo "</dl></li>\n";
}

/*
echo "<li><dl>";
echo "<dt><a href=index.php?cmd=user_pref>个人设置</a></dt>";
echo "</dl></li>\n";

*/

echo "<li><dl>";
echo "<dt><a href=index.php?cmd=logout>退出</a></dt>";
echo "</dl>";
echo "</li>\n";
echo "</ul>\n";
echo "<div id=\"navbg\"></div><p>\n";

if ($cmd=="" ) 
	$cmd = "jifang";

// JIFANG

if ($cmd=="jifang_new") {
	checkright("jifang",2);
	$displayopip = getdisplayopip();
	$huanjing = safe_get("huanjing");
	$server = safe_get("server");
	$msg = safe_get2("msg");
	$ip = $_SERVER['REMOTE_ADDR'];
	if ($displayopip)
		$ip = $_SERVER['REMOTE_ADDR'];
	else $ip="";
	$q = "insert into jifang_daily(tm,huanjing,server,msg,op,ip) values(now(),$huanjing,$server,'$msg','".$_SESSION["user"]."','$ip')";
	mysql_query($q);
	$cmd = "jifang";
}  else if ($cmd=="jifang_modi_do") {
	checkright("jifang",3);
	$id = safe_get("id");
	$huanjing = safe_get("huanjing");
	$server = safe_get("server");
	$msg = safe_get2("msg");
	$q = "update jifang_daily set huanjing=$huanjing,server=$server,msg='$msg' where id=$id";
	mysql_query($q);
	$cmd = "jifang";
} else if ($cmd=="jifang_add") {
	if (getuserright("jifang")>=2) {
		echo "新增机房巡检记录<p>";
		echo "<form action=index.php method=post>";
		echo "<input name=cmd value=jifang_new type=hidden>";
		echo "<table width=100%>";
    		echo "<tr><td width=100>环境状况: </td><td>正常<input type=radio name=huanjing value=1 checked> &nbsp; &nbsp; 异常<input type=radio name=huanjing value=0></td></tr>";
    		echo "<tr><td>服务器状况: </td><td>正常<input type=radio name=server value=1 checked> &nbsp; &nbsp; 异常<input type=radio name=server value=0></td></tr>";
		echo "<tr><td>存在问题:</td><td><input type=text size=200 name=msg></td></tr>";
		echo "</table><p>";
		echo "<input type=submit value=新增机房巡检记录>";
		echo "</form>";
	}
	exit(0);
} else if ($cmd=="jifang_modi") {
	checkright("jifang",3);
	echo "<p>修改机房巡检记录<p>";
	$id = safe_get("id");
	if ($id) {
		$q = "select id,tm,huanjing,server,msg from jifang_daily where id=".$id;
		$rr = mysql_query($q);
		$r = mysql_fetch_row($rr);
		echo "<p>";
		echo "<form action=index.php method=post>";
		echo "<input name=cmd value=jifang_modi_do type=hidden>";
		echo "<input name=id value=$r[0] type=hidden>";
		echo "<table width=100%>";
    		echo "<tr><td width=100>时间:</td><td>$r[1]</td></tr>";
    		echo "<tr><td>环境状况:</td><td>正常<input type=radio name=huanjing value=1";
		if ($r[2]=="1") echo " checked";
		echo "> &nbsp; &nbsp; 异常<input type=radio name=huanjing value=0";
		if ($r[2]=="0") echo " checked";
		echo "></td></tr>";
    		echo "<tr><td>服务器状况:</td><td>正常<input type=radio name=server value=1";
		if ($r[3]=="1") echo " checked";
		echo "> &nbsp; &nbsp; 异常<input type=radio name=server value=0";
		if ($r[3]=="0") echo " checked";
		echo "></td></tr>";
		echo "<tr><td>存在问题:</td><td><input type=text size=200 name=msg value=\"$r[4]\"></td></tr>";
		echo "</table>";
    		echo "<input type=submit value=修改机房巡检记录></form>";
	}
	exit(0);
}
if ($cmd=="jifang") {
	checkright("jifang",1);
	$displayopip = getdisplayopip();
	if (safe_get("all")=="yes")
		$q = "select id,tm,huanjing,server,msg,op,ip from jifang_daily order by id desc";
	else
		$q = "select id,tm,huanjing,server,msg,op,ip from jifang_daily order by id desc limit 30";
	$rr = mysql_query($q);
	echo "<table border=1 cellspacing=0>";
	echo "<tr><th>序号</th><th>时间</th><th>环境</th><th>服务器</th><th>事件描述</th><th>实施人</th>";
	if ($displayopip) 
		echo "<th>IP</th>";
	echo "</tr>";
	$count = 0;
	while ($r=mysql_fetch_row($rr)){
		$count++;
		echo "<tr>";
		if (getuserright("jifang")>=3) 
			echo "<td align=center><a href=index.php?cmd=jifang_modi&id=$r[0]>$count</a></td>";
		else 
			echo "<td align=center>$count</td>";
		echo "<td nowrap=\"nowrap\">".$r[1]."</td>";
		echo "<td>";
		if ($r[2]==0) echo "<font color=red>异常</font>";
		else echo "正常";
		echo "</td>";
		echo "<td align=center>";
		if ($r[3]==0) echo "<font color=red>异常</font>";
		else echo "正常";
		echo "</td>";
		echo "<td>$r[4]</td>";
		echo "<td>";
		op_display($r[5]);
		echo "</td>";
		if ($displayopip) 
			echo "<td>$r[6]</td>";
		echo "</tr>";
		echo "\n";
	}
	echo "</table>\n";
	exit(0);
} // end cmd==jifang

// TICKET

if ($cmd=="ticket_new") {
	checkright("ticket",2);
	$st = safe_get("st");
	$system = safe_get("system");
	$reason = safe_get("reason");
	$level = safe_get("level");
	$memo = safe_get2("memo");
	$memo2 = safe_get2("memo2");
	$isend = safe_get("isend");
	if ($isend ) 
		$q = "insert into ticket (st,et,system,reason,level,memo,op) values('$st','$st','$system','$reason','$level','$memo','".$_SESSION["user"]."')";
	else
		$q = "insert into ticket (st,et,system,reason,level,memo,op) values('$st','0-0-0 00:00:00','$system','$reason','$level','$memo','".$_SESSION["user"]."')";
	mysql_query($q);
	$q = "SELECT LAST_INSERT_ID()";
	$r = mysql_fetch_row(mysql_query($q));	
	$q = "insert into ticketdetail (tid,tm,memo,op) values($r[0],'$st','$memo2','".$_SESSION["user"]."')";
	mysql_query($q);
	$cmd = "ticket";
}  else if ($cmd=="ticket_modi_do") {
	checkright("ticket",3);
	$id = safe_get("id");
	$st = safe_get("st");
	$et = safe_get("et");
	$system = safe_get("system");
	$reason = safe_get("reason");
	$level = safe_get("level");
	$memo = safe_get2("memo");
	$q = "update ticket set st='$st',et='$et',system='$system',reason='$reason',level='$level',memo='$memo' where id=$id";
	mysql_query($q);
	$cmd = "ticket";
} else if($cmd=="ticketdetail_modi_do") {
	checkright("ticket",3);
	$tid = safe_get("tid");
	$did = safe_get("did");
	$tm = safe_get("tm");
	$memo = safe_get2("memo");
	$q = "update ticketdetail set tm='$tm',memo='$memo' where id=$did";
	mysql_query($q);
	$isend = safe_get("isend");
	if ($isend) {
		$q = "update ticket set et='$tm' where id=$tid";
		mysql_query($q);
	}
	$cmd = "ticket";
} else if ($cmd=="ticketdetail_new") {
	checkright("ticket",2);
	$tid = safe_get("tid");
	$tm = safe_get("tm");
	$memo = safe_get2("memo");
	$q = "insert into ticketdetail (tid,tm,memo,op) values($tid,'$tm','$memo','".$_SESSION["user"]."')";
	mysql_query($q);
	$isend = safe_get("isend");
	if ($isend)  {
		$q = "update ticket set et='$tm' where id=$tid";
		mysql_query($q);
	}
	$cmd = "ticket";
} else if ($cmd=="ticket_add") {
	echo "<p>新增故障处理事件记录<p>";
	if (getuserright("ticket")>=2) {
		echo "<form action=index.php method=post>";
		echo "<input name=cmd value=ticket_new type=hidden>";
		echo "开始时间: <input name=st value=\"";
		echo strftime("%Y-%m-%d %H:%M:00",time());
		echo "\"><br>";
		ticket_system_select("");
		ticket_reason_select("");
		ticket_level_select("");
		echo "事件描述: <input name=memo><br>";
		echo "处理描述: <input name=memo2 size=100><br>";
		echo "一次性事件，直接更新结束时间:<input type=checkbox name=isend value=1><p>";
		echo "<input type=submit value=新增故障处理事件记录>";
		echo "</form>";
	}
	exit(0);
} else if ($cmd=="ticket_modi") {
	$id = safe_get("id");
	$did = safe_get("did");
	if ($did && (getuserright("ticket")>=3)) {
		$q = "select id,tid,tm,memo from ticketdetail where id=".$did;
		$r = mysql_fetch_row(mysql_query($q));
		echo "<p>";
		echo "修改故障处理过程记录<br>";
		echo "<form action=index.php method=post>";
		echo "<input name=cmd value=ticketdetail_modi_do type=hidden>";
		echo "<input name=tid value=$r[1] type=hidden>";
		echo "<input name=did value=$r[0] type=hidden>";
    		echo "时间:<input name=tm value=\"$r[2]\"><br>";
    		echo "描述:<input name=memo value=\"$r[3]\" size=100><br>";
		echo "处理结束,更新结束时间:<input type=checkbox name=isend value=1><p>";
    		echo "<input type=submit value=修改故障处理过程记录></form>";
	} else if ($id) {
		$q="select id,st,et,system,reason,level,memo from ticket where id=".$id;
		$r = mysql_fetch_row(mysql_query($q));
		echo "<p>";
		if (getuserright("ticket")>=3) {
			echo "修改故障处理信息<br>";
			echo "<form action=index.php method=post>";
			echo "<input name=cmd value=ticket_modi_do type=hidden>";
			echo "<input name=id value=$r[0] type=hidden>";
    			echo "开始时间: <input name=st value=\"$r[1]\"><br>";
    			echo "结束时间: <input name=et value=\"$r[2]\"><br>";
			ticket_system_select($r[3]);
			ticket_reason_select($r[4]);
			ticket_level_select($r[5]);
    			echo "事件描述: <input name=memo value=\"$r[6]\"><p>";
    			echo "<input type=submit value=修改故障处理信息></form>";
		}
		if (getuserright("ticket")>=2) {
			echo "新增处理过程描述<br>";
			echo "<form action=index.php method=post>";
			echo "<input name=cmd value=ticketdetail_new type=hidden>";
			echo "<input name=tid value=$r[0] type=hidden>";
			echo "时间: <input name=tm value=\"";
			echo strftime("%Y-%m-%d %H:%M:00",time());
			echo "\"><br>";
			echo "处理描述: <input name=memo size=100><br>";
			echo "处理结束,更新结束时间:<input type=checkbox name=isend value=1><p>";
			echo "<input type=submit value=新增处理描述>";
			echo "</form>";
		}
	}
	exit(0);
} else if ($cmd=="ticket_stat") {
	for ($year=date('Y'); $year>=2015; $year--) {
		echo "<table>\n";
		echo "<tr><td colspan=4 align=center>".$year."年故障统计</td></tr>\n";
		echo "<tr><th>相关系统</th><th>故障原因</th><th>故障级别</th><th>出现次数</th></tr>\n";
		$q = "select system,reason,level,count(*) from ticket where year(st)=$year group by system,reason,level";
		$rr = mysql_query($q);
		while ($r=mysql_fetch_row($rr)){
			echo "<tr><td>";
			ticket_system_display($r[0]);
			echo "</td><td>";
			ticket_reason_display($r[1]);
			echo "</td><td>";
			ticket_level_display($r[2]);
			echo "</td><td align=center>";
			echo $r[3];
			echo "</td></tr>\n";
		}
		echo "</table><p>";
	}
	exit(0);
}

if ($cmd=="ticket") {
	checkright("ticket",1);
	if (safe_get("all")=="yes")
		$q = "select id,st,et,system,reason,level,memo,UNIX_TIMESTAMP(et)- UNIX_TIMESTAMP(st) from ticket order by st desc";
	else
		$q = "select id,st,et,system,reason,level,memo,UNIX_TIMESTAMP(et)- UNIX_TIMESTAMP(st) from ticket where ((year(st) = year(now())) or (year(et)=year(now())) or (year(et)=0)) order by st desc";
	$rr = mysql_query($q);

	echo "<table border=1 cellspacing=0>";
	echo "<tr><th>序号</th><th nowrap=\"nowrap\">故障时间</th><th nowrap=\"nowrap\">持续时间</th><th>相关系统</th><th>原因</th><th>级别</th><th>事件描述</th><th nowrap=\"nowrap\">时间</th><th>处理</th><th nowrap=\"nowrap\">实施人</th> </tr>\n";

	$count = 0;
	while ($r=mysql_fetch_row($rr)){
		$count++;
		echo "<tr>";
		$q = "select id,tm,memo,op from ticketdetail where tid='$r[0]' order by tm";
		$rr2 = mysql_query($q);
		$rows = mysql_num_rows($rr2); 
		echo "<td rowspan=$rows align=center>$count</td>";
		if (getuserright("ticket")>=2) {
			echo "<td rowspan=$rows nowrap=\"nowrap\"><a href=index.php?cmd=ticket_modi&id=$r[0]>$r[1]";
			if ($r[1]!=$r[2]) echo "<br>$r[2]";
			echo "</a><br>";
		} else {
			echo "<td rowspan=$rows nowrap=\"nowrap\">$r[1]";
			if ($r[1]!=$r[2]) echo "<br>$r[2]";
		}

		echo "<td rowspan=$rows align=right nowrap=\"nowrap\">";
		if ($r[2]=="0000-00-00 00:00:00")
			echo " ";
		else  if ($r[7]!=0)
			echo round($r[7]/3600,1),"小时";
		echo "</td>";
		echo "<td rowspan=$rows nowrap=\"nowrap\">";
		ticket_system_display($r[3]);
		echo "</td>";
		echo "<td rowspan=$rows nowrap=\"nowrap\">";
		ticket_reason_display($r[4]);
		echo "</td>";
		echo "<td rowspan=$rows nowrap=\"nowrap\">";
		ticket_level_display($r[5]);
		echo "</td>";
	
		echo "<td rowspan=$rows>$r[6]</td>";
		$firstrow = 1;
		while ($r2=mysql_fetch_row($rr2)) {
			if ($firstrow==1) 
				$firstrow = 0;
			else {
				if ($r[3]=="0000-00-00 00:00:00") 
					echo "<tr>";
				else
					echo "<tr>";
			}
			if (getuserright("ticket")>=3) 
				echo "<td nowrap=\"nowrap\"><a href=index.php?cmd=ticket_modi&did=$r2[0]>$r2[1]</a></td>";
			else
				echo "<td nowrap=\"nowrap\">$r2[1]</td>";
			echo "<td>$r2[2]</td>";
			echo "<td>";
			op_display($r2[3]);
			echo "</td>";
			echo "</tr>\n";
		}
	}
	echo "</table>";
	exit(0);
} // end cmd==ticket

// SERVER/CAB

if ($cmd=='cab_new') {
	checkright("server",2);
	$cabid = safe_get("cabid");
	$ps1 = safe_get2("ps1");
	$ps2 = safe_get2("ps2");
	$mgt = safe_get2("mgt");
	$cabuse = safe_get2("cabuse");
	if ($cabid=="") {
		echo "机柜编号不能为空";
		exit(0);
	}
	$q = "insert into JF_CAB values('$cabid','$ps1','$ps2','$mgt','$cabuse')";
	mysql_query($q);
	$cmd = 'cab_list';
} else if ($cmd=='cab_modi_do') {
	checkright("server",3);
	$oldcabid = safe_get("oldcabid");
	$cabid = safe_get("cabid");
	$ps1 = safe_get2("ps1");
	$ps2 = safe_get2("ps2");
	$mgt = safe_get2("mgt");
	$cabuse = safe_get2("cabuse");
	$q = "select * from JF_CAB where CABID ='$oldcabid'";
	$r = mysql_fetch_row(mysql_query($q));
	$q = "insert into hist (tm,oid,old,new) values (now(),'CAB$row[0]','$r[0]/$r[1]/$r[2]/$r[3]/$r[4]','$cabid/$ps1/$ps2/$mgt/$cabuse')";
	mysql_query($q);
	$q = "update JF_CAB set CABID='$cabid',PS1='$ps1',PS2='$ps2',MGT='$mgt',CABUSE='$cabuse' where CABID='$oldcabid'";
	mysql_query($q);
	$cmd = 'cab_list';
} else if ($cmd=="cab_del") {
	checkright("server",3);
	$cabid = safe_get("cabid"); 
	$q = "delete from JF_CAB where CABID='$cabid'";
	mysql_query($q);
	$cmd = 'cab_list';
} else if ($cmd=='cab_modi') {
	checkright("server",3);
	echo "<p>修改机柜信息<p>";
	$cabid = safe_get("cabid");
	echo "<form action=index.php method=post>";
	echo "<input type=hidden name=cmd value=cab_modi_do>";
	echo "<input type=hidden name=oldcabid value=$cabid>";
	$q = "select * from JF_CAB where CABID='$cabid'";
	$rr = mysql_query($q);
	if ($r=mysql_fetch_row($rr)) {
		echo "机柜编号: <input name=cabid value=$r[0]>唯一标识，字母或数字，可以包含-_<br>";
		echo "机柜用途: <input name=cabuse value='$r[4]'><br>";
		echo "主用电源: <input name=ps1 value='$r[1]' size=80><br>";
		echo "备用电源: <input name=ps2 value='$r[2]' size=80><br>";
		echo "责 任 人: <input name=mgt value='$r[3]' size=80><p>";
		echo "<input type=submit name=Submit value=修改机柜信息>";
		echo "</form>";
	}
	changehist("select * from hist where oid like 'CAB$cabid%' order by tm desc");
	echo "<p><hr width=250 align=left>";
	if (getuserright("server")>=3) 
		echo "<a href=index.php?cmd=cab_del&cabid=$r[0] onclick=\"return confirm('删除机柜 $r[0]/$r[4] ?');\">删除机柜: $r[0]/$r[4]</a></td>";
	exit(0);
} else if ($cmd=="cab_add") {
	checkright("server",2);
	echo "<p>新增机柜:<p>";
?>
	<form action=index.php method=post>
	<input type=hidden name=cmd value=cab_new>
	机柜编号: <input name=cabid>唯一标识，字母或数字，可以包含-_ <br> 
	机柜用途: <input name=cabuse size=80> <br>
	主用电源: <input name=ps1 size=80> <br>
	备用电源: <input name=ps2 size=80> <br>
	责 任 人: <input name=mgt size=80> <p>
	<input type="submit" name="Submit" value="新增机柜">
	</form>
<?php
	exit(0);
}
if ($cmd=='cab_list') {
	checkright("server",1);
	echo "<p><table border=1 cellspacing=0>";
	echo "<tr><th>机柜编号</th><th>用途</th><th>责任人</th><th>PS1</th><th>PS2</th><th>设备数</t><th>命令</th></tr>\n";

	$q = "select * from JF_CAB order by CABID";
	$rr = mysql_query($q);
	while ($r=mysql_fetch_row($rr)) {
		echo "<tr><td> "; echo "<a href=index.php?cmd=cabinfo_list&cabid=$r[0]>$r[0]</a>";
		echo "</td><td>"; echo $r[4];
		echo "</td><td>"; echo $r[3];
		echo "</td><td>"; echo $r[1];
		echo "</td><td>"; echo $r[2];
		echo "</td><td align=center>"; 
		$q = "select count(*) from JF_Server where CABID='$r[0]'";
		$r2 = mysql_fetch_row(mysql_query($q));
		echo $r2[0];
		echo "</td><td>"; 
		if (getuserright("server")>=3)
			echo "<a href=index.php?cmd=cab_modi&cabid=$r[0]>修改</a>";
		echo "</td></tr>\n";
	}
	echo "</table>";
} // end cmd = cab_list


if ($cmd=='server_new') {
	checkright("server",2);
	$serverid = safe_get("serverid");
	$cabid = safe_get("cabid");
	$startu = safe_get("startu");
	$endu = safe_get("endu");
	$kvm = safe_get2("kvm");
	$type = safe_get2("type");
	$name = safe_get2("name");
	$user = safe_get2("user");
	$mgt = safe_get2("mgt");
	$ip1 = safe_get2("ip1");
	$ip2 = safe_get2("ip2");
	$mac1 = safe_get2("mac1");
	$mac2 = safe_get2("mac2");
	$sn = safe_get2("sn");
	$connector = safe_get2("connector");
	$comment = safe_get2("comment");
	$q = "insert into JF_Server values('$serverid','$cabid',$startu,$endu,'$kvm','$type','$name','$user','$mgt','$ip1','$ip2','$mac1','$mac2','$sn','$connector','$comment')";
	mysql_query($q);
	$cmd = 'cabinfo_list';
} else if ($cmd=='server_del') {
	checkright("server",3);
	$id = safe_get("serverid"); 
	$q = "delete from JF_Server where ServerID='$id'";
	mysql_query($q);
	$cmd = 'cabinfo_list';
} else if ($cmd=='server_modi_do') {
	checkright("server",3);
	$oldserverid = safe_get("oldserverid");
	$serverid = safe_get("serverid");
	$cabid = safe_get("cabid");
	$startu = safe_get("startu");
	$endu = safe_get("endu");
	if ($startu=="") $startu="1";
	if ($endu=="") $endu="1";
	$kvm = safe_get2("kvm");
	$type = safe_get2("type");
	$name = safe_get2("name");
	$user = safe_get2("user");
	$mgt = safe_get2("mgt");
	$ip1 = safe_get2("ip1");
	$ip2 = safe_get2("ip2");
	$mac1 = safe_get2("mac1");
	$mac2 = safe_get2("mac2");
	$sn = safe_get2("sn");
	$connector = safe_get2("connector");
	$comment = safe_get2("comment");
	$q = "select * from JF_Server where ServerID ='$oldserverid'";
	$r = mysql_fetch_row(mysql_query($q));
	$q = "insert into hist (tm,oid,old,new) values (now(),'SERVER$r[0]','$r[0]/$r[1]/$r[2]/$r[3]/$r[4]/$r[5]/$r[6]/$r[7]/$r[8]/$r[9]/$r[10]/$r[11]/$r[12]/$r[13]/$r[14]/$r[15]','$serverid/$cabid/$startu/$endu/$kvm/$type/$name/$user/$mgt/$ip1/$ip2/$mac1/$mac2/$sn/$connector/$comment')";
	mysql_query($q);
	$q = "update JF_Server set ServerID='$serverid',CABID='$cabid',StartU=$startu,EndU=$endu,KVM='$kvm',Type='$type',NAME='$name',USER='$user',MGT='$mgt',IP1='$ip1',IP2='$ip2',MAC1='$mac1',MAC2='$mac2',SN='$sn',Connector='$connector',Comment='$comment' where ServerID='$oldserverid'";
	mysql_query($q);
	echo "修改完成<p>";
	$cmd = 'cabinfo_list';
} else if ($cmd=='server_modi') {
	checkright("server",3);
	$serverid = safe_get("serverid");
	echo "<form action=index.php method=post>";
	echo "<input type=hidden name=cmd value=server_modi_do>";
	echo "<input type=hidden name=oldserverid value=$serverid>";
        $q = "select * from JF_Server where ServerID='$serverid'";
        $rr = mysql_query($q);
	echo "<table>";
        if ($r=mysql_fetch_row($rr)) {
		echo "<tr>";
        	echo "<td>服务器编号</td><td><input name=serverid value=\"$r[0]\"></td></tr>";
                echo "<td>机柜编号</td><td><input name=cabid value=\"$r[1]\"> </td></tr>";
                echo "<td>开始U</td><td><input name=startu value=\"$r[2]\"></td></tr>";
                echo "<td>结束U</td><td><input name=endu value=\"$r[3]\"></td></tr>";
                echo "<td>KVM</td><td><input name=kvm value=\"$r[4]\"></td></tr>";
                echo "<td>型号</td><td><input name=type value=\"$r[5]\"></td></tr>";
                echo "<td>名称</td><td><input name=name value=\"$r[6]\"></td></tr>";
                echo "<td>用途</td><td><input name=user value=\"$r[7]\"></td></tr>";
                echo "<td>管理员</td><td><input name=mgt value=\"$r[8]\"></td></tr>";
                echo "<td>IP1</td><td><input name=ip1 value=\"$r[9]\"></td></tr>";
                echo "<td>IP2</td><td><input name=ip2 value=\"$r[10]\"></td></tr>";
                echo "<td>MAC1</td><td><input name=mac1 value=\"$r[11]\"></td></tr>";
                echo "<td>MAC2</td><td><input name=mac2 value=\"$r[12]\"></td></tr>";
                echo "<td>SN</td><td><input name=sn value=\"$r[13]\"></td></tr>";
                echo "<td>连接设备</td><td><input name=connector value=\"$r[14]\"></td></tr>";
                echo "<td>备注</td><td><input name=comment value=\"$r[15]\"></td></tr>";
		echo "</table>";
                echo "<input type=submit name=Submit value=修改>";
                echo "</form>";
        }
	changehist("select * from hist where oid like 'SERVER$serverid%' order by tm desc");
	echo "<p><hr width=250 align=left>";
	if (getuserright("server")>=3) 
		echo "<a href=index.php?cmd=server_del&cabid=$r[1]&serverid=$r[0] onclick=\"return confirm('删除服务器 $r[0]/$r[5]/$r[6] ?');\">删除服务器: $r[0]/$r[5]/$r[6]</a></td>";
	exit(0);
} else if ($cmd=='server_add') {
	$cabid = safe_get("cabid");
	checkright("server",2);
       	echo "<form action=index.php method=post>";
       	echo "<input type=hidden name=cmd value=server_new>";
	echo "<table>";
       	echo "<tr><td>服务器编号</td><td><input name=serverid>必须唯一，不能为空</td></tr>";
       	echo "<tr><td>机柜编号</td><td><input name=cabid value=\"$cabid\"></td></tr>";
       	echo "<tr><td>开始U</td><td><input name=startu value=1>数字</td></tr>";
       	echo "<tr><td>结束U</td><td><input name=endu value=1>数字</td></tr>";
       	echo "<tr><td>KVM</td><td><input name=kvm></td></tr>";
       	echo "<tr><td>型号</td><td><input name=type></td></tr>";
       	echo "<tr><td>名称</td><td><input name=name></td></tr>";
       	echo "<tr><td>用途</td><td><input name=user></td></tr>";
       	echo "<tr><td>管理员</td><td><input name=mgt></td></tr>";
       	echo "<tr><td>IP1</td><td><input name=ip1></td></tr>";
       	echo "<tr><td>IP2</td><td><input name=ip2></td></tr>";
       	echo "<tr><td>MAC1</td><td><input name=mac1></td></tr>";
       	echo "<tr><td>MAC2</td><td><input name=mac2></td></tr>";
       	echo "<tr><td>SN</td><td><input name=sn></td></tr>";
       	echo "<tr><td>连接设备</td><td><input name=connector></td></tr>";
       	echo "<tr><td>备注</td><td><input name=comment></td></tr>";
	echo "</table>";
       	echo "<input type=submit name=Submit value=新增服务器>";
       	echo "</form>";
	exit(0);
} else if ($cmd=="server_filter_set") {
	checkright("server",1);
	$serverfilter = safe_get("serverfilter");
	$_SESSION["serverfilter"] = $serverfilter;
	$cmd = "cab_server_list";
} else if ($cmd=="server_filter") {
	checkright("server",1);
	echo "<p>服务器显示筛选";
	echo "<form action=index.php method=post>";
	echo "<input name=cmd value=server_filter_set type=hidden>";
   	echo "服务器筛选字符串: <input name=serverfilter value=\"";
	echo $_SESSION["serverfilter"];
	echo "\"><p>";
	echo "<input type=submit value=设置服务器显示筛选>";
	echo "</form>";
	echo "注：设置后，仅仅显示包含以上字符串的服务器，为空则全部显示";
	exit(0);
} 
if ($cmd=='cab_server_list') {
	checkright("server",1);
	if ($_SESSION["serverfilter"]!="") {
		$str = $_SESSION["serverfilter"];
		echo "仅显示 ";
		echo $str;
		echo " 相关服务器, <a href=index.php?cmd=server_filter>重设筛选</a><p>";
		$q = "select CABID,ServerID,StartU,EndU,KVM,Type,JF_Server.NAME,JF_Server.USER,MGT,IP1,IP2,MAC1,MAC2,SN,Connector,Comment from JF_Server where CABID like '%$str%' or Type like '%$str%' or ServerID like '%$str%' or `NAME` like '%$str%' or `USER` like '%$str%' or MGT like '%$str%' or IP1 like '%$str%' or IP2 like '%$str%' or SN like '%$str%' or Comment like '%$str%' order by CABID,StartU";
	} else 
		$q = "select CABID,ServerID,StartU,EndU,KVM,Type,JF_Server.NAME,JF_Server.USER,MGT,IP1,IP2,MAC1,MAC2,SN,Connector,Comment from JF_Server order by CABID,StartU";
?>		
<p><font size=1>
	<table border=1 cellspacing=0>
	<tr><th>机柜</th><th>U</th><th>KVM</th><th>服务器型号</th><th>服务器描述</th><th>服务器用途</th>
	<th>责任人</th><th>IP地址</th><th>MAC地址</th><th>SN</th><th>网络连接</th><th>备注</th></tr>
<?php
	$rr = mysql_query($q);
	while ($r=mysql_fetch_row($rr)) {
		echo "<tr><td>"; 
		echo $r[0];
		echo "</td><td>";
		if (getuserright("server")>=3) 
			echo "<a href=index.php?cmd=server_modi&serverid=$r[1]>$r[2]</a>";
		else
			echo "$r[2]";
		if ($r[3]!= $r[2]) echo "-".$r[3];
		echo "</td><td rowspan=$r[0]>$r[4]";
		echo "</td><td rowspan=$r[0]>$r[5]";
		echo "</td><td rowspan=$r[0]>$r[6]";
		echo "</td><td rowspan=$r[0]>$r[7]";
		echo "</td><td rowspan=$r[0]>$r[8]";
		echo "</td><td rowspan=$r[0]>$r[9]";
		if ($r[10]!='') { 
			echo "<br>"; 
			echo $r[10]; 
		}
		echo "</td><td rowspan=$r[0]>$r[11]<br>$r[12]";
		echo "</td><td rowspan=$r[0]>$r[13]";
		echo "</td><td rowspan=$r[0]>$r[14]";
		echo "</td><td rowspan=$r[0]>$r[15]";
		echo "</td></tr>\n";
	}
	echo "</table>";
	exit(0);
} // end cmd = 'cab_server_list'
if ($cmd=='cabinfo_list') {
	checkright("server",1);
	$cabid = safe_get("cabid");
	$q = "select *,now() from JF_CAB where CABID='$cabid'";
	$r=mysql_fetch_row(mysql_query($q));
	echo "<table border=1 cellspacing=0 width=800>";
	echo "<tr><td width=20%>";
	echo "机柜编号:";
	echo "</td><td>";
	echo $r[0];
	echo "</td><td width=20%>";
	echo "机柜责任人:";
	echo "</td><td>";
	echo $r[3];
	echo "</td></tr>";
	echo "<tr><td>";
	echo "电源1:";
	echo "</td><td>";
	echo $r[1];
	echo "</td><td>";
	echo "电源2:";
	echo "</td><td>";
	echo $r[2];
	echo "</td></tr>";
	echo "<tr><td>";
	echo "用途:";
	echo "</td><td colspan=3>";
	echo $r[4];
	echo "</td>";
	echo "<tr><td>";
	echo "打印时间:";
	echo "</td><td colspan=3>";
	echo $r[5];
	echo "</td></tr>";
	echo "</table>";
	echo "\n";
?>		
<p><font size=1>
	<table border=1 cellspacing=0>
	<tr><th>U</th><th>KVM</th><th>服务器型号</th><th>服务器描述</th><th>服务器用途</th>
	<th>责任人</th><th>IP地址</th><th>MAC地址</th><th>SN</th><th>网络连接</th><th>备注</th></tr>
<?php
	$q = "select EndU-StartU+1,EndU,KVM,Type,JF_Server.NAME,JF_Server.USER,MGT,IP1,IP2,MAC1,MAC2,SN,Connector,Comment,ServerID from JF_Server where CABID= '$cabid' order by EndU desc";
	$rr = mysql_query($q);
	while ($r=mysql_fetch_row($rr)) {
		echo "<tr><td>"; 
		if (getuserright("server")>=3) 
			echo "<a href=index.php?cmd=server_modi&serverid=$r[14]>$r[1]</a>";
		else
			echo "$r[1]";
		echo "</td><td rowspan=$r[0]>$r[2]";
		echo "</td><td rowspan=$r[0]>$r[3]";
		echo "</td><td rowspan=$r[0]>$r[4]";
		echo "</td><td rowspan=$r[0]>$r[5]";
		echo "</td><td rowspan=$r[0]>$r[6]";
		echo "</td><td rowspan=$r[0]>$r[7]";
		if ($r[8]!='') { 
			echo "<br>"; 
			echo $r[8]; 
		}
		echo "</td><td rowspan=$r[0]>$r[9]<br>$r[10]";
		echo "</td><td rowspan=$r[0]>$r[11]";
		echo "</td><td rowspan=$r[0]>$r[12]";
		echo "</td><td rowspan=$r[0]>$r[13]";
		echo "</td></tr>\n";
		if($r[0]>1) {
			for($i=0;$i<$r[0]-1;$i++) {
				echo "<tr><td>";
				echo $r[1]-$i-1;
			}
		echo "</td></tr>\n";
		
		}

	}
	echo "</table>";

} // end cmd = 'cabinfo_list'

// ODF

if ($cmd=='odf_new') {
	checkright("odf",2);
	$bh = safe_get2("bh");
	$jf = safe_get2("jf");
	$xin = safe_get("xin");
	$use = safe_get2("use");
	$memo = safe_get2("memo");
	$q = "insert into ODF (JF,BH,`USE`,MEMO) values('$jf','$bh','$use','$memo')";
	mysql_query($q);
	for ($i=1; $i<=$xin; $i++) {
		$q="insert into ODFPAN (BH,X) values('$bh',$i)";
		mysql_query($q);
	}
	$cmd = 'odf_list';
} else if ($cmd=='odf_modi_do') {
	checkright("odf",3);
	$odfid = safe_get("odfid");
	$bh = safe_get2("bh");
	$jf = safe_get2("jf");
	$use = safe_get2("use");
	$memo = safe_get2("memo");

	$q = "select * from ODF where id ='$odfid'";
	$r = mysql_fetch_row(mysql_query($q));
	$q = "insert into hist (tm,oid,old,new) values (now(),'ODF$r[0]','$r[1]/$r[2]/$r[3]/$r[4]','$jf/$bh/$use/$memo')";
	mysql_query($q);

	$q = "update ODF set JF='$jf',BH='$bh',`USE`='$use',MEMO='$memo' where id='$odfid'";
	mysql_query($q);
	$cmd = 'odf_list';
} else if ($cmd=='odf_modi') {
	checkright("odf",3);
	$odfid = safe_get("odfid");
	echo "<form action=index.php method=post>";
	echo "<input type=hidden name=cmd value=odf_modi_do>";
	echo "<input type=hidden name=odfid value=$odfid>";
	$q = "select * from ODF where id ='$odfid'";
	$rr = mysql_query($q);
	if ($r=mysql_fetch_row($rr)) {
		echo "机房: <input name=jf value=$r[1]> <br>";
		echo "ODF编号: <input name=bh value=$r[2]><br>";
		echo "用途: <input name=use value='$r[3]' size=80> <br>";
		echo "备注: <input name=memo value='$r[4]' size=80> <br>";
		echo "<input type=submit name=Submit value=修改>";
		echo "</form>";
	}
	exit(0);
} else if ($cmd=='odf_add') {
	checkright("odf",2);
?>
	<p>新增ODF:<p><form action=index.php method=post>
	<input type=hidden name=cmd value=odf_new>
	机房: <input name=jf> <br> 
	ODF编号: <input name=bh size=20><br>
	芯数: <input name=xin size=8 value=12><br>
	用途: <input name=use size=40><br>
	备注: <input name=memo size=40><p>
	<input type="submit" name="Submit" value="新增ODF">
	</form>
<?php
	changehist("select * from hist where oid like 'ODF%' order by tm desc");
	exit(0);
}
if ($cmd=='odf_list') {
	checkright("odf",1);
	echo "<p><table border=1 cellspacing=0>";
	echo "<tr><th>机房</th><th>ODF编号</th><th>用途</th><th>备注</th><th>命令</th></tr>\n";

	$q = "select * from ODF order by JF,BH";
	$count = 0;
	$rr = mysql_query($q);
	while ($r=mysql_fetch_row($rr)) {
		$count++;
		echo "<tr><td> "; 
		echo $r[1];
		echo "</td><td>"; 
		echo "<a href=index.php?cmd=odfpan_list&bh=$r[2]>$r[2]</a>";
		echo "</td><td>"; echo $r[3];
		echo "</td><td>"; echo $r[4];
		echo "</td><td>"; 
		if (getuserright("odf")>=3)
			echo "<a href=index.php?cmd=odf_modi&odfid=$r[0]>修改</a>";
		echo "</td></tr>\n";
	}
	echo "</table>";
	exit(0);
} // end cmd = odf_list

if ($cmd=='odfpan_modi_do') {
	checkright("odf",3);
	$id = safe_get("id");
	$bh = safe_get2("bh");
	$x = safe_get2("x");
	$s = safe_get2("s");
	$dx = safe_get2("dx");
	$use = safe_get2("use");
	$tx = safe_get2("tx");
	$sb = safe_get2("sb");
	$memo = safe_get2("memo");
	$q = "select * from ODFPAN where id ='$id'";
	$r = mysql_fetch_row(mysql_query($q));
	$q = "insert into hist (tm,oid,old,new) values (now(),'PAN$r[0]','$r[1]/$r[2]/$r[3]/$r[4]/$r[5]/$r[6]/$r[7]/$r[8]','$bh/$x/$s/$dx/$use/$tx/$sb/$memo')";
	mysql_query($q);
	$q = "update ODFPAN set BH='$bh',X='$x',S='$s',DX='$dx',`USE`='$use',TX='$tx',SB='$sb',MEMO='$memo' where id='$id'";
	mysql_query($q);
	$cmd = 'odfpan_list';
} else if ($cmd=='odfpan_modi') {
	checkright("odf",3);
	$id = safe_get("id");
	echo "<form action=index.php method=post>";
	echo "<input type=hidden name=cmd value=odfpan_modi_do>";
	echo "<input type=hidden name=id value=$id>";
	$q = "select * from ODFPAN where id ='$id'";
	$rr = mysql_query($q);
	if ($r=mysql_fetch_row($rr)) {
		echo "ODF编号: <input name=bh value=$r[1]><br>";
		echo "芯号: <input name=x value=$r[2]><br>";
		echo "颜色: <input name=s value=$r[3]><br>";
		echo "对方芯号: <input name=dx value=$r[4]><br>";
		echo "用途: <input name=use value='$r[5]' size=80> <br>";
		echo "跳线: <input name=tx value=$r[6]><br>";
		echo "设备: <input name=sb value=$r[7]><br>";
		echo "备注: <input name=memo value='$r[8]' size=80> <br>";
		echo "<input type=submit name=Submit value=修改>";
		echo "</form>";
	}
	echo "<p>";
	$cmd = 'odfpan_list';
	changehist("select * from hist where oid = 'PAN$id' order by tm desc");
}
if ($cmd=='odfpan_list') {
	checkright("odf",1);
	echo "ODF盘信息<p>";
	$bh = safe_get2("bh");
	$q = "select * from ODF where BH='$bh'";
	$r = mysql_fetch_row(mysql_query($q));
	echo "机房: ".$r[1]."<br>";
	echo "ODF编号: ".$r[2]."<br>";
	echo "用途: ".$r[3]."<br>";
	echo "备注: ".$r[4]."<p>";
	echo "<table border=1 cellspacing=0>";
	echo "<tr><th>ODF编号</th><th>芯号</th><th>颜色</ht><th>对方芯号</th><th>用途</th><th>跳线</th>";
	echo "<th>设备</th><th>备注</th></tr>\n";
	$q = "select * from ODFPAN where BH='$bh' order by X";
	$count = 0;
	$rr = mysql_query($q);
	while ($r=mysql_fetch_row($rr)) {
		$count++;
		echo "<tr><td>"; echo $r[1];
		echo "</td><td> "; 
		if (getuserright("odf")>=3) 	
			echo "<a href=index.php?cmd=odfpan_modi&id=$r[0]&bh=$r[1]>$r[2]</a>";
		else
			echo "$r[2]";
		echo "</td><td>$r[3]";
		echo "</td><td>$r[4]";
		echo "</td><td>$r[5]";
		echo "</td><td>$r[6]";
		echo "</td><td>$r[7]";
		echo "</td><td>$r[8]";
		echo "</td></tr>\n";
	}
	echo "</table>";
	exit(0);
} // end cmd = odfpan_list

// IP

function mask_select($selmask) {
        echo "<select name=mask>";
        if ($selmask=="")
                echo "<option value=\"\" selected=\"selected\"></option>";
        else
                echo "<option value=\"\"></option>";
	for ($mask=32;$mask>0;$mask--) {
		$maskstr = long2ip(-1 << (32 - (int)$mask));
		echo "<option value=\"$maskstr\"";
		if ($selmask==$maskstr) echo " selected=\"selected\"";
		echo ">$maskstr/$mask</option>";
	}
	echo "</select>";
}

if ($cmd=="ip_new") {
	checkright("ip",2);
	$ip = safe_get("ip");
	$mask = safe_get("mask");
	$net = safe_get("net");
	$use = safe_get2("use");
	$lxr = safe_get2("lxr");
	$memo = safe_get2("memo");
	$q = "insert into IP(IP,MASK,net,`use`,lxr,memo) values('$ip','$mask',$net,'$use','$lxr','$memo')";
	mysql_query($q);
	$cmd = "ip";
}  else if ($cmd=="ip_modi_do") {
	checkright("ip",3);
	$id = safe_get("id");
	$ip = safe_get("ip");
	$mask = safe_get("mask");
	$net = safe_get("net");
	$use = safe_get2("use");
	$lxr = safe_get2("lxr");
	$memo = safe_get2("memo");
	$q = "select * from IP where id='$id'";
        $rr = mysql_query($q);
        $r = mysql_fetch_row($rr);
        $q = "insert into hist (tm,oid,old,new) values (now(),'IP$r[0]','$r[1]/$r[2]/$r[3]/$r[4]/$r[5]/$r[6]','$ip/$mask/$net/$use/$lxr/$memo')";
        mysql_query($q);
	$q = "update IP set IP='$ip',MASK='$mask',net=$net,`use`='$use',lxr='$lxr',memo='$memo' where id=$id";
	mysql_query($q);
	$cmd = "ip";
} else if ($cmd=="ip_del") {
	checkright("ip",3);
	$id = safe_get("ipid");
	$q = "delete from IP where id=$id";
	mysql_query($q);
	$cmd = "ip";
} else if ($cmd=="ip_filter_set") {
	checkright("ip",1);
	$ipfilter = safe_get("ipfilter");
	$_SESSION["ipfilter"] = $ipfilter;
	$cmd = "ip";
} else if ($cmd=="ip_filter") {
	checkright("ip",1);
	echo "<p>IP地址显示筛选";
	echo "<form action=index.php method=post>";
	echo "<input name=cmd value=ip_filter_set type=hidden>";
   	echo "IP地址筛选字符串: <input name=ipfilter value=\"";
	echo $_SESSION["ipfilter"];
	echo "\"><p>";
	echo "<input type=submit value=设置IP地址显示筛选>";
	echo "</form>";
	echo "注：设置后，仅仅显示包含以上字符串的IP地址，为空则全部显示";
	exit(0);
} else if ($cmd=="ip_add") {
	if (getuserright("ip")>=2) {
		echo "<p>新增IP地址记录";
		echo "<form action=index.php method=post>";
		echo "<input name=cmd value=ip_new type=hidden>";
		echo "<table>";
    		echo "<tr><td>IP</td><td><input name=ip></td></tr>";
    		echo "<tr><td>MASK</td><td>";
		mask_select("");
		echo "</td></tr>";
    		echo "<tr><td>子网?</td><td>否<input type=radio name=net value=0 checked>  是<input type=radio name=net value=1></td></tr>";
    		echo "<tr><td>用途</td><td><input name=use size=50></td></tr>";
		echo "<tr><td>联系人</td><td>";
		lxr_select("");
		echo "</td></tr>";
    		echo "<tr><td>备注</td><td><input name=memo size=100></td></tr>";
		echo "</table>";
		echo "<input type=submit value=新增IP记录>";
		echo "</form>";
	}
	exit(0);
} else if ($cmd=="ip_modi") {
	checkright("ip",3);
	$id = safe_get("id");
	if( $id ) {
		$q = "select id,IP,MASK,net,`use`,lxr,memo from IP where id=".$id;
		$r = mysql_fetch_row(mysql_query($q));
		echo "<p>";
		echo "修改记录<br>";
		echo "<form action=index.php method=post>";
		echo "<input name=cmd value=ip_modi_do type=hidden>";
		echo "<input name=id value=$r[0] type=hidden>";
		echo "<table>";
    		echo "<tr><td>IP</td><td><input name=ip value=\"$r[1]\"></td></tr>";
    		echo "<tr><td>MASK</td><td>";
		mask_select($r[2]);
		echo "</td></tr>";
		if ($r[3]=='0')
    			echo "<tr><td>子网? </td><td>no<input type=radio name=net value=0 checked>  yes<input type=radio name=net value=1>";
		else
    			echo "<tr><td>子网? </td><td>no<input type=radio name=net value=0>  yes<input type=radio name=net value=1 checked>";
		echo "</td></tr>";
    		echo "<tr><td>用途</td><td><input name=use size=50 value=\"$r[4]\"></td></tr>";
		echo "<tr><td>联系人</td><td>";
		lxr_select($r[5]);
		echo "</td></tr>";
    		echo "<tr><td>备注</td><td><input size=100 name=memo value=\"$r[6]\"></td></tr>";
		echo "</table>";
    		echo "<input type=submit name=修改记录></form>";
		changehist("select * from hist where oid = 'IP$id' order by tm desc");
		echo "<p>";
		if (getuserright("vm")>=3) 
			echo "<a href=index.php?cmd=ip_del&ipid=$r[0] onclick=\"return confirm('删除IP $r[1]/$r[2] ?');\">删除IP: $r[1]/$r[2]</a></td>";
	}
	exit(0);
}
if ($cmd=="ip") {
	checkright("ip",1);
	if ($_SESSION["ipfilter"]!="") {
		echo "仅显示 ";
		echo $_SESSION["ipfilter"];
		echo " 相关IP, <a href=index.php?cmd=ip_filter>重设筛选</a><p>";
		$q = "select id,IP,MASK,net,`use`,lxr,memo from IP where IP like '%".$_SESSION["ipfilter"]."%'order by inet_aton(IP), inet_aton(MASK)";
	} else
		$q = "select id,IP,MASK,net,`use`,lxr,memo from IP order by inet_aton(IP), inet_aton(MASK)";
	$rr = mysql_query($q);
	echo "<table border=1 cellspacing=0>";
	echo " <tr><th>序号</th><th>IP</th><th>用途</th><th>联系人</th><th>备注</th>";
	echo "</tr>";
	$count = 0;
	while ($r=mysql_fetch_row($rr)){
		$count++;
		if ($r[3]=='1')   // network 
			echo "<tr style=\"background-color:#ffff00\">";
		else 
			echo "<tr>";
		if (getuserright("ip")>=3) 
			echo "<td align=center><a href=index.php?cmd=ip_modi&id=$r[0]>$count</a></td>";
		else 
			echo "<td align=center>$count</td>";
		if ($r[3]=='1') {  // network 
			echo "<td>";
			echo "$r[1]/$r[2]";
		} else {
			echo "<td align=right>";
			if ($r[2]=="255.255.255.255") 
				echo $r[1];
			else 
				echo "$r[1]/$r[2]";
		}
		echo "</td>";
		echo "<td>$r[4]</td>";
		echo "<td>";
		lxr_display($r[5]);
		echo "</td>";
		echo "<td>$r[6]</td>";
		echo "</tr>\n";
	}
	echo "</table>";
	exit(0);
} // end cmd==ip

// VM

if ($cmd=="vm_c_new") {
	checkright("vm",2);
	$name = safe_get2("name");
	$ip = safe_get("ip");
	$memo = safe_get2("memo");
	$q = "insert into vm_cluster(name,ip,memo) values('$name','$ip','$memo')";
	mysql_query($q);
	$cmd = "vm_c";
}  else if ($cmd=="vm_c_modi") {
	checkright("vm",3);
	$id = safe_get("id");
	$name = safe_get2("name");
	$ip = safe_get("ip");
	$memo = safe_get2("memo");
	$q = "select * from vm_cluster where id ='$id'";
        $r = mysql_fetch_row(mysql_query($q));
        $q = "insert into hist (tm,oid,old,new) values (now(),'VMCLUSTER$r[0]','$r[1]/$r[2]/$r[3]','$name/$ip/$memo')";
        mysql_query($q);
	$q = "update vm_cluster set name='$name', ip='$ip', memo='$memo' where id=$id";
	mysql_query($q);
	$cmd = "vm_c";
} else if ($cmd=="vm_cluster_del") {
	checkright("vm",3);
	$id = safe_get("vmcid");
	$q = "delete from vm_cluster where id=$id";
	mysql_query($q);
	$cmd = "vm_c";
}

if ($cmd=="vm_s_new") {
	checkright("vm",2);
	$name = safe_get2("name");
	$cid = safe_get2("cid");
	$ip = safe_get("ip");
	$memo = safe_get2("memo");
	$q = "insert into vm_server(name,cid,ip,memo) values('$name','$cid','$ip','$memo')";
	mysql_query($q);
	$cmd = "vm_c";
}  else if ($cmd=="vm_s_modi_do") {
	checkright("vm",3);
	$sid = safe_get("sid");
	$name = safe_get2("name");
	$cid = safe_get2("cid");
	$ip = safe_get("ip");
	$memo = safe_get2("memo");
	$q = "select * from vm_server where id ='$sid'";
        $r = mysql_fetch_row(mysql_query($q));
        $q = "insert into hist (tm,oid,old,new) values (now(),'VMSERVER$r[0]','$r[1]/$r[2]/$r[3]/$r[4]','$name/$cid/$ip/$memo')";
        mysql_query($q);
	$q = "update vm_server set name='$name', cid='$cid', ip='$ip', memo='$memo' where id=$sid";
	mysql_query($q);
	$q = "update vm_server set name='$name', cid='$cid', ip='$ip', memo='$memo' where id=$sid";
	$cmd = "vm_c";
} else if ($cmd=="vm_s_del") {
	checkright("vm",3);
	$id = safe_get("vmsid");
	$q = "delete from vm_server where id=$id";
	mysql_query($q);
	$cmd = "vm_c";
} else if ($cmd=="vm_c_add") {
	if (getuserright("vm")>=2) {
		echo "<p>新增VM集群";
		echo "<form action=index.php method=post>";
		echo "<input name=cmd value=vm_c_new type=hidden>";
    		echo "名称: <input name=name><br>";
    		echo "IP: <input name=ip value=><br>";
    		echo "备注: <input name=memo><br>";
		echo "<input type=submit value=新增VM集群记录>";
		echo "</form>";
	}
	exit(0);
}
if ($cmd=="vm_c") {
	checkright("vm",1);
	echo "<p>";
	$q = "select * from vm_cluster order by name";
	$rr = mysql_query($q);
	echo "<table border=1 cellspacing=0>";
	echo " <tr><th>序号</th><th>名称</th><th>管理IP</th><th>成员</th><th>虚拟机</th><th>备注</th>";
	echo "</tr>";
	$count = 0;
	while ($r=mysql_fetch_row($rr)){
		$count++;
		echo "<tr>";
		if (getuserright("vm")>=3) 
			echo "<td align=center><a href=index.php?cmd=vm_c&id=$r[0]>$count</a></td>";
		else 
			echo "<td align=center>$count</td>";
		echo "<td>$r[1]</td>";
		echo "<td>$r[2]</td>";
		echo "<td>";
		$q = "select * from vm_server where cid='$r[0]'";
		$rr2 = mysql_query($q);
		while ($r2=mysql_fetch_row($rr2)){
			if (getuserright("vm")>=3) 
				echo "<a href=index.php?cmd=vm_c&id=$r[0]&vmsid=$r2[0]>";
			echo "$r2[1]/$r2[3]/$r2[4]"; 
			if (getuserright("vm")>=3) 
				echo "</a>";
			echo "<br>";
		}
		echo "</td>";
		echo "<td>";
		$q = "select * from vm_host where cid='$r[0]'";
		$rr2 = mysql_query($q);
		while ($r2=mysql_fetch_row($rr2)){
			if (getuserright("vm")>=3) 
				echo "<a href=index.php?cmd=vm_host_modi&id=$r[0]&id=$r2[0]>";
			echo "$r2[1]/$r2[4]/$r2[13]"; 
			if (getuserright("vm")>=3) 
				echo "</a>";
			echo "<br>";
		}
		echo "</td>";
		echo "<td>$r[3]</td>";
		echo "</tr>";
		echo "\n";
	}
	echo "</table>";
	$id = safe_get("id");
	if( $id <>"" ) {
		$vmsid = safe_get("vmsid");
		if( $vmsid <> "") { // 选择了某个集群成员
			$q = "select * from vm_server where id=".$vmsid;
			$r = mysql_fetch_row(mysql_query($q));
			echo "<p>";
			echo "修改/新增VM成员记录<br>";
			echo "<form action=index.php method=post>";
			echo "操作: 修改本条记录<input name=cmd value=vm_s_modi_do type=radio checked>";
			echo "&nbsp;&nbsp;&nbsp;新增一条记录<input name=cmd value=vm_s_new type=radio><br>";
			echo "<input name=sid value=\"$r[0]\" type=hidden>";
			echo "<input name=cid value=\"$r[2]\" type=hidden>";
    			echo "名称: <input name=name value=\"$r[1]\"><br>";
    			echo "IP: <input name=ip value=\"$r[3]\"><br>";
    			echo "备注: <input name=memo value=\"$r[4]\"><br>";
    			echo "<input type=submit name=修改/新增记录></form>";
			changehist("select * from hist where oid = 'VMSERVER$vmsid' order by tm desc");
			echo "<p><form action=index.php method=post>";
			echo "<input name=cmd value=vm_s_del type=hidden>";
			echo "<input name=vmsid value=$r[0] type=hidden>";
			echo "<input type=submit value=删除这条记录 onclick=\"return confirm('确信删除VM成员 $r[1] ?');\">";
			echo "</form>";
		} else {  // 未选择成员
			$q = "select * from vm_cluster where id=".$id;
			$r = mysql_fetch_row(mysql_query($q));
			echo "<p>";
			echo "修改记录<br>";
			echo "<form action=index.php method=post>";
			echo "<input name=cmd value=vm_c_modi type=hidden>";
			echo "<input name=id value=\"$r[0]\" type=hidden>";
    			echo "名称: <input name=name value=\"$r[1]\"><br>";
    			echo "IP: <input name=ip value=\"$r[2]\"><br>";
    			echo "备注: <input name=memo value=\"$r[3]\"><br>";
    			echo "<input type=submit name=修改记录></form>";
			changehist("select * from hist where oid = 'VMCLUSTER$id' order by tm desc");
			echo "<p><form action=index.php method=post>";
			echo "<input name=cmd value=vm_s_new type=hidden>";
			echo "<input name=cid value=\"$r[0]\" type=hidden>";
    			echo "名称: <input name=name><br>";
    			echo "IP: <input name=ip value=><br>";
    			echo "备注: <input name=memo><br>";
			echo "<input type=submit value=新增VM集群成员服务器>";
			echo "</form>";
		}
	}
	exit(0);
} // end cmd==vm_c


if ($cmd=="vm_host_new") {
	checkright("vm",2);
	$name = safe_get2("name");
	$inuse = safe_get("inuse");
	if ($inuse<>1) $inuse = 0;
	else $inuse = 1;
	$cid = safe_get2("cid");
	$ip = safe_get2("ip");
	$use = safe_get2("use");
	$st = safe_get2("st");
	$et = safe_get2("et");
	$lxr = safe_get("lxr");
	$cpu = safe_get2("cpu"); 
	$mem = safe_get2("mem");
	$disk = safe_get2("disk");
	$disk2 = safe_get2("disk2");
	$memo = safe_get2("memo");
	$q = "insert into vm_host(name,inuse,cid,ip,`use`,st,et,lxr,cpu,mem,disk,disk2,memo) values('$name',$inuse,'$cid','$ip','$use','$st','$et','$lxr','$cpu','$mem','$disk','$disk2','$memo')";
	mysql_query($q);
	$cmd = "vm";
}  else if ($cmd=="vm_host_modi_do") {
	checkright("vm",3);
	$id = safe_get("id");
	$name = safe_get2("name");
	$inuse = safe_get("inuse");
	if ($inuse<>1) $inuse = 0;
	else $inuse = 1;
	$cid = safe_get2("cid");
	$ip = safe_get2("ip");
	$use = safe_get2("use");
	$st = safe_get2("st");
	$et = safe_get2("et");
	$lxr = safe_get("lxr");
	$cpu = safe_get2("cpu"); 
	$mem = safe_get2("mem");
	$disk = safe_get2("disk");
	$disk2 = safe_get2("disk2");
	$memo = safe_get2("memo");
	$q = "select * from vm_host where id ='$id'";
        $r = mysql_fetch_row(mysql_query($q));
        $q = "insert into hist (tm,oid,old,new) values (now(),'VMHOST$r[0]','$r[1]/$r[2]/$r[3]/$r[4]/$r[5]/$r[6]/$r[7]/$r[8]/$r[9]/$r[10]/$r[11]/$r[12]/$r[13]','$name/$inuse/$cid/$ip/$use/$st/$et/$lxr/$cpu/$mem/$disk/$disk2/$memo')";
        mysql_query($q);
	$q = "update vm_host set name='$name', inuse=$inuse, cid='$cid', ip='$ip', `use`='$use', st='$st', et='$et', lxr='$lxr', cpu='$cpu', mem='$mem', disk='$disk', disk2='$disk2', memo='$memo' where id=$id";
	mysql_query($q);
	$cmd = "vm";
} else if ($cmd=="vm_host_del") {
	checkright("vm",3);
	$id = safe_get("hostid");
	$q = "delete from vm_host where id=$id";
	mysql_query($q);
	$cmd = "vm";
} else if ($cmd=="vm_host_modi") {
	checkright("vm",2);
	$id = safe_get("id");
	if (($id <>"") && (getuserright("vm")>=3)) {
		$q = "select * from vm_host where id=".$id;
		$r = mysql_fetch_row(mysql_query($q));
		echo "<p>";
		echo "修改/新增VM记录";
		echo "<table>";
		echo "<form action=index.php method=post>";
		echo "<input name=id value=".$r[0]." type=hidden>";
		echo "<tr><td>操作</td><td>修改本条记录<input name=cmd value=vm_host_modi_do type=radio checked>";
		echo "&nbsp;&nbsp;&nbsp;新增一条记录<input name=cmd value=vm_host_new type=radio></td></tr>";
    		echo "<tr><td>名称</td><td><input name=name value=\"$r[1]\"></td></tr>";
    		echo "<tr><td>在用</td><td>在用<input type=radio name=inuse value=1";
		if ($r[2]=="1") echo " checked";
		echo ">  不在用<input type=radio name=inuse value=0";
		if ($r[2]=="0") echo " checked";
		echo "></td></tr>";
    		echo "<tr><td>集群</td><td><select name=cid>";
		$q = "select * from vm_cluster";
		$rr2 = mysql_query($q);
		while ($r2=mysql_fetch_row($rr2)) {
			echo "<option value=\"$r2[0]\"";
			if ($r[3]==$r2[0]) echo " selected=\"selected\"";
			echo ">$r2[1]/$r2[2]</option>";
		}
		echo "</select></td></tr>";
    		echo "<tr><td>IP</td><td><input name=ip value=\"$r[4]\"></td></tr>";
    		echo "<tr><td>用途</td><td><input name=use value=\"$r[5]\"></td></tr>";
		echo "<tr><td>开始时间</td><td><input name=st value=\"$r[6]\"></td></tr>";
		echo "<tr><td>结束时间</td><td><input name=et value=\"$r[7]\"></td></tr>";
		echo "<tr><td>联系人</td><td>";
		lxr_select($r[8]);
		echo "</td></tr>";
		echo "<tr><td>CPU</td><td><input name=cpu value=\"$r[9]\"></td></tr>";	
		echo "<tr><td>MEM</td><td><input name=mem value=\"$r[10]\"></td></tr>";	
		echo "<tr><td>DISK</td><td><input name=disk value=\"$r[11]\"></td></tr>";	
		echo "<tr><td>DISK2</td><td><input name=disk2 value=\"$r[12]\"></td></tr>";	
    		echo "<tr><td>备注</td><td><input name=memo value=\"$r[13]\"></td></tr>";
		echo "</table>";
		echo "<input type=submit value=修改/新增VM记录>";
		echo "</form>";
		changehist("select * from hist where oid = 'VMHOST$id' order by tm desc");
		echo "<form action=index.php method=post>";
		echo "<input name=cmd value=vm_host_del type=hidden>";
		echo "<input name=hostid value=$r[0] type=hidden>";
		echo "<input type=submit value=删除VM记录 onclick=\"return confirm('确信删除VM $r[1] ?');\">";
		echo "</form>";
	
	}  else if (getuserright("vm")>=2) {
		echo "<p>";
		echo "新增VM记录<br>";
		echo "<table>";
		echo "<form action=index.php method=post>";
		echo "<input name=cmd value=vm_host_new type=hidden>";
    		echo "<tr><td>名称</td><td><input name=name></td></tr>";
    		echo "<tr><td>集群</td><td><select name=cid>";
		$q = "select * from vm_cluster";
		$rr = mysql_query($q);
		while ($r=mysql_fetch_row($rr)) {
			echo "<option value=\"$r[0]\">$r[1]/$r[2]</option>";
		}
		echo "</select></td></tr>";
    		echo "<tr><td>IP</td><td><input name=ip></td></tr>";
    		echo "<tr><td>在用</td><td>在用<input type=radio name=inuse value=1 checked>  不在用<input type=radio name=inuse value=0></td></tr>";
    		echo "<tr><td>用途</td><td><input name=use></td></tr>";
		echo "<tr><td>开始时间</td><td><input name=st value=\"";
		echo strftime("%Y-%m-%d",time());
		echo "\"></td></tr>";
		echo "<tr><td>结束时间</td><td><input name=et value=\"0000-00-00\"></td></tr>";
		echo "<tr><td>联系人</td><td>";
		lxr_select("");
		echo "</td></tr>";
		echo "<tr><td>CPU</td><td><input name=cpu></td></tr>";	
		echo "<tr><td>MEM</td><td><input name=mem></td></tr>";	
		echo "<tr><td>DISK</td><td><input name=disk></td></tr>";	
		echo "<tr><td>DISK2</td><td><input name=disk2></td></tr>";	
    		echo "<tr><td>备注</td><td><input name=memo></td></tr>";
		echo "</table>";
		echo "<input type=submit value=新增VM记录>";
		echo "</form>";
	}
	exit(0);
}
if ($cmd=="vm") {
	checkright("vm",1);
	$s = safe_get("s");
	if ($s == "name") $sortby = "name";
	else if ($s=="cluster") $sortby = "cid";
	else if ($s=="inuse") $sortby = "inuse";
	else if ($s=="ip") $sortby = "ip";
	else if ($s=="use") $sortby = "use`";
	else if ($s=="lxr") $sortby = "lxr";
	else if ($s=="et") $sortby = "et";
	else $sortby = "id";
	$q = "select * from vm_host order by $sortby";
	$rr = mysql_query($q);
	echo "<table border=1 cellspacing=0>";
	echo "<tr><td>序号</td><td align=center><a href=index.php?cmd=vm&s=name>名称</a></td>";
	echo "<td><a href=index.php?cmd=vm&s=inuse>在用</a></td>";
	echo "<td align=center><a href=index.php?cmd=vm&s=cluster>集群</a></td>";
	echo "<td align=center><a href=index.php?cmd=vm&s=ip>IP</a></td>";
	echo "<td align=center><a href=index.php?cmd=vm&s=use>用途</a></td><td align=center>开始时间</td>";
	echo "<td align=center><a href=index.php?cmd=vm&s=et>结束时间</a></td>";
	echo "<td align=center><a href=index.php?cmd=vm&s=lxr>联系人</a></td><td>CPU</td><td>MEM</td><td>DISK</td><td>DISK2</td><td>备注</td>";
	echo "</tr>";
	$count = 0;
	while ($r=mysql_fetch_row($rr)){
		$count++;
		echo "<tr>";
		if (getuserright("vm")>=3) 
			echo "<td align=center><a href=index.php?cmd=vm_host_modi&id=$r[0]>$count</a></td>";
		else 
			echo "<td align=center>$count</td>";
		echo "<td>$r[1]</td>";
		echo "<td>";
		if ($r[2]=="1") echo "在用";
		else echo "关机";
		echo "</td>";
		echo "<td>";
		$q = "select * from vm_cluster where id='$r[3]'";
		$r2 = mysql_fetch_row(mysql_query($q));
		if ($r2) echo $r2[1]."/".$r2[2];
		else echo $r[3]."未知集群ID";
		echo "</td>";
		echo "<td>$r[4]</td>"; // IP
		echo "<td>$r[5]</td>"; // use
		echo "<td>$r[6]</td>"; // st
		echo "<td>$r[7]</td>"; //et 
		echo "<td>";
		lxr_display($r[8]);
		echo "</td>";
		echo "<td>$r[9]</td>";
		echo "<td>$r[10]</td>";
		echo "<td>$r[11]</td>";
		echo "<td>$r[12]</td>";
		echo "<td>$r[13]</td>";
		echo "</tr>\n";
	}
	echo "</table>";
	exit(0);
} // end cmd==vm

// LXR

if ($cmd=="lxr_new") {
	checkright("lxr",2);
	$dept = safe_get2("dept");
	$name = safe_get2("name");
	$dh = safe_get2("dh");
	$sj = safe_get2("sj");
	$email = safe_get2("email");
	$qq = safe_get2("qq");
	$memo = safe_get2("memo");
	$q = "insert into lxr(dept,name,dh,sj,email,qq,memo) values('$dept','$name','$dh','$sj','$email','$qq','$memo')";
	mysql_query($q);
	$cmd = "lxr";
}  else if ($cmd=="lxr_modi_do") {
	checkright("lxr",3);
	$id = safe_get("id");
	$dept = safe_get2("dept");
	$name = safe_get2("name");
	$dh = safe_get2("dh");
	$sj = safe_get2("sj");
	$email = safe_get2("email");
	$qq = safe_get2("qq");
	$memo = safe_get2("memo");
	$q = "select * from lxr where id ='$id'";
        $r = mysql_fetch_row(mysql_query($q));
        $q = "insert into hist (tm,oid,old,new) values (now(),'VMLXR$r[0]','$r[1]/$r[2]/$r[3]/$r[4]/$r[5]/$r[6]/$r[7]','$dept/$name/$dh/$sj/$email/$qq/$memo')";
        mysql_query($q);
	$q = "update lxr set dept='$dept', name='$name', dh='$dh', sj='$sj', email='$email', qq='$qq', memo='$memo' where id=$id";
	mysql_query($q);
	$cmd = "lxr";
} else if ($cmd=="lxr_del") {
	checkright("lxr",3);
	$id = safe_get("lxrid");
	$q = "delete from lxr where id=$id";
	mysql_query($q);
	$cmd = "lxr";
} else if ($cmd=="lxr_add") {
	echo "<p>新增联系人<p>";
	if (getuserright("lxr")>=2) {
		echo "<p><form action=index.php method=post>";
		echo "<input name=cmd value=lxr_new type=hidden>";
    		echo "部门: <input name=dept><br>";
    		echo "姓名: <input name=name><br>";
    		echo "电话: <input name=dh><br>";
    		echo "手机: <input name=sj><br>";
    		echo "邮箱: <input name=email><br>";
    		echo "QQ: <input name=qq><br>";
    		echo "备注: <input name=memo><br>";
		echo "<input type=submit value=新增联系人记录>";
		echo "</form>";
	}
	exit(0);
} else if ($cmd=="lxr_modi") { $id = safe_get("id");
	checkright("lxr",3);
	if ($id<>"") {
		$q = "select * from lxr where id=".$id;
		$r = mysql_fetch_row(mysql_query($q));
		echo "<p>";
		echo "修改/新增联系人记录<br>";
		echo "<form action=index.php method=post>";
		echo "<input name=id value=$r[0] type=hidden>";
		echo "操作: 修改本条记录<input name=cmd value=lxr_modi_do type=radio checked>";
		echo "&nbsp;&nbsp;&nbsp;新增一条记录<input name=cmd value=lxr_new type=radio><br>";
    		echo "部门: <input name=dept value=\"$r[1]\"><br>";
    		echo "姓名: <input name=name value=\"$r[2]\"><br>";
    		echo "电话: <input name=dh   value=\"$r[3]\"><br>";
    		echo "手机: <input name=sj   value=\"$r[4]\"><br>";
    		echo "邮箱: <input name=email value=\"$r[5]\"><br>";
    		echo "QQ  : <input name=qq   value=\"$r[6]\"><br>";
    		echo "备注: <input name=memo value=\"$r[7]\"><br>";
    		echo "<input type=submit name=修改/新增记录></form>";
		changehist("select * from hist where oid = 'VMLXR$id' order by tm desc");
		echo "<p>";
		if (getuserright("lxr")>=3) 
			echo "<a href=index.php?cmd=lxr_del&lxrid=$r[0] onclick=\"return confirm('删除联系人 $r[1]/$r[2] ?');\">删除联系人： $r[1]/$r[2]</a></td>";
	}
	exit(0);
} else if ($cmd=="lxr_detail") {
	$id = safe_get("id");
	checkright("lxr",1);
	if ($id<>"") {
		$q = "select * from lxr where id=".$id;
		$r = mysql_fetch_row(mysql_query($q));
		echo "<p>";
		echo "联系人详细信息<p>";
    		echo "部门: $r[1]<br>";
    		echo "姓名: $r[2]<br>";
    		echo "电话: $r[3]<br>";
    		echo "手机: $r[4]<br>";
    		echo "邮箱: $r[5]<br>";
    		echo "QQ  : $r[6]<br>";
    		echo "备注: $r[7]<p>";
		changehist("select * from hist where oid = 'VMLXR$id' order by tm desc");
		echo "<p>";
	}
	exit(0);
}
if ($cmd=="lxr") {
	checkright("lxr",1);
	$q = "select * from lxr order by dept, name";
	$rr = mysql_query($q);
	echo "<table border=1 cellspacing=0>";
	echo " <tr><th>序号</th><th>部门</th><th>姓名</th><th>电话</th><th>手机</th><th>email</th><th>QQ</th><th>备注</th>";
	echo "</tr>";
	$count = 0;
	while ($r=mysql_fetch_row($rr)){
		$count ++;
		echo "<tr>";
		if (getuserright("lxr")>=3) 
			echo "<td align=center><a href=index.php?cmd=lxr_modi&id=$r[0]>$count</a></td>";
		else 
			echo "<td align=center>$count</td>";
		echo "<td>$r[1]</td>";
		echo "<td>$r[2]</td>";
		echo "<td>$r[3]</td>";
		echo "<td>$r[4]</td>";
		echo "<td>$r[5]</td>";
		echo "<td>$r[6]</td>";
		echo "<td>$r[7]</td>";
		echo "</tr>\n";
	}
	echo "</table>";
	exit(0);
} // end cmd==lxr

// INFO

if ($cmd=="info_new") {
	checkright("info",2);
	$title = safe_get2("title");
	$memo = safe_get2("memo");
	$q = "insert into info (title,memo) values('$title','$memo')";
	mysql_query($q);
	$cmd = "info";
}  else if ($cmd=="info_modi_do") {
	checkright("info",3);
	$id = safe_get("id");
	$title = safe_get2("title");
	$memo = safe_get2("memo");
	$q = "update info set title='$title',memo='$memo' where id=$id";
	mysql_query($q);
	$cmd = "info";
} else if ($cmd=="info_up") {
	checkright("info",3);
	$id = safe_get("id");
	$q = "update info set sortid=sortid-1 where id=$id";
	mysql_query($q);
	$cmd = "info";
} else if ($cmd=="info_down") {
	checkright("info",3);
	$id = safe_get("id");
	$q = "update info set sortid=sortid+1 where id=$id";
	mysql_query($q);
	$cmd = "info";
} else if ($cmd=="info_add") {
	checkright("info",2);
	echo "<form action=index.php method=post>";
	echo "<input name=cmd value=info_new type=hidden>";
	echo "标题:<input name=title size=75> <br>";
	echo "内容:<textarea name=memo cols=100 rows=40></textarea><br>";
	echo " <input type=submit value=新增常用信息> </form>";
 	exit(0);
}
if ($cmd=="info") { 
	checkright("info",1);
	$q = "select id,title,left(memo,100) from info order by sortid,id";
	$rr = mysql_query($q);
	echo "<table border=1 cellspacing=0>";
	echo " <tr><th>序号</th><th>标题</th><th>内容摘要</th><th>命令</th></tr>";

	$count = 0;
	while ($r=mysql_fetch_row($rr)){
		$count ++;
		echo "<tr><td align=center>".$count."</td>";
		echo "<td><a class=\"info\" href=index.php?cmd=info_detail&id=$r[0]>$r[1]<a/></td>";
		echo "<td><a class=\"info\" href=index.php?cmd=info_detail&id=$r[0]>$r[2]<a/></td>";
		echo "<td>";
		if (getuserright("info")>=2) 
			echo "<a href=index.php?cmd=info_modi&id=$r[0]>修改<a/> ";
		if (getuserright("info")>=3) {
			echo "<a href=index.php?cmd=info_up&id=$r[0]>上移<a/> ";
			echo "<a href=index.php?cmd=info_down&id=$r[0]>下移<a/> ";
		}
		echo "</td>";
		echo "</tr>\n";
	}
	echo "</table>";
	echo "<p>";
	exit(0);
} // end cmd==info

if ($cmd=="info_detail") {
	checkright("info",1);
	$id = safe_get("id");
	$q = "select id,title,memo from info where id=$id";
	$r = mysql_fetch_row(mysql_query($q));
	echo "<p>$r[1]";
	echo "<hr><pre>";
	echo $r[2];
	echo "</pre>";
	echo "<hr>";
        $q = "select * from file where infoid=$id";
        $rr = mysql_query($q);
        echo "附件信息<table>";
        echo "<tr><th>序号</th><th>时间</th><th>文件名</th><th>大小</th><th>类型</th><th>命令</th></tr>\n";
        $count = 0;
        while ($r=mysql_fetch_row($rr)) {
                $count++;
                echo "<tr><td>$count</td>";
                echo "<td>$r[5]</td>";
                echo "<td>$r[2]</td>";
                echo "<td>$r[3]</td>";
                echo "<td>$r[4]</td>";
                echo "<td><a href=index.php?cmd=file_down&id=$id&fid=$r[0] target=_blank>下载</a></td>";
                echo "</tr>\n";
        }
        echo "</table><p>";
	exit(0);
} // end cmd==info_detail

if ($cmd=="file_upload") {
	checkright("info",2);
	if ($_FILES['userfile']['error']<>0) { 
		echo "文件上载错误<p>";
		echo "错误代码:".$_FILES['userfile']['error'];
		$cmd = "info_modi";
	} else {
		$infoid = safe_get("id");
		echo $infoid;
		$name = mysql_escape_string($_FILES['userfile']['name']);
		$size = strval($_FILES['userfile']['size']);
		$type = $_FILES['userfile']['type'];
		checkvalue($size);
		checkvalue($type);
		$q = "insert into file (infoid,name,size,type,tm) values($infoid,'$name','$size','$type',now())";
		mysql_query($q);
		$id = strval(mysql_insert_id());
		move_uploaded_file($_FILES['userfile']['tmp_name'],$uploaddir."/".$id);
		$cmd="info_modi";
	}
} // end cmd==file_upload

if ($cmd=="file_del") {
	checkright("info",3);
	$infoid = safe_get("id");
	$fid = safe_get("fid");
	$q = "insert into file_del select * from file where id=$fid";
	mysql_query($q);
	$q = "delete from file where id=$fid";
	mysql_query($q);
	rename($uploaddir."/".$fid, $uploaddir."_del/".$fid);
	$cmd = "info_modi";
} 
if ($cmd=="info_modi") {
	checkright("info",2);
	$id = safe_get("id");
	$q = "select id,title,memo from info where id=$id";
	$r = mysql_fetch_row(mysql_query($q));
	echo "修改信息<br>";
	echo "<form action=index.php method=post>";
	echo "<input name=cmd value=info_modi_do type=hidden>";
	echo "<input name=id value=$r[0] type=hidden>";
    	echo "标题:<br><input name=title value=\"$r[1]\" size=75><p>";
   	echo "内容:<br><textarea name=memo cols=100 rows=40>";
	echo $r[2];
	echo "</textarea><p>";
	if (getuserright("info")>=3) 
   		echo "<input type=submit value=修改></form><p>";
	else echo "</form><p>";
	echo "<hr>";
	$q = "select * from file where infoid=".$id;
	$rr = mysql_query($q);
	echo "附件信息<table>";
	echo "<tr><th>序号</th><th>时间</th><th>文件名</th><th>大小</th><th>类型</th><th>命令</th></tr>\n";
	$count = 0;
	while ($r=mysql_fetch_row($rr)) {
		$count++;
		echo "<tr><td>$count</td>";
		echo "<td>$r[5]</td>";
		echo "<td>$r[2]</td>";
		echo "<td>$r[3]</td>";
		echo "<td>$r[4]</td>";
		if (getuserright("info")>=3) 
			echo "<td><a href=index.php?cmd=file_del&id=$id&fid=$r[0]>删除(移到回收站)</a></td>";
		else
			echo "<td></td>";
		echo "</tr>\n";
	}
	echo "</table><p>";
	echo "上传附件<br>";
	echo "<form enctype=\"multipart/form-data\" action=\"index.php\" method=\"post\">";
	echo "<input name=cmd value=file_upload type=hidden>";
	echo "<input name=id value=".$id." type=hidden>";
	echo "请选择文件： <br> ";
	echo "<input name=\"userfile\" type=\"file\"><br>";
	echo "<input type=\"submit\" value=\"上传文件\">   ";
	echo "</form>   ";
	exit(0);
} // end cmd==info_detail

// USER 

if ($cmd=="user_new") {
	checkright("user",3);
	$email = safe_get("email");
	$pop3server = safe_get("pop3server");
	@$fullname = safe_get2("fullname");
	$super = safe_get("super");
	$q = "delete from user where email='$email'";
	mysql_query($q);
	if ($super!="1") 
		$super="0";
	$q = "insert into user values('$email','$pop3server',$super,'$fullname')";
	mysql_query($q);
	$cmd = "user";
} else if ($cmd=="user_del") {
	checkright("user",3);
	$email = safe_get("email");
	if ($email==$_SESSION["user"]) {
		echo "<font color=red>不能删除自己</font><p>";
	} else {
		$q = "delete from user where email='$email'";
		mysql_query($q);
	}
	$cmd = "user";
} else if ($cmd=="user_right") {
	checkright("user",3);
	$user = safe_get("user");
	$module = safe_get("module");
	$right = safe_get("right");
	$q = "delete from userright where user='$user' and module='$module'";
	mysql_query($q);
	if ($right<>"0") 
		$q = "insert into userright (user,module,`right`) values('$user','$module',$right)";
	mysql_query($q);
	$cmd = "user";
} else if ($cmd=="user_add") {
	checkright("user",2);
?>
<p>新增用户<p>
登录时，利用登录名和密码连接到POP3邮件服务器上验证用户身份<br>
<form action=index.php method=get>
<input name=cmd value=user_new type=hidden>
用户邮件登录名：<input name=email><br>
POP3邮件服务器：<input name=pop3server><br>
用户姓名: <input name=fullname><br>
是否超级管理员：<input name=super type=checkbox value=1><p>
<input type=submit value=新增用户>
</form>
<?php 
	exit(0);
} else if ($cmd=="user_modi") {
	checkright("user",3);
	$email = safe_get("email");
	$q = "select email,pop3server,truename,isadmin from user where email='$email'";
	$r = mysql_fetch_row(mysql_query($q));
?>
<p>修改用户<p>
登录时，利用登录名和密码连接到POP3邮件服务器上验证用户身份<br>
<form action=index.php method=get>
<input name=cmd value=user_new type=hidden>
用户邮件登录名：<input name=email value="<?php echo $r[0]; ?>"><br>
POP3邮件服务器：<input name=pop3server value="<?php echo $r[1]; ?>"><br>
用户姓名: <input name=fullname value="<?php echo $r[2]; ?>"><br>
是否超级管理员：<input name=super type=checkbox value=1 <?php if($r[3]=="1") echo "checked"; ?>><p>
<input type=submit value=修改用户>
</form>

<hr width=500 align=left>
修改用户权限<p>
<form action=index.php method=get>
<input name=cmd value=user_right type=hidden>
用户登录名：<select name=user>
<?php
	$q = "select email from user";
	$rr = mysql_query($q);
	while ($r=mysql_fetch_row($rr)) {
		echo "<option value=\"$r[0]\">$r[0]</option>\n";
	}
?>
</select>
模块: <select name=module>
<?php
	$q = "select module,memo from module order by id";
	$rr = mysql_query($q);
	while ($r=mysql_fetch_row($rr)) {
		echo "<option value=\"$r[0]\">$r[0]/$r[1]</option>\n";
	}
?>
</select><br>
权限：<br>
<input name=right type=radio value=0 checked>无<br>
<input name=right type=radio value=1>只读<br>
<input name=right type=radio value=2>新增<br>
<input name=right type=radio value=3>所有<p>
<input type=submit value=修改用户权限>
</form>
<?php
	exit(0);
}
if ($cmd=="user") {
	checkright("user",1);
	$q = "select * from user order by email";
	$rr = mysql_query($q);
	$count = 0;
	echo "<p><table border=1 cellspacing=0>";
	echo "<tr><th>序号</th><th>登录名</th><th>POP3服务器</th><th>全名</th><th>超级管理员</th><th>分模块权限</th>";
	if (getuserright("user")>=3) 
		echo "<th>命令</th>";
	echo "</tr>\n";
	while ($r=mysql_fetch_row($rr)) {
		$count++;
		echo "<tr><td align=center>$count</td>";
		echo "<td>$r[0]</td>";
		echo "<td>$r[1]</td>";
		echo "<td>$r[3]</td>";
		echo "<td align=center>";
		if($r[2]=="0")
			echo "否";
		else echo "<font color=red>是</font>";
		echo "</td>";
		echo "<td>";
		$q = "select module.module,module.memo,userright.right from userright,module where userright.module =module.module and userright.user='$r[0]' order by module.id";
		
		$rr2 = mysql_query($q);
		echo "<table width=300>";
		while ($r2=mysql_fetch_row($rr2)) {
			echo "<tr>";
			echo "<td width=30%>$r2[0]</td>";
			echo "<td width=50%>$r2[1]</td>";
			echo "<td width=20%>";
			if($r2[2]=="1") echo "只读";
			else if($r2[2]=="2") echo "新增";
			else if($r2[2]=="3") echo "所有";
			echo "</td>";
			echo "</tr>\n";
		}
		echo "</table>";
		echo "</td>\n";
		if (getuserright("user")>=3)  {
			echo "<td>";
			echo "<a href=index.php?cmd=user_modi&email=$r[0]>修改</a> &nbsp;&nbsp;";
			echo "<a href=index.php?cmd=user_del&email=$r[0] onclick=\"return confirm('确信删除 $r[0] ?');\">删除</a>";
			echo "</td>";
		}
		echo "</tr>";
	}
	echo "</table>";
	exit(0);
} // end cmd==user

// SYSINFO

if ($cmd=="ticket_system_new") {
	checkright("sysinfo",2);
	$desc = safe_get2("desc");
	$q = "insert into ticket_system(sortid,`desc`) values(10,'$desc')";
	mysql_query($q);
	$cmd = "sysinfo";
}  else if ($cmd=="ticket_system_modi_do") {
	checkright("sysinfo",3);
	$id = safe_get("id");
	$desc = safe_get2("desc");
	$q = "update ticket_system set `desc`='$desc' where id=$id";
	mysql_query($q);
	$cmd = "sysinfo";
} else if ($cmd=="ticket_system_up") {
	checkright("sysinfo",3);
	$id = safe_get("id");
	$q = "update ticket_system set sortid=sortid-1 where id=$id";
	mysql_query($q);
	$cmd = "sysinfo";
} else if ($cmd=="ticket_system_down") {
	checkright("sysinfo",3);
	$id = safe_get("id");
	$q = "update ticket_system set sortid=sortid+1 where id=$id";
	mysql_query($q);
	$cmd = "sysinfo";
} else if ($cmd=="ticket_system_del") {
	$id = safe_get("id");
	$q = "delete from ticket_system where id=$id";
	mysql_query($q);
	$cmd = "sysinfo";
} else if ($cmd=="ticket_system_modi") {
	$id = safe_get("id");
	$q = "select id, `desc` from ticket_system where id=$id";
	$r = mysql_fetch_row(mysql_query($q));
	echo "<p><form action=index.php method=get>";
	echo "修改故障相关系统: <p>";
	echo "<input name=cmd value=ticket_system_modi_do type=hidden>";
	echo "<input name=id value=\"$r[0]\" type=hidden>";
	echo "系统：<input name=desc value=\"$r[1]\"><p>";
	echo "<input type=submit value=修改系统信息>";
	exit(0);
}
if ($cmd=="ticket_reason_new") {
	checkright("sysinfo",2);
	$desc = safe_get2("desc");
	$q = "insert into ticket_reason(sortid,`desc`) values(10,'$desc')";
	mysql_query($q);
	$cmd = "sysinfo";
}  else if ($cmd=="ticket_reason_modi_do") {
	checkright("sysinfo",3);
	$id = safe_get("id");
	$desc = safe_get2("desc");
	$q = "update ticket_reason set `desc`='$desc' where id=$id";
	mysql_query($q);
	$cmd = "sysinfo";
} else if ($cmd=="ticket_reason_up") {
	checkright("sysinfo",3);
	$id = safe_get("id");
	$q = "update ticket_reason set sortid=sortid-1 where id=$id";
	mysql_query($q);
	$cmd = "sysinfo";
} else if ($cmd=="ticket_reason_down") {
	checkright("sysinfo",3);
	$id = safe_get("id");
	$q = "update ticket_reason set sortid=sortid+1 where id=$id";
	mysql_query($q);
	$cmd = "sysinfo";
} else if ($cmd=="ticket_reason_del") {
	$id = safe_get("id");
	$q = "delete from ticket_reason where id=$id";
	mysql_query($q);
	$cmd = "sysinfo";
} else if ($cmd=="ticket_reason_modi") {
	$id = safe_get("id");
	$q = "select id, `desc` from ticket_reason where id=$id";
	$r = mysql_fetch_row(mysql_query($q));
	echo "<p><form action=index.php method=get>";
	echo "修改故障类型: <p>";
	echo "<input name=cmd value=ticket_reason_modi_do type=hidden>";
	echo "<input name=id value=\"$r[0]\" type=hidden>";
	echo "系统：<input name=desc value=\"$r[1]\"><p>";
	echo "<input type=submit value=修改故障类型>";
	exit(0);
}
if ($cmd=="sysinfo_modi") {
	checkright("sysinfo",3);
	$sysversion = safe_get2("version");
	$systitle = safe_get2("title");
	$syslxr = safe_get2("lxr");
	$displayopip = safe_get("displayopip");
	$q = "replace into sysinfo values('version','$sysversion')";
	mysql_query($q);
	$q = "replace into sysinfo values('title','$systitle')";
	mysql_query($q);
	$q = "replace into sysinfo values('lxr','$syslxr')";
	mysql_query($q);
	$q = "replace into sysinfo values('displayopip','$displayopip')";
	mysql_query($q);
	$cmd = "sysinfo";
}
if($cmd=="sysinfo") {
	checkright("sysinfo",1);
	$displayopip = getdisplayopip();
	echo $displayopip;
?>
系统信息设置<p>
<form action=index.php method=get>
<input name=cmd value=sysinfo_modi type=hidden>
系统版本：<input name=version value="<?php echo $sysversion;?>"><br>
网页标题：<input name=title value="<?php echo $systitle;?>"><br>
联系信息：<input name=lxr value="<?php echo $syslxr;?>"><p>
记录管理员IP: 
<input type=radio name=displayopip value=0<?php if($displayopip=="0") echo " checked";?>>否</input>
<input type=radio name=displayopip value=1<?php if($displayopip=="1") echo " checked";?>>是</input><p>

<?php 	if (getuserright("sysinfo")>=3) 
		echo "<input type=submit value=修改系统信息>";
	echo "</form>";

	echo "<hr width=400 align=left>故障处理中参数设置<p>";
	echo "<p><form action=index.php method=get>";
	echo "相关系统: ";
	echo "<input name=cmd value=ticket_system_new type=hidden>";
	echo "<input name=desc>";
 	if(getuserright("sysinfo")>=3) 
		echo "<input type=submit value=新增系统>";
	$q = "select id, `desc` from ticket_system order by sortid,id";
	echo "</form>";
	$rr = mysql_query($q);
	echo "<table border=1 cellspacing=0>";
	echo "<tr><th>序号</th><th>系统</th>";
	if (getuserright("sysinfo")>=3)
		echo "<th>命令</th>";
	echo "</tr>\n";

	$count = 0;
	while ($r=mysql_fetch_row($rr)){
		$count ++;
		echo "<tr><td align=center>$count</td>";
		echo "<td>$r[1]</td>";
		if (getuserright("sysinfo")>=3) {
			echo "<td>&nbsp;";
			echo "<a href=index.php?cmd=ticket_system_del&id=$r[0] onclick=\"return confirm('删除 $r[1] ?');\">删除<a/> ";
			echo "<a href=index.php?cmd=ticket_system_modi&id=$r[0]>修改<a/> ";
			echo "<a href=index.php?cmd=ticket_system_up&id=$r[0]>上移<a/> ";
			echo "<a href=index.php?cmd=ticket_system_down&id=$r[0]>下移<a/> ";
			echo "&nbsp;</td>";
		}
		echo "</tr>\n";
	}
	echo "</table>\n";


	echo "<p><form action=index.php method=get>";
	echo "故障类型: ";
	echo "<input name=cmd value=ticket_reason_new type=hidden>";
	echo "<input name=desc>";
 	if (getuserright("sysinfo")>=3) 
		echo "<input type=submit value=新增类型>";
	echo "</form>";
	$q = "select id, `desc` from ticket_reason order by sortid,id";
	$rr = mysql_query($q);
	echo "<table border=1 cellspacing=0>";
	echo "<tr><th>序号</th><th>类型</th>";
	if (getuserright("sysinfo")>=3)
		echo "<th>命令</th>";
	echo "</tr>\n";

	$count = 0;
	while ($r=mysql_fetch_row($rr)){
		$count ++;
		echo "<tr><td align=center>".$count."</td>";
		echo "<td>$r[1]</td>";
		if (getuserright("sysinfo")>=3) {
			echo "<td>&nbsp;";
			echo "<a href=index.php?cmd=ticket_reason_del&id=$r[0] onclick=\"return confirm('删除 $r[1] ?');\">删除<a/> ";
			echo "<a href=index.php?cmd=ticket_reason_modi&id=$r[0]>修改<a/> ";
			echo "<a href=index.php?cmd=ticket_reason_up&id=$r[0]>上移<a/> ";
			echo "<a href=index.php?cmd=ticket_reason_down&id=$r[0]>下移<a/> ";
			echo "&nbsp;</td>";
		}
		echo "</tr>\n";
	}
	echo "</table>";
?>

<?php
	exit(0);
}  // end cmd==sysinfo


// USER_PREF

if ($cmd=="user_pref_tdm") {
	$tdm = safe_get("tdm");
	$user = $_SESSION["user"];
	if ($tdm<>"1") $tdm = "0";
	$q = "replace into userpref values('$user','ticketdisplaymode','$tdm')";
	mysql_query($q);
	$cmd = "user_pref";
}
if ($cmd=="user_pref") {
	echo $_SESSION["truename"];
	echo " From: ";
	echo  $_SERVER["REMOTE_ADDR"]; 
	echo "<p>";
	echo "个人偏好设置<p>";
	$tdm = getticketdisplaymode();
?>

<form action=index.php method=get>
<input name=cmd value=user_pref_tdm type=hidden>
故障处理显示模式: 
<input type=radio name=tdm value=0<?php if($tdm=="0") echo " checked";?>>适合宽屏</input>
<input type=radio name=tdm value=1<?php if($tdm=="1") echo " checked";?>>适合窄屏</input>
<input type=submit value=修改故障处理显示模式>  <p>

<?php
	exit(0);
}
?>
