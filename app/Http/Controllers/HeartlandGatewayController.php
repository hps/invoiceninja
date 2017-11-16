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
        if (! $invitation = $this->invoiceRepo->findInvoiceByInvitation($invitationKey)) {
            return $this->returnError();
        }

        $invoice = $invitation->invoice;
        $client = $invoice->client;
        $account = $invoice->account;
        
        print_r($invoice);die;
        

        if (request()->silent) {
            session(['silent:' . $client->id => true]);
            return redirect(request()->url());
        }

        if (! $account->checkSubdomain(Request::server('HTTP_HOST'))) {
            return response()->view('error', [
                'error' => trans('texts.invoice_not_found'),
            ]);
        }

        $account->loadLocalizationSettings($client);

        if (! Input::has('phantomjs')) {
            $this->invoiceRepo->clearGatewayFee($invoice);
        }

    }

    }
