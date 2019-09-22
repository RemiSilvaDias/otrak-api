# otrak-api

## Commands to run to init the project

```bash
composer install
```

Create a `.env.dev.local` at the root of the repo and put the following content:
```env
# In all environments, the following files are loaded if they exist,
# the later taking precedence over the former:
#
#  * .env                contains default values for the environment variables needed by the app
#  * .env.local          uncommitted file with local overrides
#  * .env.$APP_ENV       committed environment-specific defaults
#  * .env.$APP_ENV.local uncommitted environment-specific overrides
#
# Real environment variables win over .env files.
#
# DO NOT DEFINE PRODUCTION SECRETS IN THIS FILE NOR IN ANY OTHER COMMITTED FILES.
#
# Run "composer dump-env prod" to compile .env files for production use (requires symfony/flex >=1.2).
# https://symfony.com/doc/current/best_practices/configuration.html#infrastructure-related-configuration

###> symfony/framework-bundle ###
APP_ENV=dev
#TRUSTED_PROXIES=127.0.0.1,127.0.0.2
#TRUSTED_HOSTS='^localhost|example\.com$'
###< symfony/framework-bundle ###

###> doctrine/doctrine-bundle ###
# Format described at https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#connecting-using-a-url
# For an SQLite database, use: "sqlite:///%kernel.project_dir%/var/data.db"
# Configure your db driver and server_version in config/packages/doctrine.yaml
DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name
###< doctrine/doctrine-bundle ###
```
PLEASE CHANGE THE DATABASE_URL VARIABLE WITH THE CREDENTIALS OF YOUR OWN MYSQL SERVER. db_name will be the name of the database created (so you can put the name you want)

To launch the local webserver:
```bash
php bin/console server:run #ctrl-c to shutdown the server
```

## Generate keys for JWT Authentification (for frontend <-> backend authentification system)

Commands to execute to generation the keys in bash:

```bash
echo "PASSPHRASE_IN_YOUR_ENV_FILE" | openssl genpkey -out config/jwt/private.pem -pass stdin -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
echo "PASSPHRASE_IN_YOUR_ENV_FILE" | openssl pkey -in config/jwt/private.pem -passin stdin -out config/jwt/public.pem -pubout
setfacl -R -m u:www-data:rX -m u:"$(whoami)":rwX config/jwt
setfacl -dR -m u:www-data:rX -m u:"$(whoami)":rwX config/jwt
```

## Load data into database

To execute on bash:

If the database already exists:
```bash
php bin/console doctrine:database:drop --force
```

Then:

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

A super admin user with the email 'admin@oc.io' and the password 'admin' will be created !!! only for dev purpose, DO NOT LOAD THE FIXTURES IN PROD (or change the data and then erase the file)
