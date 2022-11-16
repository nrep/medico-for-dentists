<?php

namespace App\Providers;

use App\Filament\Pages\DoctorTransactionsReport;
use App\Filament\Pages\InvoicesReport;
use App\Filament\Pages\SessionsReport;
use App\Filament\Pages\UserInvoicesReport;
use App\Filament\Pages\UserSessionsReport;
use App\Filament\Resources\ChargeListResource;
use App\Filament\Resources\ChargeResource;
use App\Filament\Resources\ChargeTypeResource;
use App\Filament\Resources\EmployeeCategoryResource;
use App\Filament\Resources\EmployeeResource;
use App\Filament\Resources\FileResource;
use App\Filament\Resources\InsuranceResource;
use App\Filament\Resources\InvoiceResource;
use App\Filament\Resources\SessionResource;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Tables\Columns\Column;
use Illuminate\Support\ServiceProvider;
use Phpsa\FilamentAuthentication\Resources\PermissionResource;
use Phpsa\FilamentAuthentication\Resources\RoleResource;
use Phpsa\FilamentAuthentication\Resources\UserResource;
use Illuminate\Foundation\Vite;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Filament::serving(function () {
            Filament::registerNavigationGroups([
                NavigationGroup::make()
                    ->label('Billing')
                    ->collapsible(false),
                NavigationGroup::make()
                    ->label('Charges'),
                NavigationGroup::make()
                    ->label('Reports'),
                NavigationGroup::make()
                    ->label('HR'),
                NavigationGroup::make()
                    ->label('Authentication')
                    ->collapsed(),
            ]);
        });

        Column::configureUsing(function (Column $column): void {
            $column
                ->toggleable()
                ->toggledHiddenByDefault(false);
        });
    }
}
