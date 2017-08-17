<?php

namespace App\Ninja\PaymentDrivers;

use Exception;
use Session;
use App\Models\GatewayType;

class HeartlandPaymentDriver extends BasePaymentDriver
{
    protected $customerReferenceParam = 'customerReference';
    protected $sourceReferenceParam = 'paymentMethodReference';
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

    protected function paymentDetails($paymentMethod = false)
    {
        $data = parent::paymentDetails($paymentMethod);

        if (! $paymentMethod && ! empty($this->input['sourceToken'])) {
            $data['token'] = $this->input['sourceToken'];
        }

        return $data;
    }

    protected function checkCustomerExists($customer)
    {
        $response = $this->gateway()
            ->fetchCustomer(['customerReference' => $customer->token])
            ->send();

        return $response->isSuccessful();
    }

    public function createToken()
    {
        if ($customer = $this->customer() && $this->customer()->token) {
            $customerReference = $customer->token;
        } else {
            $data = $this->paymentDetails();
            $tokenResponse = $this->gateway()->createCustomer([
                'firstName' => array_get($this->input, 'first_name') ?: $this->contact()->first_name,
                'lastName' => array_get($this->input, 'last_name') ?: $this->contact()->last_name,
                'company' => $this->client()->name,
                'country' => $this->client()->country->iso_3166_3,
                'primaryEmail' => $this->contact()->email,
                'phoneDay' => $this->contact()->phone,
            ])->send();
            if ($tokenResponse->isSuccessful()) {
                $customerReference = $tokenResponse->getCustomerReference();
            } else {
                return false;
            }
        }

        if ($customerReference) {
            $request = array(
                'customerReference' => $customerReference,
                'nameOnAccount' => $data['card']->getBillingFirstName() . ' ' . $data['card']->getBillingLastName(),
                'addressLine1' => $data['card']->getBillingAddress1(),
                'addressLine2' => $data['card']->getBillingAddress2(),
                'city' => $data['card']->getBillingCity(),
                'stateProvince' => $data['card']->getBillingState(),
                'zipPostalCode' => $data['card']->getBillingPostcode(),
                'country' => \App\Models\Country::where('iso_3166_2', '=', $data['card']->getBillingCountry())->firstOrFail()->iso_3166_3,
            );

            if ($this->isGatewayType(GATEWAY_TYPE_CREDIT_CARD)) {
                $request['paymentToken'] = $this->input['sourceToken'];
            }

            $tokenResponse = $this->gateway->createPaymentMethod($request)->send();
            if ($tokenResponse->isSuccessful()) {
                $this->tokenResponse = $tokenResponse;
            } else {
                return false;
            }
        }

        return parent::createToken();
    }

    public function creatingCustomer($customer)
    {
        $customer->token = $this->tokenResponse->getCustomerReference();

        return $customer;
    }

    protected function creatingPaymentMethod($paymentMethod)
    {
        $response = $this->tokenResponse;

        $paymentMethod->source_reference = $response->getPaymentMethodReference();

        if ($this->isGatewayType(GATEWAY_TYPE_CREDIT_CARD)) {
            $paymentMethod->payment_type_id = $this->parseCardType($response->getData()['cardBrand']);
            $paymentMethod->last4 = $response->getData()['accountNumberLast4'];
            $paymentMethod->expiration = sprintf(
                '20%d-%d-01',
                substr($response->getData()['expirationDate'], 2, 2),
                substr($response->getData()['expirationDate'], 0, 2)
            );
        } else {
            return null;
        }

        return $paymentMethod;
    }

    public function removePaymentMethod($paymentMethod)
    {
        parent::removePaymentMethod($paymentMethod);

        $response = $this->gateway()->deletePaymentMethod([
            'paymentMethodReference' => $paymentMethod->source_reference,
        ])->send();

        if ($response->isSuccessful()) {
            return true;
        } else {
            throw new Exception($response->getMessage());
        }
    }
}
