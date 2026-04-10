<?php

namespace Monext\Payline\Plugin\Data\Form\Element;

use Magento\Framework\Data\Form\Element\Obscure;
use Magento\Framework\Data\Form\Element\Password;
use Magento\Framework\Encryption\EncryptorInterface;
use Monext\Payline\Helper\Constants;

class ObscurePlugin
{

    /** @var EncryptorInterface  */
    private EncryptorInterface $encryptor;

    /** @var \Monext\Payline\Helper\Data  */
    private \Monext\Payline\Helper\Data $helper;

    /**
     * @param EncryptorInterface $encryptor
     * @param \Monext\Payline\Helper\Data $helper
     */
    public function __construct(EncryptorInterface $encryptor,
                                \Monext\Payline\Helper\Data $helper)
    {
        $this->encryptor = $encryptor;
        $this->helper = $helper;
    }

    /**
     * @param Obscure $subject
     * @param $result
     * @return mixed
     * @throws \Exception
     */
    public function afterGetEscapedValue(Obscure $subject, $result)
    {
        if (!$this->isPaylineField($subject->getName()) || empty($result)) {
            return $result;
        }

        $value = $subject->getValue();
        $decrypted = $this->encryptor->decrypt($value);
        if (empty($decrypted)) {
            return $result;
        }

        $subject->setType('text');
        return  $this->helper->maskAccessKey($decrypted);
    }

    protected function isPaylineField(string $name): bool
    {
        return preg_match('/^groups\[payline\]/', $name);
    }
}
