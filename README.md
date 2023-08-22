
<div align="center">
<a href="https://leantime.io"><img src="https://leantime.io/wp-content/uploads/2023/03/leantime_logo.png" alt="Leantime Logo" width="300"/></a>


### Leantime&reg; ###

Leantime is a strategic open source project management system for innovative companies and teams looking to go from start to finish. Built for the non-project manager, we combine the plans and the work while making it easy for everyone on the team to use.<br />It's an alternative to ClickUp, Monday, or Asana. As simple as Trello but as feature rich as Jira.<br />[https://leantime.io](https://leantime.io)<br />

[![License Badge](https://img.shields.io/github/license/leantime/leantime?style=flat-square)](https://www.gnu.org/licenses/agpl-3.0.en.html)
[![Version](https://img.shields.io/github/package-json/v/leantime/leantime/master?style=flat-square)](https://github.com/Leantime/leantime/releases)
[![Docker Hub Badge](https://img.shields.io/docker/pulls/leantime/leantime?style=flat-square)](https://hub.docker.com/r/leantime/leantime)
[![Discord Badge](https://img.shields.io/discord/990001288026677318?label=Discord&style=flat-square)](https://discord.gg/4zMzJtAq9z)
[![Crowdin](https://badges.crowdin.net/leantime/localized.svg)](https://crowdin.com/project/leantime)
<br />

  ![alt text](public/assets/images/Screenshots/ProjectDashboard.png "Dashboard")

</div>
<br /><br />

## Features: ##

* Task management using kanban boards, table and calendar views + sprint management
* Subtask management & dependencies
* Idea boards & idea kanban boards
* Various research and strategy boards such as SWOT, Lean Canvas, Business Model Canvas, Empathy Maps & more
* Milestone management using Gantt charts
* Timesheet management + time tracking on tasks
* Manage goals
* Retrospectives
* Project dashboards and project updates
* Project reports
* Comments / Discussion
* Confetti
* Portfolio overview with project status
* Wikis (Docs) with the ability to embed documents
* Multiple user roles
* Two-Factor Authentication
* LDAP integration
* Integrations with Mattermost, Slack, Zulip, Discord
* Export timesheets, tasks and milestones to CSV
* File storage with AWS S3 or local file system 
* Adjust color scheme and logo
* Easy installation
* Available in 19 languages


### Screenshots ###

| ![alt text](public/assets/images/Screenshots/UserDashboard.png "My Dashboard")   | ![alt text](public/assets/images/Screenshots/ToDoKanban.png "Kanban Board") | ![alt text](public/assets/images/Screenshots/ToDoTable.png "Grouped To-Dos") |
|---------------------------------------------------------------------|:--------------------------------------------------------------------:|:---------------------------------------------------------------------:|
| ![alt text](public/assets/images/Screenshots/Timesheets.png "My Timesheets") | ![alt text](public/assets/images/Screenshots/Milestones.png "Milestone Gantt Charts") |     ![alt text](public/assets/images/Screenshots/Ideas.png "Idea Board")     |
| ![alt text](public/assets/images/Screenshots/Goals.png "Calendar")      |  ![alt text](public/assets/images/Screenshots/Strategy.png "Lean Canvas")   |  ![alt text](public/assets/images/Screenshots/Reports.png "Report Screens")                                                                     |
| ![alt text](public/assets/images/Screenshots/DocsEmbed.png "Documents")      |  ![alt text](public/assets/images/Screenshots/Blueprints.png "Blueprints")   |  ![alt text](public/assets/images/Screenshots/Confetti.png "Confetti")                                                                     |

### System Requirements ###

* PHP 8+
* MySQL 5.7+
* Apache or Nginx (IIS works with some modifications)

### Installation (Production) ###

There are two main ways to install LeanTime for production. The first of which is to install all needed pieces of the system locally. The second is to use the offically supported Docker image.

#### Local Production Installation ####

* Download latest release package
* Create an empty MySQL database
* Upload entire directory to your server 
* Point your domain root to the `public/` directory
* Rename `config/.env.sample` to `config/.env`
* Fill in your database credentials (username, password, host, dbname) in `config/.env`
* Navigate to `<yourdomain.com>/install`
* Follow instructions to install database and set up first user account

#### Production Installation via Docker ####

We maintain an official <a href="https://hub.docker.com/r/leantime/leantime">Docker image on dockerhub</a>. 
To run the image enter your MySQL credentials and execute. You can pass in all the configuration variables from .env

```
docker run -d --restart unless-stopped -p 80:80 --network leantime-net \
-e LEAN_DB_HOST=mysql_leantime \
-e LEAN_DB_USER=admin \
-e LEAN_DB_PASSWORD=321.qwerty \
-e LEAN_DB_DATABASE=leantime \
-e LEAN_EMAIL_RETURN=changeme@local.local \
--name leantime leantime/leantime:latest
```

You can set any of the config variables in `config/configuration.php` when running the docker command.

Once started you can go to `<yourdomain.com>/install` and run the installation script.

### Installation (Development) ###

There are two ways to install a development setup of LeanTime. The first (but most techical) is to install all pieces of the system locally. The second (and prefered method) is to use a docker containerized development environment.

#### Local Development Installation ####

* Clone repository to your local server
* Create MySQL database
* Run wbepack builder via `make build-dev`
* Point your local domain to the `public/` directory
* Rename `config/.env.sample` to `config/.env`
* Fill in your database credentials (username, password, host, dbname) in `config/.env`
* Navigate to `<localdomain>/install`
* Follow instructions to install database and user account

#### Development Installation via Docker ####

For development, we use a dockerized development environment. You will need to have ``docker``, ``docker compose``, ``make``, ``composer``, ``git`` and ``npm`` installed.

* Notes for Windows Environments:
    - Run all commands within the git bash terminal in order to utilize unix specific commands
    - If installing php from a zip file, make sure to configure php.ini
    It does not exist initially, so copy C:\php\php.ini-development to C:\php\php.ini. You will also need to edit php.ini in a text editor and enable all needed extentions for the build process. You can find these by running the make commands and looking for any extensions that error out as missing. You can enable them by seaching php.ini for the extension that will look like: `;extension=gd` and removing the semicolon. 

In order to build the development docker image, in the root of this repository, run a primer with

```make clean build```

afterwards, run 

```make run-dev```

this will start the development server on port 8080.

The dev environment provides a mysql server, mail server, s3 server, and should be good to go for your needs out of the box. The configuration of the development environment is found in ``.dev/.env``, and is already seeded with the appropriate values. **You should probably not be modifying this unless you plan to work on a feature for a specific integration**. the applications you get are as follows

* [http://localhost:8080](http://localhost:8080) : leantime
* [http://localhost:8081](http://localhost:8081) : maildev - to check emails sent
* [http://localhost:8082](http://localhost:8082) : phpmyadmin(authentication ``leantime:leantime``) to check the DB schema and data
* [http://localhost:8083](http://localhost:8083) : s3ninja - to check s3 uploads. You need to enable this in the ``.dev/.env`` file by enabling s3

Additionally, XDebug is enabled, but you will have to modify your 
IDE key in the ``.dev/xdebug.ini`` file(or alternatively, on your IDE). You also need to have port 9003 temporarily open on your firewall so you can utilize it effectively. This is because connections from docker to the host will count as external inbound connection


### Update ###

#### Manual
* Make sure to take a backup of your database and files
* Replace all files in your directory with the updated version
* If there were any database changes, the system will redirect you to `<yourdomain.com>/update`

#### Script
* Execute ./updateLeantime.sh in the root of your leantime application

#### Docker
* Before updating, make sure your mysql container was started using a mounted volume, otherwise your content will be deleted
* Delete/Stop existing container
* Pull the latest docker image and rebuild using your compose file

## LICENSE Exceptions ##

This file forms part of the Leantime Software for which the following exception is added: Plugins within the `/app/plugins` directory which merely make function calls to the Leantime Software, and for that purpose include it by reference shall not be considered modifications of the software.

### Support ###

* Documentation [https://docs.leantime.io](https://docs.leantime.io)
* Community Forum [https://community.leantime.io](https://community.leantime.io)
* Discussions on [Discord](https://discord.gg/4zMzJtAq9z)
* File a bug report [https://github.com/Leantime/leantime/issues/new](https://github.com/Leantime/leantime/issues/new)
* Translations [https://crowdin.com/project/leantime](https://crowdin.com/project/leantime)
