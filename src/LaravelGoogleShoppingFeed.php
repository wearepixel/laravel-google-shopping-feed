<?php

declare(strict_types=1);

namespace Wearepixel\LaravelGoogleShoppingFeed;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Spatie\ArrayToXml\ArrayToXml;
use Wearepixel\LaravelGoogleShoppingFeed\Exceptions\MissingRequiredFieldException;

class LaravelGoogleShoppingFeed
{
    public readonly string $title;
    public readonly string $description;
    public readonly string $link;

    protected array $products = [];

    protected const array REQUIRED_PRODUCT_FIELDS = [
        'id',
        'link',
        'title',
        'g:price',
        'g:image_link',
    ];

    public static function init(string $title = '', string $description = '', string $link = ''): static
    {
        $feed = new static;
        $feed->title = $title;
        $feed->description = $description;
        $feed->link = $link;

        return $feed;
    }

    public function addItem(array $item): void
    {
        foreach (self::REQUIRED_PRODUCT_FIELDS as $field) {
            if (! isset($item[$field])) {
                throw new MissingRequiredFieldException("Required field '{$field}' is missing");
            }
        }

        $this->products[] = $item;
    }

    public function toXml(): string
    {
        $data = [
            'rss' => [
                '_attributes' => [
                    'xmlns:g' => 'http://base.google.com/ns/1.0',
                    'version' => '2.0',
                ],
                'channel' => [
                    'title' => $this->title,
                    'description' => $this->description,
                    'link' => $this->link,
                ],
            ],
        ];

        foreach ($this->products as $key => $product) {
            $data['rss']['channel']['item_' . $key] = $product;
        }

        $xml = ArrayToXml::convert($data, '');
        $xml = str_replace(['    ', '<root>', '</root>', "\n", "\r", '<remove>remove</remove>'], '', $xml);
        // spatie/array-to-xml does not support repeated keys, so products are keyed as
        // item_0, item_1, etc. and renamed back to <item> tags here.
        $xml = (string) preg_replace('/item_\d+/', 'item', $xml);

        return $xml;
    }

    public function generate(): Response
    {
        return ResponseFacade::make($this->toXml(), headers: ['Content-Type' => 'application/rss+xml']);
    }
}
