<?php
namespace App\Trait;
use App\Models\File;

trait HasFileRelations
{
    /**
     * Get all files associated with the model.
     */
    public function files()
    {
        return $this->morphToMany(File::class, 'entity', 'sys_file_relations');
    }

    /**
     * Attach a file to the model.
     *
     * @param  \App\Models\File  $file
     * @return void
     */
    public function attachFile(File $file)
    {
        $this->files()->attach($file);
    }

    /**
     * Detach a file from the model.
     *
     * @param  \App\Models\File  $file
     * @return void
     */
    public function detachFile(File $file)
    {
        $this->files()->detach($file);
    }
}
