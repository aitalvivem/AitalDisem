# AitalDisem

## Introduction
AitalDisem is a project initiated by Lo Congrès following its collaboration with Wikidata. This project is  the direct continuation of the project AitalvivemBot.  


After having inserted lexicographicals data in Wikidata a problem has happened : how to link all the inserted occitan's lexeme to senses while avoiding the use of the proprietarys data used by Lo Congrès ?  


The solution choosen is the creation of a web application, presented as a game, to link senses for each lexeme using the collaborative work of the community and then insert those associations in Wikdata.

## Implementation of the application
1. Define one (or more) administrator connection by editing the file sec/.htpasswd and sec/.htaccess.
2. Edit the file sec/config.php to configure the application.
3. Execute the script sec/sql/script_aitaldisem.sql on the MySQL server to create the database.
4. 4. Fill the database with your data, you can use the database creation tools.
5. Be sure to have enough validated association in the database to run the tests of reliability at the beginning of a game.


For more informations please read the documentation.
