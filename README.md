This is dockerized test task

[Here is the description of the test task](https://docs.google.com/document/d/1uUG3akf4R8A2rNBcdAi70s5EEHTqidstbXDtiHSHNXU/edit)

To start this application you have to execute command

For this test task I decided to use new Symfony api-platform.
Api platform provide flexible way to build restApi applications 
with an automatic documentation and serialization in any format dynamically out of the box

```
docker-compose up -d --build
```
it will start the application without xdebug
if you want to run application with xdebug you should run command
```
docker-compose -f docker-compose.dev up -d --build
```
Here is documentation how to configure phpstorm and dockerized project
```
https://api-platform.com/docs/distribution/debugging/#xdebug
```
in the current directory (api-platform-2.5.7)

When docker environment build will be done you need execute command which will generate keys for JWT authentication
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


You can run the test by executing command below
```
docker-compose exec php bin/phpunit
```

If you want to see documentation for api you need to visit
```
https://localhost:8443/docs
```
In this page you will find all api endpoints with descriptions and examples
