![Laravel Google Shopping Feed](docs/laravel-google-shopping-feed.png)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/wearepixel/laravel-google-shopping-feed.svg)](https://packagist.org/packages/wearepixel/laravel-google-shopping-feed)
[![Tests](https://img.shields.io/github/actions/workflow/status/wearepixel/laravel-google-shopping-feed/tests.yml?label=tests)](https://github.com/wearepixel/laravel-google-shopping-feed/actions/workflows/tests.yml)
[![PHP Version](https://img.shields.io/packagist/php-v/wearepixel/laravel-google-shopping-feed.svg)](https://packagist.org/packages/wearepixel/laravel-google-shopping-feed)
[![Total Downloads](https://img.shields.io/packagist/dt/wearepixel/laravel-google-shopping-feed.svg)](https://packagist.org/packages/wearepixel/laravel-google-shopping-feed)
[![License](https://img.shields.io/packagist/l/wearepixel/laravel-google-shopping-feed.svg)](https://packagist.org/packages/wearepixel/laravel-google-shopping-feed)

A simple package to easily create an XML feed for Google Merchant Center to parse and retrieve your products.

We recommend adding this to an API controller and generating it on the fly so your feed is always up to date.

## Requirements

- PHP 8.4+
- Laravel 10, 11, 12 or 13

## Installation

```bash
composer require wearepixel/laravel-google-shopping-feed
```

## Usage

```php
use Wearepixel\LaravelGoogleShoppingFeed\LaravelGoogleShoppingFeed;

$feed = LaravelGoogleShoppingFeed::init(
    title: 'My Store',
    description: 'My store product feed',
    link: 'https://mystore.com',
);

$feed->addItem([
    'id'           => 'item_001',
    'title'        => 'Blue Nikes',
    'link'         => 'https://mystore.com/products/blue-nikes',
    'g:price'      => '29.99 AUD',
    'g:image_link' => 'https://mystore.com/images/blue-nikes-001.jpg',
]);

return $feed->generate();
```

### Required item fields

| Field          | Description                                      |
| -------------- | ------------------------------------------------ |
| `id`           | Unique product identifier                        |
| `title`        | Product name                                     |
| `link`         | URL to the product page                          |
| `g:price`      | Price including currency code (e.g. `29.99 AUD`) |
| `g:image_link` | URL to the main product image                    |

Any additional fields supported by the [Google Product Data Specification](https://support.google.com/merchants/answer/7052112) can be passed alongside the required fields.

### Methods

**`LaravelGoogleShoppingFeed::init(string $title, string $description, string $link): static`**

Creates a new feed instance with channel metadata.

**`$feed->addItem(array $item): void`**

Adds a product to the feed. Throws `MissingRequiredFieldException` if any required field is absent.

**`$feed->toXml(): string`**

Returns the feed as an XML string.

**`$feed->generate(): Response`**

Returns an HTTP response with the XML content and `application/rss+xml` content type. Suitable for returning directly from a controller.

## Testing

```bash
composer test
```

## License

MIT
