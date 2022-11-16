<x-filament::page :widget-data="['record' => $record]" :class="\Illuminate\Support\Arr::toCssClasses([
    'filament-resources-view-record-page',
    'filament-resources-' . str_replace('/', '-', $this->getResource()::getSlug()),
    'filament-resources-record-' . $record->getKey(),
])">
    <script src="/jquery-3.6.1.min.js"></script>
    <script src="/printThis.js"></script>
    @php
        $relationManagers = $this->getRelationManagers();
    @endphp

    @if (!$this->hasCombinedRelationManagerTabsWithForm() || !count($relationManagers))
        <div style="width: 50%">
            <x-filament::card>
                <div>POLYCLINIQUE MEDICALE LA PROVIDENCE</div>
                <div>Tel: 0784022096</div>
                <div>TIN: 106636995</div>
                <div>Invoice N&deg;: PRO-{{ sprintf('%06d', $record->id) }}</div>
                <div>Date: {{ date('d/m/Y', strtotime($record->date)) }}</div>
                <div>Names: {{ $record->names }}</div>

                <table
                    class="filament-tables-table w-full text-left rtl:text-right divide-y table-auto dark:divide-gray-700">
                    <thead>
                        <tr class="bg-gray-500/5">
                            <th>N&deg;</th>
                            <th>Charge</th>
                            <th>Unit Price</th>
                            <th>Quantity</th>
                            <th>Total Price</th>
                        </tr>
                    </thead>
                    <tbody wire:sortable wire:end.stop="reorderTable($event.target.sortable.toArray())"
                        wire:sortable.options="{ animation: 100 }" @class([
                            'divide-y whitespace-nowrap',
                            'dark:divide-gray-700' => config('tables.dark_mode'),
                        ])>
                        @php
                            $number = 0;
                        @endphp
                        @foreach ($record->items as $item)
                            @php
                                $number++;
                            @endphp
                            <tr>
                                <th>{{ $number }}</th>
                                <td>{{ $item->charge->name }}</td>
                                <td>{{ $item->charge->price }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ $item->amount }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot @class([
                        'divide-y whitespace-nowrap',
                        'dark:divide-gray-700' => config('tables.dark_mode'),
                    ])>
                        @php
                            $total = $record->items->sum('amount');
                        @endphp
                        <tr class="bg-gray-50">
                            <th colspan="4">Total</th>
                            <td>{{ $record->items->sum('amount') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </x-filament::card>
        </div>
    @endif

    @if (count($relationManagers))
        @if (!$this->hasCombinedRelationManagerTabsWithForm())
            <x-filament::hr />
        @endif

        <x-filament::resources.relation-managers :active-manager="$activeRelationManager" :form-tab-label="$this->getFormTabLabel()" :managers="$relationManagers" :owner-record="$record"
            :page-class="static::class">
            @if ($this->hasCombinedRelationManagerTabsWithForm())
                <x-slot name="form">
                    {{-- {{ $this->form }} --}}
                    <div id="print-js">
                        <x-filament::card>
                            <div>POLYCLINIQUE MEDICALE LA PROVIDENCE</div>
                            <div>Tel: 0784022096</div>
                            <div>TIN: 106636995</div>
                            <div>Invoice N&deg;: PRO-{{ sprintf('%06d', $record->id) }}</div>
                            <div>Date: {{ date('d/m/Y', strtotime($record->date)) }}</div>
                            <div>Names: {{ $record->names }}</div>

                            <table
                                class="filament-tables-table w-full text-left rtl:text-right divide-y table-auto dark:divide-gray-700">
                                <thead>
                                    <tr class="bg-gray-500/5">
                                        <th>N&deg;</th>
                                        <th>Charge</th>
                                        <th>Unit Price</th>
                                        <th>Quantity</th>
                                        <th>Total Price</th>
                                    </tr>
                                </thead>
                                <tbody wire:sortable wire:end.stop="reorderTable($event.target.sortable.toArray())"
                                    wire:sortable.options="{ animation: 100 }" @class([
                                        'divide-y whitespace-nowrap',
                                        'dark:divide-gray-700' => config('tables.dark_mode'),
                                    ])>
                                    @php
                                        $number = 0;
                                    @endphp
                                    @foreach ($record->items as $item)
                                        @php
                                            $number++;
                                        @endphp
                                        <tr>
                                            <th>{{ $number }}</th>
                                            <td>{{ $item->charge->name }}</td>
                                            <td>{{ $item->charge->price }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>{{ $item->amount }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot @class([
                                    'divide-y whitespace-nowrap',
                                    'dark:divide-gray-700' => config('tables.dark_mode'),
                                ])>
                                    @php
                                        $total = $record->items->sum('amount');
                                    @endphp
                                    <tr class="bg-gray-500/5">
                                        <th colspan="4">Total</th>
                                        <td>{{ $record->items->sum('amount') }}</td>
                                    </tr>
                                    <tr class="bg-gray-500/5">
                                        <th colspan="4">Paid</th>
                                        <td>{{ $record->payments()?->sum('amount') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                            {{ date('d/m/y', strtotime($record->date)) }}
                        </x-filament::card>
                    </div>
                </x-slot>
            @endif
        </x-filament::resources.relation-managers>
    @endif
    <script>
        window.addEventListener('print-invoice', event => {
            $('#print-js').printThis({
                importCSS: false,
                loadCSS: "/print.min.css",
            });
        })
    </script>
</x-filament::page>
