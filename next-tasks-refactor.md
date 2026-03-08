# global

## daisyu
ensure our html and css is cleaned up and soley based on daisyui html/css.
We should reduce our custom css footprint dramatically

## async page loads
most pages should be loaded using async. A big antipattern we had was having to reload a page when a modal closes. 
These should be component/htmx events

## main layout
With daisyui in place we should update our main scafolding to use flex and daisyui scaffolding to prepare a much better mobile responsive experience.
As part of that we should rethink about leftNav, rightbar etc set up where we have an odd cascade of containers and hard coded margins that should just be flex elements.
with that in mind we will probably have a design that reflects canva.com current desig (or more like slack)
1. No more horizontal full bar on the top
2. Add the my work, company, projects (and also user icons) to a full height left bar. 
3. The section bar is followed by a menu which is currently the leftNav
2. then we have the maincontent (visually distinct with rounded corners, full white maybe some transparency)
3. optional right bar with icons (similar to google apps bar) where we can load special content like an AI  assistant, calendar modules or other items
I added screenshots from canva as reference. 
![Screenshot 2026-03-08 at 13.28.34.png](Screenshot%202026-03-08%20at%2013.28.34.png)
![Screenshot 2026-03-08 at 13.28.18.png](Screenshot%202026-03-08%20at%2013.28.18.png)
4. 
## js unit tests
we really need to start having js unit tests

## events in php
Instead of our string based events we should have event classes like laravel proposes, so each domain should ahve an events folder for all the events it can execute. 
We do need to stay backwards compatible with our old events and event registers though so we need to augment events with a string/ID so they can be listened to via string.

## plugins
All plugins need to be migrated to the new html/component/js/event structure

## real queue handling
We don't really have queues or tasks. we need to set that up with the standard laravel queue handling


# Canvas refactor

Most canvases should be refactored and live inside a central canvas domain. The only domains that should remain separate are goals, ideas, wikis.
Everything else regarding blueprints should be in a blueprint directory and each template type should be a yaml or so template format.
We could use gridstack js or something else to define the grid layout. But this will clean up our domain folder significantly. 
Especially since they all work the same way


# Large php refactors

## Object/Entity models instead of arrays
All repositories or at the very least all services should return objects or arrays of objects. So tickets should always be a ticket object when we add it or remove it.
As part of that I am open to moving to doctrine (and spatie doctrine for laravel) maybe with the help of data mappers. https://github.com/spatie/laravel-data
As part of this we should consider how to implement a better caching layer for heavy sql queries.

## spatie permissions
We should update our roles and permission management which is currently hard coded to use spatie permissions (https://spatie.be/docs/laravel-permission/v7/introduction)
in the first iteration we just do a drop in replacement and pre-define our existing roles and permissions using that. In a later iteration we can make that flexible.

### feature/module management
as part of permissions we should include a better module permission set up where users can pick which domains/features are available for each project. 
For example if I want to have a very small project I could decide to only have tasks and milestones and keeping most of the rest out. 

## services validation
services should validate the data that comes in using laravel validation rules https://laravel.com/docs/12.x/validation 

## router/routes
our custom frontend controller should probably be deprecated or thinned out so we can use regular laravel routes. WE already support route.php files now in domains which is the correct thing. 
I would like to see https://github.com/spatie/laravel-route-attributes implemented whcih manages and compiles routes using attributes. The problem I am seeing is with plugins and how they inject routes. 
I don't know if they need to be rebuilt or not.

## controllers
many of our controllers contain too much business logic that should live in the service layer

## migrations
It is time to update our migration/installation folder to use laravel migrations. The only difficult part here is how we handle plugin migrations as part of that. 
Once again keeping in mind that plugins will be installed via the UI while the system is running so executing cli commands is not always feasible. (Though arguably the main system may not need to know about plugin migrations).

## notifications/activitiy
As part of the events refactor we should ensure we have a strong foundation for notification management. To the point where users can choose which notifications they want on which channel for whcih project.
We should have various notification channels (messengers, push notificatins, sms, email)

## response hardening / exception management
jsonrpc and controllers will have different responses for example for validation failures etc. We need to ensure responses are managed appropriately. 
This also includes being more deliberate about exception handling. 

## Domain clean up
Some domains should really just be infrastructure. 
I think in addition to core we need an infra folder for things like queue, 2fa, authentication, notificationes etc (unless we are specifically generating a view/page controller)

## middlware management
We need to clean up our middleware to ensure that we can define separate middleware depending on the path/route
at the very least it should be possible to define different middleware for api, page, components etc.

## security harcening
we don't have a good security practice for the frontend. CSRF Protection is not available for example

## Type safety
Improve type handling across the system

# Domain feature level refactors

## clients -> teams
the concept of clients has been morphing a lot and in generally it makes a lot more sense to consider clients to be teams. 
We should take the time to refactor that, rename everything and just move on with "Teams" as the client equivalent

## Goals (+and canvases)

### multiple milestones
it should be possible to have multiple milestones per goal/idea etc.

### goals specific
we should be able to have multiple metrics per single goal which will then fully support OKR type task management



# Plugins

## AI/MCP
I want to be able to use leantime as task management for coding agents and multi agent task coordination.
So we should update our mcp to ensure it supports sharing plans and using leantime to coordinate work as well as documentation/learnings via wikis.
While I want to keep the mcp plugin I think we should move a lot of the code that lives in the mcp into core since it will be used across the system. Probably even tool definitions (just make them part of the domain).

## AI Assistant/Copilot

