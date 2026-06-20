@php
  $contact = \App\Helpers\help::receiptContact($config ?? null, $owner_id ?? null);
@endphp
<div class="{{ $rowClass ?? 'row p-2 border-top border-bottom mt-3' }}" style="font-size: 14px">
    <div class="col-6 pe-5">{{ $addressLabel ?? 'العنوان:' }} {{ $contact['address'] }}</div>
    <div class="col-6 ps-5 text-start">{{ $mobileLabel ?? 'Mobile:' }} {{ $contact['mobile'] }}</div>
</div>
