# Testing

By default, tests are run in an SQLite database, located in `var/eole-test.sqlite`.

Run tests

``` bash
php vendor/bin/phpunit -c .
```

PHP CodeSniffer is also included in dev dependencies, run phpcs:

``` bash
php vendor/bin/phpcs --standard=phpcs.xml src/
```
