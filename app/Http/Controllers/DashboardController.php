<?php

namespace App\Http\Controllers\Artisan;

use App\Http\Controllers\Controller;
use App\Models\ConstructionSite;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // Recuperiamo l'ID artigiano collegato all'utente loggato
        $artisanId = Auth::user()->artisan->id;

        // Query: Seleziona i cantieri che hanno POS legati a questo artigiano
        $commesse = ConstructionSite::whereHas('posDocuments', function($query) use ($artisanId) {
            $query->where('artisan_id', $artisanId);
        })
        ->with(['posDocuments' => function($query) use ($artisanId) {
            $query->where('artisan_id', $artisanId);
        }])
        ->get();

        return view('artisan.dashboard', compact('commesse'));
    }
}
