/*
	Script to "renumber' the base.alert table as it gets
	sometimes disordered.
	That script renumbers the alerts from MAX + 1 as found
	from the history alert table.
*/
use ossec_base;

select max(id) + 1 into @IDMAX from ossec_history.alert ;

select @IDMAX;


update ossec_base.alert set id = @IDMAX:=@IDMAX+1;

commit;

select (max(id) + 1) into @AUTOINC from ossec_base.alert ;

select @AUTOINC;

set @SQL = CONCAT('alter table ossec_base.alert AUTO_INCREMENT = ', @AUTOINC);

select @SQL ;

PREPARE st FROM @SQL;

EXECUTE st;

show create table alert \G

