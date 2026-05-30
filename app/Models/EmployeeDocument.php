<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocument extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = ['tenant_id', 'employee_id', 'document_type', 'document_name', 'file_path'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
