<?php

use App\Models\Country;
use Illuminate\Database\Seeder;
use League\Csv\Reader;

class CountriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ini_set("memory_limit", -1);
        ini_set("max_execution_time", 30000000);

        $limit = 10000;
        $offset = 0;

        $csvFile = base_path() . '/database/seeds/files/countries.csv';
        $reader = Reader::createFromPath($csvFile);
        do {
            $reader->setLimit($limit);
            $reader->setOffset($offset);
            $rows = $reader->fetchAll(function($row) {
                $country = explode(';', $row[0]);

                return [
                    'country_code' => array_get($country, 0),
                    'domain' => array_get($country, 1),
                    'country_name' => array_get($country, 2)
                ];
            });
            if (! $rows) {
                break;
            }
            Country::insert($rows);
            $total = count($rows) - 1 + $offset;
            $this->command->getOutput()->writeln("<info>Inserted:</info> $offset - $total");
            $offset += $limit;
        } while (true);
    }
}
