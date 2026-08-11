<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $utilisateur = $request->user('staff')->load(['compagnie', 'agence']);

        return view('site.dashboard', [
            'utilisateur' => $utilisateur,
        ]);
    }
}
