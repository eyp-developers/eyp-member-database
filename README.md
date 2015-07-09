# EYP Member Database

## About

The EYP Member Database is a simple web application that allows you and your NC to keep track of your members, contacts, teachers and sponsors. You can store contact information, sessions and teams, and interlink these items. The application has initially been developed for and by EYP Austria, and has been in use since 2013. You can find the current stable version (1.0) in the [Downloads section].(https://bitbucket.org/eypdevelopers/eyp-member-database/downloads)

## Current Status (v2.0)

Version 2.0 is currently under development. The key focus of this new version is to make the application faster and more modular. This means that the new version is largely a complete rewrite in order to have a plugin-based architecture that allows different users to add and remove parts of the application without having to edit the code.

## Installation (v1.0)

1. Download v1.0 from the [Downloads section](https://bitbucket.org/eypdevelopers/eyp-member-database/downloads).
2. Upload the contents of the "webapp" folder to your webserver. Make sure that you also copy all hidden files, such as .htaccess. Also keep in mind that for .htaccess files to work, you need to have mod_rewrite enabled on your server.
3. Create a new database on your MySQL server and execute the "database.sql" file. It will create all necessary database tables and create a default user.
4. Configure your database information in "app/Config/database.php" under $default.
5. You should now be able to access the web application by pointing your webbrowser at the "webapp" folder.
6. The default login is "admin", password "admin". The first thing you should do after logging in is changing this username and password. Please note that all users are full admins, meaning that every user can add, edit and delete other users.