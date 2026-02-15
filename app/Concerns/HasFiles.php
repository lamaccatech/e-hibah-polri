<?php

namespace App\Concerns;

use App\Enums\FileType;
use App\Models\File;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Trait for models that can have files attached.
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasFiles
{
    /**
     * Get all files attached to this entity.
     */
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }

    /**
     * Attach a file to this entity.
     *
     * @param  UploadedFile  $uploadedFile  The uploaded file
     * @param  FileType  $fileType  The type/category of this file
     * @param  string|null  $disk  Storage disk (default: configured default)
     * @param  bool  $makePublic  Whether to generate a public URL
     * @param  string|null  $description  Optional file description
     */
    public function attachFile(
        UploadedFile $uploadedFile,
        FileType $fileType,
        ?string $disk = null,
        bool $makePublic = false,
        ?string $description = null,
    ): File {
        $disk ??= config('filesystems.default');

        // Store the file
        $path = $uploadedFile->store($this->getFileStoragePath($fileType), $disk);

        // Generate public URL if requested
        $url = null;
        if ($makePublic) {
            $url = Storage::disk($disk)->url($path);
        }

        // Create file record via relationship
        return $this->files()->create([
            'file_type' => $fileType,
            'name' => $uploadedFile->getClientOriginalName(),
            'path' => $path,
            'url' => $url,
            'mime_type' => $uploadedFile->getMimeType(),
            'size_in_bytes' => $uploadedFile->getSize(),
            'description' => $description,
        ]);
    }

    /**
     * Get files by type.
     */
    public function getFilesByType(FileType $fileType): \Illuminate\Database\Eloquent\Collection
    {
        return $this->files()->where('file_type', $fileType)->get();
    }

    /**
     * Get the first file of a specific type.
     */
    public function getFirstFileByType(FileType $fileType): ?File
    {
        return $this->files()->where('file_type', $fileType)->first();
    }

    /**
     * Check if entity has a file of specific type.
     */
    public function hasFileOfType(FileType $fileType): bool
    {
        return $this->files()->where('file_type', $fileType)->exists();
    }

    /**
     * Detach file (soft delete) - file remains in storage for audit.
     */
    public function detachFile(File $file): bool
    {
        if ($file->fileable_id !== $this->id || $file->fileable_type !== static::class) {
            return false;
        }

        return $file->delete(); // Soft delete - keeps file in storage
    }

    /**
     * Permanently delete file record from database.
     * NOTE: This does NOT delete the file from storage (for audit/compliance).
     * To delete from storage, call $file->deleteFromStorage() explicitly.
     */
    public function permanentlyDeleteFileRecord(File $file): bool
    {
        if ($file->fileable_id !== $this->id || $file->fileable_type !== static::class) {
            return false;
        }

        return $file->forceDelete(); // Deletes DB record only, keeps file in storage
    }

    /**
     * Permanently delete file record AND the physical file from storage.
     * Use with caution - this cannot be undone.
     */
    public function permanentlyDeleteFileAndStorage(File $file, ?string $disk = null): bool
    {
        if ($file->fileable_id !== $this->id || $file->fileable_type !== static::class) {
            return false;
        }

        // Delete from storage first
        $file->deleteFromStorage($disk ?? config('filesystems.default'));

        // Then delete record
        return $file->forceDelete();
    }

    /**
     * Get the storage path for this entity's files.
     * Override this method in your model to customize the path.
     */
    protected function getFileStoragePath(FileType $fileType): string
    {
        $modelName = class_basename($this);

        return strtolower($modelName).'/'.$this->id.'/'.$fileType->value;
    }
}
