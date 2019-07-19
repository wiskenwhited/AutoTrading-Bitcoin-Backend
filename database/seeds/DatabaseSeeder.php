<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->doSeed(CountriesSeeder::class);
        $this->doSeed(CitiesSeeder::class);
        $this->doSeed(CityCountryIdsTableSeeder::class);
        $this->call(ExchangeSeeder::class);
        $this->doSeed(BillingPackagesSeeder::class);


        $this->doSeed(TradesSeeder::class);
        $this->doSeed(ScratchCodesSeederNewExtended::class);
        $this->call(ArticleSeeder::class);
    }

    private function doSeed($seed_name)
    {
        $exists = DB::table("table_seeds")->where("seed_name", $seed_name)->count();
        if (! $exists) {
            $this->call($seed_name);
            DB::table("table_seeds")->insert(array("seed_name" => $seed_name));
        }
    }
}
