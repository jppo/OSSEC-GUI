/*
	Trigger to copy "deleted" records fomr running database
	to history database.
*/

drop trigger if exists alert_del ;

delimiter //








create trigger alert_del      
	before delete on alert
	for each row
BEGIN
		insert into ossec_history.alert
		(	id,			server_id,		rule_id,	level,		
			timestamp, 	location_id,	src_ip,		
			dst_ip,		src_port, 		dst_port,		
			alertid,	user,			full_log,
			is_hidden,		tld)
		values (	old.id,			old.server_id,		old.rule_id,	old.level,		
					old.timestamp, 	old.location_id,	old.src_ip,		
					old.dst_ip,		old.src_port, 		old.dst_port,		
					old.alertid,	old.user,			old.full_log,
					old.is_hidden,	old.tld);
END;
//

delimiter ;
