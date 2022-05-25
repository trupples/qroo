drop view if exists ScansGraphByCode;
drop view if exists ScansGraphByMedium;
drop table if exists Users;
drop table if exists Scans;
drop table if exists Codes;
drop table if exists Folders;
drop table if exists Media;

create table Folders (
	id integer primary key,
	parent integer not null references Folders(id),
	name nvarchar(32) not null,
	creation_date char(10) not null default(date()),
	description text null
);

create table Media ( -- the plural of medium
	name nvarchar(32) primary key,
	description text null

	--coord_lat decimal(8, 6) null,
	--coord_lon decimal(9, 6) null,
	--check ((coord_lat is not null and coord_lon is not null) or (coord_lat is null and coord_lon is null))
);

create table Codes (
	code varchar(8) primary key,
	folder int null references Folders(id),
	medium nvarchar(32) not null references Media(name),
	redirect_url varchar(256) not null,
	creation_date char(10) not null default(date()),
	description text null
);

create table Scans (
	id integer primary key autoincrement, -- using this instead of the previous (code,scan_time) to eliminate the potential 1ms race condition
	code varchar(8) not null references Codes(code),
	scan_time char(19) not null default(strftime('%Y-%m-%d %H:%M:%S', 'now', 'localtime')),
	first_scan boolean not null
);

create table Users (
	email varchar(256) primary key,
	pwhash char(60) not null,
	has_admin_permission boolean not null default false -- can add, remove, manage accounts
);

create view ScansGraphByCode as -- scan counts by code, day and hour
select	code,
		strftime('%Y-%m-%d', scan_time) as "day",
		strftime('%H', scan_time) as "hour",
		count(*) as "total_scans",
		count(case first_scan when 1 then 1 else null end) as "unique_scans"
from Scans
group by day, hour, code
order by day asc, hour asc;

create view ScansGraphByMedium as -- scan counts by medium, day and hour
select	medium,
		strftime('%Y-%m-%d', scan_time) as "day",
		strftime('%H', scan_time) as "hour",
		count(*) as "total_scans",
		count(case first_scan when 1 then 1 else null end) as "unique_scans"
from Scans join Codes on Scans.code = Codes.code
group by day, hour, medium
order by day asc, hour asc;




drop table if exists FolderLogs;
drop table if exists CodeLogs;
drop table if exists MediumLogs;
drop table if exists UserLogs;
drop table if exists Logs;

create table Logs (
	id integer primary key autoincrement,
	author varchar(256) not null references Users(email),
	log_time char(19) not null default(strftime('%Y-%m-%d %H:%M:%S', 'now', 'localtime')),
	creation boolean not null, -- is this a creation or an edit?
	log_type char(1) not null,

	check (
		log_type = 'F' or -- folder
		log_type = 'C' or -- code
		log_type = 'M' or -- medium
		log_type = 'U'    -- user
	)
);

create table FolderLogs (
	id integer primary key references Logs(id),

	deletion boolean not null,

	folder_id integer null,
	parent integer null,
	name nvarchar(32) null,
	description text null
);

create table CodeLogs (
	id integer primary key references Logs(id),

	code varchar(8) not null,
	folder int null,
	medium nvarchar(32) null,
	redirect_url varchar(256) null,
	description text null
);

create table MediumLogs (
	id integer primary key references Logs(id),

	name nvarchar(32) not null,
	description text null
);

create table UserLogs (
	id integer primary key references Logs(id),

	deletion boolean not null,

	email varchar(256) not null,
	password_reset boolean not null,
	has_admin_permission boolean null
);


-- default data

insert into Folders(id, name, parent, description) values (1, 'Inactive codes', 0, 'Because codes are not deletable, move the ones you created by mistake or do not plan on using here, so they may be reassigned by someone else');
