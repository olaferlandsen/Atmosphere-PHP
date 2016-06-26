[![Donate](https://www.paypalobjects.com/es_XC/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8TJZSNT5JQUXL)

## Atmosphere a PHP Framework to create powerful experiences.

### Getting started, too easy!

Create you first application in `Atmosphere/applications/index/index.php`

```php
<?php
namespace Atmosphere\Controller {
    class index extends Controller {
        public function _config()
        {
        }
        public function index()
        {
            $this->responseCode(200);
            echo 'Hello World!!';
        }
    }
}
```
open in you browser `http://you-site.com/`.

### Why use Atmosphere?
Atmosphere is designed to consume the least amount of resources and focus them in place and right time.

### Is it safe to use Atmosphere?
Currently there are several systems that use Atmosphere and have not presented problems of compatibility, security or performance, except specific cases that escape from the minimum standards of programming.

### What state this development?
The development of this framework currently depends only on He-3 Technologies, founded in Chile by Olaf Erlandsen, which has freed Atmosphere for the Internet developer community could improve the user experience.

### What are the requirements of the framework?
Atmosphere only requires `PHP 5.6` or higher, in addition to Apache 2 with mod_rewrite enabled.

### Is there any support?
That's right! you can get FREE support by contacting via email, Skype or even opening a issue here on Github.

### How can I contribute?
There are several ways to contribute, some of these are by reporting errors, improving the code or simply as donations (do you like to give me a beer or coffee?).
If you decide to contribute on a regular basis, I'll add your name to the list of contributors (Would you like to be part of the development team? We will share coffee!).



## Features

 - [Magic Routes](https://github.com/olaferlandsen/Atmosphere-PHP/wiki/URL-Friendly-and-Magic-Routes#magic-routes)
 - [Custom response codes](https://github.com/olaferlandsen/Atmosphere-PHP/wiki/URL-Friendly-and-Magic-Routes#magic-routes)
 - Support to MySQL, MariaDB, PostgreSQL, SQLite, Oracle, etc
 - Support Template Engine(you can use Smarty, Dwoo, etc)
 - Custom typehint
 - [Custom URL Friendly](https://github.com/olaferlandsen/Atmosphere-PHP/wiki/URL-Friendly-and-Magic-Routes)
 - Support global configuration
 - Debug tools!
 - [Ideal to create own Rest applications!](https://github.com/olaferlandsen/Atmosphere-PHP/wiki/How-to-create-a-simple-Rest-Application%3F)


## Default Libraries
 - Atmosphere\Database - Database class
 - Atmosphere\Cache - Cache class
 - Atmosphere\Upload - Upload file class
 - Atmosphere\Utilities - Utilities
 - Smarty - Template Engine
