@extends('payments.payment_method')

@section('payment_details')
    @parent

    {!! Former::open($url) !!}

    <h3>{{ trans('texts.paypal') }}   </h3>

    <p>&nbsp;</p>

    @if (isset($amount) && $client && $account->showTokenCheckbox())
        <input id="token_billing" type="checkbox" name="token_billing" {{ $account->selectTokenCheckbox() ? 'CHECKED' : '' }} value="1" style="margin-left:0px; vertical-align:top">
        <label for="token_billing" class="checkbox" style="display: inline;">{{ trans('texts.token_billing_braintree_paypal') }}</label>
        <span class="help-block" style="font-size:15px">
            {!! trans('texts.token_billing_secure', ['link' => link_to('https://www.braintreepayments.com/', 'Braintree', ['target' => '_blank'])]) !!}
        </span>
    @endif

    <p>&nbsp;</p>

    <center>
        @if(isset($amount))
            {!! Button::normal(request()->update ? strtoupper(trans('texts.submit')) : strtoupper(trans('texts.pay_now') . ' - ' . $account->formatMoney($amount, $client, CURRENCY_DECORATOR_CODE)  ))
                            ->withAttributes([
                                    'onclick' => 'createPaypalSession()'
                                ])
                            
                            ->large() !!}
        @else
            {!! Button::success(strtoupper(trans('texts.add_paypal_account') ))
                        ->submit()
                        ->large() !!}
        @endif
    </center>

    {!! Former::close() !!}

@stop

<script type="text/javascript">

    window.invitation = {!! $invitation !!};

    function createPaypalSession(){
        $.ajax({
            url: '{{ URL::to('heartland/create_paypal_session/') }}' + '/' + window.invitation.invitation_key,
            type: 'GET',
            datatype: 'json',
            processData: false,
            contentType: 'application/json; charset=utf-8',
            success: function (result) {
                console.log(result);
                alert(result);
            },
            error: function (result) {
                console.log(result);
                alert(result);
            }
        });
    }
            
</script>