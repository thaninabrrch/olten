<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'admin@example2.com'],
            [
                'name' => 'admin',
                'firstname' => 'Super',
                'lastname'  => 'Admin',
                'password' => Hash::make('admin123'),
            ]
        );

        $roleId = DB::table('roles')->where('name', 'admin')->value('id');

        if ($roleId && !DB::table('role_user')->where('user_id', $user->id)->where('role_id', $roleId)->exists()) {
            DB::table('role_user')->insert([
                'user_id' => $user->id,
                'role_id' => $roleId,
                'user_type' => 'App\Models\User', 
            ]);
        }
    }
}
