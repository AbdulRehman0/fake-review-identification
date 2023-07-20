<?php

namespace Database\Seeders;

use App\Models\SuspeciousKeyword;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SuspeciousKeywordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $keywords = [
            [
                ["keyword"=>"amazing"],["keyword"=>"amazing"]
            ],
            [
                ["keyword"=>"perfect"],["keyword"=>"perfect"]
            ],
            [
                ["keyword"=>"fabulous"],["keyword"=>"fabulous"]
            ],
        ];

        foreach ($keywords as $key => $item) {
            SuspeciousKeyword::updateOrCreate($item[0],$item[1]);
        }
    }
}
