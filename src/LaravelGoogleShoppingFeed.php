<?php

namespace Wearepixel\LaravelGoogleShoppingFeed;

use Spatie\ArrayToXml\ArrayToXml;

class LaravelGoogleShoppingFeed
{
    public $title;
    public $description;
    public $link;

    protected $xml = [];
    protected $products = [];
    protected $currency = "AUD";

    protected $requiredProductFields = [
        'id',
        'link',
        'title',
        'price',
        'image_link'
    ];

    public static function init($title = null, $description = null, $link = null)
    {
        $feed = new self();

        $feed->title = $title;
        $feed->description = $description;
        $feed->link = $link;

        return $feed;
    }

    public function addItem(array $item): bool
    {
        foreach ($this->requiredProductFields as $field) {
            if (!isset($item[$field])) {
                throw new \Exception("Required field '{$field}' is missing");
            }
        }

        foreach ($item as $key => $value) {
            $product[$key] = $value;
        }

        $this->products[] = $product;

        return true;
    }

    public function generate()
    {
        $this->xml = [
            'rss' 	=> [
                '_attributes' => [
                    'xmlns:g' => 'http://base.google.com/ns/1.0',
                    'version' => '2.0',
                ],
                'channel' => [
                    'title' => $this->title,
                    'description' => $this->description,
                    'link' => $this->link,
                ]
            ]
        ];

        foreach ($this->products as $key => $product) {
            $this->xml['rss']['channel']['item_'.$key] = $product;
        }

        $xml = ArrayToXml::convert($this->xml, '');
        $xml = str_replace(['    ', '<root>', '</root>', "\n", "\r", '<remove>remove</remove>'], '', $xml);
        $xml = preg_replace([
            "/item_[0-9][0-9][0-9][0-9]/",
            "/item_[0-9][0-9][0-9]/",
            "/item_[0-9][0-9]/",
            "/item_[0-9]/",
        ], "item", $xml);

        return response($xml)->header('Content-Type', 'text/xml');
    }
}
