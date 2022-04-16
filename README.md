# Field Based Login

This module extends Drupal core user login. Using this module, an admin user
is allowed to configure new user account fields, which can be used to login,
instead of using username or email.

For example, you could set up a mobile number field. Each user should
have a unique mobile number, and they can essentially use this number as their
username for logging in.

Normal methods for logging in can be allowed or not as well.

For more information, please visit https://www.drupal.org/project/fbl

## Requirements

Uses/extends the Drupal core user module.

Module provides config translation if enabled, but not required.

Useful if you want to translate the custom label and description, which can
be altered on the user login form.

## Install

Install module like any other contributed module.

It is recommended to install via composer. See below for command:

```bash
composer require drupal/fbl
drush en fbl
```

## Configuration

* Goto "/admin/config/people/fbl" and configure the settings as required.

## Maintainers

Current maintainers:

* George Anderson (geoanders) - https://www.drupal.org/u/geoanders

Past maintainers:

* Krishna Kanth (krknth) - https://www.drupal.org/u/krknth
* Kishore Kumar (N.kishorekumar) - https://www.drupal.org/u/nkishorekumar
