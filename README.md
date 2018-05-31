# OSSEC-GUI
Version of OSSEC-WUI adapted to OSSEC versions >= 2.9.3, V3.0 beta included
The software is in final tests with both versions 2.9.3 and V3.0 Beta.
Should be released soon.
This version contains new functions :
- Some statitics
- Ability to remove alerts
- Functions to manage signatures/category mapping
- Management of authentication with three levels to manage tights to access some functions
- A new possibility is to use two databases, one "running" which is feeded by OSSEC where you can "delete" records that are no more interesting (problems solved ...). 
All deleted records are automagically re-inserted in the second ("history") database for statistical and historical access, thanks to a simple "trigger" on the "alert" table.

The project uses :
- Amcharts for tracing graphs (as Analogi and OSSEC-WUI)
- PHP AUTH from Delight-im for managing authentication

To use it you must install : 
- A Web server with PHP enabled (Tested with Apache 2.4.25 on a Debian Stretch)
- A Mysql database (tested with Mysql 5.7 and Mariadb 10.3).
