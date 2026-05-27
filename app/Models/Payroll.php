<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'salary_month',
        'basic_salary',
        'hra',
        'incentives',
        'bonuses',
        'allowances',
        'pf',
        'esi',
        'tax',
        'loan_deductions',
        'other_deductions',
        'gross_salary',
        'total_deductions',
        'net_salary',
        'status',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'basic_salary' => 'decimal:2',
            'hra' => 'decimal:2',
            'incentives' => 'decimal:2',
            'bonuses' => 'decimal:2',
            'allowances' => 'decimal:2',
            'pf' => 'decimal:2',
            'esi' => 'decimal:2',
            'tax' => 'decimal:2',
            'loan_deductions' => 'decimal:2',
            'other_deductions' => 'decimal:2',
            'gross_salary' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'net_salary' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function calculateSalary(): void
    {
        $this->gross_salary = $this->basic_salary 
            + $this->hra 
            + $this->incentives 
            + $this->bonuses 
            + $this->allowances;

        $this->total_deductions = $this->pf 
            + $this->esi 
            + $this->tax 
            + $this->loan_deductions 
            + $this->other_deductions;

        $this->net_salary = $this->gross_salary - $this->total_deductions;
    }
}
