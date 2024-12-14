Test PHP Project Setup Guide
1. Configure database 
In the config.php file, configure your database connection settings
2. Migrate tables 
run command to create the necessary tables 
php migrations/migrations.php
or alternatively use test.sql file located in the core folder
3. Run the Development Server
To run the server
php -S localhost:8080