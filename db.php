<?php

$db_host = "localhost";
$db_user = "root";
$db_passwd = "";
$db_dbname = "noc";


$uploaddir = "/usr/src/noc/file";

// ���ӷ�����
if(($db=mysql_connect($db_host,$db_user,$db_passwd))<0){
 	Error( "���ӷ�����ʧ��!");
}

// ѡ�����ݿ�
if(mysql_select_db($db_dbname,$db)<0){
	Error("ѡ�����ݿ����");
}

?>
