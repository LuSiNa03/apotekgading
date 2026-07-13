<?php

namespace App\Providers;

use App\Observers\RoleObserver;
use App\Policies\ActivityPolicy;
use App\Policies\KategoriObatPolicy;
use App\Policies\ObatMasukPolicy;
use App\Policies\ObatPolicy;
use App\Policies\PenjualanPolicy;
use App\Policies\RolePolicy;
use App\Policies\SupplierPolicy;
use App\Policies\UserPolicy;
use App\Models\KategoriObat;
use App\Models\ObatMasuk;
use App\Models\Obat;
use App\Models\Penjualan;
use App\Models\Supplier;
use App\Models\User;
use Filament\Actions\MountableAction;
use Filament\Notifications\Livewire\Notifications;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\VerticalAlignment;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Auto-clear Spatie permission cache setiap kali role disimpan
        // agar perubahan hak akses dari admin langsung terefleksi di navbar
        Role::observe(RoleObserver::class);

        Gate::policy(Activity::class, ActivityPolicy::class);
        Gate::policy(Penjualan::class, PenjualanPolicy::class);
        Gate::policy(Obat::class, ObatPolicy::class);
        Gate::policy(KategoriObat::class, KategoriObatPolicy::class);
        Gate::policy(ObatMasuk::class, ObatMasukPolicy::class);
        Gate::policy(Supplier::class, SupplierPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Page::formActionsAlignment(Alignment::Right);
        Notifications::alignment(Alignment::End);
        Notifications::verticalAlignment(VerticalAlignment::End);
        Page::$reportValidationErrorUsing = function (ValidationException $exception) {
            Notification::make()
                ->title($exception->getMessage())
                ->danger()
                ->send();
        };
        MountableAction::configureUsing(function (MountableAction $action) {
            $action->modalFooterActionsAlignment(Alignment::Right);
        });
    }
}
