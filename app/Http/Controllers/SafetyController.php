<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PosDocument;
use App\Notifications\PosStatusUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class SafetyController extends Controller
{
    /**
     * Elenco dei POS in attesa di approvazione
     */
    public function index(Request $request) // Aggiungi Request qui!
    {
        // 1. Recupero il filtro dall'URL (default: 'pending')
        $filter = $request->query('filter', 'pending');

        // 2. Calcolo le statistiche per i Box (queste rimangono fisse)
        $stats = [
            'pending' => PosDocument::where('status', 'in_approvazione')->count(),
            
            'valid'   => PosDocument::where('status', 'valido')
                            ->where('expiry_date', '>=', now())
                            ->count(),
            
            'expired' => PosDocument::where(function($q) {
                                $q->where('expiry_date', '<', now())
                                ->orWhere('status', 'scaduto');
                            })->count(),
                            
            'total_sites' => \App\Models\ConstructionSite::count(),
        ];

        // 3. Carico i dati filtrati per la tabella
        $query = PosDocument::with(['site', 'artisan']);

        switch ($filter) {
            case 'expired':
                $query->where('expiry_date', '<', now());
                $title = "POS Scaduti";
                break;

            case 'valid':
                $query->where('status', 'valido')->where('expiry_date', '>=', now());
                $title = "Documenti Validi";
                break;

            case 'pending':
            default:
                $query->where('status', 'in_approvazione');
                $title = "Documenti in Attesa di Revisione";
                break;
        }

        $pendingPos = $query->orderBy('id', 'desc')->get();

        // 4. Passiamo tutto alla vista, incluso il titolo dinamico e il filtro attivo
        return view('admin.safety.index', compact('pendingPos', 'stats', 'title', 'filter'));
    }

    /**
     * Visualizza un singolo POS (Opzionale, utile per i dettagli)
     */
    public function show($id)
    {
        $pos = PosDocument::with(['site', 'artisan', 'workers'])->findOrFail($id);
        return view('admin.safety.show', compact('pos'));
    }

    /**
     * Approva il POS e notifica l'artigiano
     */
    public function approve(Request $request, $id) 
    {
        $pos = PosDocument::with('artisan.user')->findOrFail($id);
        
        $pos->update([
            'status' => 'valido',
            'approved_by' => auth()->id(),
            'approval_date' => now(),
            'rejection_note' => null 
        ]);

        if ($pos->artisan && $pos->artisan->user) {
            $pos->artisan->user->notify(new PosStatusUpdated($pos));
        }

        return redirect()->route('admin.safety.index')
            ->with('success', 'POS approvato. L\'artigiano è stato notificato.');
    }

    /**
     * Rifiuta il POS con motivazione e notifica l'artigiano
     */
    public function reject(Request $request, $id) 
    {
        $request->validate([
            'rejection_note' => 'required|string|min:5'
        ]);

        $pos = PosDocument::with('artisan.user')->findOrFail($id);
        
        $pos->update([
            'status' => 'revisionato',
            'rejection_note' => $request->rejection_note,
        ]);

        if ($pos->artisan && $pos->artisan->user) {
            $pos->artisan->user->notify(new PosStatusUpdated($pos));
        }

        return redirect()->route('admin.safety.index')
            ->with('warning', 'POS rifiutato. La nota è stata inviata all\'artigiano.');
    }
}
