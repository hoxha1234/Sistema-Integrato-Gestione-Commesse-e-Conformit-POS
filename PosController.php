<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConstructionSite;
use App\Models\PosDocument;
use App\Models\User;
use App\Notifications\NewPosSubmitted;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PosController extends Controller
{
    /**
     * Visualizzazione dei POS di un cantiere specifico per l'Admin
     */
    public function showSitePos($siteId) 
    {
        $site = ConstructionSite::with(['posDocuments.workers', 'posDocuments.artisan'])->findOrFail($siteId);
        
        return view('admin.pos.index', compact('site'));
    }

    /**
     * Salvataggio nuovo POS (Lato Artigiano)
     */
    public function store(Request $request) 
    {
        // 1. Validazione Formale
        $request->validate([
            'construction_site_id' => 'required|exists:construction_sites,id',
            'pos_file'    => 'required|mimes:pdf|max:10240', 
            'title'       => 'required|string|max:255',
            'expiry_date' => 'required|date|after:today',
            'workers'     => 'required|array|min:1',
            'equipment'   => 'nullable|array'
        ]);

        $workerIds = $request->input('workers', []); 
        $equipmentIds = $request->input('equipment', []);

        // 2. Esecuzione del Vaglio Automatico (Compliance Service)
        // Questo garantisce che non vengano inviati POS con personale non idoneo
        $validator = (new \App\Services\PosValidationService())->checkCompliance($workerIds, $equipmentIds);

        if (!$validator['is_valid']) {
            return back()->withErrors($validator['errors'])->withInput();
        }

        // 3. Transazione Database per integrità dei dati
        return DB::transaction(function () use ($request, $workerIds, $equipmentIds) {
            
            // A. Upload fisico del File (cartella dedicata pos_documents)
            $path = $request->file('pos_file')->store('pos_documents', 'public');

            // B. Creazione record testata POS
            $pos = PosDocument::create([
                'site_id'     => $request->construction_site_id,
                'artisan_id'  => Auth::user()->artisan_id, 
                'title'       => $request->title,
                'file_path'   => $path,
                'expiry_date' => $request->expiry_date,
                'status'      => 'in_approvazione' 
            ]);

            // C. Associazione Lavoratori e Mezzi (Tabelle Pivot)
            $pos->workers()->attach($workerIds);

            if (!empty($equipmentIds)) {
                $pos->equipments()->attach($equipmentIds);
            }

            // D. INVIO NOTIFICA REAL-TIME (Attivata)
            // Recuperiamo gli admin per fargli sapere che c'è un nuovo fascicolo da validare
            $admins = User::where('role', 'admin')->get();
            Notification::send($admins, new NewPosSubmitted($pos));

            return redirect()->route('artisan.pos.index')
                             ->with('success', 'Documentazione inviata. Il sistema ha verificato l\'idoneità tecnica dei lavoratori. In attesa di validazione finale dall\'ufficio sicurezza.');
        });
    }
}