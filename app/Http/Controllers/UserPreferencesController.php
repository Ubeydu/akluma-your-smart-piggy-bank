<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function updatePreferences(Request $request)
    {
        $user = Auth::user();

        // Build the preferences array
        $preferences = [
            'email' => ['enabled' => $request->has('email_notifications')],
            'sms' => ['enabled' => $request->has('sms_notifications')],
            'push' => ['enabled' => $request->has('push_notifications')]
        ];

        // Update user preferences
        $user->notification_preferences = $preferences;
        $user->save();

        // If this is an AJAX request, return JSON
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Preferences updated successfully'
            ]);
        }

        // Otherwise, redirect with status
        return redirect()->route('localized.profile.edit', ['locale' => app()->getLocale()])
            ->with('status', 'preferences-updated');
    }

}
