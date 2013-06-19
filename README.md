Readable.cc
===========

This is the source code for [Readable.cc](http://readable.cc), a news reader with an emphasis on readability.

Built on [Swiftlet](http://swiftlet.org), a tiny MVC framework written in PHP.

*Licensed under the [GPL](http://opensource.org/licenses/gpl-3.0.html).*


Prerequisites
-------------

* Web server, e.g. Nginx or Apache with mod_rewrite
* PHP 5.3+
	* CLI (php5-cli)
	* cURL (php5-curl)
	* Mcrypt (php5-mcrypt)
* MySQL 5.5+

Optional:

* [SASS](http://sass-lang.com)
* [Compass](http://compass-style.org)


Installation
------------

Load `mysql/schema.sql` into MySQL to create the database.

```shell
# Unix shell command

mysql < mysql/schema.sql
```

Copy `config/pdo.example.php` to `config/pdo.php` and edit the file.

Edit `config.php`.

Make the following directories writable:

* `sessions`
* `HTMLPurifier/DefinitionCache/Serializer/HTML`
* `HTMLPurifier/DefinitionCache/Serializer/URI`

```shell
# Unix shell command

chmod 777 sessions HTMLPurifier/DefinitionCache/Serializer/HTML HTMLPurifier/DefinitionCache/Serializer/URI
```

Set up a cron job to periodically fetch feeds.

```shell
# Example crontab entry

*/5 * * * * /usr/bin/php /srv/readable.cc/index.php -q cron > /dev/null
```

Web server configuration
------------------------

### Apache HTTPD

Ensure mod\_rewrite is enabled.

```shell
# Unix shell command

a2enmod rewrite
```

Create a virtual host entry.

**/etc/apache2/site-available/readable.cc**

```apacheconf
# Simplified example

<VirtualHost *:80>
	ServerName readable.local

	DocumentRoot /var/www/readable.cc/public

	<Directory /var/www/readable.cc/public>
		Options Indexes FollowSymLinks MultiViews
		AllowOverride All
		Order allow,deny
		allow from all
	</Directory>
</VirtualHost>
```

Enable the virtual host.

```shell
# Unix shell command

a2ensite readable.cc
```

### Nginx

TODO

