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

    public function createPaypalSession($invitationKey)
    {
        if (!$invitation = $this->invoiceRepo->findInvoiceByInvitation($invitationKey)) {
            return $this->returnError();
        }

        
        $invoice = $invitation->invoice;
        $client = $invoice->client; 
        $account = $invoice->account;
        
        echo '<pre>';
        $paymentDriver = $account->paymentDriver($invitation);
/*
 * //change settings
        $this->gateway->setSecretApiKey(null);
        $this->gateway->setUsername('30360021');
        $this->gateway->setPassword('$Test1234');
        $this->gateway->setDeviceId('90911395');
        $this->gateway->setLicenseId('20527');
        $this->gateway->setSiteId('20518');
        $this->gateway->setServiceUri('https://api-uat.heartlandportico.com/paymentserver.v1/PosGatewayService.asmx?wsdl');
*/        
        // createPaypalSession        
        $buyer = array(
            'returnUrl' => 'https://developer.heartlandpaymentsystems.com',
            'cancelUrl' => 'https://developer.heartlandpaymentsystems.com'
        );

        $payment = array(
            'subtotal' => $invoice->amount,
            'shippingAmount' => '0',
            'taxAmount' => '0',
            'paymentType' => 'Sale'
        );
        
        $lineItems = array();
        $lineItem = array(
            'number' => '1',
            'quantity' => '1',
            'name' => 'Name with special',
            'description' => 'Description with special',
            'amount' => '20.00'
        );
        $lineItems[] = $lineItem;
        
        $shippingDetails = array(
            'name' => 'Joe Tester',
            'address' => '1 heartland way',
            'city' => 'Jeffersonville',
            'state' => 'IN',
            'zip' => '47130',
            'country' => 'US',
        );

        $paymentDriver->createPaypalSession($payment, $buyer, $lineItems, $shippingDetails);die;

        
/*
        $invoice->terms = trim($account->invoice_terms);
        $invoice->invoice_footer = trim($account->invoice_footer);

        $contact->first_name = 'Test';
        $contact->last_name = 'Contact';
        $contact->email = 'contact@gmail.com';
        $client->contacts = [$contact];

        $invoiceItem->cost = 100;
        $invoiceItem->qty = 1;
        $invoiceItem->notes = 'Notes';
        $invoiceItem->product_key = 'Item';
 
 */
        die;


    }

    public function paypalSessionSale()
    {
        
    }
}
