<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConstructionSite;
use Illuminate\Http\Request;

class CommessaController extends Controller
{
    /**
     * Visualizza il Quadro Comandi (Normalizzato per la Vista)
     */
    public function index()
    {
        // Definiamo i 4 percorsi ufficiali (Slug => Label)
        $percorsi = [
            'apertura'         => 'Apertura',
            'verifica_tecnica' => 'Verifica Tecnica',
            'in_corso'         => 'In Corso',
            'completato'       => 'Completato'
        ];

        $allCommesse = ConstructionSite::with(['posDocuments'])
            ->withCount(['posDocuments as pos_pending' => function($query) {
                $query->where('status', 'in_approvazione');
            }])
            ->get();

        // FORZATURA: Trasformiamo lo stato del DB per farlo combaciare con i 4 percorsi
        $commesse = $allCommesse->groupBy(function($item) {
            $status = strtolower(trim($item->status)); // tutto minuscolo e senza spazi ai lati
            $status = str_replace(' ', '_', $status);  // gli spazi diventano underscore

            // Se lo stato nel DB è "attivo" o "aperto", lo mappiamo su "in_corso"
            if ($status == 'active' || $status == 'attivo' || $status == 'aperto') return 'in_corso';
            
            return $status;
        });

        return view('admin.commesse.index', compact('commesse', 'percorsi'));
    }

    /**
     * Dettaglio Tecnico della Commessa
     */
    public function show($id)
    {
        $commessa = ConstructionSite::with([
            'posDocuments.artisan', 
            'posDocuments.workers',
        ])->findOrFail($id);

        return view('admin.commesse.show', compact('commessa'));
    }

    /**
     * Gestione dello Stato con Validazione Sicurezza
     */
    public function updateStatus(Request $request, $id)
    {
        $commessa = ConstructionSite::findOrFail($id);
        
        // Se provi a impostare "in_corso", controlliamo la sicurezza
        // Nota: uso 'in_corso' come slug normalizzato
        if ($request->status == 'in_corso' && !$commessa->isSafetyApproved()) {
            return back()->with('error', 'Cantiere Bloccato: i documenti POS non sono in regola.');
        }

        $commessa->update(['status' => $request->status]);

        return back()->with('success', 'Stato Commessa aggiornato.');
    }

    /**
     * Report di Conformità
     */
    public function safetyReport($id)
    {
        $commessa = ConstructionSite::with('posDocuments.artisan')->findOrFail($id);
        
        $report = [
            'site_info' => $commessa,
            'documents' => $commessa->posDocuments,
            'is_compliant' => $commessa->getGlobalSafetyStatus() == 'success' // Corretto da 'ok' a 'success'
        ];

        return view('admin.commesse.safety_report', compact('report'));
    }
}
