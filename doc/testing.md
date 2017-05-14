# Testing

By default, tests are run in an SQLite database, located in `var/eole-test.sqlite`.


### Docker installation

Run unit tests and codestyle checker:

``` bash
make test
```


### Raw installation

``` bash
# Create your test environment config file:
cp config/environment_test.yml.dist config/environment_test.yml

# Create test database schema
php bin/console --env=test orm:schema-tool:create
```

Run tests

``` bash
vendor/bin/phpunit -c .
```

PHP CodeSniffer is also included in dev dependencies, run phpcs:

``` bash
vendor/bin/phpcs --standard=phpcs.xml src/
```
