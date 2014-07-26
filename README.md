mentor-app
==========

Mentor matching application to assist with the task of matching mentors with mentees based on the skills they list as interests.  This is a backbone.js app using a MySQL database.

OSX Installation
================

Vagrant
-------

The system is bundled with a Vagrant set up, so you can get up and running quickly and in a common environment (for more informatin on how to work with Vagrant and why it is the greatest thing since sliced bread, visit http://docs.vagrantup.com/v2/getting-started/index.html). Download and install the latest versions of [Vagrant](http://www.vagrantup.com/downloads.html) and [VirtualBox](https://www.virtualbox.org/wiki/Downloads). Open up Terminal and run these commands to start the vagrant instance for you:

``` shell
$ git clone git@github.com:phpmentoring/mentor-app.git
$ cd mentor-app/vagrant-mentor-app/
$ vagrant up
$ vagrant ssh
$ cd /var/www
$ composer install
$ bin/phinx migrate
```

If all went well, the Mentor App is now running on your machine, the latest Composer packages are installed, and the latest Phinx database migrations have been applied.

OSX
---

Next up, you need to configure the `/etc/hosts` file so that you can actually access it in a browser. This is accomplished by appending a line to the `/etc/hosts` file:

``` shell
$ exit
$ sudo su
$ echo '192.168.56.201 mentorapp.dev' >> /etc/hosts
```

Now you can visit http://mentorapp.dev in your browser!

Testing with Behat
------------------

Before you can run the Behat tests, you must start the Selenium server on the host machine.

 - Download the latest [Chrome Webdriver](http://chromedriver.storage.googleapis.com/index.html) and place it in `/usr/local/bin`.
 - Download the [Selenium Stand-alone server](http://selenium.googlecode.com/files/selenium-server-standalone-2.35.0.jar) and place it in `/usr/local/bin`.
 - Create an alias (consider adding it to your `.bash_profile`) to your for future use and start then Selenium server:

``` shell
$ alias behat='java -jar /usr/local/bin/selenium-server-standalone-2.35.0.jar -host 192.168.56.201'
$ behat
```

Now we can run the tests!

``` shell
$ cd mentor-app/vagrant-mentor-app/
$ vagrant up
$ vagrant ssh
$ cd /var/www
$ bin/behat -e development
```

* TODO: If all goes well, you should see a Google Chrome window open up and the tests execute. (This doesn't happen with the above instructions, the tests run in the shell? Is there another plugin required?)
* TODO: Set up script to build and seed test database.

Windows Installation
====================

TODO!

Contributing
============

 1. Code!
 2. Issue a pull request.
 3. Please include any schema changes with a database migration.

Database Migration
==================

In order to keep control of the database we use a system called Phinx. Full documentation can be found at: http://docs.phinx.org/en/latest/

To create a change from the `/var/www` directory run the following command:

`$ bin/phinx create <Name for migration>`

To check the status (what's not been run yet, etc) run the following command:

`$ bin/phinx status -e development`

To apply outstanding migrations run the following:

`$ bin/phinx migrate -e development`

If you don't specify the `-e development` then it will default to development with a warning.

Seeding the Database
====================

It is useful to have data in the database to interact with the site or the API. Seeding the data is accomplished using a `phing` task.

```
$ bin/phing seed-db
```

The task will prompt you for number of skills to add and number of users. You can choose any number but remember the higher the number the longer it takes.

Styles
======

SASS 3.2.10 is required. Presently, SASS and the compiled CSS output are to reside on the same directory.
