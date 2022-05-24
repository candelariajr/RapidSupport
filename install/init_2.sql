drop database if exists rapid_support;
create database rapid_support;
use rapid_support;
create table rapid_support.incidents(
	id int not null primary key auto_increment,
    creation_date datetime default now(),
    ip varchar(64),
    username varchar(64),
    fname varchar(64),
    lname varchar(64),
    room_number varchar(64),
    description varchar(512),
    incident_type varchar(128),
    ticket_key varchar(128),
    ticket_thread varchar(256),
    ticket_open tinyint default 1,
    expire_time datetime default null
);
create table rapid_support.config(
	id int not null primary key auto_increment,
    parent_id int default 0,
    config_name varchar(128),
    config_value varchar(128)
);
insert into rapid_support.config(parent_id, config_name, config_value) values 
(null, 'key', 'a38e76d'),
(null, 'timeout_duration','40'),
(null, 'short_reply_warn', 'true'),
(null, 'short_reply_length', '80'),
(null, '','');
delimiter $$
create trigger expire_time_default before insert on `incidents`
for each row
	begin
    set 
		@timeout_interval = cast((select config_value from rapid_support.config where config_name = 'timeout_duration' limit 1) as unsigned),
		@key_start = (select config_value from rapid_support.config where config_name = 'key' limit 1),
		new.expire_time = ifnull(new.expire_time, (DATE_ADD(NOW(), INTERVAL @timeout_interval MINUTE))),
		new.ticket_key = ifnull(new.ticket_key, sha(concat(@key_start,unix_timestamp())));
	end $$
delimiter ;
create table rapid_support.replies(
	id int not null primary key auto_increment,
    support_id int not null, 
    reply_options varchar(256),
    message_reply varchar(512),
    constraint foreign key (support_id) references rapid_support.incidents (id)
);
delimiter $$
create function create_request (ip varchar(64), firstName varchar(64), lastName varchar(64), room varchar(64), description varchar (512), incident_type varchar(128)) 
RETURNS varchar(64)
begin 
	-- declare ticket_key varchar(64);
    -- set ticket_key = '';
	insert into rapid_support.incidents(ip, fname, lname, room_number, description, incident_type) values (ip, firstName, lastName, room, description, incident_type);
	set @ticket_key = (select ticket_key from rapid_support.incidents order by id desc limit 1);
	return @ticket_key;
end$$
delimiter ;
delimiter $$
create function add_thread(thread varchar(256), given_ticket_key varchar(256))
returns varchar(64)
begin
	update rapid_support.incidents set ticket_thread = thread where ticket_key = given_ticket_key;
    return 'success';
end $$
delimiter ;
delimiter $$
create function close_ticket(given_id int)
returns varchar(64)
begin
	update rapid_support.incidents set ticket_open = 0 where id = given_id;
    return 'success';
end $$
delimiter ;
insert into rapid_support.incidents(ip, fname, lname, room_number, description, incident_type) values 
('calculated_ip_address', 'firstname', 'lastname', 'room', 'description', 'type');
select sleep(1);
insert into rapid_support.incidents(ip, fname, lname, room_number, description, incident_type) values 
('calculated_ip_address', 'firstname', 'lastname', 'room', 'description', 'type');
select sleep(1);
insert into rapid_support.incidents(ip, fname, lname, room_number, description, incident_type) values 
('calculated_ip_address', 'firstname', 'lastname', 'room', 'description', 'type');
select sleep(1);
insert into rapid_support.incidents(ip, fname, lname, room_number, description, incident_type) values 
('calculated_ip_address', 'firstname', 'lastname', 'room', 'description', 'type');
select sleep(1);
select id, ip, creation_date, fname, lname, room_number, incident_type, description, ticket_key, expire_time from rapid_support.incidents order by creation_date desc;
select * from rapid_support.config;