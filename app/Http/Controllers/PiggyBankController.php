<?php

namespace App\Http\Controllers;

use Brick\Money\Money;
use Illuminate\Http\Request;

class PiggyBankController extends Controller
{
    public function index()
    {
        $piggyBanks = auth()->user()->piggyBanks()
            ->latest()
            ->get();

        // Get the value before clearing
        $newPiggyBankId = session('newPiggyBankId');

        // Clear it after getting the value
        session()->forget('newPiggyBankId');

        return view('piggy-banks.index', compact('piggyBanks', 'newPiggyBankId'));
    }
}
