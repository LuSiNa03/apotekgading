<?php

namespace App\Filament\Admin\Pages;

use App\Models\IdentitasApotek;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use App\Traits\HasLockedPageNavigation;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class IdentitasApotekPage extends Page implements HasForms
{
    use InteractsWithForms, HasLockedPageNavigation;

    public static function canAccess(): bool
    {
        return auth()->user() && auth()->user()->can('view_any_user');
    }

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Identitas Apotek';

    protected static ?string $title = 'Identitas Apotek';

    protected static string $view = 'filament.admin.pages.identitas-apotek-page';

    public ?array $data = [];

    public function mount(): void
    {
        $identitas = IdentitasApotek::getSingle();
        $this->form->fill($identitas->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Utama')
                    ->schema([
                        TextInput::make('nama_apotek')
                            ->label('Nama Apotek')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('no_telp')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email Resmi')
                            ->email()
                            ->maxLength(255),
                        Textarea::make('alamat')
                            ->label('Alamat Lengkap')
                            ->required()
                            ->rows(3),
                    ])->columnSpan(2),

                Section::make('Visual')
                    ->schema([
                        FileUpload::make('logo')
                            ->label('Logo Apotek')
                            ->image()
                            ->directory('logo-apotek')
                            ->maxSize(1024)
                            ->helperText('Logo yang tampil di sidebar & halaman login.'),
                        FileUpload::make('login_image')
                            ->label('Gambar Halaman Login')
                            ->image()
                            ->directory('login-images')
                            ->maxSize(4096)
                            ->imageResizeMode('cover')
                            ->helperText('Gambar ilustrasi yang tampil di form login semua role. Rasio 16:9 disarankan.'),
                    ])->columnSpan(1),
            ])
            ->statePath('data')
            ->columns(3);
    }

    public function save(): void
    {
        try {
            $formData = $this->form->getState();
            $identitas = IdentitasApotek::getSingle();
            $identitas->update($formData);

            Notification::make()
                ->title('Identitas apotek berhasil diperbarui!')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal memperbarui: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
