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

        return view('piggy-banks.index', compact('piggyBanks'));
    }
}
