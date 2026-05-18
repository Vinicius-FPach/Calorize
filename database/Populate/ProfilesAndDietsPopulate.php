<?php

namespace Database\Populate;

use App\Models\Profile;
use App\Models\Diet;
use App\Models\User;

class ProfilesAndDietsPopulate
{
    public static function populate()
    {
        $admin = User::findBy(['email' => 'admin@example.com']);
        $fulano = User::findBy(['email' => 'fulano@example.com']);

        $adminProfile = new Profile([
            'user_id'         => $admin->id,
            'height'          => 180,
            'age'             => 30,
            'weight'          => '85.00',
            'biotype'         => 'MESOMORFO',
            'gender'          => 'M',
            'activity_factor' => '1.550',
            'objective'       => 'GANHAR',
        ]);
        $adminProfile->save();

        $fulanoProfile = new Profile([
            'user_id'         => $fulano->id,
            'height'          => 175,
            'age'             => 25,
            'weight'          => '70.00',
            'biotype'         => 'ECTOMORFO',
            'gender'          => 'M',
            'activity_factor' => '1.375',
            'objective'       => 'MANTER',
        ]);
        $fulanoProfile->save();

        $adminDiets = [
            'Dieta Bulking', 
            'Dieta Cutting', 
            'Dieta Manutenção'
        ];
        foreach ($adminDiets as $name) {
            Diet::createFromProfile($admin, $name)->save();
        }

        $fulanoDiets = [
            'Dieta Low Carb',
            'Dieta Mediterrânea',
            'Dieta Proteica',
            'Dieta Cetogênica',
            'Dieta Paleo',
            'Dieta Vegetariana',
            'Dieta Hipocalórica',
            'Dieta Flexível',
            'Dieta Intermitente',
        ];
        foreach ($fulanoDiets as $name) {
            Diet::createFromProfile($fulano, $name)->save();
        }

        echo "Profiles and diets populated successfully\n";
    }
}