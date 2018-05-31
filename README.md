# OSSEC-GUI
Version of OSSEC-WUI adapted to OSSEC versions >= 2.9.3, V3.0 beta included
The software is in final tests with both versions 2.9.3 and V3.0 Beta.
Should be released soon.
This version contains new functions :
- Some statitics
- Ability to remove alerts
- Functions to manage signatures/category mapping
- Management of authentication with three levels to manage tights to access some functions
- A new possibility to use two databases, one "running" which is feeded by OSSEC where you can "delete" records. Deleted records are automagically re-inserted in the second ("history") database for statistical and historical access.
