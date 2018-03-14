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

    protected function paymentDetails($paymentMethod = false)
    {
        $data = parent::paymentDetails($paymentMethod);

        $country = ($this->client() && $this->client()->country) ?
            $this->client()->country->iso_3166_2 :
            ($this->account()->country ? $this->account()->country->iso_3166_2 : false);

        $data['transactionId'] = uniqid() . '-' . $data['transactionId'];
        $data['hppCustomerCountry'] = $country;
        $data['hppCustomerFirstName'] = $data['card']->getBillingFirstName();
        $data['hppCustomerLastName'] = $data['card']->getBillingLastName();
        $data['merchantResponseUrl'] = $data['returnUrl'];
        $data['hppTxstatusUrl'] = $data['returnUrl'];
        $data['hppVersion'] = 2;
        $data['testMode'] = true;

        return $data;
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
}
