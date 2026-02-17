<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PosDocument extends Model
{
    // AGGIUNGI QUESTA RIGA SE NON HAI created_at E updated_at NEL DATABASE
    public $timestamps = false;
    // Definiamo i campi che possono essere scritti nel database
    protected $fillable = [
        'site_id',
        'artisan_id',
        'user_id',
        'title',
        'file_path',
        'status',
        'expiry_date',
        'rejection_note',
        'approved_by',
        'approval_date'
    ];

    // Trasformiamo automaticamente le date in oggetti Carbon
    protected $casts = [
        'expiry_date' => 'date',
        'approval_date' => 'datetime',
    ];

    /**
     * Relazione con il Cantiere (Commessa)
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(ConstructionSite::class, 'site_id');
    }

    /**
     * Relazione con l'Artigiano (Ditta)
     */
    public function artisan(): BelongsTo
    {
        return $this->belongsTo(Artisan::class, 'artisan_id');
    }

    /**
     * Relazione con l'utente che ha caricato il file
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relazione con i lavoratori assegnati a questo specifico POS
     */
    public function workers(): BelongsToMany
    {
        // Usa il nome esatto della classe che hai creato (es: ArtisanWorker)
        return $this->belongsToMany(ArtisanWorker::class, 'pos_resource_assignments', 'pos_id', 'worker_id');
    }
}