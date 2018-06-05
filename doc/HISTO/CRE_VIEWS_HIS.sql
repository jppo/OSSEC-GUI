
create view agent as 
	select * from ossec_base.agent;

create view category as 
	select * from ossec_base.category;

create view location as 
	select * from ossec_base.location;

create view server as 
	select * from ossec_base.server;

create view signature as 
	select * from ossec_base.signature;

create view signature_category_mapping as 
	select * from ossec_base.signature_category_mapping;
