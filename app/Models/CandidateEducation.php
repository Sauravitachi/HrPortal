<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateEducation extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'candidate_education';

    protected $fillable = [
        'tenant_id',
        'candidate_application_id',
        'degree',
        'college',
        'passing_year',
    ];

    /**
     * Get the candidate application linked to this education entry.
     */
    public function candidateApplication(): BelongsTo
    {
        return $this->belongsTo(CandidateApplication::class);
    }
}
