# OSSEC-GUI
Version of OSSEC-WUI adapted to OSSEC versions >= 2.9.3, V3.0 included.
This an evolution from Analogi (from Ecsc) and OSSEC-WUI which can be found at :
https://github.com/NunesGodinho/OSSEC-WUI which uses the "old" database schema.
The software is tested with both versions 2.9.3 and V3.0 stable.
First release on 05/06/2018.
This version contains new functions :
- Some statistics.
- Ability to remove alerts.
- Ability to "reorganize" the database (Mysql/MariaDB) in a dedicated function.
- Functions to manage signatures/category mapping.
- Management of authentication with three levels to manage rights to access many functions.
- A new improvement : ability to use two databases, one "running" which is feeded by OSSEC where you can "delete" records that are no more interesting (problems solved ...). 
All deleted records are automagically re-inserted in the second ("history") database for statistical and historical access, thanks to a simple "trigger" on the "alert" table.
- Some improvements in managing Sql

The project uses :
- Amcharts for tracing graphs (as Analogi and OSSEC-WUI) : https://www.amcharts.com
- PHP AUTH from Delight-im for managing authentication : https://github.com/delight-im/PHP-Auth

It is possible to enable an authentication system, see in the doc/AUTH directory, when installed the default is to run with no authentication. 

To use OSSEC-GUI you must install : 
- A Web server with PHP enabled (Tested with Apache 2.4.25 on a Debian Stretch) with, at least :
  php7 curl
  php7 json
  php7 mbstring
  php7 mysql
  php7 xml
- A Mysql database (tested with Mysql 5.7 and Mariadb 10.1 and 10.3 on some Debian Stretch).

Release : V3.0 created on 09/06/2018

Please leave me a comment (even in "issues").
