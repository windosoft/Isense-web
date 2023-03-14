<?php

use Illuminate\Database\Seeder;
use App\Models\Permissions;
use Carbon\Carbon;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissionList = Permissions::allPermissions();

        foreach ($permissionList as $permission) {
            $checkName = Permissions::where('name', $permission)->count();
            if ($checkName == 0) {
                Permissions::create([
                    'name' => $permission,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }
        }
    }
}
