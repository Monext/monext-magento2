<?php
namespace Monext\Payline\Api\Data;

/**
 * @property setUsed()
 */
interface OrderIncrementIdTokenInterface
{
    const TOKEN_STATUS_NEW = 0;

    const TOKEN_STATUS_DUPLICATE = 4;

    const TOKEN_STATUS_RECYCLE = 5;

    const TOKEN_STATUS_DISABLED = 8;

    public function expireSoon();
}