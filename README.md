mentor-app
==========

Mentor matching application

Vagrant
=======

The system is bundled with a Vagrant set up, so you can get up and running quickly and in a common environment.

To get the Vagrant system running, go to vagrant-mentor-app-php5.4 and run the following:

$ vagrant up

This will start the vagrant instance for you. To access the web server on either you will need to open your browser and go to:

http://localhost:8080/
OR
http://mentorapp.dev:8080/

To access mentorapp.dev you will need to add the following to your hosts file:

127.0.0.1   mentorapp.dev

If you need to access your Vagrant machine at any time you can go in to the relevant directory and run:

$ vagrant ssh

When you are finished for the day, go to the relevant directory and run:

$ vagrant halt

When you are finished forever, you can completely destroy the machine by running:

$ vagrant destroy

Database Migration
==================

In order to keep control of the database we use a system called Phinx.
Full documentation can be found at: http://docs.phinx.org/en/latest/

To create a change from the /var/www directory run the following command:

$ bin/phinx create <Name for migration>

To check the status (what's not been run yet, etc) run the following command:

$ bin/phinx status -e development

To apply outstanding migrations run the following:

$ bin/phinx migrate -e development

If you don't specify the "-e development" then it will default to development with a warning.

Seeding the Database
====================

It is useful to have data in the database to interact with the site or the API. Seeding the data is accomplished using a `phing` task.

```
bin/phing seed-db
```

The task will prompt you for number of skills to add and number of users. You can choose any number but remember the higher the number the longer it takes.

Behat
=====
The following directions assume the host machine is a Mac.  For windows, I don't know what to do...
To get started with Behat:
* First, on the host machine (your Mac), download the [Chrome Webdriver](http://chromedriver.storage.googleapis.com/index.html) and place it in a convenient place.  A good spot is to create a `bin` directory within your home directory and place it there (`~/bin`).
* Again, on the host machine (your Mac), download the [Selenium Stand-alone server](http://selenium.googlecode.com/files/selenium-server-standalone-2.35.0.jar) and place it in the same `bin` directory where you placed the Webdriver.
  *(Optional) Create an alias to start the selenium server.  For example: `alias behat='java -jar ~/bin/selenium-server-standalone-2.35.0.jar -host 192.168.56.1`
* Before you can run the behat tests, you must start the selenium server on the host machine.  From the terminal, simply type “behat” and the server should start running.
* To run the tests, SSH into the VM, go to the root of the project (`/var/www` - same directory as the behat.yml file) and type `bin/behat`.  If you are using vagrant, then the VM must be running and you must be within an SSH session (vagrant ssh).

If all goes well, you should see a Google Chrome window open up and the tests execute.

### TODO:
* Set up script to build and seed test database.

Styles
======

SASS 3.2.10 is required. Presently, SASS and the compiled CSS output are to reside on the same directory.
