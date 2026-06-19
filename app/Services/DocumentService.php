<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DocumentService
{
    public function upload(UploadedFile $file, string $entityType, int $entityId, ?string $description = null, ?int $userId = null): Document
    {
        $path = $file->store("documents/{$entityType}/{$entityId}", 'public');

        return Document::create([
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'file_name'   => $file->getClientOriginalName(),
            'file_path'   => $path,
            'file_type'   => $file->getMimeType(),
            'file_size'   => $file->getSize(),
            'description' => $description,
            'created_by'  => $userId ?? auth()->id(),
        ]);
    }

    public function listFor(string $entityType, int $entityId): array
    {
        return Document::where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->orderByDesc('created_at')
            ->get()
            ->all();
    }

    public function delete(int $id): bool
    {
        $doc = Document::find($id);
        if (! $doc) return false;
        Storage::disk('public')->delete($doc->file_path);
        return (bool) $doc->delete();
    }
}
