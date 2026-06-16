<?php

namespace App\Http\Controllers;

use App\Services\FileService;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function __construct(
        protected FileService $fileService
    ) {
    }

    public function show(Request $request, String $document)
    {
        return $this->fileService->show($request->type, $document);
    }
}
