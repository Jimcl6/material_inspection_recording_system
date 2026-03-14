<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Available users:\n";
$users = DB::table('users')->select('id', 'email', 'name')->get();
foreach ($users as $user) {
    echo "- ID: {$user->id}, Email: {$user->email}, Name: {$user->name}\n";
}

// First, let's create the admin role
$adminRole = DB::table('roles')->where('slug', 'admin')->first();
if (!$adminRole) {
    $roleId = DB::table('roles')->insertGetId([
        'name' => 'Administrator',
        'slug' => 'admin',
        'description' => 'Has full access to all features',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    echo "\nCreated admin role with ID: {$roleId}\n";
    
    // Update the first user to have admin role
    if (isset($users[0])) {
        DB::table('users')
            ->where('id', $users[0]->id)
            ->update(['role_id' => $roleId]);
        
        echo "Updated user {$users[0]->email} (ID: {$users[0]->id}) to have admin role\n";
    }
} else {
    echo "\nAdmin role already exists with ID: {$adminRole->id}\n";
}
