<?php

use Illuminate\Database\Seeder;
use League\Csv\Reader;

class ScratchCodesSeederNewExtended extends Seeder
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

        \App\Models\ScratchCode::truncate();

        $limit = 20000;
        $offset = 0;

        $csvFile = base_path() . '/database/seeds/files/codes_online.csv';
        $reader = Reader::createFromPath($csvFile);
        $reader->setDelimiter("\t");
        do {
            $reader->setLimit($limit);
            $reader->setOffset($offset);
            $rows = $reader->fetchAll(function ($row) {
                $data = array_get($row, 0);
                return [
                    'code' => $data,
                    'type' => 'online',
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ];
            });
            if (!$rows) {
                break;
            }

            \App\Models\ScratchCode::insert($rows);
            $total = count($rows) - 1 + $offset;
            $this->command->getOutput()->writeln("<info>Inserted:</info> $offset - $total");
            $offset += $limit;
        } while (true);


        $limit = 20000;
        $offset = 0;

        $csvFile = base_path() . '/database/seeds/files/codes_scratch.csv';
        $reader = Reader::createFromPath($csvFile);
        $reader->setDelimiter("\t");
        do {
            $reader->setLimit($limit);
            $reader->setOffset($offset);
            $rows = $reader->fetchAll(function ($row) {
                $data = array_get($row, 0);
                return [
                    'code' => $data,
                    'type' => 'scratch_card',
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ];
            });
            if (!$rows) {
                break;
            }

            \App\Models\ScratchCode::insert($rows);
            $total = count($rows) - 1 + $offset;
            $this->command->getOutput()->writeln("<info>Inserted:</info> $offset - $total");
            $offset += $limit;
        } while (true);
    }
}