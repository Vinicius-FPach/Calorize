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
        $this->render('profile/show', compact('profile'));
    }

    public function updateAvatar(): void
    {
        $image = $_FILES['user_avatar'];

        $this->current_user->avatar()->update($image);
        $this->redirectTo(route('profile.show'));
    }

    public function newBiometric(): void
    {
        $profile = $this->current_user->profile();

        if ($profile) {
            FlashMessage::danger('Você já possui um perfil biométrico!');
            $this->redirectTo(route('profile.biometric.edit'));
            return;
        }

        $profile = new Profile(['user_id' => $this->current_user->id]);
        $title = 'Completar Perfil';
        $this->render('profile/biometric', compact('title', 'profile'));
    }

    public function createBiometric(Request $request): void
    {
        $profile = $this->current_user->profile();

        if ($profile) {
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
        $profile = $this->current_user->profile();

        if (!$profile) {
            FlashMessage::danger('Você ainda não possui um perfil biométrico!');
            $this->redirectTo(route('profile.biometric.new'));
            return;
        }

        $title = 'Editar Perfil Biométrico';
        $this->render('profile/biometric', compact('title', 'profile'));
    }

    public function updateBiometric(Request $request): void
    {
        $profile = $this->current_user->profile();

        if (!$profile) {
            FlashMessage::danger('Você ainda não possui um perfil biométrico!');
            $this->redirectTo(route('profile.biometric.new'));
            return;
        }

        $params = $request->getParam('profile');

        $profile->height          = $params['height'] ?? $profile->height;
        $profile->birthday        = $params['birthday'] ?? $profile->birthday;
        $profile->weight          = $params['weight'] ?? $profile->weight;
        $profile->biotype         = $params['biotype'] ?? $profile->biotype;
        $profile->gender          = $params['gender'] ?? $profile->gender;
        $profile->activity_factor = $params['activity_factor'] ?? $profile->activity_factor;
        $profile->objective       = $params['objective'] ?? $profile->objective;

        if (!$profile->hasChanges()) {
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
