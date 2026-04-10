<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
    ];

    /**
     * Get the attachments for the task.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class);
    }
}
