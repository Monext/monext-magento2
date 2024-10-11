<?php

namespace Monext\Payline\Controller\Adminhtml\Logs;

use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Monext\Payline\Helper\Constants as HelperConstants;

class Load extends Action
{
    protected RawFactory $rawFactory;
    protected DirectoryList $directoryList;

    public function __construct(Context     $context,
                                RawFactory $rawFactory,
                                DirectoryList $directoryList
    )
    {
        parent::__construct($context);
        $this->rawFactory = $rawFactory;
        $this->directoryList = $directoryList;
    }

    public function execute()
    {
        $filePath = $this->getFilePath();
        $lengthBefore = 50000;

        try {
            $handle = fopen($filePath, 'r');
            fseek($handle, -$lengthBefore, SEEK_END);

            if ($handle && filesize($filePath) > 0) {
                $contents = fread($handle, filesize($filePath));
                if ($contents === false) {
                    return $this->getErrorMessage($filePath);
                }
                fclose($handle);

                $resultRaw = $this->rawFactory->create();
                return $resultRaw->setContents('... <br/>'. nl2br($contents));
            }

        } catch (\Exception $e) {
            return $this->getErrorMessage($filePath);
        }
        return $this->getErrorMessage($filePath);
    }



    /**
     * @return string
     * @throws FileSystemException
     */
    public function getFilePath()
    {
        return $this->directoryList->getPath(DirectoryList::LOG) . DIRECTORY_SEPARATOR . HelperConstants::PAYLINE_LOG_FILENAME;
    }

    /**
     * @param $filePath
     * @return string
     */
    protected function getErrorMessage($filePath)
    {
        return "Log file is not readable or does not exist at this moment. File path is " . $filePath;
    }
}
