<?php

namespace Monext\Payline\Controller\WebPayment;

use Magento\Framework\Controller\ResultFactory;
use Monext\Payline\Controller\Action;

class ReturnFromWalletGateway extends Action
{
    public function execute()
    {
        // TODO This is not supposed to be called. Wallet is handle by widget
        $resultRaw = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $resultRaw->setContents('OK');
        return $resultRaw;
    }
}
