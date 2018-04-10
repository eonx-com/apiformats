# API Formats

## Installation
Use [Composer](https://getcomposer.org/) to install the package in your project:

```
composer require eoneopay/apiformats
```

## Formats
By default the package handle **JSON** (application/json) and **XML** (application/xml, text/xml) formats.
If you want to handle different formats or want to implement your own **JSON**/**XML** encoding logic, the only thing
you need to do is creating your encoder implementing `\EoneoPay\ApiFormats\Interfaces\RequestEncoderInterface` and
tell the package to use it, with which Mime types as explained in the [Configuration](#configuration) section.

## Integration
#### Laravel
To integrate the package into your [Laravel](https://laravel.com) or [Lumen](https://lumen.laravel.com)
you need to register the following service provider and middleware:

- **ServiceProvider:** `\EoneoPay\ApiFormats\Bridge\Laravel\Providers\ApiFormatsServiceProvider`
- **Middleware:** `\EoneoPay\ApiFormats\Bridge\Laravel\Middlewares\ApiFormatsMiddleware`

That's it! Your application is now able to easily receive requests and generate responses in several formats.

###### Formatted Api Response
If you want to customise the generated response as its status code or headers without handling
body encoding yourself, the package provides the `\EoneoPay\ApiFormats\Bridge\Laravel\Responses\FormattedApiResponse`
object you can return from your controllers/middlewares. The `FormattedApiResponse` accept three parameters through its
constructor as following:

- **$content (mixed):** array or object able to be cast as an array
- **$statusCode (int):** status code of response, default as 200
- **$headers (array):** headers of response, default as empty array

## Configuration
The philosophy of the package is to map encoders to a list of MIME types that they can handle through an array 
as following:

```php
$formats = [
    <RequestEncoderInterface> => [<mime_type>, <mime_type>, ...]
];
```

Each MIME type can be the exact name as `application/json` or a [Regex](http://php.net/manual/en/reference.pcre.pattern.syntax.php)
used to match multiple MIME types as `application/vnd.eoneopay.v[0-9]+\+json`.

#### Laravel
To configure supported formats in your application, create a `api-formats.php` config file with a `formats`
array using the encoders class as key and array of associated Mime types as value:

```php
// config/api-formats.php

return [
    'formats' => [
        JsonRequestEncoder::class => ['application/json'],
        XmlRequestEncoder::class => ['(application|text)/xml'],
        YourCustomerEncoder::class => ['you-custom-mime-type']
    ]
]; 
``` 
