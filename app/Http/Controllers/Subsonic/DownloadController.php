<?php

namespace App\Http\Controllers\Subsonic;

use App\Exceptions\PreviewUnavailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Subsonic\IdRequest;
use App\Repositories\SongRepository;
use App\Services\DownloadService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DownloadController extends Controller
{
    public function __construct(
        private readonly SongRepository $songRepository,
        private readonly DownloadService $downloadService,
    ) {}

    public function __invoke(IdRequest $request)
    {
        $song = $this->songRepository->getOne($request->id);
        $this->authorize('access', $song);
        throw_if($request->user()->cannot('listen', $song), PreviewUnavailableException::class);

        return $this->downloadService->getDownloadable($song)?->toResponse() ?? throw new ModelNotFoundException();
    }
}
