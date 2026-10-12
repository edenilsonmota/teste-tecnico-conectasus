<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Patient extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'name',
        'cpf',
        'birth_date',
        'phone',
        'health_unit_id',
    ];

    public function healthUnit(): BelongsTo
    {
        return $this->belongsTo(HealthUnit::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }
}
