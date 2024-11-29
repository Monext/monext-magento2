<?php

namespace Monext\Payline\Controller\Adminhtml\Logs;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\FileSystemException;
use Monext\Payline\Helper\Constants as HelperConstants;

class Download extends Action
{
    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var RawFactory
     */
    protected $rawFactory;

    /**
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param DirectoryList $directoryList
     * @param RawFactory $rawFactory
     */
    public function __construct(Context     $context,
                                FileFactory $fileFactory,
                                DirectoryList $directoryList,
                                RawFactory $rawFactory
    )
    {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->directoryList = $directoryList;
        $this->rawFactory = $rawFactory;
    }

    /**
     * @return ResponseInterface
     * @throws Exception
     * @throws FileSystemException
     */
    public function execute()
    {
        $filePath = $this->directoryList->getPath(DirectoryList::LOG) . DIRECTORY_SEPARATOR . HelperConstants::PAYLINE_LOG_FILENAME;

        $handle = fopen($filePath, "r");
        $contents = fread($handle, filesize($filePath));
        fclose($handle);

        return $this->fileFactory->create(
            HelperConstants::PAYLINE_LOG_FILENAME,
            $contents,
            DirectoryList::LOG
        );
    }
}
