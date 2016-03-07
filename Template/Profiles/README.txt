README
======

This directory should be used to place project specfic documentation including
but not limited to project notes, generated API/phpdoc documentation, or 
manual files generated or hand written.  Ideally, this directory would remain
in your development environment only and should not be deployed with your
application to it's final production location.


Setting Up Your VHOST
=====================

The following is a sample VHOST you might want to consider for your project.

<VirtualHost *:80>
   DocumentRoot "D:/apache/zendtest02/public"
   ServerName .local

   # This should be omitted in the production environment
   SetEnv APPLICATION_ENV development
    
   <Directory "D:/apache/zendtest02/public">
       Options Indexes MultiViews FollowSymLinks
       AllowOverride All
       Order allow,deny
       Allow from all
   </Directory>
    
</VirtualHost>

Setting Up Your Adapter
=======================

zf configure dbadapter "adapter=Pdo_Mysql&username=root&password=jminaya&dbname=test&host=127.0.0.1"
