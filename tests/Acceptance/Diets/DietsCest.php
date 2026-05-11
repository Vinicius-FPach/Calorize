<?php

namespace Tests\Acceptance\Diets;

use App\Models\Diets;
use App\Models\User;
use Tests\Acceptance\BaseAcceptanceCest;
use Tests\Support\AcceptanceTester;

class DietCest extends BaseAcceptanceCest
{
    public function seeMyDiets(AcceptanceTester $page): void
    {
        $user = new User([
            'name' => 'User 1',
            'email' => 'fulano@example.com',
            'password' => '123456',
            'password_confirmation' => '123456'
        ]);
        $user->save();
    }
}
