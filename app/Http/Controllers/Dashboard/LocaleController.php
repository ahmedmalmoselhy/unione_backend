<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Middleware\SetLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $locale = $request->input('locale');

        if (in_array($locale, SetLocale::SUPPORTED, true)) {
            session(['locale' => $locale]);
        }

        return redirect()->back();
    }
}
