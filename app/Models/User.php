<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'password',
    'role',
    'academic_role',
    'entry_year',
    'department_id',
    'study_program_id',

    'public_key',
    'encrypted_private_key',

    'emek_password',
    'emek_password_salt',

    'emek_recovery',
    'emek_recovery_salt',

    'must_change_password',

    'encryption_initialized_at',
])]

#[Hidden([
    'password',
    'remember_token',

    'encrypted_private_key',

    'emek_password',
    'emek_password_salt',

    'emek_recovery',
    'emek_recovery_salt',
])]

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuids, SoftDeletes;

    public $incrementing = false;

    /**
     * UUID disimpan sebagai string
     */
    protected $keyType = 'string';
    /**
     * A user belongs to a department.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * A user belongs to a study program.
     */
    public function studyProgram(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class);
    }

    /**
     * Attribute casting.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',

            'password' => 'hashed',

            'must_change_password' => 'boolean',

            'encryption_initialized_at' => 'datetime',
        ];
    }
}
