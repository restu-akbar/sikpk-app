<?php

namespace App\Http\Controllers\Module;

use App\Helpers\Toast;
use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use App\Services\ReportService;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\UserService;
use Illuminate\Validation\ValidationException;

class ReportHandlingController extends Controller
{
    public function __construct(
        protected UserService $userService,
        protected ReportService $reportService
    ) {
    }

    public function data()
    {
        return $this->reportService->index(false);
    }

    public function show($id)
    {
        $report = $this->reportService->show(
            $id,
            ['reportLogs', 'reporter', 'handlers', 'reportDocuments.attachments', 'reportEvidences', 'audioRecordings', 'korbans', 'terlapors'],
            true
        );

        $report->has_notulensi = $report->reportDocuments->contains('subtype', 'notulensi');
        $report->korban = $report->korbans->first();
        $report->terlapor = $report->terlapors->first();
        unset($report->korbans, $report->terlapors);

        $orderedIds = DB::table('report_handlers')
            ->where('report_id', $id)
            ->pluck('user_id');

        $handlersById = $report->handlers->keyBy('id');

        $report->members = $orderedIds
            ->filter(fn ($uid) => $handlersById->has($uid))
            ->map(fn ($uid) => $handlersById->get($uid))
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'academic_role' => $user->academic_role,
                    'department' => $user->department,
                ];
            })->values();

        unset($report->handlers);
        return Inertia::render('satgas/reports/handling/ShowReport', [
            'report' => $report,
        ]);
    }

    public function index()
    {
        $reports = $this->reportService->index(false, ['handlers', 'reporter'], true, [], Report::ARCHIVED_PROGRESS);

        $reports = $reports->map(function ($report) {

            return [
                'id' => $report->id,
                'caseNumber' => $report->case_number ?? $report->id,
                'title' => $report->jenis_kekerasan,
                'status' => $report->status ?? $report->progress,
                'reportDate' => $report->created_at,
                'reporter' => $report->reporter?->name,
                'progress' => $report->progress,
                'teamNumber' => $report->team_number,

                'members' => $report->handlers->map(function ($user) {
                    return [
                        'name' => $user->name,
                        'initials' => collect(explode(' ', $user->name))
                            ->map(fn ($n) => strtoupper(substr($n, 0, 1)))
                            ->join(''),
                    ];
                })->values(),
            ];
        });
        return Inertia::render('satgas/reports/handling/Index', [
            'reports' => $reports,
        ]);
    }
}
