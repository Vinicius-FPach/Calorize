<?php

namespace App\Controllers;

use App\Models\Profile;
use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\FlashMessage;

class ProfileController extends Controller
{
    public function show(): void
    {
        $profile = $this->current_user->profile();
        $title = 'Meu Perfil';
        $this->render('profile/show', compact('title', 'profile'));
    }

    public function updateAvatar(): void
    {
        $image = $_FILES['user_avatar'];

        $this->current_user->avatar()->update($image);
        $this->redirectTo(route('profile.show'));
    }

    public function newBiometric(): void
    {
        if ($this->current_user->profile()) {
            FlashMessage::danger('Você já possui um perfil biométrico!');
            $this->redirectTo(route('profile.biometric.edit'));
            return;
        }

        $profile = new Profile();
        $title = 'Completar Perfil';
        $this->render('profile/biometric', compact('title', 'profile'));
    }

    public function createBiometric(Request $request): void
    {
        if ($this->current_user->profile()) {
            FlashMessage::danger('Você já possui um perfil biométrico!');
            $this->redirectTo(route('profile.biometric.edit'));
            return;
        }

        $params = $request->getParam('profile');
        $params['user_id'] = $this->current_user->id;

        $profile = new Profile($params);

        if ($profile->save()) {
            FlashMessage::success('Perfil biométrico criado com sucesso!');
            $this->redirectTo(route('profile.show'));
        } else {
            $title = 'Completar Perfil';
            $this->render('profile/biometric', compact('title', 'profile'));
        }
    }

    public function editBiometric(): void
    {
        if (!$this->current_user->profile()) {
            FlashMessage::danger('Você ainda não possui um perfil biométrico!');
            $this->redirectTo(route('profile.biometric.new'));
            return;
        }

        $profile = $this->current_user->profile();
        $title = 'Editar Perfil Biométrico';
        $this->render('profile/biometric', compact('title', 'profile'));
    }

    public function updateBiometric(Request $request): void
    {
        if (!$this->current_user->profile()) {
            FlashMessage::danger('Você ainda não possui um perfil biométrico!');
            $this->redirectTo(route('profile.biometric.new'));
            return;
        }

        $params = $request->getParam('profile');

        $profile = $this->current_user->profile();

        $dbValues = [
            'height'          => (string) $profile->height,
            'age'             => (string) $profile->age,
            'weight'          => number_format((float)$profile->weight, 2, '.', ''),
            'biotype'         => $profile->biotype,
            'gender'          => $profile->gender,
            'activity_factor' => number_format((float)$profile->activity_factor, 3, '.', ''),
            'objective'       => $profile->objective,
        ];

        $profile->height          = $params['height'] ?? $profile->height;
        $profile->age             = $params['age'] ?? $profile->age;
        $profile->weight          = $params['weight'] ?? $profile->weight;
        $profile->biotype         = $params['biotype'] ?? $profile->biotype;
        $profile->gender          = $params['gender'] ?? $profile->gender;
        $profile->activity_factor = $params['activity_factor'] ?? $profile->activity_factor;
        $profile->objective       = $params['objective'] ?? $profile->objective;

        $postValues = [
            'height'          => (string) $profile->height,
            'age'             => (string) $profile->age,
            'weight'          => number_format((float)$profile->weight, 2, '.', ''),
            'biotype'         => $profile->biotype,
            'gender'          => $profile->gender,
            'activity_factor' => number_format((float)$profile->activity_factor, 3, '.', ''),
            'objective'       => $profile->objective,
        ];

        if ($dbValues === $postValues) {
            FlashMessage::warning('Nenhuma alteração detectada em relação aos dados atuais.');
            $this->redirectTo(route('profile.show'));
            return;
        }

        if ($profile->save()) {
            FlashMessage::success('Perfil biométrico atualizado com sucesso!');
            $this->redirectTo(route('profile.show'));
        } else {
            $title = 'Editar Perfil Biométrico';
            $this->render('profile/biometric', compact('title', 'profile'));
        }
    }
}
