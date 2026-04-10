<?php

declare(strict_types=1);

use Wearepixel\LaravelGoogleShoppingFeed\Exceptions\MissingRequiredFieldException;
use Wearepixel\LaravelGoogleShoppingFeed\LaravelGoogleShoppingFeed;

function validProduct(array $overrides = []): array
{
    return array_merge([
        'id' => 'prod-1',
        'link' => 'https://example.com/product',
        'title' => 'Test Product',
        'g:price' => '29.99 AUD',
        'g:image_link' => 'https://example.com/image.jpg',
    ], $overrides);
}

describe('init', function () {
    it('stores title, description, and link', function () {
        $feed = LaravelGoogleShoppingFeed::init('My Store', 'My Desc', 'https://example.com');

        expect($feed->title)->toBe('My Store')
            ->and($feed->description)->toBe('My Desc')
            ->and($feed->link)->toBe('https://example.com');
    });

    it('defaults to empty strings', function () {
        $feed = LaravelGoogleShoppingFeed::init();

        expect($feed->title)->toBe('')
            ->and($feed->description)->toBe('')
            ->and($feed->link)->toBe('');
    });
});

describe('addItem', function () {
    it('accepts a valid item without throwing', function () {
        $feed = LaravelGoogleShoppingFeed::init('', '', '');
        $feed->addItem(validProduct());

        expect($feed)->toBeInstanceOf(LaravelGoogleShoppingFeed::class);
    });

    it('throws for missing required field', function (string $field) {
        $product = validProduct();
        unset($product[$field]);

        expect(fn () => LaravelGoogleShoppingFeed::init()->addItem($product))
            ->toThrow(MissingRequiredFieldException::class);
    })->with(['id', 'link', 'title', 'g:price', 'g:image_link']);

    it('throws with a message containing the field name', function () {
        $product = validProduct();
        unset($product['g:price']);

        expect(fn () => LaravelGoogleShoppingFeed::init()->addItem($product))
            ->toThrow(MissingRequiredFieldException::class, "g:price");
    });
});

describe('toXml', function () {
    it('returns a string', function () {
        $feed = LaravelGoogleShoppingFeed::init('Store', 'Desc', 'https://example.com');

        expect($feed->toXml())->toBeString();
    });

    it('contains the RSS xmlns:g attribute', function () {
        $feed = LaravelGoogleShoppingFeed::init();
        $xml = $feed->toXml();

        expect($xml)->toContain('xmlns:g="http://base.google.com/ns/1.0"');
    });

    it('contains the RSS version attribute', function () {
        $feed = LaravelGoogleShoppingFeed::init();
        $xml = $feed->toXml();

        expect($xml)->toContain('version="2.0"');
    });

    it('includes the channel title, description and link', function () {
        $feed = LaravelGoogleShoppingFeed::init('My Store', 'My Description', 'https://example.com');
        $xml = $feed->toXml();

        expect($xml)
            ->toContain('<title>My Store</title>')
            ->toContain('<description>My Description</description>')
            ->toContain('<link>https://example.com</link>');
    });

    it('includes product data', function () {
        $feed = LaravelGoogleShoppingFeed::init('Store', 'Desc', 'https://example.com');
        $feed->addItem(validProduct());
        $xml = $feed->toXml();

        expect($xml)
            ->toContain('<id>prod-1</id>')
            ->toContain('<title>Test Product</title>');
    });

    it('wraps products in item tags', function () {
        $feed = LaravelGoogleShoppingFeed::init('Store', 'Desc', 'https://example.com');
        $feed->addItem(validProduct());
        $xml = $feed->toXml();

        expect($xml)
            ->toContain('<item>')
            ->toContain('</item>');
    });

    it('handles multiple products', function () {
        $feed = LaravelGoogleShoppingFeed::init('Store', 'Desc', 'https://example.com');
        $feed->addItem(validProduct(['id' => 'prod-1']));
        $feed->addItem(validProduct(['id' => 'prod-2']));
        $xml = $feed->toXml();

        expect(substr_count($xml, '<item>'))->toBe(2)
            ->and($xml)->toContain('<id>prod-1</id>')
            ->and($xml)->toContain('<id>prod-2</id>');
    });

    it('includes namespaced google fields in output', function () {
        $feed = LaravelGoogleShoppingFeed::init('', '', '');
        $feed->addItem(validProduct());
        $xml = $feed->toXml();

        expect($xml)
            ->toContain('<g:price>29.99 AUD</g:price>')
            ->toContain('<g:image_link>https://example.com/image.jpg</g:image_link>');
    });

    it('generates valid xml with no products', function () {
        $feed = LaravelGoogleShoppingFeed::init('My Store', 'My Desc', 'https://example.com');
        $xml = $feed->toXml();

        expect($xml)
            ->toBeString()
            ->toContain('<title>My Store</title>')
            ->not->toContain('<item>');
    });

    it('includes the xml declaration', function () {
        $feed = LaravelGoogleShoppingFeed::init('', '', '');
        expect($feed->toXml())->toStartWith('<?xml');
    });
});

describe('generate', function () {
    it('returns an HTTP 200 response', function () {
        $feed = LaravelGoogleShoppingFeed::init('Store', 'Desc', 'https://example.com');
        $response = $feed->generate();

        expect($response->getStatusCode())->toBe(200);
    });

    it('sets the content type to application/rss+xml', function () {
        $feed = LaravelGoogleShoppingFeed::init('Store', 'Desc', 'https://example.com');
        $response = $feed->generate();

        expect($response->headers->get('Content-Type'))->toContain('application/rss+xml');
    });

    it('returns the xml in the response body', function () {
        $feed = LaravelGoogleShoppingFeed::init('Store', 'Desc', 'https://example.com');
        $feed->addItem(validProduct());
        $response = $feed->generate();

        expect($response->getContent())->toBe($feed->toXml());
    });
});
