<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\InvoicesReport;
use App\Filament\Pages\SessionsReport;
use App\Filament\Pages\UserInvoicesReport;
use App\Filament\Pages\UserSessionsReport;
use App\Models\File;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Session;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Database\Eloquent\Builder;

class SessionsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    public $since;
    public $until;

    protected $queryString = [
        'since',
        'until'
    ];

    protected function getCards(): array
    {
        $payments = InvoicePayment::query()
            ->when($this->since, function ($query, $since) {
                $query->whereRelation('invoice', fn ($query) => $query->whereRelation('session', 'date', '>=', $since));
            })
            ->when($this->until, function ($query, $until) {
                $query->whereRelation('invoice', fn ($query) => $query->whereRelation('session', 'date', '<=', $until));
            })
            ->get();

        $patients = File::query()
            ->when($this->since, function ($query, $since) {
                $query->where('registration_date', '>=', $since);
            })
            ->when($this->until, function ($query, $until) {
                $query->where('registration_date', '<=', $until);
            })
            ->get();

        return [
            Card::make('Receptions', number_format($this->getSessionsData()["count"]))
                ->description("{$this->getSessionsData()['diff']['percentage']}% increase")
                ->descriptionIcon($this->getSessionsData()['diff']['percentage'] > 0 ? 'heroicon-s-trending-up' : 'heroicon-s-trending-down')
                ->chart([
                    $this->getSessionsData()["diff"]["counts"][0],
                    $this->getSessionsData()["diff"]["counts"][1],
                    $this->getSessionsData()["count"]
                ])
                ->color($this->getSessionsData()['diff']['percentage'] > 0 ? 'success' : 'warning')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'wire:click' => 'viewReport("sessions")',
                ]),
            Card::make('Invoices', number_format($this->getInvoicesData()["count"]))
                ->description("{$this->getInvoicesData()['diff']['percentage']}% increase")
                ->descriptionIcon($this->getInvoicesData()['diff']['percentage'] > 0 ? 'heroicon-s-trending-up' : 'heroicon-s-trending-down')
                ->chart([
                    $this->getInvoicesData()["diff"]["counts"][0],
                    $this->getInvoicesData()["diff"]["counts"][1],
                    $this->getInvoicesData()["count"]
                ])
                ->color($this->getInvoicesData()['diff']['percentage'] > 0 ? 'primary' : 'warning'),
            Card::make('Payments', 'RWF ' . (auth()->user()->hasRole('Admin') ? number_format($payments->sum('amount')) : number_format($payments->where('done_by', auth()->id())->sum('amount'))))
                ->description('32k increase')
                ->descriptionIcon('heroicon-s-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('secondary')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'wire:click' => 'viewReport("invoice-payments")',
                ]),
            Card::make('Patients', number_format($patients->sum('amount')))
                ->description('32k increase')
                ->descriptionIcon('heroicon-s-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('warning'),
        ];
    }

    protected function getSessionsData(): array
    {
        $currentSessions = Session::query()
            ->when($this->since, function ($query, $since) {
                $query->where('date', '>=', $since);
            })
            ->when($this->until, function ($query, $until) {
                $query->where('date', '<=', $until);
            })
            ->when(auth()->user()->hasRole('Receptionist') && !auth()->user()->hasRole('Admin'), function ($query, $role) {
                $query->where('done_by', auth()->id());
            })
            ->get();

        // dd($currentSessions);

        return [
            "count" => $currentSessions->count(),
            "diff" => [
                "counts" => [
                    $this->getDiffSessionsCount($this->getDiffSessionsCount($this->since, $this->until)["since"], $this->getDiffSessionsCount($this->since, $this->until)["until"])["count"],
                    $this->getDiffSessionsCount($this->since, $this->until)["count"],
                ],
                "percentage" => (($currentSessions->count() > 0 ? $currentSessions->count() : 1) * 100) / (($this->getDiffSessionsCount($this->since, $this->until)["count"] > 0) ? $this->getDiffSessionsCount($this->since, $this->until)["count"] : 1),
            ]
        ];
    }

    protected function getDiffSessionsCount($since, $until): array
    {
        $since = $since ?? $this->since;
        $until = $until ?? $this->until;

        $diffSessions = Session::query()
            ->when($since, function ($query, $since) {
                $query->where('date', '<=', $since);
            })
            ->when($until, function ($query, $until) use ($since) {
                $query->where('date', '>=', Carbon::parse($since)->subDays(round((strtotime($until) - strtotime($since)) / (60 * 60 * 24))));
            })
            ->when(auth()->user()->roles()->pluck('name'), function ($query, $roles) {
                switch ($roles) {
                    case 'Receptionist':
                        $query->where('done_by', auth()->user()->id);
                        break;
                    default:
                        # code...
                        break;
                }
                $query;
            })
            ->get();

        return [
            "count" => $diffSessions->count(),
            "since" => $until,
            "until" => Carbon::parse($since)->subDays(round((strtotime($until) - strtotime($since)) / (60 * 60 * 24)))
        ];
    }

    protected function getInvoicesData(): array
    {
        $currentInvoices = Invoice::query()
            ->when($this->since, function ($query, $since) {
                $query->whereRelation('session', 'date', '>=', $since);
            })
            ->when($this->until, function ($query, $until) {
                $query->whereRelation('session', 'date', '<=', $until);
            })
            ->when(auth()->user()->hasRole('Receptionist') && !auth()->user()->hasRole('Admin'), function ($query, $role) {
                $query->whereRelation('payments', fn (Builder $query) => $query->where('done_by', auth()->id()));
            })
            ->get();

        return [
            "count" => $currentInvoices->count(),
            "diff" => [
                "counts" => [
                    $this->getDiffInvoices($this->getDiffInvoices($this->since, $this->until)["since"], $this->getDiffInvoices($this->since, $this->until)["until"])["count"],
                    $this->getDiffInvoices($this->since, $this->until)["count"],
                ],
                "percentage" => (($currentInvoices->count() > 0 ? $currentInvoices->count() : 1) * 100) / ($this->getDiffInvoices($this->since, $this->until)["count"] > 0 ? $this->getDiffInvoices($this->since, $this->until)["count"] : 1),
            ]
        ];
    }

    protected function getDiffInvoices($since, $until): array
    {
        $since = $since ?? $this->since;
        $until = $until ?? $this->until;

        $diffInvoices = Invoice::query()
            ->when($since, function ($query, $since) {
                $query->whereRelation('session', 'date', '<=', $since);
            })
            ->when($until, function ($query, $until) use ($since) {
                $query->whereRelation('session', 'date', '>=', Carbon::parse($since)->subDays(round((strtotime($until) - strtotime($this->since)) / (60 * 60 * 24))));
            })
            ->get();

        return [
            "count" => $diffInvoices->count(),
            "since" => $until,
            "until" => Carbon::parse($since)->subDays(round((strtotime($until) - strtotime($since)) / (60 * 60 * 24)))
        ];
    }

    public function viewReport($reportType = "sessions")
    {
        switch ($reportType) {
            case 'sessions':
                if (auth()->user()->hasAnyRole(["Admin", "Data Manager"])) {
                    redirect(UserSessionsReport::getUrl([
                        "tableFilters" => [
                            "date" => [
                                "date" => Carbon::now()
                            ],
                        ]
                    ]));
                } else if (auth()->user()->hasRole("Receptionist")) {
                    redirect(SessionsReport::getUrl([
                        "tableFilters" => [
                            "date" => [
                                "date" => Carbon::now()
                            ],
                        ]
                    ]));
                }
                break;
            case 'invoice-payments':
                if (auth()->user()->hasAnyRole(["Admin", "Data Manager"])) {
                    redirect(UserInvoicesReport::getUrl([
                        "tableFilters" => [
                            "date" => [
                                "date" => Carbon::now()
                            ],
                        ]
                    ]));
                } else if (auth()->user()->hasRole("Cashier")) {
                    redirect(InvoicesReport::getUrl([
                        "tableFilters" => [
                            "date" => [
                                "date" => Carbon::now()
                            ],
                        ]
                    ]));
                }
                break;
        }
    }
}
