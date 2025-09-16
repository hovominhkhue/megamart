<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $cats = collect(['Mobile','Cosmetics','Electronics','Furniture','Watches'])
            ->map(fn($c) => \App\Models\Category::create([
                'name'=>$c, 'slug'=>Str::slug($c)
            ]));

        \App\Models\Product::factory(30)->create()->each(function($p) use ($cats){
            $p->category_id = $cats->random()->id;
            $p->save();
        });
    }
}
