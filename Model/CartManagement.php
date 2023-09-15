<?php

namespace Monext\Payline\Model;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Checkout\Model\Cart as CheckoutCart;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Monext\Payline\Model\OrderIncrementIdTokenManagement;
use Monext\Payline\Helper\Constants as HelperConstants;

class CartManagement
{
    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var CartManagementInterface
     */
    protected $cartManagement;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var OrderIncrementIdTokenManagement
     */
    protected $orderIncrementIdTokenManagement;

    /**
     * @var CheckoutCart
     */
    protected $checkoutCart;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

    protected $cartByToken = [];

    protected  $scopeConfig;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        CartManagementInterface $cartManagement,
        OrderIncrementIdTokenManagement $orderIncrementIdTokenManagement,
        QuoteFactory $quoteFactory,
        CheckoutCart $checkoutCart,
        ProductCollectionFactory $productCollectionFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->cartRepository = $cartRepository;
        $this->cartManagement = $cartManagement;
        $this->quoteFactory = $quoteFactory;
        $this->orderIncrementIdTokenManagement = $orderIncrementIdTokenManagement;
        $this->checkoutCart = $checkoutCart;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->scopeConfig = $scopeConfig;
    }

    public function handleReserveCartOrderId($cartId, $forceReserve = false)
    {
        $cart = $this->cartRepository->getActive($cartId);

        if ($forceReserve) {
            $cart->setReservedOrderId(null);
        }

        if (!$cart->getReservedOrderId()) {
            $cart->reserveOrderId();
            $this->cartRepository->save($cart);
        }

        return $this;
    }

    public function placeOrderByToken($token)
    {
        $quote = $this->getCartByToken($token);
        $orderId = $this->cartManagement->placeOrder($quote->getId());
        $this->saveOrderIdOnToken($token, $orderId);

        return $this;
    }

    public function restoreCartFromOrder(Order $order)
    {
        foreach ($order->getItemsCollection() as $orderItem) {
            $this->checkoutCart->addOrderItem($orderItem);
        }

        // TODO Handle couponCode

        $this->checkoutCart->save();
        return $this;
    }

    /**
     * @param $token
     * @return \Magento\Quote\Model\Quote
     */
    public function getCartByToken($token)
    {
        if(!isset($this->cartByToken[$token])) {
            $orderIncrementId = $this->orderIncrementIdTokenManagement->getOrderIncrementIdByToken($token);
            // TODO Use QuoteRepository instead of quote::load
            $this->cartByToken[$token] = $this->quoteFactory->create()->load($orderIncrementId, 'reserved_order_id');
        }
        return $this->cartByToken[$token];
    }


    public function saveOrderIdOnToken($token, $orderId)
    {
        $this->orderIncrementIdTokenManagement->saveOrderIdOnToken($token, $orderId);
    }


    /**
     * Retrieve cart product collection with payline_category_mapping
     *
     *
     * @param $cartId
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductCollectionFromCart($cartId)
    {
        $cart = $this->cartRepository->getActive($cartId);

        $productIds = [];
        $categoryIds = [];
        $productCollection = $this->productCollectionFactory->create();
        $categoryCollection = $this->categoryCollectionFactory->create();

        foreach ($cart->getItems() as $item) {
            $productIds[] = $item->getProductId();
        }

        $productCollection
            ->addAttributeToFilter('entity_id', ['in' => $productIds])
            ->addAttributeToSelect('*')
            ->addCategoryIds();

        foreach ($productCollection as $product) {
            $categoryIds = array_merge($categoryIds, $product->getCategoryIds());
        }

        $categoryCollection
            ->addAttributeToFilter('entity_id', ['in' => $categoryIds])
            ->addAttributeToSelect(['name', 'payline_category_mapping', 'level'])
            ->addAttributeToFilter('payline_category_mapping', ['gt'=>0]);

        foreach ($productCollection as $product) {
            $categoryCandidate = null;

            foreach ($product->getCategoryIds() as $categoryId) {
                $tmpCategory = $categoryCollection->getItemById($categoryId);
                if (!$tmpCategory) {
                    continue;
                }

                if (!$categoryCandidate || $tmpCategory->getLevel() > $categoryCandidate->getLevel()) {
                    $categoryCandidate = $tmpCategory;
                }
            }

            if($categoryCandidate && $categoryCandidate->getPaylineCategoryMapping()) {
                $product->setPaylineCategoryMapping($categoryCandidate->getPaylineCategoryMapping());
            } else {
                $product->setPaylineCategoryMapping($this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_DEFAULT_CATEGORY,
                    ScopeInterface::SCOPE_STORE));
            }
        }

        return $productCollection;
    }
}
