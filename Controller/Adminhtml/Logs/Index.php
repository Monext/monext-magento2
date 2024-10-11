<?php

namespace Monext\Payline\Controller\Adminhtml\Logs;

use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action implements HttpGetActionInterface
{
    protected PageFactory $pageFactory;

    public function __construct(Context     $context,
                                PageFactory $pageFactory,
    )
    {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $resultPage = $this->pageFactory->create();
        $resultPage->setActiveMenu('Monext_Payline::payline_logs');
        $resultPage->getConfig()->getTitle()->prepend(__('Payment Logs'));

        return $resultPage;
    }
}
