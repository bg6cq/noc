<?php
include("db.php");
$q="select info from sysinfo where name='version'";
$r=mysql_fetch_row(mysql_query($q));
$sysversion=$r[0];
$q="select info from sysinfo where name='title'";
$r=mysql_fetch_row(mysql_query($q));
$systitle=$r[0];
$q="select info from sysinfo where name='lxr'";
$r=mysql_fetch_row(mysql_query($q));
$syslxr=$r[0];

session_start();

function checkvalue($str) {
	for($i = 0 ; $i < strlen($str) ; $i++) {
        	if( ($str[$i] >='a') && ($str[$i]<='z') ) continue;
        	if( ($str[$i] >='A') && ($str[$i]<='Z') ) continue;
        	if( ($str[$i] >='0') && ($str[$i]<='9') ) continue;
        	if( $str[$i] == '@' ) continue;
        	if( $str[$i] == '-' ) continue;
        	if( $str[$i] == '_' ) continue;
        	if( $str[$i] == ' ' ) continue;
        	if( $str[$i] == '.' ) continue;
        	if( $str[$i] == '/' ) continue;
        	if( $str[$i] == ':' ) continue;
        	echo $str."�е�".$i."�Ƿ��ַ�".$str[$i];
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
	echo "�޸ļ�¼<p><table border=1>";
        echo "<tr><th>ʱ��</th><th>�޸�����</th></tr>\n";

        $count = 0;
        $rr=mysql_query($q);
        while($row=mysql_fetch_row($rr)) {
                $count++;
                echo "<tr><td>";
                echo $row[1];
                echo "</td><td>";
		echo $row[3];
		echo "<br>";
		echo $row[4];
                echo "</td></tr>\n";
        }
        echo "</table>";
}

// 0 no right
// 1 readonly right
// 2 add right
// 3 full right 
function getuserright($module) {
	$user = $_SESSION["user"];
	$q = "select isadmin from user where email='$user'";
        $rr=mysql_query($q);
        $row=mysql_fetch_row($rr);
	if($row[0]=="1") // super user
		return 3;  // full right
	$q = "select max(`right`) from userright where user='$user' and ( module='ALL' or module='$module')";
        $rr=mysql_query($q);
        $row=mysql_fetch_row($rr);
	return intval($row[0]);
}

function checkright($module, $right) {
	if(getuserright($module)<$right) {
		echo "��Ȩ��";
		exit(0);
	}
}

// "0" or "1"
function getticketdisplaymode(){
	$user = $_SESSION["user"];
        $q = "select value from userpref where user='$user' and name='ticketdisplaymode'";
        $rr=mysql_query($q);
        if($rr) {
		$row=mysql_fetch_row($rr);
		if( $row[0]=="1" ) 
                	return "1";  
	}
       	return "0";  
}

$cmd=safe_get("cmd");

if ($cmd=="file_down") {
	$login=$_SESSION["login"];
	if($login<>1) {   // �û�û�е�¼
		echo "���¼������";
		exit(0);
	}
	if(getuserright("info")<1) {
		echo "��Ȩ������";
		exit(0);
	}
	$fid=safe_get("fid");
	$q="select * from file where id=".$fid;
        $rr=mysql_query($q);
	$r=mysql_fetch_row($rr);
	$file=$uploaddir."/".$fid;
	if (file_exists($file)) {
    		header('Content-Description: File Transfer');
    		header('Content-Type: application/octet-stream');
		$fn=iconv("gb2312","utf-8",$r[2]);
    		header('Content-Disposition: attachment; filename="'.$fn.'"');
    		header('Expires: 0');
    		header('Cache-Control: must-revalidate');
    		header('Pragma: public');
    		header('Content-Length: ' . $r[3]);
    		readfile($file);
    		exit(0);
	}else  {
		echo "�ļ�������<p>";
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
	$_SESSION["login"]=0;
	$_SESSION["isadmin"]=0;
	echo "��¼�Ѿ��˳�";
}

if ($cmd=="login") {
	$id=safe_get("id");
	$pass=$_REQUEST["pass"];
	
	if( $id<>"" ) {
		$q="select isadmin,truename,pop3server from user where email='".$id."'";
		$rr=mysql_query($q);
		$r=mysql_fetch_row($rr);
		if($r) {
			$_SESSION["isadmin"]=$r[0];
			$_SESSION["truename"]=$r[1];
			$r = imap_open("{".$r[2].":110/pop3/novalidate-cert}INBOX",$id,$pass,0,1);
			if( $r ) {
				$_SESSION["login"]=1;
				$_SESSION["user"]=$id;
				echo "��¼����,��ѡ������ĸ���˵�";
				echo "<script language=JavaScript> parent.location='index.php?cmd=jifang'; </script>";
				exit(0);
			}
			echo "<font color=red>�����������</font>";

		} else
			echo "<font color=red>�û��������ڣ�����ϵ����Ա</font>";
	}
} // end cmd==login

@$login=$_SESSION["login"];
@$isadmin=$_SESSION["isadmin"];
if($login<>1) {   // �û�û�е�¼
	$login=0;
	$_SESSION["login"]=0;
	echo "<p>ϵͳ�汾: ".$sysversion;
	echo "<p>���κ���������ϵ ".$syslxr;
	echo "<p>";
	echo "����������������¼<p>";
	echo "ϵͳ�����ӵ��ʼ�POP3��������¼��֤����";
	echo " <form action=index.php method=post>";
	echo "<input name=cmd type=hidden value=login>";
	echo "�û�����:<input name=id><br>";
	echo "��������:<input name=pass type=password><p>";
	echo "<input type=submit value=\"�� ¼\"></form>";
	exit(0);
} // login <> 1

echo "<div id=\"nav\"><ul>";

if(getuserright("jifang")>0) 
	echo "<li><a href=index.php?cmd=jifang>����Ѳ��</a></li>";
if(getuserright("ticket")>0) 
	echo "<li><a href=index.php?cmd=ticket>���ϴ���</a></li>";
if(getuserright("server")>0) 
	echo "<li><a href=index.php?cmd=cab_list>����������</a></li>";
if(getuserright("odf")>0) 
	echo "<li><a href=index.php?cmd=odf_list>ODF����</a></li>";
if(getuserright("ip")>0) 
	echo "<li><a href=index.php?cmd=ip>IP����</a></li>";
if(getuserright("info")>0) 
	echo "<li><a href=index.php?cmd=info>������Ϣ</a></li>";
if(getuserright("user")>0) 
	echo "<li><a href=index.php?cmd=user>�û�����</a></li>";
if(getuserright("sysinfo")>0) 
	echo "<li><a href=index.php?cmd=sysinfo>ϵͳ����</a></li>";

echo "<li><a href=index.php?cmd=user_pref>��������</a></li>";

echo "<li>";
echo $_SESSION["truename"];
echo " From: ";
echo  $_SERVER["REMOTE_ADDR"]; 
echo "</li>";
echo "<li><a href=index.php?cmd=logout>�˳�</a></li>";
echo "</ul>";
echo "</div>";
echo "<div id=\"navbg\"></div><p>";

if ($cmd=="" ) 
	$cmd="jifang";

// JIFANG

if($cmd=="jifang_new") {
	checkright("jifang",2);
	$cmd="jifang";
	$huanjing=safe_get("huanjing");
	$server=safe_get("server");
	$msg=safe_get2("msg");
	$q="insert into jifang_daily(tm,huanjing,server,msg,op) values(now(),".$huanjing.",".$server.",'".$msg."','".$_SESSION["user"]."')";
	mysql_query($q);
}  else if($cmd=="jifang_modido") {
	checkright("jifang",3);
	$cmd="jifang";
	$id=safe_get("id");
	$huanjing=safe_get("huanjing");
	$server=safe_get("server");
	$msg=safe_get2("msg");
	$q="update jifang_daily set huanjing=".$huanjing.",server=".$server.",msg='".$msg."' where id=".$id;
	mysql_query($q);
}

if ( $cmd=="jifang") {
	checkright("jifang",1);
	echo "<a href=index.php?cmd=jifang&all=yes>�г����м�¼</a><p>";

	if( safe_get("all") == "yes" )
		$q="select id,tm,huanjing,server,msg,truename from jifang_daily,user where op=email order by id desc";
	else
		$q="select id,tm,huanjing,server,msg,truename from jifang_daily,user where op=email order by id desc limit 30";
	$rr=mysql_query($q);

	echo "<table border=1 cellspacing=0>";
	echo " <tr> <th>���</th> <th>ʱ��</th> <th>����</th> <th>������</th> <th>�¼�����</th> <th>ʵʩ��</th> </tr>";
	$count=0;

while($r=mysql_fetch_row($rr)){
	$count++;
	if ( ($r[3] == 1) ||($r[3] == 1)  ) 
		echo "<tr>";
	else
		echo "<tr>";
	if(getuserright("jifang")>=3) 
		echo "<td align=center><a href=index.php?cmd=jifang&id=".$r[0].">".$count."</a></td>";
	else 
		echo "<td align=center>".$count."</td>";
	echo "<td nowrap=\"nowrap\">".$r[1]."</td>";
	echo "<td>";
	if ($r[2] == 0) echo "<font color=red>�쳣</font>";
	else echo "����";
	echo "</td>";
	echo "<td>";
	if ($r[3] == 0) echo "<font color=red>�쳣</font>";
	else echo "����";
	echo "</td>";
	echo "<td>".$r[4]."</td>";
	echo "<td>".$r[5]."</td>";
	echo "</tr>";
	echo "\n";
}
	echo "</table>\n";
	$id = safe_get("id");
	if( $id ) {
		$q="select id,tm,huanjing,server,msg from jifang_daily where id=".$id;
		$rr=mysql_query($q);
		$r=mysql_fetch_row($rr);
		echo "<p>";
		echo "�޸ļ�¼<br>";
		echo "<form action=index.php method=post>";
		echo "<input name=cmd value=jifang_modido type=hidden>";
		echo "<input name=id value=".$r[0]." type=hidden>";
    		echo "ʱ��:".$r[1]."<br>";
    		echo "����״��: &nbsp;&nbsp;����<input type=radio name=huanjing value=1";
		if ($r[2] =="1") echo " checked";
		echo "> &nbsp; &nbsp; �쳣<input type=radio name=huanjing value=0";
		if ($r[2] =="0") echo " checked";
		echo "><br>";
    		echo "������״��: ����<input type=radio name=server value=1";
		if ($r[3] =="1") echo " checked";
		echo "> &nbsp; &nbsp; �쳣<input type=radio name=server value=0";
		if ($r[3] =="0") echo " checked";
		echo "><br>";
		echo "��������:<input type=text size=200 name=msg value=\"".$r[4]."\"><br>";
    		echo "<input type=submit name=�޸ļ�¼></form>";

	} else if(getuserright("jifang")>=2) {
		echo "<form action=index.php method=post>";
		echo "<input name=cmd value=jifang_new type=hidden>";
    		echo "����״��: &nbsp;����<input type=radio name=huanjing value=1 checked> &nbsp; &nbsp; �쳣<input type=radio name=huanjing value=0><br>";
    		echo "������״��: ����<input type=radio name=server value=1 checked> &nbsp; &nbsp; �쳣<input type=radio name=server value=0><br>";
		echo "��������:<input type=text size=200 name=msg><br>";
		echo "<input type=submit value=����Ѳ���¼>";
		echo "</form>";
	}
} // end cmd==jifang

// TICKET

if($cmd=="ticket_new") {
	checkright("ticket",2);
	$cmd="ticket";
	$st=safe_get("st");
	$memo=safe_get2("memo");
	$memo2=safe_get2("memo2");
	$isend=safe_get("isend");
	if( $isend ) 
		$q="insert into ticket (st,et,memo,op) values('".$st."','".$st."','".$memo."','".$_SESSION["user"]."')";
	else
		$q="insert into ticket (st,et,memo,op) values('".$st."','0-0-0 00:00:00','".$memo."','".$_SESSION["user"]."')";
	mysql_query($q);
	$q="SELECT LAST_INSERT_ID()";
	$rr=mysql_query($q);
	$r=mysql_fetch_row($rr);	
	$q="insert into ticketdetail (tid,tm,memo,op) values(".$r[0].",'".$st."','".$memo2."','".$_SESSION["user"]."')";
	mysql_query($q);
}  else if($cmd=="ticket_modi") {
	checkright("ticket",3);
	$cmd="ticket";
	$id=safe_get("id");
	$st=safe_get("st");
	$et=safe_get("et");
	$memo=safe_get2("memo");
	$q="update ticket set st='".$st."',et='".$et."',memo='".$memo."' where id='".$id."'";
	mysql_query($q);
} else if($cmd=="ticketdetail_modi") {
	checkright("ticket",3);
	$cmd="ticket";
	$tid=safe_get("tid");
	$did=safe_get("did");
	$tm=safe_get("tm");
	$memo=safe_get2("memo");
	$q="update ticketdetail set tm='".$tm."',memo='".$memo."' where id='".$did."'";
	mysql_query($q);
	$isend=safe_get("isend");
	if( $isend ) {
		$q="update ticket set et=\"".$tm."\" where id=".$tid;
		mysql_query($q);
	}
} else if($cmd=="ticketdetail_new") {
	checkright("ticket",2);
	$cmd="ticket";
	$tid=safe_get("tid");
	$tm=safe_get("tm");
	$memo=safe_get2("memo");
	$q="insert into ticketdetail (tid,tm,memo,op) values(".$tid.",'".$tm."','".$memo."','".$_SESSION["user"]."')";
	mysql_query($q);
	$isend=safe_get("isend");
	if( $isend )  {
		$q="update ticket set et=\"".$tm."\" where id=".$tid;
		mysql_query($q);
	}
}

if ($cmd=="ticket") {
	checkright("ticket",1);
	$tdm = getticketdisplaymode();
	
	echo "<a href=index.php?cmd=ticket&all=yes>�г����м�¼</a><p>";
	if( safe_get("all") == "yes" )
		$q="select id,st,et,memo,truename,UNIX_TIMESTAMP(et)- UNIX_TIMESTAMP(st) from ticket,user where op=email order by st desc";
	else
		$q="select id,st,et,memo,truename,UNIX_TIMESTAMP(et)- UNIX_TIMESTAMP(st) from ticket,user where op=email and ((year(st) = year(now())) or (year(et)=year(now())) or (year(et)=0)) order by st desc";
	$rr=mysql_query($q);

	echo "<table border=1 cellspacing=0>";
	if($tdm=="1")
		echo "<tr><th>���</th><th>��ʼʱ��</th><th>����ʱ��</th><th>����ʱ��</th><th>�¼�����/����</th><th>ʵʩ��</th> </tr>\n";
	else 
		echo "<tr><th>���</th><th>��ʼʱ��</th><th>����ʱ��</th><th>����ʱ��</th><th>�¼�����</th><th>ʱ��</th><th>����</th><th>ʵʩ��</th> </tr>\n";

	$count=0;
while($r=mysql_fetch_row($rr)){
	$count++;
	if ( $r[2] == "0000-00-00 00:00:00" ) 
		echo "<tr>";
	else
		echo "<tr>";
	$q="select id,tm,memo,truename from ticketdetail,user where op=email and tid='".$r[0]."' order by tm";
	$rr2=mysql_query($q);
	$rows=mysql_num_rows($rr2); 
	if($tdm=="1") {
		echo "<td rowspan=";
		echo $rows+1;
		echo " align=center>".$count."</td>";
	} else {
		echo "<td rowspan=".$rows." align=center>".$count."</td>";
	}
	if(getuserright("ticket")>=2) 
		if($tdm=="1") 
			echo "<td nowrap=\"nowrap\"><a href=index.php?cmd=ticket&id=".$r[0].">".$r[1]."</a></td>";
		else
			echo "<td rowspan=".$rows." nowrap=\"nowrap\"><a href=index.php?cmd=ticket&id=".$r[0].">".$r[1]."</a></td>";
	else
		if($tdm=="1") 
			echo "<td nowrap=\"nowrap\">".$r[1]."</td>";
		else 
			echo "<td rowspan=".$rows." nowrap=\"nowrap\">".$r[1]."</td>";
	if($tdm=="1")  {
		echo "<td nowrap=\"nowrap\">".$r[2]."</td>";
		echo "<td align=right nowrap=\"nowrap\">";
	} else {
		echo "<td rowspan=".$rows." nowrap=\"nowrap\">".$r[2]."</td>";
		echo "<td rowspan=".$rows." align=right nowrap=\"nowrap\">";
	}
	if ( $r[2] == "0000-00-00 00:00:00" )
		echo " ";
	else 
		echo round($r[5]/3600,1),"Сʱ";
	echo "</td>";
	
	if($tdm=="1")  {
		echo "<td>".$r[3]."</td>";
		echo "<td>".$r[4]."</td>";
		echo "</tr>";
	} else {
		echo "<td rowspan=".$rows.">".$r[3]."</td>";
	}
	$firstrow=1;
	while($r2=mysql_fetch_row($rr2)) {
		if($firstrow==1) 
			$firstrow=0;
		else {
			if ( $r[3] == "0000-00-00 00:00:00" ) 
				echo "<tr>";
			else
				echo "<tr>";
		}
		if($tdm=="1") echo "<td></td>";
		if(getuserright("ticket")>=3) 
			echo "<td nowrap=\"nowrap\"><a href=index.php?cmd=ticket&did=".$r2[0].">".$r2[1]."</a></td>";
		else
			echo "<td nowrap=\"nowrap\">".$r2[1]."</td>";
		if($tdm=="1")echo "<td></td>";
		echo "<td>".$r2[2]."</td>";
		echo "<td>".$r2[3]."</td>";
		echo "</tr>\n";
	}
}
	echo "</table>";
	$id = safe_get("id");
	$did = safe_get("did");
	if ( $did && (getuserright("ticket")>=3)) {
		$q="select id,tid,tm,memo from ticketdetail where id=".$did;
		$rr=mysql_query($q);
		$r=mysql_fetch_row($rr);
		echo "<p>";
		echo "�޸�ticket������Ϣ<br>";
		echo "<form action=index.php method=post>";
		echo "<input name=cmd value=ticketdetail_modi type=hidden>";
		echo "<input name=tid value=".$r[1]." type=hidden>";
		echo "<input name=did value=".$r[0]." type=hidden>";
    		echo "ʱ��:<input name=tm value=\"".$r[2]."\"><br>";
    		echo "����:<input name=memo value=\"".$r[3]."\" size=100><br>";
		echo "�������,���½���ʱ��:<input type=checkbox name=isend value=1><br>";
    		echo "<input type=submit value=�޸Ĵ����¼></form>";
	} else if( $id ) {
		$q="select id,st,et,memo from ticket where id=".$id;
		$rr=mysql_query($q);
		$r=mysql_fetch_row($rr);
		echo "<p>";
		if(getuserright("ticket")>=3) {
			echo "�޸�ticket<br>";
			echo "<form action=index.php method=post>";
			echo "<input name=cmd value=ticket_modi type=hidden>";
			echo "<input name=id value=".$r[0]." type=hidden>";
    			echo "��ʼʱ��:<input name=st value=\"".$r[1]."\"><br>";
    			echo "����ʱ��:<input name=et value=\"".$r[2]."\"><br>";
    			echo "�¼�����:<input name=memo value=\"".$r[3]."\"><br>";
    			echo "<input type=submit name=�޸�ticket></form>";
		}

		if(getuserright("ticket")>=2) {
			echo "������������<br>";
			echo "<form action=index.php method=post>";
			echo "<input name=cmd value=ticketdetail_new type=hidden>";
			echo "<input name=tid value=".$r[0]." type=hidden>";
			echo "ʱ��:<input name=tm value=\"";
			echo strftime("%Y-%m-%d %H:%M:00",time());
			echo "\"><br>";
			echo "��������:<input name=memo size=100><br>";
			echo "�������,���½���ʱ��:<input type=checkbox name=isend value=1><br>";
			echo "<input type=submit value=������������>";
			echo "</form>";
		}
	} else if(getuserright("ticket")>=2){
		echo "<form action=index.php method=post>";
		echo "<input name=cmd value=ticket_new type=hidden>";
		echo "��ʼʱ��:<input name=st value=\"";
		echo strftime("%Y-%m-%d %H:%M:00",time());
		echo "\"><br>";
		echo "�¼�����:<input name=memo><br>";
		echo "��������:<input name=memo2 size=100><br>";
		echo "һ�����¼���ֱ�Ӹ��½���ʱ��:<input type=checkbox name=isend value=1><br>";
		echo "<input type=submit value=�����¼���¼>";
		echo "</form>";
	}
} // end cmd==ticket

// SERVER/CAB

if($cmd=='cab_add') {
	checkright("server",2);
	$cabid = safe_get("cabid");
	$ps1= safe_get2("ps1");
	$ps2= safe_get2("ps2");
	$mgt = safe_get2("mgt");
	$cabuse = safe_get2("cabuse");
	$q="insert into JF_CAB values('$cabid','$ps1','$ps2','$mgt','$cabuse')";
	mysql_query($q);
	echo "�������<p>";
	$cmd='cab_list';
}

if($cmd=='cab_modido') {
	checkright("server",3);
	$oldcabid = safe_get("oldcabid");
	$cabid = safe_get("cabid");
	$ps1= safe_get2("ps1");
	$ps2= safe_get2("ps2");
	$mgt = safe_get2("mgt");
	$cabuse = safe_get2("cabuse");
	$q="update JF_CAB set CABID='$cabid',PS1='$ps1',PS2='$ps2',MGT='$mgt',CABUSE='$cabuse'  where CABID='$oldcabid'";
	mysql_query($q);
	echo "�޸����<p>";
	$cmd='cab_list';
}

if($cmd=='cab_modi') {
	checkright("server",3);
	$cabid = safe_get("cabid");
	echo "<form action=index.php method=post>";
	echo "<input type=hidden name=cmd value=cab_modido>";
	echo "<input type=hidden name=oldcabid value=$cabid>";
	$q="select * from JF_CAB where CABID='$cabid'";
	$rr=mysql_query($q);
	if($row=mysql_fetch_row($rr)) {
		echo "������: <input name=cabid value=$row[0]> <br>";
		echo "��;: <input name=cabuse value=$row[4]><br>";
		echo "PS1: <input name=ps1 value='$row[1]' size=80> <br>";
		echo "PS2: <input name=ps2 value='$row[2]' size=80> <br>";
		echo "������: <input name=mgt value='$row[3]' size=80> <br>";
		echo "<input type=submit name=Submit value=�޸�>";
		echo "</form>";
	}
}

if ($cmd=='cab_list') {
	checkright("server",1);
	echo "������Ϣ<p><table border=1>";
	echo "<tr><th>������</th><th>��;</th><th>������</th><th>PS1</th><th>PS2</th><th>�豸��</t><th>����</th></tr>\n";

	$q="select * from JF_CAB order by CABID";
	$rr=mysql_query($q);
	while($row=mysql_fetch_row($rr)) {
		echo "<tr><td> "; echo "<a href=index.php?cmd=cabinfo_list&cabid=$row[0]>$row[0]</a>";
		echo "</td><td>"; echo $row[4];
		echo "</td><td>"; echo $row[3];
		echo "</td><td>"; echo $row[1];
		echo "</td><td>"; echo $row[2];
		echo "</td><td>"; 
		$rr2=mysql_query("select count(*) from JF_Server where CABID='$row[0]'");
		$r=mysql_fetch_row($rr2);
		echo $r[0];
		echo "</td><td>"; 
		if(getuserright("server")>=3)
			echo "<a href=index.php?cmd=cab_modi&cabid=$row[0]>�޸�</a>";
		echo "</td></tr>\n";
	}
	echo "</table>";
	if(getuserright("server")>=2) {
?>

	<form action=index.php method=post>
	<input type=hidden name=cmd value=cab_add>
	���ӻ���:<p>
	����ID: <input name=cabid>��ĸ�����֣����԰���-_ <br> 
	��;: <input name=cabuse size=80> <br>
	PS1: <input name=ps1 size=80> <br>
	PS2: <input name=ps2 size=80> <br>
	������: <input name=mgt size=80> <br>
	<input type="submit" name="Submit" value="����">
	</form>
<?php
	}
	exit(0);
} // end cmd = cab_list


if($cmd=='server_add') {
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
        $q="insert into JF_Server values('$serverid','$cabid',$startu,$endu,'$kvm','$type','$name','$user','$mgt','$ip1','$ip2','$mac1','$mac2','$sn','$connector','$comment')";
        mysql_query($q);
	echo $q;
        echo "�������<p>";
        $cmd='cabinfo_list';
}

if($cmd=='server_modido') {
	checkright("server",3);
        $oldserverid = safe_get("oldserverid");
        $serverid = safe_get("serverid");
        $cabid = safe_get("cabid");
        $startu = safe_get("startu");
        $endu = safe_get("endu");
	if($startu=="") $startu="1";
	if($endu=="") $endu="1";
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
        $q="update JF_Server set ServerID='$serverid',CABID='$cabid',StartU=$startu,EndU=$endu,KVM='$kvm',Type='$type',NAME='$name',USER='$user',MGT='$mgt',IP1='$ip1',IP2='$ip2',MAC1='$mac1',MAC2='$mac2',SN='$sn',Connector='$connector',Comment='$comment' where ServerID='$oldserverid'";
        mysql_query($q);
        echo "�޸����<p>";
        $cmd='cabinfo_list';
}

if($cmd=='server_modi') {
	checkright("server",3);
        $serverid = safe_get("serverid");
        echo "<form action=index.php method=post>";
        echo "<input type=hidden name=cmd value=server_modido>";
        echo "<input type=hidden name=oldserverid value=$serverid>";
        $q="select * from JF_Server where ServerID='$serverid'";
        $rr=mysql_query($q);
        if($row=mysql_fetch_row($rr)) {
        	echo "Server���: <input name=serverid value=\"$row[0]\"><br>";
                echo "������: <input name=cabid value=\"$row[1]\"> <br>";
                echo "��ʼU: <input name=startu value=\"$row[2]\"><br>";
                echo "����U: <input name=endu value=\"$row[3]\"><br>";
                echo "KVM: <input name=kvm value=\"$row[4]\"><br>";
                echo "�ͺ�: <input name=type value=\"$row[5]\"><br>";
                echo "����: <input name=name value=\"$row[6]\"><br>";
                echo "��;: <input name=user value=\"$row[7]\"><br>";
                echo "����Ա: <input name=mgt value=\"$row[8]\"><br>";
                echo "IP1: <input name=ip1 value=\"$row[9]\"><br>";
                echo "IP2: <input name=ip2 value=\"$row[10]\"><br>";
                echo "MAC1: <input name=mac1 value=\"$row[11]\"><br>";
                echo "MAC2: <input name=mac2 value=\"$row[12]\"><br>";
                echo "SN: <input name=sn value=\"$row[13]\"><br>";
                echo "�����豸: <input name=connector value=\"$row[14]\"><br>";
                echo "��ע: <input name=comment value=\"$row[15]\"><br>";
                echo "<input type=submit name=Submit value=�޸�>";
                echo "</form>";
        }
}


if ( $cmd=='cabinfo_list') {
	checkright("server",1);
	$cabid = safe_get("cabid");
	$q="select *,now() from JF_CAB where CABID='$cabid'";
	$rr=mysql_query($q);
	$row=mysql_fetch_row($rr);
	echo "<table border=1 width=800>";
	echo "<tr><td width=20%>";
	echo "������:";
	echo "</td><td>";
	echo $row[0];
	echo "</td><td width=20%>";
	echo "����������:";
	echo "</td><td>";
	echo $row[3];
	echo "</td></tr>";
	echo "<tr><td>";
	echo "��Դ1:";
	echo "</td><td>";
	echo $row[1];
	echo "</td><td>";
	echo "��Դ2:";
	echo "</td><td>";
	echo $row[2];
	echo "</td></tr>";
	echo "<tr><td>";
	echo "��;:";
	echo "</td><td colspan=3>";
	echo $row[4];
	echo "</td>";
	echo "<tr><td>";
	echo "��ӡʱ��:";
	echo "</td><td colspan=3>";
	echo $row[5];
	echo "</td></tr>";
	echo "</table>";
	echo "\n";
?>		
<p><font size=1>
	<table border=1>
	<tr><th>U</th><th>KVM</th><th>�������ͺ�</th><th>����������</th><th>��������;</th>
	<th>������</th><th>IP��ַ</th><th>MAC��ַ</th><th>SN</th><th>��������</th><th>��ע</th></tr>
<?php
	$q="select EndU-StartU+1,EndU,KVM,Type,JF_Server.NAME,JF_Server.USER,MGT,IP1,IP2,MAC1,MAC2,SN,Connector,Comment,ServerID from JF_Server where CABID= '$cabid' order by EndU desc";
	$rr=mysql_query($q);
	while($row=mysql_fetch_row($rr)) {
		echo "<tr><td>"; 
		if(getuserright("server")>=3) 
			echo "<a href=index.php?cmd=server_modi&serverid=$row[14]>$row[1]</a>";
		else
			echo "$row[1]";
		echo "</td><td rowspan=".$row[0].">"; echo $row[2];
		echo "</td><td rowspan=".$row[0].">"; echo $row[3];
		echo "</td><td rowspan=".$row[0].">"; echo $row[4];
		echo "</td><td rowspan=".$row[0].">"; echo $row[5];
		echo "</td><td rowspan=".$row[0].">"; echo $row[6];
		echo "</td><td rowspan=".$row[0].">"; echo $row[7];
		if( $row[8]!='') { echo "<br>"; echo $row[8]; }
		echo "</td><td rowspan=".$row[0].">"; echo $row[9];echo "<br>";echo $row[10];
		echo "</td><td rowspan=".$row[0].">"; echo $row[11];
		echo "</td><td rowspan=".$row[0].">"; echo $row[12];
		echo "</td><td rowspan=".$row[0].">"; echo $row[13];
		echo "</td></tr>\n";
		if($row[0]>1) {
			for($i=0;$i<$row[0]-1;$i++) {
				echo "<tr><td>";
				echo $row[1]-$i-1;
			}
		echo "</td></tr>\n";
		
		}

	}
	echo "</table>";

	if(getuserright("server")>=2) {
        	echo "<form action=index.php method=post>";
        	echo "<input type=hidden name=cmd value=server_add>";
        	echo "Server���: <input name=serverid>����Ψһ������Ϊ��<br>";
        	echo "������: <input name=cabid value=\"$cabid\"> <br>";
        	echo "��ʼU: <input name=startu>����<br>";
        	echo "����U: <input name=endu>����<br>";
        	echo "KVM: <input name=kvm><br>";
        	echo "�ͺ�: <input name=type><br>";
        	echo "����: <input name=name><br>";
        	echo "��;: <input name=user><br>";
        	echo "����Ա: <input name=mgt><br>";
        	echo "IP1: <input name=ip1><br>";
        	echo "IP2: <input name=ip2><br>";
        	echo "MAC1: <input name=mac1><br>";
        	echo "MAC2: <input name=mac2><br>";
        	echo "SN: <input name=sn><br>";
        	echo "�����豸: <input name=connector><br>";
        	echo "��ע: <input name=comment><br>";
        	echo "<input type=submit name=Submit value=����>";
        	echo "</form>";
	}
} // end cmd = 'cabinfo_list'

// ODF

if($cmd=='odf_new') {
	checkright("odf",2);
	$bh = safe_get2("bh");
	$jf= safe_get2("jf");
	$use= safe_get2("use");
	$memo = safe_get2("memo");
	$q="insert into ODF (JF,BH,`USE`,MEMO) values('$jf','$bh','$use','$memo')";
	mysql_query($q);
	for ($i=1; $i<=12; $i++) {
		$q="insert into ODFPAN (BH,X) values('$bh',$i)";
		mysql_query($q);
	}
	echo "�������<p>";
	$cmd='odf_list';
}

if($cmd=='odf_modido') {
	checkright("odf",3);
	$odfid = safe_get("odfid");
	$bh = safe_get2("bh");
	$jf= safe_get2("jf");
	$use= safe_get2("use");
	$memo = safe_get2("memo");

	$q="select * from ODF where id ='$odfid'";
	$rr=mysql_query($q);
	$row=mysql_fetch_row($rr);
	$q="insert into hist (tm,oid,old,new) values (now(),'ODF$row[0]','$row[1]/$row[2]/$row[3]/$row[4]','$jf/$bh/$use/$memo')";
	mysql_query($q);

	$q="update ODF set JF='$jf',BH='$bh',`USE`='$use',MEMO='$memo' where id='$odfid'";
	mysql_query($q);
	echo "�޸����<p>";
	$cmd='odf_list';
}

if($cmd=='odf_modi') {
	checkright("odf",3);
	$odfid = safe_get("odfid");
	echo "<form action=index.php method=post>";
	echo "<input type=hidden name=cmd value=odf_modido>";
	echo "<input type=hidden name=odfid value=$odfid>";
	$q="select * from ODF where id ='$odfid'";
	$rr=mysql_query($q);
	if($row=mysql_fetch_row($rr)) {
		echo "����: <input name=jf value=$row[1]> <br>";
		echo "ODF���: <input name=bh value=$row[2]><br>";
		echo "��;: <input name=use value='$row[3]' size=80> <br>";
		echo "��ע: <input name=memo value='$row[4]' size=80> <br>";
		echo "<input type=submit name=Submit value=�޸�>";
		echo "</form>";
	}
}
if ($cmd=='odf_list') {
	checkright("odf",1);
	echo "ODF��Ϣ<p><table border=1>";
	echo "<tr><th>����</th><th>ODF���</th><th>��;</th><th>��ע</th><th>����</th></tr>\n";

	$q="select * from ODF order by JF,BH";
	$count = 0;
	$rr=mysql_query($q);
	while($row=mysql_fetch_row($rr)) {
		$count++;
		echo "<tr><td> "; 
		echo $row[1];
		echo "</td><td>"; 
		echo "<a href=index.php?cmd=odfpan_list&bh=$row[2]>$row[2]</a>";
		echo "</td><td>"; echo $row[3];
		echo "</td><td>"; echo $row[4];
		echo "</td><td>"; 
		if(getuserright("odf")>=3)
			echo "<a href=index.php?cmd=odf_mod&odfid=$row[0]>�޸�</a>";
		echo "</td></tr>\n";
	}
	echo "</table>";
	if(getuserright("odf")>=2) {
?>
	<form action=index.php method=post>
	<input type=hidden name=cmd value=odf_new>
	����ODF:<p>
	����: <input name=jf> <br> 
	ODF���: <input name=bh size=80> <br>
	��;: <input name=use size=80> <br>
	��ע: <input name=memo size=80> <br>
	<input type="submit" name="Submit" value="����">
	</form>
<?php
	}
	changehist("select * from hist where oid like 'ODF%' order by tm desc");
	exit(0);
} // end cmd = odf_list

if($cmd=='odfpan_modido') {
	checkright("odf",3);
	$id = safe_get("id");
	$bh = safe_get2("bh");
	$x = safe_get2("x");
	$s = safe_get2("s");
	$dx = safe_get2("dx");
	$use= safe_get2("use");
	$tx = safe_get2("tx");
	$sb = safe_get2("sb");
	$memo = safe_get2("memo");

	$q="select * from ODFPAN where id ='$id'";
	$rr=mysql_query($q);
	$row=mysql_fetch_row($rr);
	$q="insert into hist (tm,oid,old,new) values (now(),'PAN$row[0]','$row[1]/$row[2]/$row[3]/$row[4]/$row[5]/$row[6]/$row[7]/$row[8]','$bh/$x/$s/$dx/$use/$tx/$sb/$memo')";
	mysql_query($q);
	$q="update ODFPAN set BH='$bh',X='$x',S='$s',DX='$dx',`USE`='$use',TX='$tx',SB='$sb',MEMO='$memo' where id='$id'";
	mysql_query($q);
	echo "�޸����<p>";
	$cmd='odfpan_list';
}

if($cmd=='odfpan_modi') {
	checkright("odf",3);
	$id = safe_get("id");
	echo "<form action=index.php method=post>";
	echo "<input type=hidden name=cmd value=odfpan_modido>";
	echo "<input type=hidden name=id value=$id>";
	$q="select * from ODFPAN where id ='$id'";
	$rr=mysql_query($q);
	if($row=mysql_fetch_row($rr)) {
		echo "ODF���: <input name=bh value=$row[1]><br>";
		echo "о��: <input name=x value=$row[2]><br>";
		echo "��ɫ: <input name=s value=$row[3]><br>";
		echo "�Է�о��: <input name=dx value=$row[4]><br>";
		echo "��;: <input name=use value='$row[5]' size=80> <br>";
		echo "����: <input name=tx value=$row[6]><br>";
		echo "�豸: <input name=sb value=$row[7]><br>";
		echo "��ע: <input name=memo value='$row[8]' size=80> <br>";
		echo "<input type=submit name=Submit value=�޸�>";
		echo "</form>";
	}
	echo "<p>";
	$cmd='odfpan_list';
	changehist("select * from hist where oid = 'PAN$id' order by tm desc");
}
if ($cmd=='odfpan_list') {
	checkright("odf",1);
	echo "ODF����Ϣ<p>";
	$bh = safe_get2("bh");
	$q="select * from ODF where BH='$bh'";
    	$rr=mysql_query($q);
    	$row=mysql_fetch_row($rr);
	echo "����: ".$row[1]."<br>";
	echo "ODF���: ".$row[2]."<br>";
	echo "��;: ".$row[3]."<br>";
	echo "��ע: ".$row[4]."<p>";

	echo "<table border=1>";
	echo "<tr><th>ODF���</th><th>о��</th><th>��ɫ</ht><th>�Է�о��</th><th>��;</th><th>����</th><th>�豸</th><th>��ע</th></tr>\n";

	$q="select * from ODFPAN where BH='$bh' order by X";
	$count = 0;
	$rr=mysql_query($q);
	while($row=mysql_fetch_row($rr)) {
		$count++;
		echo "<tr><td>"; echo $row[1];
		echo "</td><td> "; 
		if(getuserright("odf")>=3) 	
			echo "<a href=index.php?cmd=odfpan_modi&id=$row[0]&bh=$row[1]>$row[2]</a>";
		else
			echo "$row[2]";
		echo "</td><td>"; echo $row[3];
		echo "</td><td>"; echo $row[4];
		echo "</td><td>"; echo $row[5];
		echo "</td><td>"; echo $row[6];
		echo "</td><td>"; echo $row[7];
		echo "</td><td>"; echo $row[8];
		echo "</td></tr>\n";
	}
	echo "</table>";
	exit(0);
} // end cmd = odfpan_list

// IP

if($cmd=="ip_new") {
	checkright("ip",2);
	$cmd="ip";
	$ip=safe_get("ip");
	$mask=safe_get("mask");
	$net=safe_get("net");
	$use=safe_get2("use");
	$lxr=safe_get2("lxr");
	$memo=safe_get2("memo");
	$q="insert into IP(IP,MASK,net,`use`,lxr,memo) values('$ip','$mask',$net,'$use','$lxr','$memo')";
	mysql_query($q);
}  else if($cmd=="ip_modi") {
	checkright("ip",3);
	$cmd="ip";
	$id=safe_get("id");
	$ip=safe_get("ip");
	$mask=safe_get("mask");
	$net=safe_get("net");
	$use=safe_get2("use");
	$lxr=safe_get2("lxr");
	$memo=safe_get2("memo");
	$q="update IP set IP='$ip',MASK='$mask',net=$net,`use`='$use',lxr='$lxr',memo='$memo' where id=$id";
	mysql_query($q);
}

if ( $cmd=="ip") {
	checkright("ip",1);
	$q="select id,IP,MASK,net,`use`,lxr,memo from IP order by inet_aton(IP)";
	$rr=mysql_query($q);
	echo "<table border=1 cellspacing=0>";
	echo " <tr> <th>���</th> <th>IP</th> <th>��;</th> <th>��ϵ��</th> <th>��ע</th> </tr>";
	$count=0;
while($r=mysql_fetch_row($rr)){
	$count++;
	if( $r[3] == '1' )   // network 
		echo "<tr style=\"background-color:#ffff00\">";
	else 
		echo "<tr>";
	if(getuserright("ip")>=3) 
		echo "<td align=center><a href=index.php?cmd=ip&id=".$r[0].">".$count."</a></td>";
	else 
		echo "<td align=center>".$count."</td>";
	if( $r[3] == '1' ) {  // network 
		echo "<td>";
		echo $r[1]."/".$r[2];
	} else {
		echo "<td align=right>";
		if ( $r[2]=="255.255.255.255" ) 
			echo $r[1];
		else 
			echo $r[1]."/".$r[2];
	}
	echo "</td>";
	echo "<td>".$r[4]."</td>";
	echo "<td>".$r[5]."</td>";
	echo "<td>".$r[6]."</td>";
	echo "</tr>";
	echo "\n";
}
	echo "</table>";
	$id = safe_get("id");
	if( $id ) {
		$q="select id,IP,MASK,net,`use`,lxr,memo from IP where id=".$id;
		$rr=mysql_query($q);
		$r=mysql_fetch_row($rr);
		echo "<p>";
		echo "�޸ļ�¼<br>";
		echo "<form action=index.php method=post>";
		echo "<input name=cmd value=ip_modi type=hidden>";
		echo "<input name=id value=".$r[0]." type=hidden>";
    		echo "IP: <input name=ip value=\"".$r[1]."\"><br>";
    		echo "MASK: <input name=mask value=\"".$r[2]."\"><br>";
		if ( $r[3] == '0' )
    			echo "network? no<input type=radio name=net value=0 checked>  yes<input type=radio name=net value=1>";
		else
    			echo "network? no<input type=radio name=net value=0>  yes<input type=radio name=net value=1 checked>";
		echo "<br>";
    		echo "��;: <input name=use value=\"".$r[4]."\"><br>";
    		echo "��ϵ��: <input name=lxr value=\"".$r[5]."\"><br>";
    		echo "��ע: <input name=memo value=\"".$r[6]."\"><br>";
    		echo "<input type=submit name=�޸ļ�¼></form>";

	} else if(getuserright("ip")>=2) {
		echo "<p><form action=index.php method=post>";
		echo "<input name=cmd value=ip_new type=hidden>";
    		echo "IP: <input name=ip><br>";
    		echo "MASK: <input name=mask><br>";
    		echo "network? no<input type=radio name=net value=0 checked>  yes<input type=radio name=net value=1><br>";
    		echo "��;: <input name=use><br>";
    		echo "��ϵ��: <input name=lxr><br>";
    		echo "��ע: <input name=memo><br>";
		echo "<input type=submit value=����IP��¼>";
		echo "</form>";
	}
} // end cmd==ip

// INFO

if($cmd=="info_new") {
	checkright("info",2);
	$cmd="info";
	$title=safe_get2("title");
	$memo=safe_get2("memo");
	if($title=="") {
		echo "<form action=index.php method=post>";
		echo "<input name=cmd value=info_new type=hidden>";
		echo "����:<input name=title size=75> <br>";
		echo "����:<textarea name=memo cols=100 rows=40></textarea><br>";
		echo " <input type=submit value=����������Ϣ> </form>";
 		exit(0);
	}
	$q="insert into info (title,memo) values('$title','$memo')";
	mysql_query($q);
}  else if($cmd=="info_modido") {
	checkright("info",3);
	$cmd="info";
	$id=safe_get("id");
	$title=safe_get2("title");
	$memo=safe_get2("memo");
	$q="update info set title='$title',memo='$memo' where id=$id";
	mysql_query($q);
} // end cmd==info_new

if ($cmd=="info_up") {
	checkright("info",3);
	$cmd="info";
	$id=safe_get("id");
	$q="update info set sortid=sortid-1 where id=$id";
	mysql_query($q);
}
if ($cmd=="info_down") {
	checkright("info",3);
	$cmd="info";
	$id=safe_get("id");
	$q="update info set sortid=sortid+1 where id=$id";
	mysql_query($q);
}

if ($cmd=="info") { 
	checkright("info",1);
	$q= "select id,title,left(memo,100) from info order by sortid,id";
	$rr=mysql_query($q);
	echo "<table border=1 cellspacing=0>";
	echo " <tr> <th>���</th> <th>����</th> <th>����ժҪ</th> <th>����</th> </tr>";

	$count=0;
	while($r=mysql_fetch_row($rr)){
		$count++;
		echo "<tr><td align=center>".$count."</td>";
		echo "<td><a href=index.php?cmd=info_detail&id=$r[0]>".$r[1]."<a/></td>";
		echo "<td><a href=index.php?cmd=info_detail&id=$r[0]>".$r[2]."<a/></td>";
		echo "<td>";
		if(getuserright("info")>=2) 
			echo "<a href=index.php?cmd=info_modi&id=$r[0]>�޸�<a/> ";
		if(getuserright("info")>=3) {
			echo "<a href=index.php?cmd=info_up&id=$r[0]>����<a/> ";
			echo "<a href=index.php?cmd=info_down&id=$r[0]>����<a/> ";
		}
		echo "</td>";
		echo "</tr>\n";
	}
	echo "</table>";
	echo "<p>";
	if(getuserright("info")>=2) 
		echo "<a href=index.php?cmd=info_new>����������Ϣ</a>";
} // end cmd==info

if ($cmd=="info_detail") {
	checkright("info",1);
	$id = safe_get("id");
	$q="select id,title,memo from info where id=".$id;
	$rr=mysql_query($q);
	$r=mysql_fetch_row($rr);
	echo "<p>";
	echo $r[1];
	echo "<hr><pre>";
	echo $r[2];
	echo "</pre>";
	echo "<hr>";
        $q="select * from file where infoid=".$id;
        $rr=mysql_query($q);
        echo "������Ϣ<table>";
        echo "<tr><th>���</th><th>ʱ��</th><th>�ļ���</th><th>��С</th><th>����</th><th>����</th></tr>\n";
        $count = 0;
        while($r=mysql_fetch_row($rr)) {
                $count++;
                echo "<tr><td>";echo $count;"</td>";
                echo "<td>".$r[5]."</td>";
                echo "<td>".$r[2]."</td>";
                echo "<td>".$r[3]."</td>";
                echo "<td>".$r[4]."</td>";
                echo "<td><a href=index.php?cmd=file_down&id=".$id."&fid=".$r[0]." target=_blank>����</a></td>";
                echo "</tr>\n";
        }
        echo "</table><p>";

} // end cmd==info_detail

if ($cmd=="file_upload") {
	checkright("info",2);
	if($_FILES['userfile']['error']<>0) { 
		echo "�ļ����ش���<p>";
		echo "�������:".$_FILES['userfile']['error'];
		$cmd="info_modi";
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
	$cmd="info_modi";
}

if ($cmd=="info_modi") {
	checkright("info",2);
	$id = safe_get("id");
	$q ="select id,title,memo from info where id=".$id;
	$rr=mysql_query($q);
	$r=mysql_fetch_row($rr);

	echo "�޸���Ϣ<br>";
	echo "<form action=index.php method=post>";
	echo "<input name=cmd value=info_modido type=hidden>";
	echo "<input name=id value=".$r[0]." type=hidden>";
    	echo "����:<br><input name=title value=\"".$r[1]."\" size=75><p>";
   	echo "����:<br><textarea name=memo cols=100 rows=40>";
	echo $r[2];
	echo "</textarea><p>";
	if(getuserright("info")>=3) 
   		echo "<input type=submit value=�޸�></form><p>";
	else echo "</form><p>";
	echo "<hr>";

	$q="select * from file where infoid=".$id;
	$rr=mysql_query($q);
	echo "������Ϣ<table>";
	echo "<tr><th>���</th><th>ʱ��</th><th>�ļ���</th><th>��С</th><th>����</th><th>����</th></tr>\n";
	$count = 0;
	while($r=mysql_fetch_row($rr)) {
		$count++;
		echo "<tr><td>";echo $count;"</td>";
		echo "<td>".$r[5]."</td>";
		echo "<td>".$r[2]."</td>";
		echo "<td>".$r[3]."</td>";
		echo "<td>".$r[4]."</td>";
		if(getuserright("info")>=3) 
			echo "<td><a href=index.php?cmd=file_del&id=".$id."&fid=".$r[0].">ɾ��(�Ƶ�����վ)</a></td>";
		else
			echo "<td></td>";
		echo "</tr>\n";
	}
	echo "</table><p>";
	echo "�ϴ�����<br>";
	echo "<form enctype=\"multipart/form-data\" action=\"index.php\" method=\"post\">";
	echo "<input name=cmd value=file_upload type=hidden>";
	echo "<input name=id value=".$id." type=hidden>";
	echo "��ѡ���ļ��� <br> ";
	echo "<input name=\"userfile\" type=\"file\"><br>";
	echo "<input type=\"submit\" value=\"�ϴ��ļ�\">   ";
	echo "</form>   ";
} // end cmd==info_detail


// USER 

if($cmd=="user_new") {
	checkright("user",3);
	$email = safe_get("email");
	$pop3server = safe_get("pop3server");
	@$fullname = safe_get2("fullname");
	$super = safe_get("super");
	$q="delete from user where email='$email'";
	mysql_query($q);
	if($super=="1") 
		$q="insert into user values('$email','$pop3server',1,'$fullname')";
	else 
		$q="insert into user values('$email','$pop3server',0,'$fullname')";
	mysql_query($q);
	$cmd="user";
}

if($cmd=="user_del") {
	checkright("user",3);
	$email = safe_get("email");
	if($email==$_SESSION["user"]) {
		echo "<font color=red>����ɾ���Լ�</font><p>";
	} else {
		$q="delete from user where email='$email'";
		mysql_query($q);
	}
	$cmd="user";
}

if($cmd=="user_right") {
	checkright("user",3);
	$user = safe_get("user");
	$module = safe_get("module");
	$right = safe_get("right");
	$q="delete from userright where user='$user' and module='$module'";
	mysql_query($q);
	if($right<>"0") 
		$q="insert into userright (user,module,`right`) values('$user','$module',$right)";
	mysql_query($q);
	$cmd="user";
}

if($cmd=="user") {
	checkright("user",1);
	$q="select * from user";
	$rr=mysql_query($q);
	$count = 0;
	echo "�û���Ϣ<p><table border=1>";
	echo "<tr><th>���</th><th>��¼��</th><th>POP3������</th><th>ȫ��</th><th>��������Ա</th><th>��ģ��Ȩ��</th>";
	if(getuserright("user")>=3) 
		echo "<th>����</th>";
	echo "</tr>\n";
	while($r=mysql_fetch_row($rr)) {
		$count++;
		echo "<tr><td align=center>";echo $count;"</td>";
		echo "<td>".$r[0]."</td>";
		echo "<td>".$r[1]."</td>";
		echo "<td>".$r[3]."</td>";
		echo "<td align=center>";
		if($r[2]=="0")
			echo "��";
		else echo "��";
		echo "</td>";
		echo "<td>";
		$q="select module.module,module.memo,userright.right from userright,module where userright.module =module.module and userright.user='".$r[0]."' order by module.id";
		
		$rr2=mysql_query($q);
		echo "<table>";
		while($r2=mysql_fetch_row($rr2)) {
			echo "<tr>";
			echo "<td>".$r2[0]."</td>";
			echo "<td>".$r2[1]."</td>";
			echo "<td>";
			if($r2[2]=="1") echo "ֻ��";
			else if($r2[2]=="2") echo "����";
			else if($r2[2]=="3") echo "����";
			echo "</td>";
			echo "</tr>";
		}
		echo "</table>";
		echo "</td>";
		if(getuserright("user")>=3)  {
			echo "<td>";
			echo "<a href=index.php?cmd=user&email=".$r[0].">�޸�</a> &nbsp;&nbsp;";
			echo "<a href=index.php?cmd=user_del&email=".$r[0]." onclick=\"return confirm('ȷ��ɾ�� $r[0] ?');\">ɾ��</a>";
			echo "</td>";
		}
		echo "</tr>";
	}
	echo "</table>";
	echo "<hr width=500 align=left><p>";

	$email=safe_get("email");
	if( $email=="") {
		if(getuserright("user")>=2) {
?>

��¼ʱ�����õ�¼�����������ӵ�POP3�ʼ�����������֤�û����<br>
<form action=index.php method=get>
<input name=cmd value=user_new type=hidden>
�û��ʼ���¼����<input name=email><br>
POP3�ʼ���������<input name=pop3server><br>
�û�����: <input name=fullname><br>
�Ƿ񳬼�����Ա��<input name=super type=checkbox value=1><br>
<input type=submit value=�����û�>
</form>

<?php 
		}
	} else {
		if(getuserright("user")>=3) {
			$q="select email,pop3server,truename,isadmin from user where email='$email'";
			$rr=mysql_query($q);
			$r=mysql_fetch_row($rr);
?>
��¼ʱ�����õ�¼�����������ӵ�POP3�ʼ�����������֤�û����<br>
<form action=index.php method=get>
<input name=cmd value=user_new type=hidden>
�û��ʼ���¼����<input name=email value="<?php echo $r[0]; ?>"><br>
POP3�ʼ���������<input name=pop3server value="<?php echo $r[1]; ?>"><br>
�û�����: <input name=fullname value="<?php echo $r[2]; ?>"><br>
�Ƿ񳬼�����Ա��<input name=super type=checkbox value=1 <?php if($r[3]=="1") echo "checked"; ?>><br>
<input type=submit value=�޸��û�>
</form>

<?php
		}
	}
	if(getuserright("user")>=3) {
?>

<hr width=500 align=left>
<form action=index.php method=get>
<input name=cmd value=user_right type=hidden>
�û���¼����<select name=user>
<?php
	$q="select email from user";
	$rr=mysql_query($q);
	while($r=mysql_fetch_row($rr)) {
		echo "<option value=\"$r[0]\">$r[0]</option>\n";
	}
?>
</select>
ģ��: <select name=module>
<?php
	$q="select module,memo from module order by id";
	$rr=mysql_query($q);
	while($r=mysql_fetch_row($rr)) {
		echo "<option value=\"$r[0]\">$r[0]/$r[1]</option>\n";
	}
?>
</select><br>
Ȩ�ޣ�<br>
<input name=right type=radio value=0 checked>��<br>
<input name=right type=radio value=1>ֻ��<br>
<input name=right type=radio value=2>����<br>
<input name=right type=radio value=3>����<br>
<input type=submit value=�޸��û�Ȩ��>
</form>
<?php
	} // 
} // end cmd==user

// SYSINFO

if($cmd=="sysinfo_modi") {
	checkright("sysinfo",3);
	$sysversion = safe_get2("version");
	$systitle = safe_get2("title");
	$syslxr = safe_get2("lxr");
	$q="replace into sysinfo values('version','$sysversion')";
	mysql_query($q);
	$q="replace into sysinfo values('title','$systitle')";
	mysql_query($q);
	$q="replace into sysinfo values('lxr','$syslxr')";
	mysql_query($q);
	echo "�޸����";
	exit(0);
}

if($cmd=="sysinfo") {
	checkright("sysinfo",1);
?>

ϵͳ��Ϣ����<p>
<form action=index.php method=get>
<input name=cmd value=sysinfo_modi type=hidden>
ϵͳ�汾��<input name=version value="<?php echo $sysversion;?>"><br>
��ҳ���⣺<input name=title value="<?php echo $systitle;?>"><br>
��ϵ��Ϣ��<input name=lxr value="<?php echo $syslxr;?>"><br>

<?php 	if(getuserright("sysinfo")>=3) 
		echo "<input type=submit value=�޸�ϵͳ��Ϣ>";
?>

</form>
<?php
}  // end cmd==sysinfo


// USER_PREF

if($cmd=="user_pref_tdm") {
	$tdm=safe_get("tdm");
	$user = $_SESSION["user"];
	if( $tdm <> "1" ) $tdm = "0";
	$q = "replace into userpref values('$user','ticketdisplaymode','$tdm')";
	mysql_query($q);
	$cmd="user_pref";
}
if($cmd=="user_pref") {
	echo "����ƫ������<p>";
	$tdm = getticketdisplaymode();
?>

<form action=index.php method=get>
<input name=cmd value=user_pref_tdm type=hidden>
���ϴ�����ʾģʽ: 
<input type=radio name=tdm value=0<?php if($tdm=="0") echo " checked";?>>�ʺϿ���</input>
<input type=radio name=tdm value=1<?php if($tdm=="1") echo " checked";?>>�ʺ�խ��</input>
<input type=submit value=�޸Ĺ��ϴ�����ʾģʽ>  <p>

<?php
	
}
?>
