<?php

namespace Database\Populate;

use App\Models\Food;
use App\Models\User;

class FoodsPopulate
{
    public static function populate()
    {
        $admin = User::findBy(['email' => 'admin@example.com']);
        $fulano = User::findBy(['email' => 'fulano@example.com']);

        $adminFoods = [
            ['name' => 'Frango Grelhado',  'category' => 'Carnes',      'unit' => 'g', 'kcal' => 165, 'protein' => 31,  'carbs' => 0.1,   'fats' => 4],
            ['name' => 'Arroz Branco',     'category' => 'Cereais',     'unit' => 'g', 'kcal' => 130, 'protein' => 2.7, 'carbs' => 28,  'fats' => 0.3],
            ['name' => 'Ovo Cozido',       'category' => 'Ovos',        'unit' => 'g', 'kcal' => 155, 'protein' => 13,  'carbs' => 1.1, 'fats' => 11],
            ['name' => 'Patinho Moído',    'category' => 'Carnes',      'unit' => 'g', 'kcal' => 219, 'protein' => 26,  'carbs' => 0.1,   'fats' => 13],
            ['name' => 'Salmão',           'category' => 'Peixes',      'unit' => 'g', 'kcal' => 208, 'protein' => 20,  'carbs' => 0.1,   'fats' => 13],
            ['name' => 'Feijão Preto',     'category' => 'Leguminosas', 'unit' => 'g', 'kcal' => 77,  'protein' => 5,   'carbs' => 14,  'fats' => 0.5],
            ['name' => 'Banana',           'category' => 'Frutas',      'unit' => 'g', 'kcal' => 89,  'protein' => 1.1, 'carbs' => 23,  'fats' => 0.3],
            ['name' => 'Azeite de Oliva',  'category' => 'Óleos',       'unit' => 'ml','kcal' => 884, 'protein' => 0.1, 'carbs' => 0.1,   'fats' => 100],
            ['name' => 'Iogurte Grego',    'category' => 'Laticínios',  'unit' => 'g', 'kcal' => 59,  'protein' => 10,  'carbs' => 3.6, 'fats' => 0.4],
        ];

        foreach ($adminFoods as $data) {
            $food = new Food(array_merge($data, ['user_id' => $admin->id]));
            $food->save();
        }

        $fulanoFoods = [
            ['name' => 'Batata Doce',      'category' => 'Tubérculos',  'unit' => 'g', 'kcal' => 86,  'protein' => 1.6, 'carbs' => 20,  'fats' => 0.1],
            ['name' => 'Whey Protein',     'category' => 'Suplementos', 'unit' => 'g', 'kcal' => 120, 'protein' => 24,  'carbs' => 3,   'fats' => 1.5],
            ['name' => 'Aveia',            'category' => 'Cereais',     'unit' => 'g', 'kcal' => 389, 'protein' => 17,  'carbs' => 66,  'fats' => 7],
            ['name' => 'Atum em Lata',     'category' => 'Peixes',      'unit' => 'g', 'kcal' => 132, 'protein' => 28,  'carbs' => 0.1, 'fats' => 1],
            ['name' => 'Peito de Peru',    'category' => 'Carnes',      'unit' => 'g', 'kcal' => 109, 'protein' => 24,  'carbs' => 0.1, 'fats' => 1],
            ['name' => 'Leite Desnatado',  'category' => 'Laticínios',  'unit' => 'ml','kcal' => 35,  'protein' => 3.4, 'carbs' => 5,   'fats' => 0.1],
            ['name' => 'Brócolis',         'category' => 'Vegetais',    'unit' => 'g', 'kcal' => 34,  'protein' => 2.8, 'carbs' => 7,   'fats' => 0.4],
            ['name' => 'Amendoim',         'category' => 'Oleaginosas', 'unit' => 'g', 'kcal' => 567, 'protein' => 26,  'carbs' => 16,  'fats' => 49],
            ['name' => 'Maçã',             'category' => 'Frutas',      'unit' => 'g', 'kcal' => 52,  'protein' => 0.3, 'carbs' => 14,  'fats' => 0.2],
        ];

        foreach ($fulanoFoods as $data) {
            $food = new Food(array_merge($data, ['user_id' => $fulano->id]));
            $food->save();
        }

        echo "Foods populated successfully\n";
    }
}