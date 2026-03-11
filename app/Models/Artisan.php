<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Artisan extends Model
{
    protected $fillable = ['company_name', 'category_slug', 'email', 'phone', 'is_active'];

    // Relazione con le recensioni ricevute
    public function reviews()
    {
        return $this->hasMany(ArtisanReview::class, 'artisan_id');
    }

    /**
     * Accessor per la media stelle.
     * Si usa richiamando $artisan->star_rating
     */
    public function getStarRatingAttribute()
    {
        // Usiamo avg('average_rating') che punta alla colonna calcolata in ArtisanReview
        $average = $this->reviews()->avg('average_rating');
        return $average ? round($average, 1) : 0;
    }

    /**
     * Relazione utile per vedere i lavori (Deals) assegnati a questo artigiano
     */
    public function deals()
    {
        return $this->hasMany(Deal::class, 'artisan_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
