/*

*/

select * from alert  AL, signature SG
where SG.rule_id = AL.rule_id 
and   AL.rule_id in ( 	select rule_id
						from signature_category_mapping
						where cat_id = 56
					)
;
