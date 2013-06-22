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
mysql < mysql/schema.sql
```

Copy `config/pdo.example.php` to `config/pdo.php` and edit the file.

Edit `config/main.php`.

Make the following directories writable:

* `sessions`
* `HTMLPurifier/DefinitionCache/Serializer/HTML`
* `HTMLPurifier/DefinitionCache/Serializer/URI`

```shell
chmod 777 sessions HTMLPurifier/DefinitionCache/Serializer/HTML HTMLPurifier/DefinitionCache/Serializer/URI
```

Set up a cron job to periodically fetch feeds.

```shell
*/5 * * * * /usr/bin/php /srv/readable.cc/public/index.php -q cron 2>&1 > /dev/null
```

Web server configuration
------------------------

### Apache

*If you have rewrites enabled you should be able to place the source code in your document root and
view the website by navigating to `http://localhost/readable.cc/public`.*

Ensure mod\_rewrite is enabled.

```shell
a2enmod rewrite
```

Create a virtual host entry, point the document root to the `public` directory.

**/etc/apache2/sites-available/readable.cc**

```apacheconf
<VirtualHost *:80>
	ServerName readable.local

	DocumentRoot /srv/readable.cc/public

	<Directory /srv/readable.cc/public>
		Options Indexes FollowSymLinks MultiViews
		AllowOverride All
		Order allow,deny
		allow from all
	</Directory>
</VirtualHost>
```

Enable the virtual host and reload Apache.

```shell
a2ensite readable.cc
service apache reload
```


### Nginx with PHP-FastCGI

Create a virtual host entry, point the document root to the `public` directory.

**/etc/nginx/sites-available/readable.cc**

```nginx
server {
	listen 80;

	server_name readable.local;

	root /srv/readable.cc/public;

	location / {
		index index.php;

		if ( !-e $request_filename ) {
			rewrite ^/(.*)$ /index.php?q=$1 last;
		}
	}

	location = /index.php {
		fastcgi_pass  unix:/var/run/php-fastcgi/php-fastcgi.socket;
		fastcgi_index index.php;
		fastcgi_param SCRIPT_FILENAME $document_root/$fastcgi_script_name;
		include       fastcgi_params;
	}
}
```

Enable the virtual host and reload Nginx.

```shell
ln -s /etc/nginx/sites-available/readable.cc /etc/nginx/sites-enabled/readable.cc
service nginx reload
```
