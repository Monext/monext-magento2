<?php

namespace Monext\Payline\PaylineApi\Request\DoWebPaymentType;

use Monext\Payline\Helper\Constants;

class Rec extends AbstractDoWebPaymentType
{
    const PAYMENT_METHOD = Constants::WEB_PAYMENT_REC;

    /**
     * @param array $data
     * @return array
     * @throws \Exception
     * @see https://docs.payline.com/display/DT/Paiement+n+fois
     * @see https://docs.monext.fr/display/DT/Object+-+recurring
     */
    public function getData(&$data)
    {
        $usedContracts = $this->contractManagement->getUsedContracts();
        $data['payment']['contractNumber'] = $usedContracts->getFirstItem()->getNumber();
        $data['contracts'] = $usedContracts->getColumnValues('number');
        $this->addConfigData($data);
        $this->prepareUrls($data);
        return $data;
    }

    protected function prepareUrls(&$data)
    {
        $data['returnURL'] = $this->urlBuilder->getUrl('payline/webpayment/returnfrompaymentgateway');
        $data['cancelURL'] = $this->urlBuilder->getUrl('payline/webpayment/returnfrompaymentgateway');
        $data['notificationURL'] = $this->urlBuilder->getUrl('payline/webpayment/notifycyclingpaymentfrompaymentgateway');
    }

    /**
     * @param $data
     * @return void
     * @throws \DateMalformedStringException
     */
    protected function addConfigData(&$data)
    {
        $startCycle = $this->paylineHelper->getRecStartCycle();
        $billingDay = $this->paylineHelper->getRecBillingDay();
        $billingNumber = $this->paylineHelper->getRecBillingNumber();
        $billingCycle = $this->paylineHelper->getRecBillingCycle();
        $intervalMapping = $this->paylineHelper->getIntervalMapping();

        if (!isset($intervalMapping[$billingCycle])) {
            throw new \Exception(sprintf('Invalid billing cycle: %s', $billingCycle));
        }

        $recurringStartDate = new \DateTime();

        //Static day only for monthly recurring
        if($billingDay > 0 && $billingCycle >= 40) {
            $targetDate = \DateTime::createFromFormat('d/m/Y', date($billingDay.'/m/Y'));
            if ($targetDate < $recurringStartDate) {
                $recurringStartDate = $targetDate->modify('+1 month');
            }else{
                $recurringStartDate = $targetDate;
            }
        }

        if($startCycle > 0) {
            $recurringStartDate->modify(sprintf('+ %d %s', $startCycle * $intervalMapping[$billingCycle]['multiplier'], $intervalMapping[$billingCycle]['unit']));
        }

        // Calculate end date if there are multiple billing cycles
        if ($billingNumber > 0) {
            $endDateTime = clone $recurringStartDate;
            $endDateTime->modify(sprintf('+ %d %s', ($billingNumber -1) * $intervalMapping[$billingCycle]['multiplier'], $intervalMapping[$billingCycle]['unit']));
            //Only add to force Monext to set last cycle in schedule - Maybe to delete in future
            $endDateTime->modify('+1 day');

            $data['recurring']['endDate'] = $endDateTime->format('d/m/Y');
        }

        $data['recurring']['firstAmount'] = $data['payment']['amount'];
        $data['recurring']['amount'] = $data['payment']['amount'];
        $data['recurring']['billingCycle'] = $billingCycle;
        $data['recurring']['billingDay'] = ($billingDay && $billingCycle >= 40) ? sprintf('%02d', $billingDay) : date('d');
        $data['recurring']['startDate'] = $recurringStartDate->format('d/m/Y');
    }
}
