<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtisanWorker extends Model
{
    use HasFactory;

    protected $table = 'artisan_workers'; // Assicurati che il nome tabella sia corretto

    protected $fillable = [
        'artisan_id',
        'first_name',
        'last_name',
        'fiscal_code',
        'role',
        'safety_training_expiry',
        'medical_visit_expiry'
    ];

    protected $casts = [
        'safety_training_expiry' => 'date',
        'medical_visit_expiry' => 'date',
    ];

    /**
     * Relazione con la ditta (Artigiano)
     */
    public function artisan(): BelongsTo
    {
        return $this->belongsTo(Artisan::class);
    }
}