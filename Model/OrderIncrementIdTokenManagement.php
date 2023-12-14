<?php

namespace Monext\Payline\Model;

use Magento\Quote\Api\Data\CartInterface;
use Monext\Payline\Api\Data\OrderIncrementIdTokenInterface;

use Monext\Payline\Helper\Constants as HelperConstants;
use Psr\Log\LoggerInterface as Logger;

class OrderIncrementIdTokenManagement implements \Monext\Payline\Api\TokenManagementInterface
{
    /** @var OrderIncrementIdTokenFactory */
    private $orderIncrementIdTokenFactory;

    /** @var ResourceModel\OrderIncrementIdToken\CollectionFactory */
    private $orderIncrementIdTokenCollectionFactory;

    /** @var Monolog\Logger */
    private $paylineLogger;
    /**
     * @var \Monext\Payline\Helper\Data
     */
    private $helperData;

    /**
     * @var array
     */
    private $orderTokenCollectinByIncrementId = [];

    /**
     * @param ResourceModel\OrderIncrementIdToken\CollectionFactory $orderIncrementIdTokenCollectionFactory
     * @param OrderIncrementIdTokenFactory $orderIncrementIdTokenFactory
     * @param \Monext\Payline\Helper\Data $helperData
     * @param Logger $paylineLogger
     */
    public function __construct(\Monext\Payline\Model\ResourceModel\OrderIncrementIdToken\CollectionFactory $orderIncrementIdTokenCollectionFactory,
                                \Monext\Payline\Model\OrderIncrementIdTokenFactory                          $orderIncrementIdTokenFactory,
                                \Monext\Payline\Helper\Data                                                 $helperData,
                                Logger                                                                      $paylineLogger)
    {
        $this->orderIncrementIdTokenCollectionFactory = $orderIncrementIdTokenCollectionFactory;
        $this->orderIncrementIdTokenFactory = $orderIncrementIdTokenFactory;
        $this->paylineLogger = $paylineLogger;
        $this->helperData = $helperData;
    }

    /**
     * @param  $orderIncrementId
     * @return \Monext\Payline\Model\ResourceModel\OrderIncrementIdToken\Collection
     */
    protected function getTokenCollectionFromIncrementId($orderIncrementId)
    {

        if (!isset($this->orderTokenCollectinByIncrementId[$orderIncrementId])) {
            $this->orderTokenCollectinByIncrementId[$orderIncrementId] = $this->orderIncrementIdTokenCollectionFactory->create()
                ->addFieldToFilter('order_increment_id', $orderIncrementId);
        }

        return $this->orderTokenCollectinByIncrementId[$orderIncrementId];
    }


    /**
     * @param  $token
     * @return \Monext\Payline\Model\ResourceModel\OrderIncrementIdToken\Collection
     */
    protected function getTokenCollectionFromToken($token)
    {
        return $this->orderIncrementIdTokenCollectionFactory->create()
            ->addFieldToFilter('token', $token);
    }

    /**
     * @param $token
     * @return false
     */
    protected function getIncrementIdToken($token)
    {
        /** @var \Monext\Payline\Model\OrderIncrementIdToken $token */
        $orderToken = $this->orderIncrementIdTokenFactory->create();
        $orderToken->load($token, 'token');

        if (!$orderToken->getId()) {
            $this->paylineLogger->error(__METHOD__ . ", Cannot retrieve token: " . $token);
        }

        return $orderToken;
    }

    /**
     * Called after doWebpayment to save token in database
     *
     * renamed from associateTokenToOrderIncrementId
     *
     * @param CartInterface $cart
     * @param $token
     * @return $this
     */
    public function associateTokenToCart($cart, $token, $cartSha = '')
    {
        $orderIncrementId = $cart->getReservedOrderId();
        $orderTokenCollection = $this->getTokenCollectionFromIncrementId($orderIncrementId);

        $tokenId = null;
        $newTokenState = OrderIncrementIdTokenInterface::TOKEN_STATUS_NEW;
        if ($this->helperData->getTokenUsage() == HelperConstants::TOKEN_USAGE_ONCE) {
            $orderToken = $orderTokenCollection->getFirstItem();
            if ($orderToken && $orderToken->getId()) {
                $tokenId = $orderToken->getId();
            }
        } else {
            $tokenNbr = 0;
            foreach ($orderTokenCollection as $orderToken) {
                if ($orderToken->getState() != OrderIncrementIdTokenInterface::TOKEN_STATUS_DISABLED) {
                    $orderToken->setState(OrderIncrementIdTokenInterface::TOKEN_STATUS_DISABLED)->save();
                }
                $tokenNbr++;
            }

            if ($tokenNbr > 0) {
                $newTokenState = OrderIncrementIdTokenInterface::TOKEN_STATUS_DUPLICATE;
            }
        }

        // Create or update token
        $orderToken = $this->orderIncrementIdTokenFactory->create();
        return $orderToken->setOrderIncrementId($orderIncrementId)
            ->setId($tokenId)
            ->setToken($token)
            ->setState($newTokenState)
            ->setSha($cartSha)
            ->save();
    }

    /**
     *
     * @param $token
     * @return null|string
     */
    public function getOrderIncrementIdByToken($token)
    {
        $orderToken = $this->getTokenCollectionFromToken($token)->getFirstItem();

        if (empty($orderToken) || !$orderToken->getId()) {
            return null;
        }

        return $orderToken->getOrderIncrementId();
    }

    /**
     * Used to managed operation on existing order
     * - Capture
     * - Void
     * - Refund
     *
     * @param $orderIncrementId
     * @return string|null
     */
    public function getTokenByOrderIncrementId($orderIncrementId)
    {
        $orderTokenCollection = $this->getTokenCollectionFromIncrementId($orderIncrementId);

        $token = null;
        foreach ($orderTokenCollection as $orderToken) {
            if(in_array($orderToken->getState(), [OrderIncrementIdTokenInterface::TOKEN_STATUS_DISABLED])) {
               continue;
            }

            $token = $orderToken->getToken();
            if ($orderToken->getOrderEntityId()) {
                break;
            }
        }

        return $token;
    }


    /**
     *
     * @param $orderIncrementId
     * @param $cartSha
     *
     * @return false|mixed
     */
    public function getExistingTokenByOrderIncrementId($orderIncrementId, $cartSha)
    {
        if (empty($cartSha)) {
            return false;
        }
        $orderTokenCollection = $this->getTokenCollectionFromIncrementId($orderIncrementId);

        foreach ($orderTokenCollection as $orderToken) {
            if ($orderToken->getSha() == $cartSha
                && $orderToken->getState() != OrderIncrementIdTokenInterface::TOKEN_STATUS_DISABLED
                && !$orderToken->expireSoon()
            ) {
                return $orderToken->setState(OrderIncrementIdTokenInterface::TOKEN_STATUS_RECYCLE)->save();
            }
        }
        return false;
    }


    /**
     * @param $token
     * @param $orderId
     * @return false
     */
    public function saveOrderIdOnToken($token, $orderId = 0)
    {
        /** @var \Monext\Payline\Model\OrderIncrementIdToken $token */
        $orderToken = $this->orderIncrementIdTokenFactory->create();
        $orderToken->load($token, 'token');

        if (!$orderToken->getId()) {
            $this->paylineLogger->error(__METHOD__ . ", Cannot retrieve token: " . $token);
            return false;
        }

        return $orderToken->setOrderEntityId($orderId)->save();
    }

    /**
     * @param $token
     * @return bool
     */
    protected function flagTokenState($token, $state)
    {
        /** @var \Monext\Payline\Model\OrderIncrementIdToken $orderToken */
        $orderToken = $this->getIncrementIdToken($token);

        if ($orderToken->getId()) {
            return false;
        }

        if ($orderToken->getState() != $state) {
            $orderToken->setState($state)->save();
        }

        return true;
    }

    /**
     * @param $token
     * @return bool
     */
    public function flagTokenAsDisabled($token)
    {
        return $this->flagTokenState($token, OrderIncrementIdTokenInterface::TOKEN_STATUS_DISABLED);
    }

}
