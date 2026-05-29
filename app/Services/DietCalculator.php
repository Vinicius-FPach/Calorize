<?php

namespace App\Services;

use App\Models\Profile;

class DietCalculator
{
    private Profile $profile;

    public function __construct(Profile $profile)
    {
        $this->profile = $profile;
    }

    public function tmb(): float
    {
        if ($this->profile->gender === 'M') {
            return 66.5 + (13.75 * $this->profile->weight) + (5.003 * $this->profile->height) - (6.75 * $this->profile->age());
        }

        if ($this->profile->gender === 'F') {
            return 655.1 + (9.563 * $this->profile->weight) + (1.850 * $this->profile->height) - (4.676 * $this->profile->age());
        }

        // Não Informado — média entre masculino e feminino
        $tmbM = 66.5 + (13.75 * $this->profile->weight) + (5.003 * $this->profile->height) - (6.75 * $this->profile->age());
        $tmbF = 655.1 + (9.563 * $this->profile->weight) + (1.850 * $this->profile->height) - (4.676 * $this->profile->age());
        return ($tmbM + $tmbF) / 2;
    }

    public function get(): float
    {
        return $this->tmb() * (float) $this->profile->activity_factor;
    }

    public function kcalGoal(): float
    {
        return round(match ($this->profile->objective) {
            'EMAGRECER' => $this->get() - 500,
            'GANHAR'    => $this->get() + 300,
            default     => $this->get()
        }, 2);
    }

    public function protein(): float
    {
        return round(match ($this->profile->objective) {
            'EMAGRECER' => 2.0 * $this->profile->weight,
            'GANHAR'    => 2.0 * $this->profile->weight,
            default     => 1.6 * $this->profile->weight
        }, 2);
    }

    public function fat(): float
    {
        return round(($this->get() * 0.25) / 9, 2);
    }

    public function carbs(): float
    {
        $proteinKcal = $this->protein() * 4;
        $fatKcal = $this->fat() * 9;
        return round(($this->kcalGoal() - $proteinKcal - $fatKcal) / 4, 2);
    }
}
