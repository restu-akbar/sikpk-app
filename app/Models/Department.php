<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = [
        'name',
    ];

    /**
     * A department has many study programs.
     */
    public function studyPrograms(): HasMany
    {
        return $this->hasMany(StudyProgram::class);
    }

    /**
     * A department has many users (satgas members).
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
