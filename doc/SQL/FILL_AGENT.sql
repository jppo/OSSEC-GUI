/*
desc agent;
+--------------+----------------------+------+-----+---------+----------------+
| Field        | Type                 | Null | Key | Default | Extra          |
+--------------+----------------------+------+-----+---------+----------------+
| id           | smallint(5) unsigned | NO   | PRI | NULL    | auto_increment |
| server_id    | smallint(5) unsigned | NO   | PRI | NULL    |                |
| last_contact | int(10) unsigned     | NO   |     | NULL    |                |
| ip_address   | varchar(46)          | NO   |     | NULL    |                |
| version      | varchar(32)          | NO   |     | NULL    |                |
| name         | varchar(64)          | NO   |     | NULL    |                |
| information  | varchar(128)         | NO   |     | NULL    |                |
+--------------+----------------------+------+-----+---------+----------------+

desc location;
+-----------+----------------------+------+-----+---------+----------------+
| Field     | Type                 | Null | Key | Default | Extra          |
+-----------+----------------------+------+-----+---------+----------------+
| id        | smallint(5) unsigned | NO   | PRI | NULL    | auto_increment |
| server_id | smallint(5) unsigned | NO   | PRI | NULL    |                |
| name      | varchar(128)         | NO   |     | NULL    |                |
+-----------+----------------------+------+-----+---------+----------------+


*/
insert into agent ( server_id, name,ip_address)
select server_id,
	SUBSTRING_INDEX(SUBSTRING_INDEX(name, ' ', 1), '->', 1),
	SUBSTRING_INDEX(SUBSTRING_INDEX(name,')',-1), '->',1)
from location
where name like '(%)%'
group by 2,3,1;
--
/*

update agent set last_contact = 
    (select max(al.timestamp) from alert al
     where al.location_id in 
        ( select location_id from location lo
          where lo.name like CONCAT(name,'%')
        )
	)
; 

select max(al.timestamp) from alert al, location lo
     where al.location_id  = lo.id
       and lo.name like '(kvm-web)%';

*/

*/
