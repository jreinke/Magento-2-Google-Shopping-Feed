<?php

declare(strict_types=1);

namespace RunAsRoot\GoogleShoppingFeed\Converter;

use Magento\Framework\Stdlib\DateTime\DateTime;

class ArrayToXmlConverter
{
    private DateTime $dateTime;

    public function __construct(DateTime $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    public function convert(array $rows): string
    {
        $xml = $this->getOpenRootTag() . PHP_EOL;
        $xml .= $this->getCreationTimeTag() . PHP_EOL;

        foreach ($rows as $row) {
            $xml .= $this->getItem($row) . PHP_EOL;
        }

        $xml .= $this->getCloseRootTag();
        return $xml;
    }

    private function getOpenRootTag(): string
    {
        return <<<XML
<?xml version="1.0"?>
<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">
	<channel>
XML;
    }

    private function getCreationTimeTag(): string
    {
        return <<<XML
        <created_at>{$this->dateTime->gmtDate('Y-m-d H:i')}</created_at>
XML;
    }

    private function getItem(array $row): string
    {
        return <<<XML
        <item>
			<g:id><![CDATA[{$row['sku']}]]></g:id>
			<g:title><![CDATA[{$row['name']}]]></g:title>
			<g:description><![CDATA[{$row['description']}]]></g:description>
			<g:link><![CDATA[{$row['url']}]]></g:link>
			<g:image_link><![CDATA[{$row['image_link']}]]></g:image_link>
			<g:availability><![CDATA[{$row['is_in_stock']}]]></g:availability>
            <g:product_type><![CDATA[{$row['product_type']}]]></g:product_type>
			<g:price><![CDATA[{$row['price']}]]></g:price>
			<g:brand><![CDATA[{$row['manufacturer']}]]></g:brand>
			<g:gtin><![CDATA[{$row['ean']}]]></g:gtin>
            <g:mpn><![CDATA[{$row['sku']}]]></g:mpn>
            <g:color><![CDATA[{$row['color']}]]></g:color>
            <g:gender><![CDATA[{$row['gender']}]]></g:gender>
            <g:material><![CDATA[{$row['material']}]]></g:material>
            <g:pattern><![CDATA[{$row['pattern']}]]></g:pattern>
            <g:item_group_id><![CDATA[{$row['item_group_id']}]]></g:item_group_id>
            {$this->getProductDetail($row['product_detail'])}
			{$this->getShipping($row['shipping'])}
			{$this->getAdditionalImageLinks($row['additional_image_link'])}
		</item>
XML;
    }

    private function getAdditionalImageLinks(array $imageLinks): string
    {
        $result = '';
        foreach ($imageLinks as $imageLink) {
            $result .= "<g:additional_image_link><![CDATA[$imageLink]]></g:additional_image_link>";
        }
        return $result;
    }

    private function getProductDetail(array $productDetails): string
    {
        $result = '';
        foreach ($productDetails as $data) {
            $result .= "
            <g:product_detail>
                <g:section_name><![CDATA[General]]></g:section_name>
                <g:attribute_name><![CDATA[{$data['attribute_label']}]]></g:attribute_name>
                <g:attribute_value><![CDATA[{$data['attribute_value']}]]></g:attribute_value>
            </g:product_detail>
            ";
        }
        return $result;
    }

    private function getShipping(array $shippingData): string
    {
        $shipping = '';
        foreach ($shippingData as $data) {
            $shipping .= "
            <g:shipping>
                <g:country><![CDATA[{$data['country']}]]></g:country>
                <g:price><![CDATA[{$data['price']}]]></g:price>
             </g:shipping>
            ";
        }
        return $shipping;
    }

    private function getCloseRootTag(): string
    {
        return <<<XML
    </channel>
</rss>
XML;
    }
}
