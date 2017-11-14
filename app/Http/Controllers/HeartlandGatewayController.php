<?php
namespace App\Http\Controllers;

use App\Events\InvoiceInvitationWasViewed;
use App\Events\QuoteInvitationWasViewed;
use App\Models\Contact;
use App\Models\Document;
use App\Models\Gateway;
use App\Models\Invitation;
use App\Models\PaymentMethod;
use App\Ninja\Repositories\ActivityRepository;
use App\Ninja\Repositories\CreditRepository;
use App\Ninja\Repositories\DocumentRepository;
use App\Ninja\Repositories\InvoiceRepository;
use App\Ninja\Repositories\PaymentRepository;
use App\Services\PaymentService;
use Auth;
use Barracuda\ArchiveStream\ZipArchive;
use Cache;
use Datatable;
use Exception;
use Input;
use Redirect;
use Request;
use Response;
use Session;
use URL;
use Utils;
use Validator;
use View;
use stdClass;

class HeartlandGatewayController extends BaseController
{

    private $invoiceRepo;
    private $paymentRepo;
    private $documentRepo;

    public function __construct(InvoiceRepository $invoiceRepo, PaymentRepository $paymentRepo, ActivityRepository $activityRepo, DocumentRepository $documentRepo, PaymentService $paymentService, CreditRepository $creditRepo)
    {
        $this->invoiceRepo = $invoiceRepo;
        $this->paymentRepo = $paymentRepo;
        $this->activityRepo = $activityRepo;
        $this->documentRepo = $documentRepo;
        $this->paymentService = $paymentService;
        $this->creditRepo = $creditRepo;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function createPaypalSession($invitationKey)
    {
        $invoiceDetails = $this->getInvoiceDetails($invitationKey);

        $data = $invoiceDetails->paymentDriver->createPaypalSession(
            $invoiceDetails->payment, $invoiceDetails->buyer, $invoiceDetails->lineItems, $invoiceDetails->shippingDetails
        );
        
        return Response::json($data);
    }

    public function paypalSessionSale($invitationKey)
    {
        $queryString = Request::getQueryString();
        parse_str($queryString, $parsedData);
        if (!empty($parsedData['PayerID']) && !empty($parsedData['token'])) {
            $invoiceDetails = $this->getInvoiceDetails($invitationKey, true, $parsedData);

            $data = $invoiceDetails->paymentDriver->paypalSessionSale(
                $parsedData['token'], $invoiceDetails->payment, $invoiceDetails->buyer, $invoiceDetails->lineItems, $invoiceDetails->shippingDetails
            );
            
            return Response::json($data);
        } else {
            return response()->view('error', [
                    'error' => trans('texts.invoice_not_found'),
                    'hideHeader' => true,
            ]);
        }
    }

    private function getInvoiceDetails($invitationKey, $isSale = false, $paypalDetails = array())
    {
        if (!$invitation = $this->invoiceRepo->findInvoiceByInvitation($invitationKey)) {
            return response()->view('error', [
                    'error' => trans('texts.invoice_not_found'),
                    'hideHeader' => true,
            ]);
        }

        $invoice = $invitation->invoice;
        $client = $invoice->client;
        $account = $invoice->account;

        if ($isSale === true) {
            //paypal sale
            $buyer = array(
                'returnUrl' => URL::to('/heartland/paypal_session_sale/' . $invitationKey),
                'cancelUrl' => URL::to('/heartland/paypal_session_sale/' . $invitationKey),
                'payerId' => $paypalDetails['PayerID']
            );
        } else {
            // createPaypalSession        
            $buyer = array(
                'returnUrl' => URL::to('/heartland/paypal_session_sale/' . $invitationKey),
                'cancelUrl' => URL::to('/heartland/paypal_session_sale/' . $invitationKey)
            );
        }

        $payment = array(
            'subtotal' => $invoice->amount,
            'shippingAmount' => '0',
            'taxAmount' => '0',
            'paymentType' => 'Sale'
        );

        $lineItems = $shippingDetails = array();
        //form line items
        if (!empty($invoice->invoice_items)) {
            foreach ($invoice->invoice_items as $index => $item) {
                $lineItems[] = array(
                    'number' => $index + 1,
                    'quantity' => intval($item->qty),
                    'name' => intval($item->qty) . ' ' . $item->product_key,
                    'description' => $item->notes,
                    'amount' => $item->cost
                );
            }
        }

        //form shipping details
        if (!empty($shippingDetails)) {
            $shippingDetails = array(
                'name' => $client->name,
                'address' => $client->address1,
                'city' => $client->city,
                'state' => 'IN', //$client->state,
                'zip' => $client->postal_code,
                'country' => $client->country->iso_3166_2,
            );
        }

        $paymentDriver = $account->paymentDriver($invitation);

        //return data
        $invoiceDetails = new stdClass();
        $invoiceDetails->buyer = $buyer;
        $invoiceDetails->payment = $payment;
        $invoiceDetails->lineItems = $lineItems;
        $invoiceDetails->shippingDetails = $shippingDetails;
        $invoiceDetails->paymentDriver = $paymentDriver;

        return $invoiceDetails;
    }
}
