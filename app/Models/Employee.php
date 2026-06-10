<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasUuids;

    protected $fillable = [
        'uuid',
        'dni',
        'name',
        'lastname',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function employeeAreas()
    {
        return $this->hasMany(EmployeeArea::class);
    }

    public function areas()
    {
        return $this->belongsToMany(Area::class, 'employee_area')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public function activeAreas()
    {
        return $this->areas()->wherePivot('is_active', true);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->name . ' ' . $this->lastname);
    }

    public function uniqueIds(): array
    {
        return ['uuid'];
    }
}
