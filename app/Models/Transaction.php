<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $keyType = 'string';

    protected $fillable = [
        'compte_id',
         'type',
         'montant',
         'destinataire_id'
    ];

    public function compte()
    {
        return $this->belongsTo(Compte::class);
    }
}
