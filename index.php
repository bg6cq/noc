<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" /> 
<link href="table.css" type="text/css" rel="stylesheet" /> 
<title>NOC.ustc.edu.cn</title>
</head>
<body bgcolor="#dddddd" text="#000000">

<?php
include("db.php");
session_start();

function checkvalue($str) {
	for($i = 0 ; $i < strlen($str) ; $i++) {
        	if( ($str[$i] >='a') && ($str[$i]<='z') ) continue;
        	if( ($str[$i] >='A') && ($str[$i]<='Z') ) continue;
        	if( ($str[$i] >='0') && ($str[$i]<='9') ) continue;
        	if( $str[$i] == '-' ) continue;
        	if( $str[$i] == '_' ) continue;
        	if( $str[$i] == ' ' ) continue;
        	if( $str[$i] == ':' ) continue;
        	echo $str."�е�".$i."�Ƿ��ַ�".$str[$i];
		exit(0);
	}
}

function safe_get($str) {
	$x = $_REQUEST[$str];
	checkvalue($x);
	return $x;
}

function changehist ($qstr) {
	echo "�޸ļ�¼<p><table border=1>";
        echo "<tr><th>ʱ��</th><th>�޸�����</th></tr>\n";

        $count = 0;
        $rr=mysql_query($qstr);
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

$cmd=safe_get("cmd");

if ($cmd=="logout") {
	$_SESSION["login"]=0;
	$_SESSION["isadmin"]=0;
	echo "��¼�Ѿ��˳�";
}

if ($cmd=="login") {
	$id=safe_get("id");
	$pass=$_REQUEST["pass"];
	
	if( $id<>"" ) {
		$query="select isadmin,truename from user where email='".$id."'";
		$result=mysql_query($query,$db);
		$r=mysql_fetch_row($result);
		if($r) {
			$_SESSION["isadmin"]=$r[0];
			$_SESSION["truename"]=$r[1];
			$r = imap_open("{202.38.64.8:110/pop3/novalidate-cert}INBOX",$id."@ustc.edu.cn",$pass,0,1);
			if( $r ) {
				$_SESSION["login"]=1;
				$_SESSION["user"]=$id;
				echo "��¼����,��ѡ������ĸ���˵�";
				echo "<script language=JavaScript> parent.location='index.php?cmd=jifang'; </script>";
				exit(0);
			}
		}
		echo "�û������������,����������";
	}
} // end cmd==login

$login=$_SESSION["login"];
$isadmin=$_SESSION["isadmin"];
if($login<>1) {   // �û�û�е�¼
	$login=0;
	$_SESSION["login"]=0;
	echo "<p>���κ���������ϵ james@ustc.edu.cn";
	echo "<p>";
	echo "�����������id�������¼<p>";
	echo " <form action=index.php method=post>";
	echo "<input name=cmd type=hidden value=login>����id:<input name=id>@ustc.edu.cn<br>";
	echo "��������: <input name=pass type=password><br>";
	echo "<input type=submit value=��¼></form>";
	exit(0);
} // login <> 1
?>

<a href=index.php?cmd=jifang>����Ѳ��</a>  
<a href=index.php?cmd=ticket>���ϴ���</a>  
<a href=index.php?cmd=cab_list>����������</a>  
<a href=index.php?cmd=odf_list>ODF����</a>  
<a href=index.php?cmd=ip>IP����</a>  
<a href=index.php?cmd=info>������Ϣ</a>  
<a href=index.php?cmd=logout>�˳�</a>

<?
echo $_SESSION["truename"];
echo " From:";
echo  $_SERVER["REMOTE_ADDR"]; 
echo "���κ���������ϵ james@ustc.edu.cn <hr>";

if ($cmd=="" ) {
	$cmd="jifang";
}

if($cmd=="jifang_new") {
	$cmd="jifang";
	$huanjing=safe_get("huanjing");
	$server=safe_get("server");
	$msg=mysql_escape_string($_REQUEST["msg"]);
	$q="insert into jifang_daily(tm,huanjing,server,msg,op) values(now(),".$huanjing.",".$server.",'".$msg."','".$_SESSION["user"]."')";
	mysql_query($q,$db);
}  else if($cmd=="jifang_modido") {
	$cmd="jifang";
	$id=safe_get("id");
	$huanjing=safe_get("huanjing");
	$server=safe_get("server");
	$msg=mysql_escape_string($_REQUEST["msg"]);
	$q="update jifang_daily set huanjing=".$huanjing.",server=".$server.",msg='".$msg."' where id=".$id;
	mysql_query($q,$db);
}

if ( $cmd=="jifang") {
	echo "<a href=index.php?cmd=jifang&all=yes>�г����м�¼</a><p>";

	if( $_REQUEST["all"] == "yes" )
		$query="select id,tm,huanjing,server,msg,truename from jifang_daily,user where op=email order by id desc";
	else
		$query="select id,tm,huanjing,server,msg,truename from jifang_daily,user where op=email order by id desc limit 30";
	$result=mysql_query($query,$db);

	echo "<table border=1 cellspacing=0>";
	echo " <tr> <th>���</th> <th>ʱ��</th> <th>����</th> <th>������</th> <th>�¼�����</th> <th>ʵʩ��</th> </tr>";

	$count=0;

while($r=mysql_fetch_row($result)){
	$count++;
	if ( ($r[3] == 1) ||($r[3] == 1)  ) 
		echo "<tr style=\"background-color:#6dc334\">";
	else
		echo "<tr style=\"background-color:#3592e2\">";
	echo "<td align=center><a href=index.php?cmd=jifang&id=".$r[0].">".$count."</a></td>";
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
		$query="select id,tm,huanjing,server,msg from jifang_daily where id=".$id;
		$result=mysql_query($query,$db);
		$r=mysql_fetch_row($result);
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

	} else {
		echo "<form action=index.php method=post>";
		echo "<input name=cmd value=jifang_new type=hidden>";
    		echo "����״��: &nbsp;&nbsp;����<input type=radio name=huanjing value=1 checked> &nbsp; &nbsp; �쳣<input type=radio name=huanjing value=0><br>";
    		echo "������״��: ����<input type=radio name=server value=1 checked> &nbsp; &nbsp; �쳣<input type=radio name=server value=0><br>";
		echo "��������:<input type=text size=200 name=msg><br>";
		echo "<input type=submit value=����Ѳ���¼>";
		echo "</form>";
	}
} // end cmd==jifang


if($cmd=="ticket_new") {
	$cmd="ticket";
	$st=safe_get("st");
	$memo=mysql_escape_string($_REQUEST["memo"]);
	$memo2=mysql_escape_string($_REQUEST["memo2"]);
	$q="insert into ticket (st,et,memo,op) values('".$st."','0-0-0 00:00:00','".$memo."','".$_SESSION["user"]."')";
	mysql_query($q,$db);
	$q="SELECT LAST_INSERT_ID()";
	$result=mysql_query($q,$db);
	$r=mysql_fetch_row($result);	
	$q="insert into ticketdetail (tid,tm,memo,op) values(".$r[0].",'".$st."','".$memo2."','".$_SESSION["user"]."')";
	mysql_query($q,$db);
}  else if($cmd=="ticket_modi") {
	$cmd="ticket";
	$id=safe_get("id");
	$st=safe_get("st");
	$et=safe_get("et");
	$memo=mysql_escape_string($_REQUEST["memo"]);
	$q="update ticket set st='".$st."',et='".$et."',memo='".$memo."' where id='".$id."'";
	mysql_query($q,$db);
} else if($cmd=="ticketdetail_modi") {
	$cmd="ticket";
	$tid=safe_get("tid");
	$did=safe_get("did");
	$tm=safe_get("tm");
	$memo=mysql_escape_string($_REQUEST["memo"]);
	$q="update ticketdetail set tm='".$tm."',memo='".$memo."' where id='".$did."'";
	mysql_query($q,$db);
	$isend=safe_get("isend");
	if( $isend ) 
	$q="update ticket set et=\"".$tm."\" where id=".$tid;
	mysql_query($q,$db);
} else if($cmd=="ticketdetail_new") {
	$cmd="ticket";
	$tid=safe_get("tid");
	$tm=safe_get("tm");
	$memo=mysql_escape_string($_REQUEST["memo"]);
	$q="insert into ticketdetail (tid,tm,memo,op) values(".$tid.",'".$tm."','".$memo."','".$_SESSION["user"]."')";
	mysql_query($q,$db);
	$isend=safe_get("isend");
	if( $isend )  {
		$q="update ticket set et=\"".$tm."\" where id=".$tid;
		mysql_query($q,$db);
	}
}

if ($cmd=="ticket") {
	echo "<a href=index.php?cmd=ticket&all=yes>�г����м�¼</a><p>";
	if( safe_get("all") == "yes" )
		$query="select id,st,et,memo,op,UNIX_TIMESTAMP(et)- UNIX_TIMESTAMP(st) from ticket order by st desc";
	else
		$query="select id,st,et,memo,op,UNIX_TIMESTAMP(et)- UNIX_TIMESTAMP(st) from ticket where (year(st) = year(now())) or (year(et)=year(now())) or (year(et)=0) order by st desc";
	$result=mysql_query($query,$db);

	echo "<table border=1 cellspacing=0>";
	echo " <tr> <th>���</th> <th>��ʼʱ��</th> <th>����ʱ��</th> <th>����ʱ��</th> <th>�¼�����</th> <th>ʱ��</th> <th>����</th> <th>ʵʩ��</th> </tr>\n";

	$count=0;
while($r=mysql_fetch_row($result)){
	$count++;
	if ( $r[2] == "0000-00-00 00:00:00" ) 
		echo "<tr style=\"background-color:#3592e2\">";
	else
		echo "<tr style=\"background-color:#6dc334\">";
	$q="select id,tm,memo,truename from ticketdetail,user where op=email and tid='".$r[0]."' order by tm";
	$result2=mysql_query($q,$db);
	$rows=mysql_num_rows($result2); 
	echo "<td rowspan=".$rows." align=center>".$count."</td>";
	if( $isadmin ==1 ) 
		echo "<td rowspan=".$rows." nowrap=\"nowrap\"><a href=index.php?cmd=ticket&id=".$r[0].">".$r[1]."</a></td>";
	else echo "<td rowspan=".$rows.">".$r[1]."</td>";
		echo "<td rowspan=".$rows." nowrap=\"nowrap\">".$r[2]."</td>";
	echo "<td rowspan=".$rows." align=right nowrap=\"nowrap\">";
	if ( $r[2] == "0000-00-00 00:00:00" )
		echo " ";
	else 
		echo round($r[5]/3600,1),"Сʱ";
	echo "</td>";
	
	echo "<td rowspan=".$rows.">".$r[3]."</td>";
	$firstrow=1;
	while($r2=mysql_fetch_row($result2)) {
		if($firstrow==1) 
			$firstrow=0;
		else {
			if ( $r[3] == "0000-00-00 00:00:00" ) 
				echo "<tr style=\"background-color:#3592e2\">";
			else
				echo "<tr style=\"background-color:#6dc334\">";
		}
		echo "<td nowrap=\"nowrap\"><a href=index.php?cmd=ticket&did=".$r2[0].">".$r2[1]."</a></td>";
		echo "<td>".$r2[2]."</td>";
		echo "<td>".$r2[3]."</td>";
		echo "</tr>\n";
	}
}
	echo "</table>";
	$id = safe_get("id");
	$did = safe_get("did");
	if ( $did ) {
		$query="select id,tid,tm,memo from ticketdetail where id=".$did;
		$result=mysql_query($query,$db);
		$r=mysql_fetch_row($result);
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
		$query="select id,st,et,memo from ticket where id=".$id;
		$result=mysql_query($query,$db);
		$r=mysql_fetch_row($result);
		echo "<p>";
		echo "�޸�ticket<br>";
		echo "<form action=index.php method=post>";
		echo "<input name=cmd value=iticket_modi type=hidden>";
		echo "<input name=id value=".$r[0]." type=hidden>";
    		echo "��ʼʱ��:<input name=st value=\"".$r[1]."\"><br>";
    		echo "����ʱ��:<input name=et value=\"".$r[2]."\"><br>";
    		echo "�¼�����:<input name=memo value=\"".$r[3]."\"><br>";
    		echo "<input type=submit name=�޸�ticket></form>";

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
	} else {
		echo "<form action=index.php method=post>";
		echo "<input name=cmd value=ticket_new type=hidden>";
		echo "��ʼʱ��:<input name=st value=\"";
		echo strftime("%Y-%m-%d %H:%M:00",time());
		echo "\"><br>";
		echo "�¼�����:<input name=memo><br>";
		echo "��������:<input name=memo2 size=100><br>";
		echo "<input type=submit value=�����¼���¼>";
		echo "</form>";
	}
} // end cmd==ticket


if($cmd=="info_new") {
	$cmd="info";
	$title=mysql_escape_string($_REQUEST["title"]);
	$memo=mysql_escape_string($_REQUEST["memo"]);
	if($title=="") {
		echo "<form action=index.php method=post>";
		echo "<input name=cmd value=info_new type=hidden>";
		echo "����:<input name=title size=75> <br>";
		echo "����:<textarea name=memo cols=100 rows=40></textarea><br>";
		echo " <input type=submit value=����������Ϣ> </form>";
 		exit(0);
	}
	$q="insert into info (title,memo) values('$title','$memo')";
	mysql_query($q,$db);
}  else if($cmd=="info_modido") {
	$cmd="info";
	$id=safe_get("id");
	$title=mysql_escape_string($_REQUEST["title"]);
	$memo=mysql_escape_string($_REQUEST["memo"]);
	$q="update info set title='$title',memo='$memo' where id=$id";
	mysql_query($q,$db);
} // end cmd==info_new

if ($cmd=="info_up") {
	$cmd="info";
	$id=safe_get("id");
	$q="update info set sortid=sortid-1 where id=$id";
	mysql_query($q,$db);
}
if ($cmd=="info_down") {
	$cmd="info";
	$id=safe_get("id");
	$q="update info set sortid=sortid+1 where id=$id";
	mysql_query($q,$db);
}

if ($cmd=="info") { 
	$query= "select id,title,left(memo,100) from info order by sortid,id";
	$result=mysql_query($query,$db);
	echo "<table border=1 cellspacing=0>";
	echo " <tr> <th>���</th> <th>����</th> <th>����ժҪ</th> <th>����</th> </tr>";

	$count=0;
	while($r=mysql_fetch_row($result)){
		$count++;
		echo "<tr><td>".$count."</td>";
		echo "<td><a href=index.php?cmd=info_detail&id=$r[0]>".$r[1]."<a/></td>";
		echo "<td><a href=index.php?cmd=info_detail&id=$r[0]>".$r[2]."<a/></td>";
		echo "<td><a href=index.php?cmd=info_modi&id=$r[0]>�޸�<a/> ";
		echo "<a href=index.php?cmd=info_up&id=$r[0]>����<a/> ";
		echo "<a href=index.php?cmd=info_down&id=$r[0]>����<a/> ";
		echo "</td>";
		echo "</tr>\n";
	}
	echo "</table>";
	echo "<p>";
	echo "<a href=index.php?cmd=info_new>����������Ϣ</a>";
} // end cmd==info

if ($cmd=="info_detail") {
	$id = safe_get("id");
	$query="select id,title,memo from info where id=".$id;
	$result=mysql_query($query,$db);
	$r=mysql_fetch_row($result);
	echo "<p>";
	echo $r[1];
	echo "<hr><pre>";
	echo $r[2];
	echo "</pre>";
} 

if ($cmd=="info_modi") {
	$id = safe_get("id");
	$query="select id,title,memo from info where id=".$id;
	$result=mysql_query($query,$db);
	$r=mysql_fetch_row($result);

	echo "�޸���Ϣ<br>";
	echo "<form action=index.php method=post>";
	echo "<input name=cmd value=info_modido type=hidden>";
	echo "<input name=id value=".$r[0]." type=hidden>";
    	echo "����:<input name=title value=\"".$r[1]."\" size=75><br>";
   	echo "����:<textarea name=memo cols=100 rows=40>";
	echo $r[2];
	echo "</textarea><br>";
   	echo "<input type=submit value=�޸�></form>";
} // end cmd==info_detail

if($cmd=='cab_add') {
	$cabid = safe_get("cabid");
	$ps1= safe_get("ps1");
	$ps2= safe_get("ps2");
	$mgt = mysql_escape_string($_REQUEST["mgt"]);
	$cabuse = mysql_escape_string($_REQUEST["cabuse"]);
	$qstr="insert into JF_CAB values('$cabid','$ps1','$ps2','$mgt','$cabuse')";
	$rr=mysql_query($qstr);
	echo "�������<p>";
	$cmd='cab_list';
}

if($cmd=='cab_modido') {
	$oldcabid = safe_get("oldcabid");
	$cabid = safe_get("cabid");
	$ps1= safe_get("ps1");
	$ps2= safe_get("ps2");
	$mgt = mysql_escape_string($_REQUEST["mgt"]);
	$cabuse = mysql_escape_string($_REQUEST["cabuse"]);
	$qstr="update JF_CAB set CABID='$cabid',PS1='$ps1',PS2='$ps2',MGT='$mgt',CABUSE='$cabuse'  where CABID='$oldcabid'";
	$rr=mysql_query($qstr);
	echo "�޸����<p>";
	$cmd='cab_list';
}

if($cmd=='cab_modi') {
	$cabid = safe_get("cabid");
	echo "<form action=index.php method=post>";
	echo "<input type=hidden name=cmd value=cab_modido>";
	echo "<input type=hidden name=oldcabid value=$cabid>";
	$qstr="select * from JF_CAB where CABID='$cabid'";
	$rr=mysql_query($qstr);
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
	echo "������Ϣ<p><table border=1>";
	echo "<tr><th>������</th><th>��;</th><th>������</th><th>PS1</th><th>PS2</th><th>�豸��</t><th>����</th></tr>\n";

	$qstr="select * from JF_CAB order by CABID";
	$rr=mysql_query($qstr);
	while($row=mysql_fetch_row($rr)) {
		echo "<tr><td> "; echo "<a href=index.php?cmd=cabinfo_list&cabid=$row[0]>$row[0]</a>";
		echo "</td><td>"; echo $row[4];
		echo "</td><td>"; echo $row[3];
		echo "</td><td>"; echo $row[1];
		echo "</td><td>"; echo $row[2];
		echo "</td><td>"; 
		$r=mysql_query("select count(*) from JF_Server where CABID='$row[0]'");
		$r=mysql_fetch_row($r);
		echo $r[0];
		echo "</td><td>"; 
		echo "<a href=index.php?cmd=cab_modi&cabid=$row[0]>�޸�</a>";
		echo "</td></tr>\n";
	}
	echo "</table>";
?>

	<form action=index.php method=post>
	<input type=hidden name=cmd value=cab_add>
	���ӻ���:<p>
	����ID: <input name=cabid> <br> 
	��;: <input name=cabuse size=80> <br>
	PS1: <input name=ps1 size=80> <br>
	PS2: <input name=ps2 size=80> <br>
	������: <input name=mgt size=80> <br>
	<input type="submit" name="Submit" value="����">
	</form>
<?
	exit(0);
} // end cmd = cab_list


if($cmd=='server_add') {
	$serverid = safe_get("serverid");
        $cabid = safe_get("cabid");
        $startu = safe_get("startu");
        $endu = safe_get("endu");
        $kvm = safe_get("kvm");
        $type = safe_get("type");
        $name = safe_get("name");
        $user = safe_get("user");
        $mgt = safe_get("mgt");
        $ip1 = safe_get("ip1");
        $ip2 = safe_get("ip2");
        $mac1 = safe_get("mac1");
        $mac2 = safe_get("mac2");
        $sn = safe_get("sn");
        $connector = safe_get("connector");
        $comment = safe_get("comment");
        $qstr="insert into JF_Server values('$serverid','$cabid',$startu,$endu,'$kvm','$type','$name','$user','$mgt','$ip1','$ip2','$mac1','$mac2','$sn','$connector','$comment')";
        $rr=mysql_query($qstr);
        echo "�������<p>";
        $cmd='cabinfo_list';
}

if($cmd=='server_modido') {
        $oldserverid = safe_get("oldserverid");
        $serverid = safe_get("serverid");
        $cabid = safe_get("cabid");
        $startu = safe_get("startu");
        $endu = safe_get("endu");
        $kvm = safe_get("kvm");
        $type = safe_get("type");
        $name = safe_get("name");
        $user = safe_get("user");
        $mgt = safe_get("mgt");
        $ip1 = safe_get("ip1");
        $ip2 = safe_get("ip2");
        $mac1 = safe_get("mac1");
        $mac2 = safe_get("mac2");
        $sn = safe_get("sn");
        $connector = safe_get("connector");
        $comment = safe_get("comment");
        $qstr="update JF_Server set ServerID='$serverid',CABID='$cabid',StartU=$startu,EndU=$endu,KVM='$kvm',Type='$type',NAME='$name',USER='$user',MGT='$mgt',IP1='$ip1',IP2='$ip2',MAC1='$mac1',MAC2='$mac2',SN='$sn',Connector='$connector',Comment='$comment' where ServerID='$oldserverid'";
        $rr=mysql_query($qstr);
        echo "�޸����<p>";
        $cmd='cabinfo_list';
}

if($cmd=='server_modi') {
        $serverid = safe_get("serverid");
        echo "<form action=index.php method=post>";
        echo "<input type=hidden name=cmd value=server_modido>";
        echo "<input type=hidden name=oldserverid value=$serverid>";
        $qstr="select * from JF_Server where ServerID='$serverid'";
        $rr=mysql_query($qstr);
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
	$cabid = safe_get("cabid");
	$qstr="select *,now() from JF_CAB where CABID='$cabid'";
	$rr=mysql_query($qstr);
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

<p>
<p><font size=1>
	<table border=1>
	<tr><th>U</th><th>KVM</th><th>�������ͺ�</th><th>����������</th><th>��������;</th>
	<th>������</th><th>IP��ַ</th><th>MAC��ַ</th><th>SN</th><th>��������</th><th>��ע</th></tr>
<?
	$qstr="select EndU-StartU+1,EndU,KVM,Type,JF_Server.NAME,JF_Server.USER,MGT,IP1,IP2,MAC1,MAC2,SN,Connector,Comment,ServerID from JF_Server where CABID= '$cabid' order by EndU desc";
	$rr=mysql_query($qstr);
	while($row=mysql_fetch_row($rr)) {
		echo "<tr><td>"; 
		echo "<a href=index.php?cmd=server_modi&serverid=$row[14]>$row[1]</a>";
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

        echo "<form action=index.php method=post>";
        echo "<input type=hidden name=cmd value=server_add>";
        echo "Server���: <input name=serverid><br>";
        echo "������: <input name=cabid value=\"$cabid\"> <br>";
        echo "��ʼU: <input name=startu><br>";
        echo "����U: <input name=endu><br>";
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

	
} // end cmd = 'cabinfo_list'


if($cmd=="ip_new") {
	$cmd="ip";
	$ip=safe_get("ip");
	$mask=safe_get("mask");
	$net=safe_get("net");
	$use=safe_get("use");
	$lxr=safe_get("lxr");
	$memo=safe_get("memo");
	$q="insert into IP(IP,MASK,net,`use`,lxr,memo) values('$ip','$mask',$net,'$use','$lxr','$memo')";
	mysql_query($q,$db);
}  else if($cmd=="ip_modi") {
	$cmd="ip";
	$id=safe_get("id");
	$ip=safe_get("ip");
	$mask=safe_get("mask");
	$net=safe_get("net");
	$use=safe_get("use");
	$lxr=safe_get("lxr");
	$memo=safe_get("memo");
	$q="update IP set IP='$ip',MASK='$mask',net=$net,`use`='$use',lxr='$lxr',memo='$memo' where id=$id";
	mysql_query($q,$db);
}

if ( $cmd=="ip") {
	$query="select id,IP,MASK,net,`use`,lxr,memo from IP order by inet_aton(IP)";
	$result=mysql_query($query,$db);
	echo "<table border=1 cellspacing=0>";
	echo " <tr> <th>���</th> <th>IP</th> <th>��;</th> <th>��ϵ��</th> <th>��ע</th> </tr>";
	$count=0;
while($r=mysql_fetch_row($result)){
	$count++;
	if( $r[3] == '1' )   // network 
		echo "<tr style=\"background-color:#ffff00\">";
	else 
		echo "<tr>";
	echo "<td align=center><a href=index.php?cmd=ip&id=".$r[0].">".$count."</a></td>";
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
		$query="select id,IP,MASK,net,`use`,lxr,memo from IP where id=".$id;
		$result=mysql_query($query,$db);
		$r=mysql_fetch_row($result);
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

	} else {
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


if($cmd=='odf_new') {
	$bh = safe_get("bh");
	$jf= safe_get("jf");
	$use= safe_get("use");
	$memo = safe_get("memo");
	$qstr="insert into ODF (JF,BH,`USE`,MEMO) values('$jf','$bh','$use','$memo')";
	$rr=mysql_query($qstr);
	for ($i=1; $i<=12; $i++) {
		$qstr="insert into ODFPAN (BH,X) values('$bh',$i)";
		$rr=mysql_query($qstr);
	}
	echo "�������<p>";
	$cmd='odf_list';
}

if($cmd=='odf_modido') {
	$odfid = safe_get("odfid");
	$bh = safe_get("bh");
	$jf= safe_get("jf");
	$use= safe_get("use");
	$memo = safe_get("memo");

	$qstr="select * from ODF where id ='$odfid'";
	$rr=mysql_query($qstr);
	$row=mysql_fetch_row($rr);
	$qstr="insert into hist (tm,oid,old,new) values (now(),'ODF$row[0]','$row[1]/$row[2]/$row[3]/$row[4]','$jf/$bh/$use/$memo')";
	mysql_query($qstr);

	$qstr="update ODF set JF='$jf',BH='$bh',`USE`='$use',MEMO='$memo' where id='$odfid'";
	mysql_query($qstr);
	echo "�޸����<p>";
	$cmd='odf_list';
}

if($cmd=='odf_modi') {
	$odfid = safe_get("odfid");
	echo "<form action=index.php method=post>";
	echo "<input type=hidden name=cmd value=odf_modido>";
	echo "<input type=hidden name=odfid value=$odfid>";
	$qstr="select * from ODF where id ='$odfid'";
	$rr=mysql_query($qstr);
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
	echo "ODF��Ϣ<p><table border=1>";
	echo "<tr><th>����</th><th>ODF���</th><th>��;</th><th>��ע</th><th>����</th></tr>\n";

	$qstr="select * from ODF order by JF,BH";
	$count = 0;
	$rr=mysql_query($qstr);
	while($row=mysql_fetch_row($rr)) {
		$count++;
		echo "<tr><td> "; 
		echo $row[1];
		echo "</td><td>"; 
		echo "<a href=index.php?cmd=odfpan_list&bh=$row[2]>$row[2]</a>";
		echo "</td><td>"; echo $row[3];
		echo "</td><td>"; echo $row[4];
		echo "</td><td>"; 
		echo "<a href=index.php?cmd=odf_mod&odfid=$row[0]>�޸�</a>";
		echo "</td></tr>\n";
	}
	echo "</table>";
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
<?
	changehist("select * from hist where oid like 'ODF%' order by tm desc");
	exit(0);
} // end cmd = odf_list


if($cmd=='odfpan_modido') {
	$id = safe_get("id");
	$bh = safe_get("bh");
	$x = safe_get("x");
	$s = safe_get("s");
	$dx = safe_get("dx");
	$use= safe_get("use");
	$tx = safe_get("tx");
	$sb = safe_get("sb");
	$memo = safe_get("memo");

	$qstr="select * from ODFPAN where id ='$id'";
	$rr=mysql_query($qstr);
	$row=mysql_fetch_row($rr);
	$qstr="insert into hist (tm,oid,old,new) values (now(),'PAN$row[0]','$row[1]/$row[2]/$row[3]/$row[4]/$row[5]/$row[6]/$row[7]/$row[8]','$bh/$x/$s/$dx/$use/$tx/$sb/$memo')";
	mysql_query($qstr);
	$qstr="update ODFPAN set BH='$bh',X='$x',S='$s',DX='$dx',`USE`='$use',TX='$tx',SB='$sb',MEMO='$memo' where id='$id'";
	$rr=mysql_query($qstr);
	$rr=mysql_query("commit");
	echo "�޸����<p>";
	$cmd='odfpan_list';
}

if($cmd=='odfpan_modi') {
	$id = safe_get("id");
	echo "<form action=index.php method=post>";
	echo "<input type=hidden name=cmd value=odfpan_modido>";
	echo "<input type=hidden name=id value=$id>";
	$qstr="select * from ODFPAN where id ='$id'";

	$rr=mysql_query($qstr);
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
	echo "ODF����Ϣ<p>";
	$bh = safe_get("bh");
	$qstr="select * from ODF where BH='$bh'";
    	$rr=mysql_query($qstr);
    	$row=mysql_fetch_row($rr);
	echo "����: ".$row[1]."<br>";
	echo "ODF���: ".$row[2]."<br>";
	echo "��;: ".$row[3]."<br>";
	echo "��ע: ".$row[4]."<p>";

	echo "<table border=1>";
	echo "<tr><th>ODF���</th><th>о��</th><th>��ɫ</ht><th>�Է�о��</th><th>��;</th><th>����</th><th>�豸</th><th>��ע</th></tr>\n";

	$qstr="select * from ODFPAN where BH='$bh' order by X";
	$count = 0;
	$rr=mysql_query($qstr);
	while($row=mysql_fetch_row($rr)) {
		$count++;
		echo "<tr><td>"; echo $row[1];
		echo "</td><td> "; echo "<a href=index.php?cmd=odfpan_modi&id=$row[0]&bh=$row[1]>$row[2]</a>";
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
?>

