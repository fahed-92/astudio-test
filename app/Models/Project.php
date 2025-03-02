<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status'
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(Attribute::class);
    }

    public function timesheets(): HasMany
    {
        return $this->hasMany(Timesheet::class);
    }

    public function findProjectAttribute(string $name)
    {
        return $this->attributes()
            ->where('name', $name)
            ->first();
    }

    public function getProjectAttributeValue(string $name)
    {
        $attribute = $this->findProjectAttribute($name);
        return $attribute ? $attribute->value : null;
    }

    public function setProjectAttributeValue(string $name, $value)
    {
        $attribute = $this->findProjectAttribute($name);

        if ($attribute) {
            $attribute->update(['value' => $value]);
        } else {
            // You might want to add type detection or require type to be specified
            $this->attributes()->create([
                'name' => $name,
                'value' => $value,
                'type' => 'string' // Default type
            ]);
        }
    }
}
