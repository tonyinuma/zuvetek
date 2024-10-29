@php
    use Modules\Template\Helpers\TemplatePdf;

    $accounts = (new TemplatePdf)->getBankAccountsForPdf($document->establishment_id);

    $establishment = $document->establishment;
    $customer = $document->customer;
    $invoice = $document->invoice;
    $document_base = ($document->note) ? $document->note : null;

    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style1.css');
    $document_number = $document->series.'-'.str_pad($document->number, 8, '0', STR_PAD_LEFT);
    $accounts = \App\Models\Tenant\BankAccount::all();

    if($document_base) {

        $affected_document_number = ($document_base->affected_document) ? $document_base->affected_document->series.'-'.str_pad($document_base->affected_document->number, 8, '0', STR_PAD_LEFT) : $document_base->data_affected_document->series.'-'.str_pad($document_base->data_affected_document->number, 8, '0', STR_PAD_LEFT);

    } else {

        $affected_document_number = null;
    }

    $payments = $document->payments;

    $document->load('reference_guides');

    $total_payment = $document->payments->sum('payment');
    $balance = ($document->total - $total_payment) - $document->payments->sum('change');

    //calculate items
    $allowed_items = 75;
    $quantity_items = $document->items()->count();
    $cycle_items = $allowed_items - ($quantity_items * 3);
    $total_weight = 0;

    $logo = "storage/uploads/logos/{$company->logo}";
    if($establishment->logo) {
        $logo = "{$establishment->logo}";
    }
@endphp
<html>
<head>
    {{--<title>{{ $document_number }}</title>--}}
    {{--<link href="{{ $path_style }}" rel="stylesheet" />--}}
</head>
<body>

@if($document->state_type->id == '11')
    <div class="company_logo_box" style="position: absolute; text-align: center; top:30%;">
        <img src="data:{{mime_content_type(public_path("status_images".DIRECTORY_SEPARATOR."anulado.png"))}};base64, {{base64_encode(file_get_contents(public_path("status_images".DIRECTORY_SEPARATOR."anulado.png")))}}" alt="anulado" class="" style="opacity: 0.6;">
    </div>
@else
<div class="item_watermark" style="position: absolute; text-align: center; top:42%;">
    <img style="width: 100%" height="200px" src="data:{{mime_content_type(public_path("{$logo}"))}};base64, {{base64_encode(file_get_contents(public_path("{$logo}")))}}" alt="{{$company->name}}" alt="anulado" class="" style="opacity: 0.1;width: 95%">
</div>
@endif
<table class="full-width">

    <tr>
        @if($company->logo)
            <td width="20%">
                <div class="company_logo_box">
                    <img src="data:{{mime_content_type(public_path("{$logo}"))}};base64, {{base64_encode(file_get_contents(public_path("{$logo}")))}}" alt="{{$company->name}}" class="company_logo" style="max-width: 250px;">
                </div>
            </td>
        @else
            <td width="20%">
                {{--<img src="{{ asset('logo/logo.jpg') }}" class="company_logo" style="max-width: 150px">--}}
            </td>
        @endif
        <td width="40%" class="pl-3">
            <div class="text-left">
                <h4 class="">{{ $company->name }}</h4>
                <h5>{{ 'RUC '.$company->number }}</h5>
                <h6 style="text-transform: uppercase;">
                    {{ ($establishment->address !== '-')? $establishment->address : '' }}
                    {{ ($establishment->district_id !== '-')? ', '.$establishment->district->description : '' }}
                    {{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}
                    {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}
                </h6>

                @isset($establishment->trade_address)
                    <h6>{{ ($establishment->trade_address !== '-')? 'D. Comercial: '.$establishment->trade_address : '' }}</h6>
                @endisset

                <h6>{{ ($establishment->telephone !== '-')? 'Central telefónica: '.$establishment->telephone : '' }}</h6>

                <h6>{{ ($establishment->email !== '-')? 'Email: '.$establishment->email : '' }}</h6>

                @isset($establishment->web_address)
                    <h6>{{ ($establishment->web_address !== '-')? 'Web: '.$establishment->web_address : '' }}</h6>
                @endisset

                @isset($establishment->aditional_information)
                    <h6>{{ ($establishment->aditional_information !== '-')? $establishment->aditional_information : '' }}</h6>
                @endisset
            </div>
        </td>
        <td width="40%" class="border-box py-2 px-2 text-center">
            <h3 class="font-bold">{{ 'R.U.C. '.$company->number }}</h3>
            <h3 class="text-center font-bold">{{ $document->document_type->description }}</h3>
            <br>
            <h3 class="text-center font-bold">{{ $document_number }}</h3>
        </td>
    </tr>
</table>
<table class="full-width mt-3">
    <tr>
        <td width="47%" class="border-box pl-3 align-top">
            <table class="full-width">
                <tr>
                    <td class="font-sm" width="80px">
                        <strong>Razón Social</strong>
                    </td>
                    <td class="font-sm" width="8px">:</td>
                    <td class="font-sm">
                        {{ $customer->name }}
                    </td>
                </tr>
                <tr>
                    <td class="font-sm" width="80px">
                        <strong>{{$customer->identity_document_type->description}}</strong>
                    </td>
                    <td class="font-sm" width="8px">:</td>
                    <td class="font-sm">
                        {{$customer->number}}
                    </td>
                </tr>
                <tr>
                    <td class="font-sm align-top" width="80px">
                        <strong>Dirección</strong>
                    </td>
                    <td class="font-sm align-top" width="8px">:</td>
                    <td class="font-sm">
                        @if ($customer->address !== '')
                            <span style="text-transform: uppercase;">
                                {{ $customer->address }}
                                {{ ($customer->district_id !== '-')? ', '.$customer->district->description : '' }}
                                {{ ($customer->province_id !== '-')? ', '.$customer->province->description : '' }}
                                {{ ($customer->department_id !== '-')? '- '.$customer->department->description : '' }}
                            </span>
                        @endif
                    </td>
                </tr>

                @if(!is_null($document_base))
                <tr>
                    <td class="font-sm font-bold" width="80px">Doc. Afectado</td>
                    <td class="font-sm" width="8px">:</td>
                    <td class="font-sm">{{ $affected_document_number }}</td>
                </tr>
                <tr>
                    <td class="font-sm font-bold" width="80px">Tipo de nota</td>
                    <td class="font-sm">:</td>
                    <td class="font-sm">{{ ($document_base->note_type === 'credit')?$document_base->note_credit_type->description:$document_base->note_debit_type->description}}</td>
                </tr>
                <tr>
                    <td class="font-sm font-bold" width="80px">Descripción</td>
                    <td class="font-sm">:</td>
                    <td class="font-sm">{{ $document_base->note_description }}</td>
                </tr>
                @endif
            </table>
        </td>
        <td width="3%"></td>
        <td width="50%" class="border-box pl-1 align-top">
            <table class="full-width">
                <tr>
                    <td class="font-sm" width="90px">
                        <strong>Fecha Emisión</strong>
                    </td>
                    <td class="font-sm" width="8px">:</td>
                    <td class="font-sm">
                        {{ $document->date_of_issue->format('Y-m-d') }}
                    </td>
                    <td class="font-sm" width="70px">
                        <strong>H. Emisión</strong>
                    </td>
                    <td class="font-sm" width="8px">:</td>
                    <td class="font-sm">
                        {{ $document->time_of_issue }}
                    </td>
                </tr>
                <tr>
                    @if($invoice)
                        <td class="font-sm" width="90px">
                            <strong>Fecha de Vcto</strong>
                        </td>
                        <td class="font-sm" width="8px">:</td>
                        <td class="font-sm">
                            {{$invoice->date_of_due->format('Y-m-d')}}
                        </td>
                    @endif

                    <td class="font-sm align-top" width="70px">
                        <strong>Moneda</strong>
                    </td>
                    <td class="font-sm align-top" width="8px">:</td>
                    <td class="font-sm">
                        {{ $document->currency_type->description }}
                    </td>
                </tr>
                <tr>
                    @if($document->purchase_order)
                        <td class="font-sm" width="90px">
                            <strong>Orden de Compra</strong>
                        </td>
                        <td class="font-sm" width="8px">:</td>
                        <td class="font-sm">
                            {{ $document->purchase_order }}
                        </td>
                    @endif

                    @if($document->payments()->count() > 0)
                        <td class="font-sm align-top" width="70px">
                            <strong>F. Pago</strong>
                        </td>
                        <td class="font-sm align-top" width="8px">:</td>
                        <td class="font-sm">
                            {{ $document->payments()->first()->payment_method_type->description }} - {{ $document->currency_type_id }} {{ $document->payments()->first()->payment }}
                        </td>
                    @endif
                </tr>
                @if($document->guides)
                    <tr>
                        <td class="font-sm" width="100px">
                            <strong>Guía de Remisión</strong>
                        </td>
                        <td class="font-sm" width="8px">:</td>
                        <td class="font-sm" colspan="4">
                            @foreach ($document->guides as $item)
                            {{ $item->document_type_description }}:  {{ $item->number }}<br>
                            @endforeach
                        </td>
                    </tr>
                @endif

            </table>
        </td>
        {{-- <td width="5%" class="p-0 m-0">
            <img src="data:image/png;base64, {{ $document->qr }}" class="p-0 m-0" style="width: 120px;" />
        </td> --}}
    </tr>
</table>
<table class="full-width my-2 text-center" border="0">
    <tr>
        <td class="desc"></td>
    </tr>
</table>


<table class="full-width mt-0 mb-0" >
    <thead >
        <tr class="">
            <th class="border-top-bottom text-center py-1 desc" class="cell-solid"  width="12%">CÓDIGO</th>
            <th class="border-top-bottom text-center py-1 desc" class="cell-solid"  width="8%">CANT.</th>
            <th class="border-top-bottom text-center py-1 desc" class="cell-solid"  width="8%">U.M.</th>
            <th class="border-top-bottom text-center py-1 desc" class="cell-solid"  width="40%">DESCRIPCIÓN</th>
            <th class="border-top-bottom text-right py-1 desc" class="cell-solid"  width="12%">P.UNIT</th>
            <th class="border-top-bottom text-center py-1 desc" class="cell-solid"  width="8%">SIN IGV</th>
            <th class="border-top-bottom text-center py-1 desc" class="cell-solid"  width="12%">TOTAL</th>
        </tr>
    </thead>
    <tbody class="">
        @foreach($document->items as $row)
            <tr>
                <td class="p-1 text-center align-top desc cell-solid-rl">{{ $row->item->internal_id }}</td>
                <td class="p-1 text-center align-top desc cell-solid-rl">
                    @if(((int)$row->quantity != $row->quantity))
                        {{ $row->quantity }}
                    @else
                        {{ number_format($row->quantity, 0) }}
                    @endif
                </td>
                <td class="p-1 text-center align-top desc cell-solid-rl">{{ $row->item->unit_type_id }}</td>
                <td class="p-1 text-left align-top desc text-upp cell-solid-rl">
                    @if($row->name_product_pdf)
                        {!!$row->name_product_pdf!!}
                    @else
                        {!!$row->item->description!!}
                    @endif

                    @if (!empty($row->item->presentation)) {!!$row->item->presentation->description!!} @endif

                    @if($row->attributes)
                        @foreach($row->attributes as $attr)
                            @if($attr->attribute_type_id === '5032')
                            @php
                                $total_weight += $attr->value * $row->quantity;
                            @endphp
                            @endif
                            <br/><span style="font-size: 9px">{!! $attr->description !!} : {{ $attr->value }}</span>
                        @endforeach
                    @endif
                    {{-- @if($row->discounts)
                        @foreach($row->discounts as $dtos)
                            <br/><span style="font-size: 9px">{{ $dtos->factor * 100 }}% {{$dtos->description }}</span>
                        @endforeach
                    @endif --}}

                    @if($row->item->is_set == 1)
                     <br>
                     @inject('itemSet', 'App\Services\ItemSetService')
                        {{join( "-", $itemSet->getItemsSet($row->item_id) )}}
                    @endif
                </td>
                <td class="p-1 text-right align-top desc cell-solid-rl">{{ number_format($row->unit_price, 2) }}</td>
                <td class="p-1 text-right align-top desc cell-solid-rl">{{ number_format($row->unit_value, 2) }}</td>
                <td class="p-1 text-right align-top desc cell-solid-rl">{{ number_format($row->total, 2) }}</td>
            </tr>

        @endforeach

        @for($i = 0; $i < $cycle_items; $i++)
        <tr>
            <td class="p-1 text-center align-top desc cell-solid-rl"></td>
            <td class="p-1 text-center align-top desc cell-solid-rl">
            </td>
            <td class="p-1 text-center align-top desc cell-solid-rl"></td>
            <td class="p-1 text-left align-top desc text-upp cell-solid-rl">
            </td>
            <td class="p-1 text-right align-top desc cell-solid-rl"></td>
            <td class="p-1 text-right align-top desc cell-solid-rl">
            </td>
            <td class="p-1 text-right align-top desc cell-solid-rl"></td>
        </tr>
        @endfor

        <tr>
            <td class="p-1 text-left align-top desc cell-solid" colspan="3"><strong> VENDEDOR:</strong> {{ $document->user->name }}</td>
            <td class="p-1 text-left align-top desc cell-solid font-bold">
                SON:
                @foreach(array_reverse( (array) $document->legends) as $row)
                    @if ($row->code == "1000")
                        {{ $row->value }} {{ $document->currency_type->description }}
                    @else
                        {{$row->code}}: {{ $row->value }}
                    @endif
                @endforeach
            </td>
            <td class="p-1 text-right align-top desc cell-solid font-bold" colspan="2">
                OP. GRAVADA {{$document->currency_type->symbol}}
            </td>
            <td class="p-1 text-right align-top desc cell-solid font-bold">{{ number_format($document->total_taxed, 2) }}</td>
        </tr>

        <tr>
            <td class="p-1 text-left align-top desc cell-solid" colspan="3" rowspan="6">
                @php
                    $total_packages = $document->items()->sum('quantity');
                @endphp
                <strong> Total bultos:</strong>
                    @if(((int)$total_packages != $total_packages))
                        {{ $total_packages }}
                    @else
                        {{ number_format($total_packages, 0) }}
                    @endif
                <br>
                <strong> Total Peso:</strong>
                    {{$total_weight}} KG
                <br>
                @foreach($document->additional_information as $information)
                    @if ($information)
                        @if($loop->first)
                            <strong> Observación:</strong>
                        @endif
                        {{ $information }} <br>
                    @endif
                @endforeach
                @if($document->retention)
                    <span class="font-bold">Información de la retención</span>
                    <br><span class="">Valor total del comprobante: </span>{{$document->currency_type->symbol}}
                    {{ $document->currency_type->id == 'USD' ? number_format(($document->getRetentionTaxBase()/$document->exchange_rate_sale), 2) : $document->getRetentionTaxBase() }}
                    <br><span class="">Porcentaje de la retención: </span>{{ $document->retention->percentage * 100 }}%
                    <br><span class="">Monto de la retención: </span>{{$document->currency_type->symbol}} {{ $document->currency_type->id == 'USD' ? number_format(($document->retention->amount_pen/$document->exchange_rate_sale), 2) : $document->getRetentionTaxBase()}}
                @endif
                @if ($document->detraction)
                    <span class="font-bold">N. Cta. detracciones:</span> {{ $document->detraction->bank_account }}
                    @inject('detractionType', 'App\Services\DetractionTypeService')
                    <br><span class="font-bold">B/S sujeto a detracción:</span> {{$document->detraction->detraction_type_id}} - {{ $detractionType->getDetractionTypeDescription($document->detraction->detraction_type_id ) }}
                    <br><span class="font-bold">Método de pago:</span> {{ $detractionType->getPaymentMethodTypeDescription($document->detraction->payment_method_id ) }}
                    <br><span class="font-bold">Porcentaje detracción:</span> {{ $document->detraction->percentage}}%
                    <br><span class="font-bold">Monto detracción:</span> {{$document->currency_type->symbol}} {{$document->currency_type->id == 'USD' ? number_format(($document->detraction->amount/$document->exchange_rate_sale), 2) : $document->detraction->amount}}
                @endif
            <td class="p-1 text-center align-top desc cell-solid " rowspan="6">
                <img src="data:image/png;base64, {{ $document->qr }}" class="p-0 m-0" style="width: 120px;" /><br>
                Código Hash: {{ $document->hash }}
            </td>
            <td class="p-1 text-right align-top desc cell-solid font-bold" colspan="2">
                OP. INAFECTAS {{$document->currency_type->symbol}}
            </td>
            <td class="p-1 text-right align-top desc cell-solid font-bold">{{ number_format($document->total_unaffected, 2) }}</td>
        </tr>
        <tr>
            <td class="p-1 text-right align-top desc cell-solid font-bold" colspan="2">
                OP. EXONERADAS {{$document->currency_type->symbol}}
            </td>
            <td class="p-1 text-right align-top desc cell-solid font-bold">{{ number_format($document->total_exonerated, 2) }}</td>
        </tr>
        <tr>
            <td class="p-1 text-right align-top desc cell-solid font-bold" colspan="2">
                OP. GRATUITAS {{$document->currency_type->symbol}}
            </td>
            <td class="p-1 text-right align-top desc cell-solid font-bold">{{ number_format($document->total_free, 2) }}</td>
        </tr>
        <tr>
            <td class="p-1 text-right align-top desc cell-solid font-bold" colspan="2">
                TOTAL DCTOS. {{$document->currency_type->symbol}}
            </td>
            <td class="p-1 text-right align-top desc cell-solid font-bold">{{ number_format($document->total_discount, 2) }}</td>
        </tr>
        <tr>

            <td class="p-1 text-right align-top desc cell-solid font-bold" colspan="2">
                IGV. {{$document->currency_type->symbol}}
            </td>
            <td class="p-1 text-right align-top desc cell-solid font-bold">{{ number_format($document->total_igv, 2) }}</td>
        </tr>
        <tr>
            <td class="p-1 text-right align-top desc cell-solid font-bold" colspan="2">
                TOTAL A PAGAR. {{$document->currency_type->symbol}}
            </td>
            <td class="p-1 text-right align-top desc cell-solid font-bold">{{ number_format($document->total, 2) }}</td>
        </tr>
        @if(($document->retention || $document->detraction) && $document->total_pending_payment > 0)
            <tr>
                <td colspan="6" class="p-1 text-right align-top desc cell-solid font-bold">
                    M. PENDIENTE. {{ $document->currency_type->symbol }}
                </td>
                <td class="p-1 text-right align-top desc cell-solid font-bold">{{ number_format($document->total_pending_payment, 2) }}</td>
            </tr>
        @endif
    </tbody>

</table>

@if ($document->terms_condition)
    <table class="full-width">
        <tr>
            <td>
                <h6 style="font-size: 12px; font-weight: bold;">Términos y condiciones del servicio</h6>
                {!! $document->terms_condition !!}
            </td>
        </tr>
    </table>
@endif
@if ($document->detraction)
    <table class="full-width">
        <tr>
            <td>
                Operación sujeta al Sistema de Pago de Obligaciones Tributarias
            </td>
        </tr>
    </table>
@endif
@if(in_array($document->document_type->id,['01','03']))
    @foreach($accounts as $account)
        <p>
            <span
                class="font-bold">{{$account->bank->description}}</span> {{$account->currency_type->description}}
            <span class="font-bold">N°:</span> {{$account->number}}
            @if($account->cci)
                <span class="font-bold">CCI:</span> {{$account->cci}}
            @endif
        </p>
    @endforeach
@endif


@php
    $paymentCondition = \App\CoreFacturalo\Helpers\Template\TemplateHelper::getDocumentPaymentCondition($document);
@endphp
{{-- Condicion de pago  Crédito / Contado --}}
<table class="full-width">
    <tr>
        <td>
            <strong>CONDICIÓN DE PAGO: {{ $paymentCondition }} </strong>
        </td>
    </tr>
</table>

@if($document->payment_method_type_id)
    <table class="full-width">
        <tr>
            <td>
                <strong>MÉTODO DE PAGO: </strong>{{ $document->payment_method_type->description }}
            </td>
        </tr>
    </table>
@endif

@if ($document->payment_condition_id === '01')
    @if($payments->count())
        <table class="full-width">
            <tr>
                <td><strong>PAGOS:</strong></td>
            </tr>
            @php $payment = 0; @endphp
            @foreach($payments as $row)
                <tr>
                    <td>&#8226; {{ $row->payment_method_type->description }}
                        - {{ $row->reference ? $row->reference.' - ':'' }} {{ $document->currency_type->symbol }} {{ $row->payment + $row->change }}</td>
                </tr>
                @endforeach
                </tr>
        </table>
    @endif
@else
    <table class="full-width">
        @foreach($document->fee as $key => $quote)
            <tr>
                <td>
                    &#8226; {{ (empty($quote->getStringPaymentMethodType()) ? 'Cuota #'.( $key + 1) : $quote->getStringPaymentMethodType()) }}
                    / Fecha: {{ $quote->date->format('d-m-Y') }} /
                    Monto: {{ $quote->currency_type->symbol }}{{ $quote->amount }}</td>
            </tr>
            @endforeach
            </tr>
    </table>
@endif
</body>
</html>