<?php

namespace App\Settings;

use ApollocatDev\FilamentSettings\Contracts\Settings;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Actions\Action;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class EmailSettings extends Settings
{
    public string $host = '127.0.0.1';
    public int $port = 1025;
    public string $encryption = 'none';
    public ?string $username = null;
    public ?string $password = null;

    public static function default(): static
    {
        $instance = new static();
        $instance->host = '127.0.0.1';
        $instance->port = 1025;
        $instance->encryption = 'none';
        $instance->username = null;
        $instance->password = null;
        return $instance;
    }

    public function getLabel(): string
    {
        return 'Email Configuration';
    }

    public function getDescription(): ?string
    {
        return 'Configure SMTP settings for email notifications and communications';
    }

    public function getIcon(): ?string
    {
        return 'heroicon-o-envelope';
    }

    public function getSortOrder(): int
    {
        return 3;
    }

    public function getFormSchema(): array
    {
        return [
            Section::make('SMTP Configuration')
                ->schema([
                    TextInput::make('host')
                        ->label('SMTP Host')
                        ->required(),
                    TextInput::make('port')
                        ->label('SMTP Port')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->maxValue(65535),
                    Select::make('encryption')
                        ->label('Encryption')
                        ->options([
                            'none' => 'None',
                            'ssl' => 'SSL',
                            'tls' => 'TLS',
                        ])
                        ->required(),
                    TextInput::make('username')
                        ->label('Username'),
                    TextInput::make('password')
                        ->label('Password')
                        ->password(),

                    // Test SMTP Button
                    Action::make('test_smtp')
                        ->label('Test SMTP Configuration')
                        ->color('success')
                        ->icon('heroicon-o-paper-airplane')
                        ->action(function (Get $get) {
                            try {
                                // Get current form values
                                $host = $get('host');
                                $port = $get('port');
                                $encryption = $get('encryption');
                                $username = $get('username');
                                $password = $get('password');

                                // Configure SMTP settings temporarily
                                Config::set('mail.default', 'smtp');
                                Config::set('mail.mailers.smtp.host', $host);
                                Config::set('mail.mailers.smtp.port', $port);
                                Config::set('mail.mailers.smtp.encryption', $encryption === 'none' ? null : $encryption);
                                Config::set('mail.mailers.smtp.username', $username);
                                Config::set('mail.mailers.smtp.password', $password);

                                // Send test email using the ScheduleNotification mailable
                                Mail::to(Auth::user()->email)->send(new \App\Mail\ScheduleNotification([
                                    'message' => __('This is a test email from your Patrimeo application.'),
                                    'schedule' => (object) ['name' => __('SMTP Test'), 'cron' => 'test'],
                                    'valuations' => collect(),
                                    'assets' => collect(),
                                ]));

                                Notification::make()
                                    ->title(__('SMTP test successful'))
                                    ->body(__('Test email sent successfully to :email', ['email' => Auth::user()->email]))
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title(__('SMTP test failed'))
                                    ->body(__('Error: :error', ['error' => $e->getMessage()]))
                                    ->danger()
                                    ->send();
                            }
                        })
                ])
                ->columns(2)
        ];
    }

    public function toMailConfig(): array
    {
        return [
            'host' => $this->host,
            'port' => $this->port,
            'encryption' => $this->encryption === 'none' ? null : $this->encryption,
            'username' => $this->username,
            'password' => $this->password,
        ];
    }
}
