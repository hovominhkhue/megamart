<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 0) Charger le JSON local de FakeStore (“Get all products”)
        // Placez votre fichier ici : database/seeders/data/fakestore_products.json
        $path = database_path('seeders/data/fakestore_products.json');
        if (!file_exists($path)) {
            $this->command->error("Fichier introuvable : $path");
            return;
        }

        $json  = file_get_contents($path);
        $items = json_decode($json, true);

        if (!$items || !is_array($items)) {
            $this->command->error('JSON invalide ou vide.');
            return;
        }

        // 1) Créer/mettre à jour les catégories à partir du JSON
        // FakeStore : "electronics", "jewelery", "men's clothing", "women's clothing"
        $categoryMap = collect($items)
            ->pluck('category')
            ->unique()
            ->mapWithKeys(function ($catName) {
                $cat = \App\Models\Category::firstOrCreate(
                    ['slug' => Str::slug($catName)],
                    ['name' => ucfirst($catName)]
                );
                return [$catName => $cat->id];
            });

        // 2) Insérer/mettre à jour les produits
        foreach ($items as $p) {
            // Champs FakeStore: title, price, description, category, image
            if (!is_array($p)) continue; // sécurité

            $name  = $p['title']       ?? 'Produit';
            $price = (float)($p['price'] ?? 0);
            $desc  = $p['description'] ?? '';
            $img   = $p['image']       ?? null;
            $catId = $categoryMap->get($p['category']) ?? null; // accès sûr

            // Slug unique (ajoute -2, -3… si collision)
            $base = Str::slug($name);
            $slug = $this->uniqueSlug($base);

            \App\Models\Product::updateOrCreate(
                // clé de correspondance : le slug
                ['slug' => $slug],
                [
                    'name'        => $name,
                    'description' => $desc,
                    'price_cents' => (int) round($price * 100), // <-- ton schéma
                    'cover_image' => $img,                      // <-- ton schéma
                    'stock'       => rand(5, 50),               // petit stock fictif
                    'category_id' => $catId,
                ]
            );
        }

        $this->command->info('Seed terminé : catégories + produits FakeStore ✅');
    }

    /**
     * Génère un slug unique dans la table products.
     */
    private function uniqueSlug(string $base): string
    {
        $slug = $base ?: Str::random(8);
        $i = 2;

        while (\App\Models\Product::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i;
            $i++;
            if ($i > 1000) break; // sécurité
        }

        return $slug;
    }
}