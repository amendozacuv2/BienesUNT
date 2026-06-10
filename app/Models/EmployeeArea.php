<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeArea extends Model
{
    protected $table = 'employee_area';

    protected $fillable = [
        'employee_id',
        'area_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }
}
