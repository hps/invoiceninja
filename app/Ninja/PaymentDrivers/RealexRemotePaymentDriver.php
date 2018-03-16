<?php
namespace App\Ninja\PaymentDrivers;

use Exception;
use Session;
use App\Models\GatewayType;

class RealexRemotePaymentDriver extends BasePaymentDriver
{

    protected $customerReferenceParam = 'customerReference';
    protected $sourceReferenceParam = 'paymentMethodReference';
    public $canRefundPayments = true;

    public function gatewayTypes()
    {
        $types = [
            GATEWAY_TYPE_REALEX,
            GATEWAY_TYPE_CREDIT_CARD
        ];
              
        return $types;
    }

    protected function paymentDetails($paymentMethod = false)
    {
        $data = parent::paymentDetails($paymentMethod);

        $data['transactionId'] = uniqid() . '-' . $data['transactionId'];
        
        if (!$this->isGatewayType(GATEWAY_TYPE_REALEX)) {
            return $data;
        }

        $country = ($this->client() && $this->client()->country) ?
            $this->client()->country->iso_3166_2 :
            ($this->account()->country ? $this->account()->country->iso_3166_2 : false);

        $data['hppCustomerCountry'] = $country;
        $data['hppCustomerFirstName'] = $data['card']->getBillingFirstName();
        $data['hppCustomerLastName'] = $data['card']->getBillingLastName();
        $data['merchantResponseUrl'] = $data['returnUrl'];
        $data['hppTxstatusUrl'] = $data['returnUrl'];
        $data['comment1'] = 'Invoice Id: ' . $data['transactionId'];

        //echo '<pre>';print_r($this->accountGateway->getConfig());die;

        return $data;
    }
    
    public function startPurchase($input = false, $sourceId = false){
        if ($this->isGatewayType(GATEWAY_TYPE_REALEX)) {
            $this->accountGateway->gateway->provider = 'Realex_Hosted';
            $this->accountGateway->gateway->is_offsite = 1;
        }
        return parent::startPurchase($input, $sourceId);
    }

    protected function creatingPayment($payment, $paymentMethod)
    {
        if ($this->isGatewayType(GATEWAY_TYPE_REALEX)) {
            $payment->payer_id = $this->input['guid'];
        }

        return $payment;
    }

    public function completeOffsitePurchase($input)
    {
        if ($this->isGatewayType(GATEWAY_TYPE_REALEX)) {
            if (!empty($input['guid'])) {
                return $this->createPayment($input['guid']);
            } else {
                throw new Exception('Invalid Realex transaction');
            }
        }
        parent::completeOffsitePurchase($input);
    }
}
