/*
	SQL to detect rules not linked to category
	The alerts existing appear first
*/
select 'Signatures not mapped ';
select SG.rule_id from signature SG
where SG.rule_id not in ( select MP.rule_id from signature_category_mapping MP);

select 'Category not mapped';
select CT.cat_id from category CT
where CT.cat_id not in ( select MP.cat_id from signature_category_mapping MP);

select 'Mapping on inexistent signatures';
select CT.cat_id from category CT
where CT.cat_id not in ( select MP.cat_id from signature_category_mapping MP);

/*
	To delete unused maps :
delete from signature_category_mapping
where rule_id not in ( select SG.rule_id from signature SG);
*/

select 'Signatures mapped to more then one category';
select SG.rule_id,SG.description,count(*)
from signature SG, signature_category_mapping MP
where SG.rule_id = MP.rule_id
group by SG.rule_id,SG.description
having  count(*) > 1;

select SG.rule_id,SG.description,MP.cat_id,CT.cat_name
from signature SG, signature_category_mapping MP, category CT
where SG.rule_id in (
select SSG.rule_id 
 	from signature SSG, signature_category_mapping MMP 
 	where SSG.rule_id = MMP.rule_id 
 	group by SSG.rule_id 
 	having count(*) > 1 )
and SG.rule_id = MP.rule_id
and MP.cat_id  = CT.cat_id
order by SG.rule_id;


/*
	To delete multiple mappings

create table toto as
select SG.rule_id
from signature SG, signature_category_mapping MP
where SG.rule_id = MP.rule_id
group by SG.rule_id
having  count(*) > 1;

delete from signature_category_mapping 
where rule_id in ( select SG.rule_id
from toto SG );

drop table toto;

*/


/*
select SG.rule_id,SG.description,MP.cat_id,CT.cat_name
from signature SG, signature_category_mapping MP, category CT
where SG.rule_id = MP.rule_id 
  and MP.cat_id  = CT.cat_id 
  and SG.rule_id in (
 2503,11107     
)
order by SG.rule_id,SG.description;

*/					

select 'Mapping on inexistent category';
select MP.cat_id,MP.rule_id from signature_category_mapping MP
where MP.cat_id not in ( select CT.cat_id from category CT);
