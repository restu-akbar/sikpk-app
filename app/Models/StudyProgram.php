<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudyProgram extends Model
{
    protected $fillable = [
        'department_id',
        'name',
        'degree_level',
    ];

    /**
     * A study program belongs to a department.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * A study program has many users (satgas members).
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
