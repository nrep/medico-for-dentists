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
        /* Filament::navigation(function (NavigationBuilder $builder): NavigationBuilder {
            return $builder
                ->items([
                    ...Dashboard::getNavigationItems(),
                    ...InsuranceResource::getNavigationItems(),
                    ...FileResource::getNavigationItems(),
                    ...SessionResource::getNavigationItems(),
                    ...InvoiceResource::getNavigationItems(),
                ])
                ->groups([
                    NavigationGroup::make('Charges')
                        ->items([
                            ...ChargeTypeResource::getNavigationItems(),
                            ...ChargeListResource::getNavigationItems(),
                            ...ChargeResource::getNavigationItems()
                        ])
                        ->icon('eos-product-subscriptions-o')
                        ->collapsed(),
                    NavigationGroup::make('HR')
                        ->items([
                            ...EmployeeCategoryResource::getNavigationItems(),
                            ...EmployeeResource::getNavigationItems(),
                        ])
                        ->icon('clarity-employee-group-line')
                        ->collapsed(),
                    NavigationGroup::make('Users, Roles & Permissions')
                        ->items([
                            ...PermissionResource::getNavigationItems(),
                            ...RoleResource::getNavigationItems(),
                            ...UserResource::getNavigationItems()
                        ])
                        ->icon('heroicon-o-users')
                        ->collapsed(),
                    NavigationGroup::make('Reports')
                        ->items([
                            ...UserSessionsReport::getNavigationItems(),
                            ...UserInvoicesReport::getNavigationItems(),
                            ...DoctorTransactionsReport::getNavigationItems(),
                            // ...SessionsReport::getNavigationItems(),
                            // ...InvoicesReport::getNavigationItems()
                        ])
                        ->icon('tabler-report')
                        ->collapsed(false)
                        ->collapsible(false),
                ]);
        }); */

        /* Filament::serving(function () {
            Filament::registerNavigationGroups([
                NavigationGroup::make('Charges')
                    ->icon('eos-product-subscriptions-o')
                    ->collapsed(),
                NavigationGroup::make('Reports')
                    ->icon('tabler-report')
                    ->collapsed(false)
                    ->collapsible(false),
                NavigationGroup::make('HR')
                    ->icon('clarity-employee-group-line')
                    ->collapsed(),
                NavigationGroup::make('Authentication')
                    ->icon('heroicon-o-users')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('Settings')
                    ->icon('heroicon-s-cog')
                    ->collapsed(),
            ]); */

            /* Filament::registerTheme(
                app(Vite::class)('resources/css/app.css'),
            ); */
        // });

        Column::configureUsing(function (Column $column): void {
            $column
                ->toggleable()
                ->toggledHiddenByDefault(false);
        });
    }
}
