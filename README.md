# README

## Carabus Manuel Alexandru

Wordpress plugin for fetching and displaying users from REST API.

## Informations

On activation, using hooks and registrations method WordPress provided, this plugin adds multiple functionalities.
Such as:

1. Provides an administration field for the REST API link on the admin page. On installation, a default link is set. The plugins admin template includes the WordPress Settings API.

2. Creates an endpoint where we can find the plugin page:
* **/carabus** where can be found in your WordPress installation. EX: www.mywebsite.domain/carabus

3. Ands two REST API endpoints:
- **/wp-json/carabus/plugin/users** list of the users from the API link *you* provide. USE: www.mywebsite.domain/wp-json/carabus/plugin/users
- **/wp-json/carabus/plugin/user?id=** full details of one particullary user. This is triggered when you clicked on a row on the user table. EX: www.mywebsite.domain/wp-json/carabus/plugin/user?id=1

4. HTTP Caching. To avoid multiple API requests send, the API response is saved in a database for 24h under _transient_users_api using WordPress transient function for storing data temporarily in the database.

5. Plugin Translation for various words. Languages provided: US and DE. 

6. The plugin *DOES* not includes his assets on other pages other than his own.

Because this plugin was focused mainly on the backend, the frontend user table has been created dynamically using jQuery DataTables (assets provided). No external CSS code has been written because I do not see the point of doing this, looks decent.
The details container is added using AJAX.

## Requirements

* PHP 7.3+
* PHPUNIT 7
* Wordpress 5.2+

## Install
Go to the plugin folder located in wp-content and open the terminal. 
Type this commands:
1. mkdir carabus
2. cd carabus/
3. git init
4. git remote add origin https://github.com/Hawksama/api_users.git
5. git fetch --all
6. composer update

Finish.

## Settings
Under admin, under the menu, you can find the plugin name (Carabus).

## Automated Tests
An automated test has been made and using PHPUnit and testing the constructor from carabus.php. 1 test, 1 assertion.

## License

This plugin is open source and released under the GNU license. See LICENSE file for more info.

## Develop Enviroment

This plugin has being developed under WordPress TwentyTwenty theme. Other themes, can have the container fullwidth because the 'alignwide' class used on container is not defined.

## Who am I

Hi, I'm a Romanian Developer with a Bachelor's Degree in Computer Science (English language) and with 4 years of WEB Development.

I've been working with Magento 2 for the past 18 months (mainly on frontend), and with Wordpress for the last 3 years.

Anything more about me? Let's get in touch.

Contact: manue971@icloud.com OR manue97132@gmail.com