<?php

namespace Monext\Payline\Controller\WebPayment;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\CartFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Monext\Payline\Model\PaymentManagement as PaylinePaymentManagement;
use Monext\Payline\Model\OrderManagement as PaylineOrderManagement;
use Monext\Payline\Helper\Constants as PaylineConstants;


class CancelFromPaymentGateway extends ReturnFromPaymentGateway
{
    /**
     * @var PaylineOrderManagement
     */
    protected $paylineOrderManagement;

    /**
     * @var CartFactory
     */
    protected $cartFactory;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param Context $context
     * @param \Psr\Log\LoggerInterface $loggerPayline
     * @param PaylinePaymentManagement $paylinePaymentManagement
     * @param PaylineOrderManagement $paylineOrderManagement
     * @param CartFactory $cartFactory
     */
    public function __construct(Context                  $context,
                                \Psr\Log\LoggerInterface $loggerPayline,
                                PaylinePaymentManagement $paylinePaymentManagement,
                                ScopeConfigInterface     $scopeConfig,
                                CartFactory              $cartFactory,
                                PaylineOrderManagement   $paylineOrderManagement

    )
    {
        parent::__construct($context, $loggerPayline, $paylinePaymentManagement);
        $this->scopeConfig = $scopeConfig;
        $this->cartFactory = $cartFactory;
        $this->paylineOrderManagement = $paylineOrderManagement;
    }


    /**
     * @param $success
     * @return \Magento\Framework\Controller\Result\Raw|\Magento\Framework\Controller\Result\Redirect
     */
    protected function getRedirect($success)
    {
        $returnRefused = $this->scopeConfig->getValue(PaylineConstants::CONFIG_PATH_PAYLINE_RETURN_REFUSED, ScopeInterface::SCOPE_STORE);
        if(!$success) {
            $token = $this->getToken();
            if($token && $returnRefused==PaylineConstants::PAYLINE_RETURN_CART_FULL) {
                $order = $this->paylineOrderManagement->getOrderByToken($token);
                if($order->getId() && $order->canReorder()) {
                    $cart = $this->cartFactory->create();
                    $items = $order->getItemsCollection();
                    $canSaveCart = true;
                    foreach ($items as $item) {
                        try {
                            $cart->addOrderItem($item);
                        } catch (\Magento\Framework\Exception\LocalizedException $e) {
                            $this->messageManager->addErrorMessage($e->getMessage());
                            $canSaveCart = false;
                        } catch (\Exception $e) {
                            $this->messageManager->addExceptionMessage(
                                $e,
                                __('We can\'t add this item to your shopping cart right now.')
                            );
                            $canSaveCart = false;
                        }
                    }

                    if($canSaveCart) {
                        $cart->save();
                    }
                }
            }
        }

        return parent::getRedirect($success);
    }


}
