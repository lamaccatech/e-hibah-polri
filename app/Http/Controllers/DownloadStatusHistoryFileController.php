<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Grant;
use App\Models\GrantStatusHistory;
use App\Repositories\GrantDetailRepository;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadStatusHistoryFileController extends Controller
{
    public function __invoke(Grant $grant, File $file, GrantDetailRepository $repo): StreamedResponse
    {
        abort_unless($repo->canView($grant, auth()->user()->unit), 403);

        abort_unless(
            $file->fileable_type === GrantStatusHistory::class
            && $file->fileable?->id_hibah === $grant->id,
            404,
        );

        return Storage::download($file->path, $file->name);
    }
}
