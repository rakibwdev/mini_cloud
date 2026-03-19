<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'hash',
        'size',
    ];

    /**
     * Get the user files associated with this physical file.
     */
    public function userFiles()
    {
        return $this->hasMany(UserFile::class);
    }
}
