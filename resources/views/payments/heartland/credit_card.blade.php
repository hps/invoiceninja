@extends('payments.credit_card')

@section('head')
    @parent

    <script type="text/javascript" src="https://api2.heartlandportico.com/SecureSubmit.v1/token/2.1/securesubmit.min.js"></script>
    <script type="text/javascript" >
        $(function() {
            var $form = $('.payment-form');
            @if (isset($amount))
                var submitText = "{{ request()->update ? strtoupper(trans('texts.submit')) : strtoupper(trans('texts.pay_now') . ' - ' . $account->formatMoney($amount, $client, CURRENCY_DECORATOR_CODE)) }}";
            @else
                var submitText = "{{ strtoupper(trans('texts.add_credit_card')) }}";
            @endif
            var hps = new Heartland.HPS({
                publicKey: '{{ $accountGateway->getConfig()->publicApiKey }}',
                type: 'iframe',
                fields: {
                    cardNumber: {
                        target: "card_number",
                        placeholder: "{{ trans('texts.card_number') }}"
                    },
                    cardCvv: {
                        target: "cvv",
                        placeholder: "{{ trans('texts.cvv') }}"
                    },
                    cardExpiration: {
                        target: "expiration",
                        placeholder: "{{ trans('texts.expiration_month') }}"
                    },
                    submit: {
                        target: "submit",
                        value: submitText
                    }
                },
                style: {
                    'input': {
                        'background': 'transparent',
                        'border': '0',
                        'font-family': {!!  json_encode(Utils::getFromCache($account->getBodyFontId(), 'fonts')['css_stack']) !!},
                        'font-weight': "{{ Utils::getFromCache($account->getBodyFontId(), 'fonts')['css_weight'] }}",
                        'font-size': '16px',
                        'outline': 'none'
                    },
                    'input[type="submit"]': {
                        'color': '#fff',
                        'display': 'block',
                        'font-size': '18px',
                        'line-height': '1.33',
                        'margin-top': '3px',
                        'padding': '0',
                        'vertical-align': 'middle',
                        'width': '100%'
                    }
                },
                onTokenSuccess: function (e) {
                    // Insert the token into the form so it gets submitted to the server
                    $form.append($('<input type="hidden" name="sourceToken"/>').val(e.token_value));
                    // and submit
                    $form.get(0).submit();
                },
                onTokenError: function (e) {
                    $form.find('button').prop('disabled', false);
                    // Show the errors on the form
                }
            });
            // move sandbox warning to not shift styles
            $('#card_number > div').prependTo($('#card_number').parent());
            $('.payment-form').submit(function(event) {
                var $form = $(this);

                // Disable the submit button to prevent repeated clicks
                $form.find('button').prop('disabled', true);
                $('#js-error-message').hide();
            });
        });
    </script>
@stop
