<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Services\UserService;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    public function index()
    {
        return Inertia::render('master/user/Index', [
            'users' => $this->userService->getAnggota(),
        ]);
    }
}
