dump:
#dump all data
#	mysqldump --default-character-set=latin1 noc > noc.sql
#dump table create
	mysqldump -d --default-character-set=latin1 noc > noc.sql
	mysqldump --compact -d --default-character-set=latin1 noc IP JF_CAB JF_Server ODF ODFPAN file file_del hist info jifang_daily ticket ticketdetail user userright userpref lxr vm_cluster vm_server vm_host > noc.sql
	mysqldump --compact --default-character-set=latin1 --skip-extended-insert noc module sysinfo ticket_system ticket_reason ticket_level >> noc.sql
