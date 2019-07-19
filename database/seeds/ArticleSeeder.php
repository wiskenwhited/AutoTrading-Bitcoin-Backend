<?php

use App\Models\Country;
use Illuminate\Database\Seeder;
use League\Csv\Reader;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $article = \App\Models\Article::whereSlug('terms')->first();
        if(!$article){
            $article = new \App\Models\Article();
            $article->slug = 'terms';
            $article->status_id = \App\Models\Article::StatusPublished;
            $article->save();
        }
    }
}
