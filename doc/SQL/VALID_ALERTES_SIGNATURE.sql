/*
	Verify signatures from alerts
*/

select AL.rule_id
from alert AL
where AL.rule_id not in (select SG.rule_id from signature SG)
order by AL.rule_id;

