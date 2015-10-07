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
