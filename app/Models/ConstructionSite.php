<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class ConstructionSite extends Model
{
    // Usiamo 'construction_sites' come tabella di riferimento per le Commesse
    protected $table = 'construction_sites';

    protected $fillable = [
        'name', 
        'address', 
        'city', 
        'commessa_code', 
        'status', 
        'start_date', 
        'end_date'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * RELAZIONI
     */

    public function posDocuments(): HasMany
    {
        return $this->hasMany(PosDocument::class, 'site_id');
    }

    /**
     * LOGICA DI BUSINESS (Sicurezza)
     */

    /**
     * Verifica se un singolo artigiano è autorizzato a lavorare oggi
     */
    public function isArtisanAuthorized($artisanId): bool
    {
        return $this->posDocuments()
            ->where('artisan_id', $artisanId)
            ->where('status', 'valido')
            ->where('expiry_date', '>', now())
            ->exists();
    }

    /**
     * Recupera lo stato globale della sicurezza per la Commessa.
     * Restituisce: 'success' (Tutto ok), 'warning' (In revisione), 'danger' (Blocco)
     */
    public function getGlobalSafetyStatus(): string
    {
        $posCollection = $this->posDocuments;

        // 1. Blocco se non ci sono documenti caricati
        if ($posCollection->isEmpty()) {
            return 'danger'; 
        }

        // 2. Blocco se c'è anche un solo documento SCADUTO o RIFIUTATO (revisionato)
        $hasCriticalIssues = $posCollection->contains(function ($pos) {
            return $pos->status === 'revisionato' || ($pos->expiry_date && $pos->expiry_date < now());
        });

        if ($hasCriticalIssues) {
            return 'danger';
        }

        // 3. Attenzione se ci sono documenti ancora in fase di approvazione
        $hasPending = $posCollection->contains('status', 'in_approvazione');

        if ($hasPending) {
            return 'warning';
        }

        // 4. Tutto valido
        return 'success';
    }

    /**
     * Metodo specifico per la validazione nel CommessaController.
     */
    public function isSafetyApproved(): bool
    {
        return $this->getGlobalSafetyStatus() === 'success';
    }

    /**
     * SUPPORTO UI (Badge e Visualizzazione)
     */

    /**
     * Genera l'HTML del Badge per il Quadro Comandi Commesse.
     * Nota: usa {!! $commessa->getGlobalSafetyBadge() !!} nelle viste Blade
     */
    public function getGlobalSafetyBadge(): string
    {
        $status = $this->getGlobalSafetyStatus();

        switch ($status) {
            case 'success':
                return '<span class="badge badge-success"><i class="fas fa-check-shield"></i> Idoneità OK</span>';
            case 'warning':
                return '<span class="badge badge-warning text-dark"><i class="fas fa-hourglass-half"></i> In Verifica</span>';
            case 'danger':
            default:
                return '<span class="badge badge-danger"><i class="fas fa-ban"></i> Blocco / Irregolare</span>';
        }
    }

    /**
     * Restituisce l'elenco dei nomi delle ditte con problemi documentali.
     */
    public function getIrregularArtisans()
    {
        return $this->posDocuments()
            ->with('artisan')
            ->where(function($query) {
                $query->where('status', '!=', 'valido')
                      ->orWhere('expiry_date', '<', now());
            })
            ->get()
            ->map(function($pos) {
                return $pos->artisan->company_name ?? 'Ditta Sconosciuta';
            })
            ->unique();
    }
}
