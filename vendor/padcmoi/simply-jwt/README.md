# SimplyJWT
JsonWebToken(JWT) simple d'utilisation

# ➡️Install
```
composer require padcmoi/simply-jwt
```


# ➡️Usage
***Exemple***
```php
use Padcmoi\JWT\SimplyJWT;

SimplyJWT::init('***PRIVATE_KEY***', 'HS256', 3600); // KEY, Algorithm, Expire Timestamp

$serializedToken = SimplyJWT::encode([
    "exp" => time() + 3600,
    "iat" => time(),
    "uid" => -1, // Id account
]);

$payload = SimplyJWT::decode($serializedToken); 
```

# ➡️Others
##### 🧳Packagist
https://packagist.org/packages/padcmoi/simply-jwt

##### 🔖Licence
Ce travail est sous licence [MIT](/LICENSE).

##### 🔥Pour me contacter sur discord
Lien discord [discord.gg/257rUb9](https://discord.gg/257rUb9)

##### 🍺Si vous souhaitez m’offrir une bière
Me faire un don 😍 [par Paypal](https://www.paypal.com/paypalme/Julien06100?locale.x=fr_FR)