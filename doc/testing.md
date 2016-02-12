# Testing

By default, tests are run in an SQLite database, located in `var/eole-test.sqlite`.

Run tests

``` bash
vendor/bin/phpunit -c .
```

PHP CodeSniffer is also included in dev dependencies, run phpcs:

``` bash
vendor/bin/phpcs --standard=phpcs.xml src/
```
