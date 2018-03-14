<?php
namespace App\Ninja\PaymentDrivers;

use Exception;
use Session;
use App\Models\GatewayType;

class RealexHostedPaymentDriver extends BasePaymentDriver
{

    protected $customerReferenceParam = 'customerReference';
    protected $sourceReferenceParam = 'paymentMethodReference';
    public $canRefundPayments = true;

    public function gatewayTypes()
    {
        $types = [
            GATEWAY_TYPE_REALEX
        ];

        return $types;
    }

    protected function creatingPayment($payment, $paymentMethod)
    {
        $payment->payer_id = $this->input['guid'];

        return $payment;
    }

    public function completeOffsitePurchase($input)
    {
        if (!empty($input['guid'])) {
            return $this->createPayment($input['guid']);
        } else {
            throw new Exception('Invalid Realex transaction');
        }
    }

    protected function paymentDetails($paymentMethod = false)
    {
        $data = parent::paymentDetails($paymentMethod);

        echo '<pre> in realex';

        $data['hppCustomerCountry'] = 'US';
        $data['hppCustomerFirstName'] = 'James';
        $data['hppCustomerLastName'] = 'Mason';
        $data['merchantResponseUrl'] = $data['returnUrl'];
        $data['hppTxstatusUrl'] = $data['cancelUrl'];
        $data['hppVersion'] = 2;
        $data['comment1'] = 2;
        $data['comment2'] = 2;
        $data['testMode'] = true;

        return $data;
    }
}
