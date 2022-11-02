<?php

namespace App\Filament\Widgets;

use Filament\Widgets\LineChartWidget;
use phpDocumentor\Reflection\PseudoTypes\False_;

class PaymentsChart extends LineChartWidget
{
    protected static ?string $heading = 'Payments';

    public ?string $filter = 'today';

    protected static ?int $sort = 3;

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'week' => 'Last week',
            'month' => 'Last month',
            'year' => 'This year',
        ];
    }

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Blog posts created',
                    'data' => [0, 10, 5, 2, 21, 32, 45, 74, 65, 45, 77, 89],
                    "fill" => false,
                    "borderColor" => 'rgb(75, 192, 192)',
                    "tension" => 0.1
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }
}
