# EYP Member Database

## About

The EYP Member Database is a web application that allows you and your NC to keep track of your members, contacts, schools, sponsors, media contacts and payments. You can store contact information, sessions and teams, and interlink these items.

## Localization

Some parts of the application, such as states and types of schools, have to be localized. The following localizations are currently available:

- Austria (at)
- Germany (de)
- Denmark (dk)

There are two ways to request a new localization:

- If you can read the build.sh file and understand how the localization process works, you can simply add a new localization and create a pull request.
- Otherwise, you can [open a new issue](https://github.com/eyp-developers/eyp-member-database/issues) to request the localization. Please provide the country, list of states, and list of school types in the issue.

## Installation

1. Download the current release of your localization from the '[Releases](https://github.com/eyp-developers/eyp-member-database/releases)' section. If there is no appropriate localization yet, have a look at [how to create or request a new localization](#Localization).
2. Unpack the downloaded release and upload all files from the `dist/` folder to your webserver. Make sure that you also copy all hidden files, such as .htaccess.
3. Make sure you have mod_rewrite enabled on your server (should be enabled by default on 99% of all webservers)
4. If you are running this software on its own subdomain (such as data.my_nc.org), the default config should work for you. Otherwise, you might have to edit the last line in `backend/.htaccess` and add the path to your index.php file.
5. Open the URL of your webserver in your browser and follow the installation instructions on screen.
6. Log in with the default username and password ('admin').
7. Go to `Settings -> User Management` and change the default password.

## Adding new modules

By default, the application does not install any modules. You can add the modules you need under `Settings -> Module Management`. Please keep in mind that some modules depend on other modules (such as Schools, which depends on People). Also keep in mind that deleting a module will also delete all data associated with this module.

## Adding new users

You can add new users under `Settings -> User Management`. When you create a new user, you will be asked for a default permisison. By default, the user will receive this permission for all modules. If you want to manage permissions on a per-module basis, you can do so by editing the user after it was created.

A possible use-case for this might be someone who is only responsible for fundraising. This person should have a 'write' permission for the Sponsors module so they can add new fundraising information, but probably should not be able to edit (or even see) people's personal information or membership payments.

Please note the special permissions for the Settings module:

- Write: This user will be able to add and remove modules and users, and change users' permissions. Only your system administrator should have this permission.
- Read: This user can log in, but can not add or remove modules and users.
- None: This user can not log in. The account exists, but it is deactivated.

## Migrating old data

If you are running the old EYP Member Database, you can migrate your existing data by following these steps:

1. Install the new version and set up all the modules you need
2. Make a full SQL dump of your old database (including data and structure) and insert this dump into the new database directly via SQL.
3. Run the SQL script `installer/transfer.sql` on the new database. It will transfer the old data into the new database.
4. If you had pictures in your old database, copy the files to the `uploads` directory in your new database's folder.
5. Make sure that the data was copied successfully.
6. If you want, you can run the SQL script `installer/remove_old.sql` to remove the copy of your old database you created in step 3.

## License

This software is licensed under the MIT License. See LICENSE.txt for more information.
