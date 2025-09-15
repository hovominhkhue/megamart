If you already have PHP and Composer installed, you may install the Laravel installer via Composer:
- composer global require laravel/installer

- npm install && npm run build
- composer run dev (sans autre framework, avec db est sqlite)

Laravel Breeze: pour auth (choisir blade)

générer les modèles:
php artisan make:model Category -m
php artisan make:model Product -m
php artisan make:model ProductImage -m
php artisan make:model Cart -m
php artisan make:model CartItem -m
php artisan make:model Order -m
php artisan make:model OrderItem -m

Blueprint est une classe Laravel (Illuminate\Database\Schema\Blueprint) qui décrit la structure d’une table.
Quand tu écris $table->string('name'), tu dis : “ajoute une colonne de type VARCHAR appelée name”.
Laravel traduit ensuite ce “plan” (blueprint en anglais) en SQL (MySQL, SQLite, PostgreSQL, etc.).

Étape B — Modèles + Migrations (produits, panier, commandes)

1.Lister les fonctionnalités.
2.Repérer les entités → tables. 
3.Dessiner les relations.
4.Lister les colonnes par table. 
Conception DB: https://dbdiagram.io/d/DB_megamart-68c81d44ce69eed1118c6f04
5.Traduire en migrations.
6.Tester avec des seeds, corriger si besoin.

Étape C — Seed de démo (catégories + produits)