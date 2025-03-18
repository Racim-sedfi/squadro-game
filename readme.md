
# Squadro Game


## Build

Dans votre invit de commandes executer
```bash
  docker-compose build
```

## Test

Dans votre invit de commandes executer
```bash
  XDEBUG_MODE=coverage ./vendor/bin/phpunit --testdox tests --coverage-html coverage --coverage-filter app/
```
