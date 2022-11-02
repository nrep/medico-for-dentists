<x-filament::widget>
    <x-filament::card>
        Total: {{ $record->charges()->sum('total_price') }} {{ $currency }}
    </x-filament::card>
</x-filament::widget>
