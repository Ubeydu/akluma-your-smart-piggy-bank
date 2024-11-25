<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
       $piggyBanks = collect([
           (object) [
               'name' => 'Cool glasses for dad',
               'price' => 5000,
               'starting_amount' => 2900,
               'purchase_date' => '2024-12-15',
           ],
           (object) [
               'name' => 'Vitruta backpack (for meee!)',
               'price' => 2000,
               'starting_amount' => 800,
               'purchase_date' => '2025-01-20',
           ],
       ]);

       return view('dashboard', compact('piggyBanks'));
    }
}
