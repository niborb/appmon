AppMon (0.0.1)
==============

How to install?
===============

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

       # the domain name where
       ServerName app-mon.localhost
       ErrorLog "logs/app-mon-localhost"
       CustomLog "logs/app-mon.localhost" common
    </VirtualHost>

2. Database
===========

2.1 cp parameters.dist.ini (</path/to/appmon>/app/config/) to parameters.ini
2.2 open parameters.ini in your text editor, and change, if necessary the
 database settings:

    database_driver   = pdo_mysql
    database_host     = localhost
    database_port     =
    database_name     = appmon
    database_user     = root
    database_password =

2.3
Open your console/terminal. And go to the folder where you have installed AppMon
(for instance /var/www/Appmon)

    cd /var/wwww/AppMon/web
    php app/console doctrine:database:create
    php app/console doctrine:schema:create

That's all, now you fire up your browser and go the configured URL.





