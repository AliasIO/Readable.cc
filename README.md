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

`$ mysql < mysql/schema.sql`

Copy `config/pdo.example.php` to `config/pdo.php` and edit the file.

Edit `config.php`.

Make the following directories writable:

  * `sessions`
	* `HTMLPurifier/DefinitionCache/Serializer/HTML`
	* `HTMLPurifier/DefinitionCache/Serializer/URI`

```
$ chmod 777 sessions HTMLPurifier/DefinitionCache/Serializer/HTML HTMLPurifier/DefinitionCache/Serializer/URI
```

Set up a cron job to periodically fetch feeds.

```
# Example crontab entry

*/5 * * * * /usr/bin/php /srv/readable.cc/index.php -q cron > /dev/null
```
