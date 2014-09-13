Sequence
========

A content management system for stories and comics

Installation
------------

1. Download the code (all the files in this repository)
1. Create a MySQL database and user account.
1. Open PHP My Admin and go to your database, and go to the SQL tab.
1. Copy and paste the contents of create.sql into the field, and click "go". If asked whether you want to drop the tables, say yes. (The tables don't exist yet, so you won't loose data.)
1. Do the same with the contents of insert.sql.
1. Open data/config.inc.php in a text editor and fill in the database info (user's username, user's password, databsase name)
1. Use a FTP program to upload the files to your web host
1. Still using the FTP program, change the permissions of the data and business directories to 700 (rwx------)
1. Go to the directory where you installed Sequence, add /admin/login to the end of the URL to get to the admin login page, and log in as "admin" with the password "password".
1. Click the "admin" button in the top left and change your username, email and password to whatever you want.
1. Build your site!

Other Resources
---------------
* [Download](http://sequence.mareofnight.com/index.html) as a zip file
* [Admin Tutorial](http://sequence.mareofnight.com/tutorial.html)
