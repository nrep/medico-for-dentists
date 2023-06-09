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
    <div style="width: 70%; margin: auto;">
        <x-filament::card>
            <div>POLYCLINIQUE MEDICALE LA PROVIDENCE</div>
            <div>Tel: 0784022096</div>
            <div>TIN: 106636995</div>
            <div>Invoice N&deg;: PROV-{{ sprintf('%06d', $record->invoice->session->id) }}</div>
            <div>Date: {{ date('d/m/Y', strtotime($record->invoice->session->date)) }}</div>
            <div>Names: {{ $record->invoice->session->fileInsurance->file->names }}</div>

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
                    wire:sortable.options="{ animation: 100 }" @class([ 'divide-y whitespace-nowrap'
                    , 'dark:divide-gray-700'=> config('tables.dark_mode'),
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
                        <td>{{ $item->sold_at }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->total_price }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot @class([ 'divide-y whitespace-nowrap' , 'dark:divide-gray-700'=> config('tables.dark_mode'),
                    ])>
                    @php
                    $total = $record->items->sum('total_price');
                    $insurancePays = round($total * ($record->invoice->discount->discount / 100), 0);
                    $patientPays = $total - $insurancePays;
                    @endphp
                    <tr class="bg-gray-500/5">
                        <th colspan="4">Total</th>
                        <td>{{ $record->items->sum('total_price') }}</td>
                    </tr>
                    <tr class="bg-gray-500/5">
                        <th colspan="4">Patient</th>
                        <td>{{ $patientPays }}</td>
                    </tr>
                    <tr class="bg-gray-500/5">
                        <th colspan="4">Insurance</th>
                        <td>{{ $insurancePays }}</td>
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

    <x-filament::resources.relation-managers :active-manager="$activeRelationManager"
        :form-tab-label="$this->getFormTabLabel()" :managers="$relationManagers" :owner-record="$record"
        :page-class="static::class">
        @if ($this->hasCombinedRelationManagerTabsWithForm())
        <x-slot name="form">
            {{-- {{ $this->form }} --}}
            <div id="print-js">
                <x-filament::card>
                    <div style="font-weight: bold">POLYCLINIQUE MEDICALE LA PROVIDENCE</div>
                    <div><span style="font-weight: bold">Tel:</span> 0784022096</div>
                    <div><span style="font-weight: bold">TIN:</span> 106636995</div>
                    <div><span style="font-weight: bold">Invoice N&deg;:</span> PROV-{{ sprintf('%06d',
                        $record->invoice->session->id) }}</div>
                    <div><span style="font-weight: bold">Date:</span> {{ date('d/m/Y',
                        strtotime($record->invoice->session->date)) }}</div>
                    <div><span style="font-weight: bold">Names:</span> {{
                        $record->invoice->session->fileInsurance->file->names }}</div>

                    <table
                        class="filament-tables-table w-full text-left rtl:text-right divide-y table-auto dark:divide-gray-700">
                        <thead>
                            <tr class="bg-gray-500/5">
                                <th>N&deg;</th>
                                <th>Charge</th>
                                <th>P.U</th>
                                <th>Qty</th>
                                <th>P.T</th>
                            </tr>
                        </thead>
                        <tbody wire:sortable wire:end.stop="reorderTable($event.target.sortable.toArray())"
                            wire:sortable.options="{ animation: 100 }" @class([ 'divide-y whitespace-nowrap'
                            , 'dark:divide-gray-700'=> config('tables.dark_mode'),
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
                                <td>{{ $item->sold_at }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td style="font-weight: bold">{{ $item->total_price }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot @class([ 'divide-y whitespace-nowrap bg-gray-500/5' , 'dark:divide-gray-700'=>
                            config('tables.dark_mode'),
                            ])>
                            @php
                            $total = $record->items->sum('total_price');
                            $insurancePays = round($total * ($record->invoice->discount->discount / 100), 0);
                            $patientPays = $total - $insurancePays;
                            @endphp
                            <tr class="bg-gray-500/5">
                                <th colspan="4">Total</th>
                                <td style="font-weight: bold">{{ $record->items->sum('total_price') }}</td>
                            </tr>
                            <tr class="bg-gray-500/5">
                                <th colspan="4">Patient</th>
                                <td style="font-weight: bold">{{ $patientPays }}</td>
                            </tr>
                            <tr class="bg-gray-500/550">
                                <th colspan="4">Insurance</th>
                                <td style="font-weight: bold">{{ $insurancePays }}</td>
                            </tr>
                            <tr class="bg-gray-500/5">
                                <th colspan="4">Paid</th>
                                <td style="font-weight: bold">{{ $record->payments()?->sum('amount') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                    @php
                    $doneBy = "";
                    foreach ($record->payments as $key => $payment) {
                    $doneBy = $payment->recordedBy->name;
                    if ($key != $record->payments()->count() - 1) {
                    $doneBy .= ' and ';
                    }
                    }
                    @endphp
                    <div class="flex justify-between">
                        <div>Done by <span style="font-weight: bold">{{ $doneBy }}</span> on <span
                                style="font-weight: bold">{{ date('d/m/Y', strtotime( $record->payments()->count() > 0 ?
                                $record->payments()->latest()?->first()?->created_at :
                                $record->items()->latest()?->first()?->created_at)) }}</span></div>
                        <div>Printed by <span style="font-weight: bold">{{ auth()->user()->name }}</span> on <span
                                style="font-weight: bold">{{ date('d/m/Y') }}</span></div>
                    </div>
                </x-filament::card>
            </div>
        </x-slot>
        @endif
    </x-filament::resources.relation-managers>
    @endif
    <script>
        window.addEventListener('print-invoice', event => {
            /* printJS({
                printable: 'print-js',
                type: 'html',
                style: 'table, td, th { border: .5px solid; } table { width: 30% }',
            }); */

            $('#print-js').printThis({
                importCSS: false,
                loadCSS: "/print.min.css",
            });
        })
    </script>
</x-filament::page>