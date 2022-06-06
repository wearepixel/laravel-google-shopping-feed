Laravel Google Shopping Feed
==============

A simple package to easily create an XML feed for Google Merchant Center to parse and retrieve your products on a frequent basis.

We recommend adding this to an API controller and generating it on the fly each time so you can be sure your products are always up to date within your merchant center.

## Installation

```
composer require wearepixel/laravel-google-shopping-feed
```

## Example
```php
use Wearepixel\LaravelGoogleShoppingFeed\LaravelGoogleShoppingFeed;

$feed = LaravelGoogleShopping::init(
	'Product Feed',
	'App product Feed',
	'https://mystore.com'
);

$feed->addItem([
	'id' => 'item_001',
	'title' => 'Blue Nikes',
	'link' => 'https://mystore.com/products/blue-nikes',
	'image_link' => 'https://mystore.com/images/blue-nikes-001.jpg',
	'price' => 29.99,
]);

return $feed->generate();
```