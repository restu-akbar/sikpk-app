<?php

namespace App\Http\Controllers;

use App\Models\ReportEvidence;

class EvidenceController extends Controller
{
    public function show(ReportEvidence $evidence)
    {
        return response()->file(
            storage_path('app/private/' . $evidence->path)
        );
    }
}
