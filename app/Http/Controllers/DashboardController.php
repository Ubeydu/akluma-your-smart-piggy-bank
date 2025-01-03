<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        $piggyBanks = auth()->user()->piggyBanks()
            ->latest()
            ->get();

        return view('piggy-banks.index', compact('piggyBanks'));
    }
}
