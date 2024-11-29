<?php

namespace Monext\Payline\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Monext\Payline\Helper\Constants as HelperConstants;

class Logs extends Template
{

    /**
     * @var string
     */
    protected $_template = 'Monext_Payline::payline_logs.phtml';

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @param Context $context
     * @param DirectoryList $directoryList
     * @param array $data
     */
    public function __construct(
        Context  $context,
        DirectoryList $directoryList,
        array         $data = []
    )
    {
        parent::__construct($context, $data);
        $this->directoryList = $directoryList;
    }

    /**
     * @return array
     * @throws FileSystemException
     */
    public function getFileContent()
    {
        $result = null;
        $filePath = $this->getFilePath();
        $lengthBefore = 100000;
        
        try {
            $handle = fopen($filePath, 'r');
            fseek($handle, -$lengthBefore, SEEK_END);
            if (!$handle) {
                return $this->getErrorMessage($filePath);
            }

            if (filesize($filePath) > 0) {
                $contents = fread($handle, filesize($filePath));
                if ($contents === false) {
                    return $this->getErrorMessage($filePath);
                }
                fclose($handle);
                $result['content'] = '... ' . PHP_EOL . $this->_escaper->escapeHtml($contents);
            }
        } catch (\Exception $e) {
            return $this->getErrorMessage($filePath);
        }


        return $result;
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
     * @return array
     */
    protected function getErrorMessage($filePath)
    {
        $result['error'] = "Log file is not readable or does not exist at this moment. File path is " . $filePath;
        return $result;
    }

}
