AppMon
==============
www: http://niborb.github.com/appmon


How to install?
===============

0. Requirements

GIT should be installed, and accessible from your PATH.

http://git-scm.com/

1. Apache - VirtualHost
=======================

You can add the following VirtualHost to your apache
virtualhost configuration file (for instance http-vhosts.conf)

http-vhosts.conf:


    <VirtualHost *:80>
       # change to your email address
       ServerAdmin admin@localhost
       # change the path to the location of AppMon (add /web to the end of the path)
       DocumentRoot "/var/www/AppMon/web"

       # the domain name
       ServerName app-mon.localhost
       ErrorLog "logs/app-mon-localhost"
       CustomLog "logs/app-mon.localhost" common
    </VirtualHost>

2. Database
===========

2.1 cp parameters.dist.yml (</path/to/appmon>/app/config/) to parameters.yml

2.2 open parameters.ini in your text editor, and change, if necessary the
 database settings:

    database_driver   = pdo_mysql
    database_host     = localhost
    database_port     = ~
    database_name     = appmon
    database_user     = root
    database_password =

2.3

Open your console/terminal. And go to the directory where you have installed AppMon
(for instance /var/www/Appmon)

First we run a command which does some basic checks (file permissions, php version, date-time zone etc)

    cd /var/wwww/AppMon
    mkdir app/cache app/logs
    php app/check.php
    
Install dependencies

    php composer.phar install

Build bootstrap

    php bin/build_bootstrap

Install assets

    php app/console assets:install ./web

Correct anything if necessary, and then run the following two commands:
    
    php app/console doctrine:database:create
    php app/console doctrine:schema:create

Create an admin user

    php app/console fos:user:create

Promote the newly created admin user

    php app/console fos:user:promote

type role: ROLE_SUPER_ADMIN

Activate newly created user

    php app/console fos:user:activate

Configure LDAP (Active Directory) settings

edit the parameters.ini file.

That's all, now you fire up your browser and go the configured URL.

