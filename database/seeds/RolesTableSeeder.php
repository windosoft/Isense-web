<?php

use Illuminate\Database\Seeder;
use App\Models\Roles;
use App\Models\Helpers;
use Carbon\Carbon;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roleList = Roles::$roleList;
        foreach ($roleList as $value) {
            $checkName = Roles::where('name', $value)->count();
            if ($checkName == 0) {
                $insertData = [
                    "uuid" => Helpers::getUuid(),
                    "name" => $value,
                    "status" => 'A',
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ];

                Roles::create($insertData);
            }
        }
    }
}
