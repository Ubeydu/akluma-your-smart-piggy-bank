<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserPreferencesController extends Controller
{
    public function updateTimezone(Request $request)
    {
        $request->validate([
            'timezone' => ['string', 'required']
        ]);

        auth()->user()->updateTimezone($request->timezone);
        session(['user_timezone' => $request->timezone]);

        return response()->json(['status' => 'success']);
    }
}
