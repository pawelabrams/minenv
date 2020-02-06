# minenv
Feeds `getenv()`, `$_ENV` and `$_SERVER` with `.env` file's contents. One procedure, no bullshit.

An incomplete rewrite of [PHP dotenv](https://github.com/vlucas/phpdotenv).

## Purpose

**[You should never commit your production keys to a public repo.](http://www.securityweek.com/github-search-makes-easy-discovery-encryption-keys-passwords-source-code)** [Never.](http://blog.nortal.com/mining-passwords-github-repositories/) [Ever.](http://evandrix.svbtle.com/how-i-gained-access-to-amazon-ec2-servers-from-github-search-adapted) You can publish developer settings, sure -- if you know your setup is safe and unavailable to access from outside. But for most people, configuration contains sensitive data like keys to external APIs, live database credentials and other details that the public shall not see.

Ignored config files are good, but setting the sensitive data directly in the environment is better on the production servers. And to imitate the behaviour in the development? Just use minenv.

## Usage

List the variables in `.env` file in a convenient location, away from users
```
SECRET_KEY=314b0r47353cr37k3y
APP_DSN="mysql:host=localhost;dbname=yourdb;charset=utf8"
APP_USER=yourname
APP_PASS=w3zn13pr0bujh4k0w4c
```

To use the variables, require the `minenv.php` and `loadenv()`.
```php
<?php
require 'minenv.php';
loadenv(__DIR__);
```

You can use composer, too! Just add `pawelabrams/minenv` to your project requirements.
```php
<?php
require 'vendor/autoload.php';
loadenv(__DIR__);
```

The arguments are:
1. the path in where to look for `.env` (required),
2. the name of the file if other than `.env`,
3. options array.

Currently, the only option to set is mutability: both `['mutable' => true]` and `['mutable']` set the precedence of the `.env` variables higher than those which were already set.

## More tricks

Comments can be added using `#`; be sure to include at least one space between the value and the comment when using them on the same line.
```
# this is a comment!
APP_USER = root # the comment's ok, but don't use root user for apps unless necessary
APP_PASS = 3#thisIsStillPartOfPassword
```

Use a different filename:
```php
<?php
require 'minenv.php';
loadenv(__DIR__, '.config');
```

Set to mutable (`.env` overwrites anything it is able to):
```php
<?php
require 'minenv.php';
loadenv(__DIR__, '.env', ['mutable']); # or array('mutable') / array('mutable' => true)
```

As in dotenv, you can add `export` to be able to `source` the file in bash, but nested variables (`$APP_DSN`) are not currently supported in minenv. They will work in bash, though, so you were warned!

```
export S3_BUCKET=bucketname
```
