darksky-php
===========

A simple PHP wrapper for interacting with the [DarkSky API](https://developer.darkskyapp.com/docs).


Requirements
------------

 * PHP 5.2.0+
 * a DarkSky API key


Installation
------------

Installing `darksky-php` is simple, and it is recommended that you use [Composer](http://getcomposer.org/) to
use it in your project. If you're new to Composer, please see the Composer website for more information on what
Composer is and how to use it. Once you have your project set up to use Composer, to include `darksky-php`, add
the following to your `composer.json` file:

```json
{
    "require": {
        "epochblue/darksky-php": "dev-master"
    }
}
```

With that added, run a `php composer.phar update epochblue/darksky-php` from your project root, and you're
ready to go.


Usage
-----

_All the public methods in the `darksky-php` wrapper return associative arrays_.
Below is an example of all the public methods available via this API wrapper.
Please refer to the [DarkSky API Documentation](https://developer.darkskyapp.com/docs) for more
details about the return values.

```php
<?php
// Creates an instance of the API wrapper.
$darksky = new DarkSky('<YOUR API KEY HERE>');

// Returns a forecast for the next hour at a specific location
$forecast = $darksky->getForecast(36.126617, -86.842756);

// Returns a (slightly more brief) forecast for the next hour at a specific location
$brief_forecast = $darksky->getBriefForecast(36.126617, -86.842756);

// Returns forecasts for given locations at specific points in time
// NOTE: the 'time' array key is optional. If it's not included, the current time will be used.
$precipitation  = $darksky->getPrecipitation(
    array(
        'lat'  => 36.126617,
        'long' => -86.842756,
        'time' => 1350612061
    ),
    array(
        'lat'  => 36.126617,
        'long' => -86.842756,
        'time' => 1350600061
    ),
    array(
        'lat'  => 36.126617,
        'long' => -86.842756
    )
);

// Returns a list of interesting storms going on at this moment
$interesting = $darksky->getInterestingStorms();
```

Author
------

Bill Israel - [https://github.com/epochblue](https://github.com/epochblue) - [@epochblue](http://twitter.com/epochblue)

Copyright
---------

Copyright (c) 2012 Bill Israel. See the included `LICENSE` file for further details.