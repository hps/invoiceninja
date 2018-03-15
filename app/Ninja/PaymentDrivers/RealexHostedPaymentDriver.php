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
            GATEWAY_TYPE_REALEX,
            //GATEWAY_TYPE_CREDIT_CARD
        ];
        //$this->accountGateway->gateway->provider = strtolower('Realex_Remote');
        return $types;
    }
    
    
    protected function paymentDetails($paymentMethod = false)
    {        
        $data = parent::paymentDetails($paymentMethod);
        
        if (!$this->isGatewayType(GATEWAY_TYPE_REALEX)) {
            return $data;
        }

        $country = ($this->client() && $this->client()->country) ?
            $this->client()->country->iso_3166_2 :
            ($this->account()->country ? $this->account()->country->iso_3166_2 : false);

        $data['transactionId'] = uniqid() . '-' . $data['transactionId'];
        $data['hppCustomerCountry'] = $country;
        $data['hppCustomerFirstName'] = $data['card']->getBillingFirstName();
        $data['hppCustomerLastName'] = $data['card']->getBillingLastName();
        $data['merchantResponseUrl'] = $data['returnUrl'];
        $data['hppTxstatusUrl'] = $data['returnUrl'];
        $data['comment1'] = 'Invoice Id: ' . $data['transactionId'];
        
        //echo '<pre>';print_r($this->accountGateway->getConfig());die;

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
