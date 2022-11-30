<?php

namespace Monext\Payline\Api;

interface TokenManagementInterface
{
    public function associateTokenToCart($cart, $token, $cartSha='');

    public function getOrderIncrementIdByToken($token);

    public function getTokenByOrderIncrementId($orderIncrementId);

    public function getExistingTokenByOrderIncrementId($orderIncrementId, $cartSha);

    public function saveOrderIdOnToken($token, $orderId=0);

    public function flagTokenAsDisabled($token);
}