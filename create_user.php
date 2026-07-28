<?php

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::firstOrCreate(
    ['email' => 'test@example.com'],
    [
        'name' => 'Test User',
        'password' => \Illuminate\Support\Facades\Hash::make('password123'),
    ]
);

echo "User created/updated: " . $user->email . "\n";
echo "Password: password123\n";
