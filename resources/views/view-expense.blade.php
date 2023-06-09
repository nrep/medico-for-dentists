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
    <div style="width: 100%; font-weight:bold; font-size:20px" id="print-js">
        <x-filament::card>
            <div style="display:flex; flex-direction: row;">
                <div style="flex: 1;">
                    <div>REPUBLIQUE DU RWANDA</div>
                    <div>MINISTERE DE LA SANTE</div>
                    <div>PROVINCE DU SUD</div>
                    <div>DISTRICT DE MUHANGA</div>
                    <div style="font-weight: bold; color: blue;">POLYCLINIQUE MEDICALE LA PROVIDENCE</div>
                    <div style="font-weight: 500; color: green;" class="mb-sm-4">E-mail: cliniquemedicalelaprovidence@gmail.com</div>
                    <div>Invoice N&deg;: PROV-{{ sprintf('%06d', $record->id) }}</div>
                    <div>Date: {{ date('d/m/Y', strtotime($record->date)) }}</div>
                    <div>Names: {{ $record->expenseable?->names ?? $record->expenseable?->name }}</div>
                </div>
                <div>
                    <img style="height: 180px;" src="/logo.png" alt="PMP Logo">
                </div>
            </div>
            <div style="text-transform: uppercase; text-align:center;"><u>Bon de Sortie de Caisse N&deg; {{ $record->bill_no }}</u></div>
            <table class="filament-tables-table w-full text-left rtl:text-right divide-y table-auto dark:divide-gray-700">
                <thead>
                    <tr class="bg-gray-500/5">
                        <th>N&deg;</th>
                        <th>Reason</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody wire:sortable wire:end.stop="reorderTable($event.target.sortable.toArray())" wire:sortable.options="{ animation: 100 }" @class([ 'divide-y whitespace-nowrap' , 'dark:divide-gray-700'=> config('tables.dark_mode'),
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
                        <td>{{ $item->reason }}</td>
                        <td>{{ $item->amount }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot @class([ 'divide-y whitespace-nowrap bg-gray-500/5 ' , 'dark:divide-gray-700'=> config('tables.dark_mode'),
                    ])>
                    @php
                    $total = $record->items->sum('amount');
                    @endphp
                    <tr class="bg-gray-500/5">
                        <th colspan="2">Total</th>
                        <td>{{ $total }}</td>
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

    <x-filament::resources.relation-managers :active-manager="$activeRelationManager" :form-tab-label="$this->getFormTabLabel()" :managers="$relationManagers" :owner-record="$record" :page-class="static::class">
        @if ($this->hasCombinedRelationManagerTabsWithForm())
        <x-slot name="form">
            {{-- {{ $this->form }} --}}
            <div>
                <x-filament::card>
                    <div style="font-weight: bold">POLYCLINIQUE MEDICALE LA PROVIDENCE</div>
                    <div><span style="font-weight: bold">Tel:</span> 0784022096</div>
                    <div><span style="font-weight: bold">TIN:</span> 106636995</div>
                    <div><span style="font-weight: bold">Invoice N&deg;:</span> PROV-{{ sprintf('%06d', $record->session->id) }}</div>
                    <div><span style="font-weight: bold">Date:</span> {{ date('d/m/Y', strtotime($record->session->date)) }}</div>
                    <div><span style="font-weight: bold">Names:</span> {{ $record->session->fileInsurance->file->names }}</div>

                    <table class="filament-tables-table w-full text-left rtl:text-right divide-y table-auto dark:divide-gray-700">
                        <thead>
                            <tr class="bg-gray-500/5">
                                <th>N&deg;</th>
                                <th>Charge</th>
                                <th>P.U</th>
                                <th>Qty</th>
                                <th>P.T</th>
                            </tr>
                        </thead>
                        <tbody wire:sortable wire:end.stop="reorderTable($event.target.sortable.toArray())" wire:sortable.options="{ animation: 100 }" @class([ 'divide-y whitespace-nowrap' , 'dark:divide-gray-700'=> config('tables.dark_mode'),
                            ])>
                            @php
                            $number = 0;
                            @endphp
                            @foreach ($record->days as $dayIndex => $day)
                            @foreach ($day->items as $item)
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
                            @endforeach
                        </tbody>
                        <tfoot @class([ 'divide-y whitespace-nowrap bg-gray-500/5' , 'dark:divide-gray-700'=> config('tables.dark_mode'),
                            ])>
                            @php
                            $total = $record->charges->sum('total_price');
                            $insurancePays = round($total * ($record->discount->discount / 100), 0);
                            $patientPays = $total - $insurancePays;
                            @endphp
                            <tr class="bg-gray-500/5">
                                <th colspan="4">Total</th>
                                <td style="font-weight: bold">{{ $record->charges->sum('total_price') }}</td>
                            </tr>
                            <tr class="bg-gray-500/550">
                                <th colspan="4">Insurance</th>
                                <td style="font-weight: bold">{{ $insurancePays }}</td>
                            </tr>
                            <tr class="bg-gray-500/5">
                                <th colspan="4">Patient</th>
                                <td style="font-weight: bold">{{ $patientPays }}</td>
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
                        <div>Done by <span style="font-weight: bold">{{ $doneBy }}</span> on <span style="font-weight: bold">{{ date('d/m/Y', strtotime( $record->payments()->count() > 0 ? $record->payments()->latest()?->first()?->created_at : $record->charges()->latest()?->first()?->created_at)) }}</span></div>
                        <div>Printed by <span style="font-weight: bold">{{ auth()->user()->name }}</span> on <span style="font-weight: bold">{{ date('d/m/Y') }}</span></div>
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
                importCSS: true,
                loadCSS: "/print.min.css",
            });
        })
    </script>
</x-filament::page>