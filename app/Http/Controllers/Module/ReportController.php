<?php

namespace App\Http\Controllers\Module;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ReportService;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReportController extends Controller
{
    public function __construct(
        protected UserService $userService,
        protected ReportService $reportService
    ) {}

    public function index()
    {
        return Inertia::render('module/reports/Index', [
            'rows' => $this->reportService->index()
        ]);
    }

    public function create()
    {
        return Inertia::render('module/reports/Create');
    }



    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama' => ['required', 'string', 'max:255'],
                'whatsapp' => ['required', 'string', 'max:30'],
                'statusPelapor' => ['required', 'string'],
                'statusCivitas' => ['required', 'string'],
                'namaTerlapor' => ['required', 'string', 'max:255'],
                'statusTerlapor' => ['required', 'string'],
                'jenisKekerasan' => ['required', 'string'],
                'tempatKejadian' => ['required', 'string'],
                'waktuKejadian' => ['required', 'date'],
                'kronologi' => ['required', 'string'],
                'agreed' => ['accepted'],
                'disabilitas' => ['required', 'array', 'min:1'],

                'bukti' => ['nullable', 'array'],
                'bukti.*.file' => ['required'],
                'bukti.*.edeks' => ['required', 'string'],
            ]);
        } catch (ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first();

            return back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('toast', [
                    'type' => 'error',
                    'message' => $firstError ?? 'Silakan periksa kembali form Anda.',
                ]);
        }
        $this->reportService->store($validated, $request);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Laporan anda berhasil disimpan',
        ]);
    }

    public function update(
        Request $request,
        User $user
    ) {
        $validated = $request->validate([
            'name' => ['required'],
            'email' => [
                'required',
                'email',
                Rule::unique('users')
                    ->ignore($user->id),
            ]
        ]);

        $this->userService->updateAnggota(
            $user,
            $validated
        );

        return back()->with(
            'success',
            'User berhasil diupdate',
        );
    }

    public function destroy(User $user)
    {
        $this->userService->deleteAnggota($user);

        return back()->with(
            'success',
            'User berhasil dihapus',
        );
    }
}
