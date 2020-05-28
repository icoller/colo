# What is Colo?

Colo is a fast, simple, extensible framework for PHP. Colo enables you to 
quickly and easily build RESTful web applications.

```php
require 'colo/Colo.php';

Colo::route('/', function(){
    echo 'hello world!';
});

Colo::start();
```