# otrak-api

## Generate keys for JWT Authentification

Commands to execute to generation the keys in bash:

```bash
echo "PASSPHRASE_IN_YOUR_ENV_FILE" | openssl genpkey -out config/jwt/private.pem -pass stdin -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
echo "PASSPHRASE_IN_YOUR_ENV_FILE" | openssl pkey -in config/jwt/private.pem -passin stdin -out config/jwt/public.pem -pubout
setfacl -R -m u:www-data:rX -m u:"$(whoami)":rwX config/jwt
setfacl -dR -m u:www-data:rX -m u:"$(whoami)":rwX config/jwt
```
