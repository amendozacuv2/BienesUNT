<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasUuids;

    protected $fillable = [
        'uuid',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function userAreas()
    {
        return $this->hasMany(UserArea::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_area')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public function employeeAreas()
    {
        return $this->hasMany(EmployeeArea::class);
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_area')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public function activeLocations()
    {
        return $this->locations()->where('is_active', true);
    }

    public function activeEmployees()
    {
        return $this->employees()->wherePivot('is_active', true);
    }

    public function canBeDeleted(): bool
    {
        return ! $this->users()->exists()
            && ! $this->locations()->exists();
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

}
