<x-filament::widget>
    <x-filament::card>
        Patient: {{ (100 - $record->discount->discount) > 0 ? round(($record->charges()->sum('total_price') * ((100 - $record->discount->discount) / 100))) : 0 }} {{ $currency }}
    </x-filament::card>
</x-filament::widget>
