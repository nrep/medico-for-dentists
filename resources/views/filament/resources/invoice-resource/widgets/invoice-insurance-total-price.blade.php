<x-filament::widget>
    <x-filament::card>
        Insurance: {{ $record->session->discount->discount > 0 ? round($record->charges()->sum('total_price') * ($record->session->discount->discount / 100)) : 0 }} {{ $currency }}
    </x-filament::card>
</x-filament::widget>
