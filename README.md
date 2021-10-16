# Bundle API Slim or others
Pack de Class utilitaires pour la création d'une API sur SLIM

# ➡️Install
```
composer require padcmoi/bundle-api-slim
```

# ➡️Main Features
- ✔️ Database PDO(SQL)
- ✔️ DotEnv
- ✔️ SanitizeData
- ✔️ JWT Auth
- ✔️ CSRF
- ✔️ Captcha
- ✔️ Misc
- ✔️ ...

# ➡️Usage
***Exemple***
```php
use Padcmoi\BundleApiSlim\Database;
use Padcmoi\BundleApiSlim\Misc;
use Padcmoi\BundleApiSlim\SanitizeData;
use Padcmoi\BundleApiSlim\Token\JwtToken;

// database
$lastInsertId = Database::insert(
    "INSERT INTO `token` SET
        `payload` = md5(:payload),
        `header` = 'jwt',
        `uid` = :uid,
        `not_before_renew` = DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL :nbf SECOND),
        `expire_at` = DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL :exp SECOND)",
    array(':payload' => $serializedToken, ':uid' => $uid, ':nbf' => $nbf, ':exp' => $expire)
);
// ...

// auth token
$jwt_token = JwtToken::create();
$uid = JwtToken::getUid($jwt_token);
var_dump(JwtToken::check($jwt_token));


SanitizeData::without(['ab', 'baa', 'aa']);
SanitizeData::clean(true, []);

Misc::snakeCase('aze ert uUu . tt.oo__aa//jjj;içp');
```

# ➡️Others
##### 🧳Packagist
https://packagist.org/packages/padcmoi/bundle-api-slim

##### 🔖Licence
Ce travail est sous licence [MIT](/LICENSE).

##### 🔥Pour me contacter sur discord
Lien discord [discord.gg/257rUb9](https://discord.gg/257rUb9)

##### 🍺Si vous souhaitez m’offrir une bière
Me faire un don 😍 [par Paypal](https://www.paypal.com/paypalme/Julien06100?locale.x=fr_FR)