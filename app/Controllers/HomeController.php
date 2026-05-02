<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index(): void
    {
        if ($this->current_user->is_admin) {
            $this->redirectTo(route('admin.index'));
        } else {
            $this->redirectTo(route('problems.index'));
        }
    }
}
