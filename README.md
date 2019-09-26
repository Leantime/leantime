
# Leantime #

Leantime is an open source project management system for small teams. [https://leantime.io](https://leantime.io)
<br /><br />
It is written in PHP, Javascript with MySQL. 

![Build Status](public/images/Screenshots/Dashboard.png)

![Build Status](public/images/Screenshots/ToDos_Kanban.png)

![Build Status](public/images/Screenshots/Milestones_Gantt.png)

### Installation (Production) ###

* Download latest release package
* Create MySQL database
* Upload entire directory to your server 
* Point your domain to the "public/" directory
* Rename config/configuration.sample.php to config/configuration.php
* Fill in your database credentials (username, password, host, dbname)
* Navigate to yourdomain.com/install
* Follow instructions to install database and user account

### Installation (Development) ###

* Clone Repository to your local server
* Create MySQL database
* Execute:
```
composer install
```
to load the php dependencies, then
```
npm install
```
to load Javascript dependencies and finally run the grunt task to create the comiled js files
```
grunt default
```
* Point your local domain to the "public/" directory
* Rename config/configuration.sample.php to config/configuration.php
* Fill in your database credentials (username, password, host, dbname)
* Navigate to localhost/install
* Follow instructions to install database and user account

### Learn More ###
Documentation can be found at [https://help.leantime.io](https://help.leantime.io)