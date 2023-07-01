<?php

declare(strict_types=1);

namespace RunAsRoot\GoogleShoppingFeed\DataProvider\AttributeHandlers;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Url;
use Magento\Store\Model\StoreManagerInterface;
use RunAsRoot\GoogleShoppingFeed\ConfigProvider\UrlSuffixProvider;
use RunAsRoot\GoogleShoppingFeed\DataProvider\ParentProductProvider;

class ProductUrlProvider implements AttributeHandlerInterface
{
    private Url $url;
    private StoreManagerInterface $storeManager;
    private ParentProductProvider $productProvider;
    private UrlSuffixProvider $urlSuffixProvider;

    public function __construct(
        Url                   $url,
        StoreManagerInterface $storeManager,
        ParentProductProvider $productProvider,
        UrlSuffixProvider     $urlSuffixProvider
    ) {
        $this->url = $url;
        $this->storeManager = $storeManager;
        $this->productProvider = $productProvider;
        $this->urlSuffixProvider = $urlSuffixProvider;
    }

    public function get(Product $product): ?string
    {
        try {
            $store = $this->storeManager->getStore($product->getStoreId());
        } catch (NoSuchEntityException $exception) {
            return null;
        }

        $productForUrlRetrieval = $this->productProvider->get($product);

        $url = sprintf(
            '%s%s',
            $productForUrlRetrieval->getData('url_key'),
            $this->urlSuffixProvider->get()
        );

        $this->url->setScope($store);

        $routeParamsShort = [
            '_direct' => $url,
            '_nosid' => true,
            '_scope' => $this->url->getData('scope'),
        ];

        return $this->url->getUrl('', $routeParamsShort);
    }
}
