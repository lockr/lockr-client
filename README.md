# Lockr API Client

## Usage Overview

```php
$cert_path = '/path/to/lockr/client/cert.pem';
$partner_name = 'lockr partner name';

// Partner is the authentication unit for Lockr.
// $cert_path is the path to a PEM encoded x509 certificate
// provided by Lockr.
// $partner_name is the name of the partner to authenticate,
// usually 'custom' for certificates provided directly by Lockr.
$partner = new \Lockr\Partner($cert_path, $partner_name);
$client = new \Lockr\Lockr($partner);

// SiteClient provides operations on a site.
$site_client = new \Lockr\SiteClient($client);

list($exists, $available) = $site_client->exists();

// KeyClient provides operations for keys.
$key_client = new \Lockr\KeyClient($client);

$key_client->set('my_key', 'secret key value', 'My Key');
$key_value = $key_client->get('my_key');
```

