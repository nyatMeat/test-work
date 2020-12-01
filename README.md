This is dockerized test task

[Here is the desciption of test task](https://docs.google.com/document/d/1uUG3akf4R8A2rNBcdAi70s5EEHTqidstbXDtiHSHNXU/edit)

To start this application you have to execute command

```
docker-compose up -d --build
```
in current directory (api-platform-2.5.7). This command will install whole environment for work

When environment will be ready you have to execute command in php docker container
```
docker-compose exec php composer install
```
it will install all necessary packages

and
```
docker-compose exec php sh -c '
    set -e
    apk add openssl
    mkdir -p config/jwt
    jwt_passphrase=${JWT_PASSPHRASE:-$(grep ''^JWT_PASSPHRASE='' .env | cut -f 2 -d ''='')}
    echo "$jwt_passphrase" | openssl genpkey -out config/jwt/private.pem -pass stdin -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
    echo "$jwt_passphrase" | openssl pkey -in config/jwt/private.pem -passin stdin -out config/jwt/public.pem -pubout
    setfacl -R -m u:www-data:rX -m u:"$(whoami)":rwX config/jwt
    setfacl -dR -m u:www-data:rX -m u:"$(whoami)":rwX config/jwt
'
```
previous command create keys for jwt token system

To run the test before start you have to run command
```
docker-compose exec php bin/console doctrine:database:create --env test
```
This command will create database for functional tests

And then you can run the test by using command
```
docker-compose exec php bin/phpunit
```

If you want to see documentation for api you need to visit
```
https://localhost:8443/docs
```
