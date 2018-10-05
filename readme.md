## Dev
Something broken? Uncomment the following line (73) from app/Exceptons/Handler.php
```
//return parent::render($request, $exception);
```
To help you track down what the problem might be

## Roll Out
```
# cp .env.example .env
# php artisan key:generate
# vim .env

    DB_HOST       = ...
    DB_DATABASE   = ...
    DB_USERNAME   = ...
    DB_PASSWORD   = ...

# php artisan migrate
```
**NOTE: Redis is local for all environments (dev, live, test, etc...)**

## Tests
```
# vendor\bin\phpunit tests\WebTest.php
# vendor\bin\phpunit tests\ApiTest.php
# vendor\bin\phpunit tests\CacheTest.php
```
** NOTE: dont run tests\CacheTest.php in live as it will delete all the local cache**
