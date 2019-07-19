<?php

use Illuminate\Database\Seeder;
use App\Models\Country;
use Illuminate\Support\Facades\DB;

class CityCountryIdsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $countries = Country::get();

        foreach ($countries as $country) {
            $count = DB::table("cities")
                ->where("country_code", $country->country_code)
                ->update(['country_id' => $country->id]);
            $this->command->getOutput()->writeln("<info>Updated:</info> $count cities for $country->country_name [$country->country_code]");
        }
    }
}
