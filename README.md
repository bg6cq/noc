�򵥵Ļ�������������

<pre>

1. ��װ����

��򵥵�ʹ�÷������������������

1.1 �����ovf�ļ��뵽 http://staff.ustc.edu.cn/~james/noc ����
    �����root����Ϊ noctest, ���������ϵͳ�������������������������޸�����
    �������޸�IP��ַ������INSTALL.txt 2.5 ������һ���û�����ʹ�ã���ִ������
mysql noc
> INSERT INTO user VALUES ('xyz@x.y.z', 'x.x.x.x', 1, '��һ���û�');

����xyz@x.y.z �ǵ�¼����x.x.x.x�Ƕ�Ӧ��POP3��������1 �ĺ����ǳ�������Ա
���������û�������û��������룬����pop3Э�����ӵ��ʼ��������ϲ���֤���

�������������汾Ϊ4.7 2015.08.28 �汾��ʹ������������������밴������1.4�������

1.2 ���������ʼ��װ����ϸ������ο� INSTALL.txt

1.3 ���������ϵͳ��װ, ��ο�INSTALL.txt

1.4 �����������, ��ο�INSTALL.txt
cd /usr/src/noc/web
git pull
�����������µ����

������ݿ�ṹ��Ҫ������ֻ���ֹ�����������ο������޸���־���֡�

2. ��֤˵��
���������û�������û��������룬����pop3Э�����ӵ��ʼ��������ϲ���֤���
������Լ��Ļ������޸���֤����

3. ��֪����
3.1 163.com ��ҵ�����û���POP3��������pop.qiye.163.com���޷�ʹ��POP3��¼��ԭ����

4. �޸���־
4.1 2015.09.24 ���� ������Ϣ �и�������
4.2 2015.09.25 �����û�Ȩ�޹���
4.3 2015.09.25 ���� pop3server �ֶ�
4.4 2015.09.26 ���� sysinfo ϵͳ����
4.5 2015.09.26 ���� INSTALL.txt 
4.6 2015.09.26 �������������
4.7 2015.09.28 ���ӹ��ϴ�����ʾ�Ŀ���/խ��ѡ��
ע����ʹ�������������� userpref table
CREATE TABLE `userpref` (
  `user` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `value` varchar(200) NOT NULL,
  PRIMARY KEY  (`user`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

4.8 2015.09.30 ������ϵ��/VM����
ע����ʹ�����������޸ı�ṹ         

ALTER TABLE  JF_Server CHANGE USER USER VARCHAR( 50 ) ;

update module set id=97 where id=7;
update module set id=98 where id=8;

insert into module values(7,'vm','VM����ģ��');
insert into module values(8,'lxr','��ϵ�˹���ģ��');

CREATE TABLE `lxr` (
  `id` int(5) unsigned NOT NULL auto_increment,
  `dept` varchar(40) NOT NULL,
  `name` varchar(20) NOT NULL,
  `dh` varchar(20) NOT NULL,
  `sj` varchar(20) NOT NULL,
  `email` varchar(50) NOT NULL,
  `qq` varchar(20) NOT NULL,
  `memo` varchar(200) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
CREATE TABLE `vm_cluster` (
  `id` int(5) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `ip` varchar(100) NOT NULL,
  `memo` varchar(200) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
CREATE TABLE `vm_server` (
  `id` int(5) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `cid` int(8) NOT NULL,
  `ip` varchar(50) NOT NULL,
  `memo` varchar(200) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
CREATE TABLE `vm_host` (
  `id` int(6) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `inuse` int(1) NOT NULL,
  `cid` varchar(50) NOT NULL,
  `ip` varchar(100) NOT NULL,
  `use` varchar(100) NOT NULL,
  `st` date NOT NULL,
  `et` date NOT NULL,
  `lxr` varchar(20) NOT NULL,
  `cpu` varchar(3) NOT NULL,
  `mem` varchar(3) NOT NULL,
  `disk` varchar(8) NOT NULL,
  `disk2` varchar(8) NOT NULL,
  `memo` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

4.9 2015.10.04 ���ӹ��ϴ�����ϵͳ/ԭ��/������Ϣ
ע����ʹ�����������޸ı�ṹ         

CREATE TABLE `ticket_system` (
  `id` int(5) unsigned NOT NULL auto_increment,
  `sortid` int(5) NOT NULL default '0',
  `desc` varchar(40) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

insert into ticket_system values (1,1,'������');
insert into ticket_system values (2,2,'��������');
insert into ticket_system values (3,3,'�����豸');
insert into ticket_system values (4,4,'����������');
insert into ticket_system values (5,5,'ר��');
insert into ticket_system values (6,6,'������ʩ');

CREATE TABLE `ticket_reason` (
  `id` int(5) unsigned NOT NULL auto_increment,
  `sortid` int(5) NOT NULL default '0',
  `desc` varchar(40) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

insert into ticket_reason values (1,1,'����');
insert into ticket_reason values (2,2,'��Դ');
insert into ticket_reason values (3,3,'����');
insert into ticket_reason values (4,4,'Ӳ��');
insert into ticket_reason values (5,5,'�ڴ�');
insert into ticket_reason values (6,6,'ģ��');
insert into ticket_reason values (7,7,'����');

ALTER TABLE  `ticket` ADD  `system` VARCHAR( 10 ) NOT NULL AFTER  `et`;
ALTER TABLE  `ticket` ADD  `reason` VARCHAR( 10 ) NOT NULL AFTER  `system` ;
ALTER TABLE  `ticket` ADD  `level` VARCHAR( 1 ) NOT NULL AFTER  `reason` ;

CREATE TABLE `ticket_level` (
  `id` int(2) NOT NULL auto_increment,
  `desc` varchar(40) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

insert into ticket_level values (1,'δ��');
insert into ticket_level values (2,'��΢');
insert into ticket_level values (3,'����');
insert into ticket_level values (4,'�ش�');

5. ��л
��л������ʦ���������ͽ��飺
����������ҵְҵ����ѧԺ �컪��
�Ϸʹ�ҵ��ѧ �̿���

</pre>
