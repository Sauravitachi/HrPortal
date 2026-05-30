<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interview extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = ['tenant_id', 'candidate_application_id', 'interview_date', 'interview_panel', 'notes', 'status'];

    protected function casts(): array
    {
        return [
            'interview_date' => 'datetime',
        ];
    }

    public function candidateApplication(): BelongsTo
    {
        return $this->belongsTo(CandidateApplication::class);
    }
}
