<?php
namespace Monext\Payline\Block\Checkout;


use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;
use Monext\Payline\Helper\Constants as PaylineConstants;



class GatewayError extends Template
{

    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template = 'Monext_Payline::gateway/error.phtml';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(Template\Context     $context,
                                ScopeConfigInterface $scopeConfig,
                                array                $data = [])
    {
        parent::__construct($context, $data);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get reorder URL
     *
     * @param object $order
     * @return string
     */
    public function getRedirectUrl()
    {
        //return $this->getUrl('sales/order/reorder', ['order_id' => $order->getId()]);
        if($this->scopeConfig->getValue(PaylineConstants::CONFIG_PATH_PAYLINE_RETURN_REFUSED, ScopeInterface::SCOPE_STORE) == PaylineConstants::PAYLINE_RETURN_HISTORY_ORDERS) {
            return $this->getUrl('sales/order/history');
        }
        return $this->getUrl('checkout/cart');
    }
}
