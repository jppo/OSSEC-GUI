/*
This trigger on the "alert" table is intended to insert new rows n the "signature" table
as needed for the Web Interface.
So you will never get an alert without a signature.
No care is taken for the mapping from signature to category, a special interface exists
in the web application (management --> test map Sign/Categ.
*/

drop trigger if exists alert_signature ;

delimiter //

create trigger alert_signature
	before insert on alert
	for each row
BEGIN
DECLARE		flag	int(4);
DECLARE		l_id	int(10);
DECLARE		l_level tinyint(3);
DECLARE		l_desc  varchar(32);

set flag    := 0;
set l_id    := new.rule_id;
set l_level := new.level;
set l_desc  := concat("Signature id = ",l_id);

select count(*) into flag 
from signature sig
where sig.rule_id = l_id;
if ( flag <> 1 )
   then
		insert into signature 
		(id,rule_id,level,description)
		values (l_id,l_id,l_level,l_desc);
end if;

END;
//

delimiter ;
