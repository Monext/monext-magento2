<?php

namespace Monext\Payline\Model\Category\Attribute\Source;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\File\Csv;
use Monext\Payline\Helper\Constants as HelperConstants;
use Monext\Payline\Helper\Data as PaylineHelper;

/**
 * This class serve to map Magento Categories to Payline Categories : https://docs.payline.com/display/DT/Codes+-+Category
 */
class CategoryMapping extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var \Magento\Framework\Component\ComponentRegistrar
     */
    protected $componentRegistrar;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvReader;
    private $helperPayline;

    public function __construct(
        ComponentRegistrar $componentRegistrar,
        Csv $csvReader,
        PaylineHelper $helperPayline
    ) {
        $this->componentRegistrar = $componentRegistrar;
        $this->csvReader = $csvReader;
        $this->helperPayline = $helperPayline;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [];
            $rows = $this->helperPayline->getDefaultCategories();
            foreach ($rows as $row) {
                $this->_options[] = ['value' => $row['value'], 'label' => __($row['name'])];
            }

            array_unshift($this->_options, ['value' => '', 'label' => __('Please select a category mapping...')]);
        }
        return $this->_options;
    }
}
