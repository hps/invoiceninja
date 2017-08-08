<?php

namespace App\Ninja\PaymentDrivers;

use Exception;
use Session;
use App\Models\GatewayType;

class HeartlandPaymentDriver extends BasePaymentDriver
{
    protected $customerReferenceParam = 'customerId';
    protected $sourceReferenceParam = 'paymentMethodToken';
    public $canRefundPayments = true;

    public function gatewayTypes()
    {
        $types = [
            GATEWAY_TYPE_CREDIT_CARD,
            GATEWAY_TYPE_TOKEN,
        ];

        return $types;
    }

    public function tokenize()
    {
        return true;
    }
}
