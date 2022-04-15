# Field Based Login

This module extends Drupal core User login features. Using this module, Admin
is allowed to configure new user account fields as loggable like mobile number
or user unique Id number as a field. And users can also log in with the same
mobile number or user serial number.

For more info, Please visit https://www.drupal.org/project/fbl

## Requirements

This module does not have any special dependencies.

## Install

Install module like any other contributed module.

It is recommended to install via composer. See below for command:

```bash
composer require drupal/fbl
drush en fbl
```

## Configuration

* Copy the "fbl" directory under "modules/" or "sites/all/modules";
* Go to "admin/modules" and enable "Field Based Login" module;
* Go to "admin/config/people/fbl" and configure the settings.

## Maintainers

Current maintainers:

* George Anderson (geoanders) - https://www.drupal.org/u/geoanders

Past maintainers:

* Krishna Kanth (krknth) - https://www.drupal.org/u/krknth
* Kishore Kumar (N.kishorekumar) - https://www.drupal.org/u/nkishorekumar
