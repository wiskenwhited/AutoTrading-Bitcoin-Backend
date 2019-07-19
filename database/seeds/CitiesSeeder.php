<?php

use App\Models\City;
use Illuminate\Database\Seeder;
use League\Csv\Reader;

class CitiesSeeder extends Seeder
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

        $csvFile = base_path() . '/database/seeds/files/cities.csv';
        $reader = Reader::createFromPath($csvFile);
        $reader->setDelimiter("\t");
        do {
            $reader->setLimit($limit);
            $reader->setOffset($offset);
            $rows = $reader->fetchAll(function($row) {
                return [
                    'country_code' => array_get($row, 0),
                    'city_name' => array_get($row, 1)
                ];
            });
            if (! $rows) {
                break;
            }
            City::insert($rows);
            $total = count($rows) - 1 + $offset;
            $this->command->getOutput()->writeln("<info>Inserted:</info> $offset - $total");
            $offset += $limit;
        } while (true);
    }
}