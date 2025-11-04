<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compte extends Model
{
    use HasFactory;

    protected $keyType = 'string';

    protected $fillable = [
        'client_id',
        'numero_compte',
        'type',
        'statut',
        'motif_blocage',
    ];
    
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
