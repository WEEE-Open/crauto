# crauto

Creatore e
Rimuovitore
Autogestito di
Utenti che
Tutto
Offre

## What

Administration panel, self-service account management for users, user registration and other bits and bobs.
Connects to an LDAP backend and possibly some day in the future to Keycloak via APIs.

## How

### Baremetal

```shell
composer install
# The defaults are good for development, change them for production
cp config/config-example.php config/config.php
php -S localhost:8777 -t public
```

### Docker
*NOT MEANT FOR PRODUCTION*
```shell
# Should have preconfigured connection to ldap and keycloak
cp config/config-example-docker.php config/config.php
docker-compose up
```

### SIR generation

To set up that functionality:

1. Create a `resources/pdftemplates` directory
2. Add the `F-MOD-LABORATORI.pdf` file
3. Add `template-dottorandi.csv` and `template-studenti.csv`, you can look at the ones in `resources/pdftemplates-test` for examples

`F-MOD-LABORATORI.pdf` **must** be converted to PDF 1.4 or PDF/A, PDF 1.5 will fail with an error since FPDF cannot parse it.

### Tests

```shell
composer test
```

### Lint

`composer lint` to view the errors, `composer lintfix` to fix them

# License

MIT, however favicon.ico [was originally public domain](https://www.wpclipart.com/food/vegetables/cabbage/green_cabbage.png.html).
