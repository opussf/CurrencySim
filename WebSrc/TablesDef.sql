create database currency;
use currency;

drop table Config;
create table Config (
    staminaMax int unsigned not null,
    staminaGainSeconds int unsigned not null
);

drop table Users;
create table Users (
    id int unsigned not null primary key auto_increment,
    name varchar(20) not null,
    pword varchar(20) not null,
    stamina int unsigned not null,
    last timestamp not null,
    isadmin tinyint(1) default 0
);

drop table Currencies;
create table Currencies (
    id int unsigned not null primary key auto_increment,
    name varchar(40) not null,
    icon varchar(40) not null,
    type int unsigned not null,
    level int unsigned not null,
    cost int unsigned not null
);

drop table UserCurrencies;
create table UserCurrencies (
    userid int unsigned not null,
    currencyid int unsigned not null,
    number int unsigned not null
); 



grant select,insert,update on currency.* to 'opus'@'localhost';

