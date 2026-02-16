<?php

namespace App\Http\Controllers;

use App\Enums\FileType;
use App\Models\Grant;
use App\Models\GrantDocument;
use App\Repositories\GrantDetailRepository;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadDocumentController extends Controller
{
    public function __invoke(Grant $grant, GrantDocument $grantDocument, GrantDetailRepository $repo): StreamedResponse
    {
        abort_unless($grantDocument->id_hibah === $grant->id, 404);
        abort_unless($repo->canView($grant, auth()->user()->unit), 403);

        $file = $grantDocument->getFirstFileByType(FileType::GeneratedDocument);
        abort_unless($file, 404);

        return Storage::download($file->path, $file->name);
    }
}
