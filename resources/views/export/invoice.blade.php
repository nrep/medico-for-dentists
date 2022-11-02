<div>POLYCLINIQUE MEDICALE LA PROVIDENCE</div>
<div>Tel: 0784022096</div>
<div>TIN: 106636995</div>
<div>Invoice N&deg;: PROV-{{ sprintf('%06d', $record['session']->id) }}</div>
<div>Date: {{ date('d/m/Y', strtotime($record['session']->date)) }}</div>
<div>Names: {{ $record['session']->fileInsurance->file->names }}</div>

<table class="filament-tables-table w-full text-left rtl:text-right divide-y table-auto dark:divide-gray-700">
    <thead>
        <tr class="bg-gray-500/5">
            <th>N&deg;</th>
            <th>Charge</th>
            <th>Unit Price</th>
            <th>Quantity</th>
            <th>Total Price</th>
        </tr>
    </thead>
    <tbody wire:sortable wire:end.stop="reorderTable($event.target.sortable.toArray())" wire:sortable.options="{ animation: 100 }" @class([ 'divide-y whitespace-nowrap' , 'dark:divide-gray-700'=> config('tables.dark_mode'),
        ])>
        @php
        $number = 0;
        @endphp
        @foreach ($record['days'] as $dayIndex => $day)
        @foreach ($day->items as $item)
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
        @endforeach
    </tbody>
    <tfoot @class([ 'divide-y whitespace-nowrap bg-gray-500/5' , 'dark:divide-gray-700'=> config('tables.dark_mode'),
        ])>
        @php
        $total = $record['charges']->sum('total_price');
        $insurancePays = round($total * ($record['session']->discount->discount / 100), 0);
        $patientPays = $total - $insurancePays;
        @endphp
        <tr class="bg-gray-500/5">
            <th colspan="4">Total</th>
            <td>{{ $record['charges']->sum('total_price') }}</td>
        </tr>
        <tr class="bg-gray-500/550">
            <th colspan="4">Insurance</th>
            <td>{{ $insurancePays }}</td>
        </tr>
        <tr class="bg-gray-500/5">
            <th colspan="4">Patient</th>
            <td>{{ $patientPays }}</td>
        </tr>
    </tfoot>
</table>
Done by {{ $record['session']->recordedBy->name }} on
{{ date('d/m/y', strtotime($record['session']->date)) }}