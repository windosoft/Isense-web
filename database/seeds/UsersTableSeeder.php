<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Models\Helpers;
use App\Models\Roles;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $email = 'admin@admin.com';
        $addUsers = [
            'uuid' => Helpers::getUuid(),
            'role_id' => Roles::$admin,
            'first_name' => 'Will',
            'last_name' => 'Peter',
            'phone' => '1234567890',
            'email' => $email,
            'password' => Hash::make('admin@123'),
            'user_type' => User::$admin,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];
        $checkUser = User::where('email', $email)->count();
        if ($checkUser == 0) {
            User::create($addUsers);
        }
    }
}
