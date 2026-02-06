<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('user:reset-password {email} {--password=} {--generate}', function () {
    $email = (string) $this->argument('email');
    $user = User::query()->where('email', $email)->first();

    if (! $user) {
        $this->error("No existe un usuario con el correo: {$email}");
        return self::FAILURE;
    }

    $passwordOption = $this->option('password');
    $useGeneratedPassword = (bool) $this->option('generate');

    if ($passwordOption && $useGeneratedPassword) {
        $this->error('Usa solo una opción: --password o --generate.');
        return self::FAILURE;
    }

    if ($useGeneratedPassword) {
        $newPassword = Str::password(16);
        $this->warn('Se generó una contraseña temporal.');
    } elseif ($passwordOption) {
        $newPassword = (string) $passwordOption;
    } else {
        $newPassword = (string) $this->secret('Nueva contraseña');
        $confirmPassword = (string) $this->secret('Confirmar contraseña');

        if ($newPassword !== $confirmPassword) {
            $this->error('Las contraseñas no coinciden.');
            return self::FAILURE;
        }
    }

    if (mb_strlen($newPassword) < 8) {
        $this->error('La contraseña debe tener al menos 8 caracteres.');
        return self::FAILURE;
    }

    $user->forceFill([
        'password' => Hash::make($newPassword),
    ])->save();

    $this->info("Contraseña actualizada para {$email}");

    if ($useGeneratedPassword) {
        $this->line("Contraseña temporal: {$newPassword}");
    }

    return self::SUCCESS;
})->purpose('Resetear contraseña de un usuario por correo');
