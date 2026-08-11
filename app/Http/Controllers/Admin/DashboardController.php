<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Compagnie;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $compagnies = Compagnie::withCount(['agences', 'utilisateurs'])->orderBy('nom_compagnie')->get();

        return view('admin.dashboard', [
            'compagnies' => $compagnies,
        ]);
    }
}
