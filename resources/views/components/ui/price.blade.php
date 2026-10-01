{{--
    Price (Menu IA spec §6): one figure with two decimals, isolated with <bdi>, read in full by screen readers
    ("2.50 دينار أردني" / "2.50 Jordanian dinars"). The amount comes from Master Data in fils (1 JOD = 1000 fils);
    no "+ tax", never a pre-tax price.
--}}
@props([
    'fils',
    'currency' => 'JOD',
])
@php
    if (! in_array($currency, ['JOD'], true)) {
        throw new InvalidArgumentException("x-ui.price: unsupported currency [{$currency}].");
    }
    $amount = number_format(((int) $fils) / 1000, 2, '.', '');
@endphp
<span {{ $attributes->class('ui-price') }}><span aria-hidden="true"><bdi>{{ $amount }}</bdi> {{ __('ui.currency.'.$currency.'.symbol') }}</span><span class="ui-visually-hidden">{{ $amount }} {{ __('ui.currency.'.$currency.'.name') }}</span></span>
