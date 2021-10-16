# bundle-api-slim
Pack de Class utilitaires pour la création d'une API sur SLIM

# 🔖 Install
```
composer require padcmoi/bundle-api-slim
```

# 🔖 Usage
```php
use Padcmoi\BundleApiSlim\Misc;
use Padcmoi\BundleApiSlim\SanitizeData;
use Padcmoi\BundleApiSlim\Token\JwtToken;


$jwt_token = JwtToken::create();

SanitizeData::without(['ab', 'baa', 'aa']);
SanitizeData::clean(true, []);

Misc::snakeCase('aze ert uUu . tt.oo__aa//jjj;içp');
```

# Others
### 🔖 Packagist
https://packagist.org/packages/padcmoi/bundle-api-slim

##### 🔖 Licence
Ce travail est sous licence [MIT](/LICENSE).

##### 🔥 Pour me contacter sur discord
Lien discord [discord.gg/257rUb9](https://discord.gg/257rUb9)

##### 🍺 Si vous souhaitez m’offrir une bière
😍 Me faire un don [par Paypal](https://www.paypal.com/paypalme/Julien06100?locale.x=fr_FR)