<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportSubject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    private const STATUS_CIVITAS_LABELS = [
        'mahasiswa' => 'Mahasiswa',
        'dosen' => 'Dosen',
        'tendik' => 'Tenaga Kependidikan',
    ];

    private const STATUS_TERLAPOR_LABELS = [
        'mahasiswa' => 'Mahasiswa',
        'dosen' => 'Dosen',
        'tendik' => 'Tenaga Kependidikan',
        'masyarakat_umum' => 'Masyarakat Umum',
        'tidak_diketahui' => 'Tidak Diketahui',
    ];

    private const GENDER_LABELS = [
        'laki-laki' => 'Laki-laki',
        'perempuan' => 'Perempuan',
    ];

    private const JENIS_KEKERASAN_LABELS = [
        'belum_yakin' => 'Saya belum yakin',
        'seksual' => 'Kekerasan Seksual',
        'perundungan' => 'Perundungan / Bullying',
        'fisik' => 'Kekerasan Fisik',
        'psikis' => 'Kekerasan Psikis',
        'diskriminasi' => 'Diskriminasi dan Intoleransi',
        'kebijakan' => 'Kebijakan yang mengandung kekerasan',
    ];

    public function index(Request $request): Response
    {
        $user = $request->user();

        if (!($user instanceof User)) {
            return Inertia::render('dashboard/Dashboard');
        }

        $period = $this->resolvePeriodFilter($request);
        $reportsInPeriod = $period['query'];
        $reportIdsInPeriod = (clone $reportsInPeriod)->pluck('id');

        $trend = $this->getMonthlyTrend($period['trendYear']);
        $laporanStats = $this->getLaporanStatusStats($reportsInPeriod);
        $jenisKekerasan = $this->getJenisKekerasanDistribution($reportsInPeriod);

        $pelapor = $this->getPelaporDemografi($reportIdsInPeriod);
        $korban = $this->getSubjectDemografi($reportIdsInPeriod, 'korban');
        $terlapor = $this->getSubjectDemografi($reportIdsInPeriod, 'terlapor');

        return Inertia::render('dashboard/Dashboard', [
            'analytics' => [
                'filter' => [
                    'year' => $period['year'],
                    'semester' => $period['semester'],
                    'availableYears' => $this->getAvailableYears(),
                ],
                'trend' => $trend,
                'laporan' => $laporanStats,
                'jenisKekerasan' => $jenisKekerasan,
                'demografi' => [
                    'pelaporPeran' => $pelapor['peran'],
                    'pelaporJurusan' => $pelapor['jurusan'],
                    'korbanPeranGender' => $korban['peranGender'],
                    'korbanJurusan' => $korban['jurusan'],
                    'terlaporPeranGender' => $terlapor['peranGender'],
                    'terlaporJurusan' => $terlapor['jurusan'],
                ],
                'disabilitas' => [
                    'korban' => $korban['disabilitas'],
                    'terlapor' => $terlapor['disabilitas'],
                ],
            ],
        ]);
    }

    private function resolvePeriodFilter(Request $request): array
    {
        $year = $request->query('year', 'all');
        $semester = $request->query('semester', 'all');

        $query = Report::query();

        if ($year !== 'all') {
            $query->whereBetween('created_at', $this->resolveYearDateRange($year, $semester));
        } elseif ($semester !== 'all') {
            $monthRange = $semester === '1' ? [1, 6] : [7, 12];
            $query->whereRaw('EXTRACT(MONTH FROM created_at) BETWEEN ? AND ?', $monthRange);
        }

        return [
            'year' => $year,
            'semester' => $semester,
            'query' => $query,
            'trendYear' => $year !== 'all' ? (int) $year : now()->year,
        ];
    }

    private function resolveYearDateRange(string $year, string $semester): array
    {
        return match ($semester) {
            '1' => ["{$year}-01-01 00:00:00", "{$year}-06-30 23:59:59"],
            '2' => ["{$year}-07-01 00:00:00", "{$year}-12-31 23:59:59"],
            default => ["{$year}-01-01 00:00:00", "{$year}-12-31 23:59:59"],
        };
    }

    private function getMonthlyTrend(int $year): Collection
    {
        $countPerMonth = Report::query()
            ->whereBetween('created_at', ["{$year}-01-01 00:00:00", "{$year}-12-31 23:59:59"])
            ->selectRaw("to_char(created_at, 'YYYY-MM') as bucket, count(*) as total")
            ->groupBy('bucket')
            ->pluck('total', 'bucket');

        return collect(range(1, 12))->map(function (int $month) use ($countPerMonth, $year) {
            $bucket = sprintf('%04d-%02d', $year, $month);

            return [
                'label' => Carbon::createFromDate($year, $month, 1)->format('M'),
                'total' => (int) ($countPerMonth[$bucket] ?? 0),
            ];
        });
    }

    private function getLaporanStatusStats(Builder $reportsInPeriod): array
    {
        $countPerStatus = (clone $reportsInPeriod)
            ->selectRaw('progress, count(*) as total')
            ->groupBy('progress')
            ->pluck('total', 'progress');

        $selesai = (int) ($countPerStatus['Selesai'] ?? 0);
        $laporanBaru = (int) ($countPerStatus['Laporan Baru'] ?? 0);
        $dibatalkan = (int) ($countPerStatus['Laporan Ditolak'] ?? 0)
            + (int) ($countPerStatus['Laporan Dihentikan'] ?? 0);
        $masuk = (int) $countPerStatus->sum();
        $berlangsung = $masuk - $selesai - $dibatalkan - $laporanBaru;

        return [
            'masuk' => $masuk,
            'dibatalkan' => $dibatalkan,
            'berlangsung' => $berlangsung,
            'selesai' => $selesai,
        ];
    }

    private function getJenisKekerasanDistribution(Builder $reportsInPeriod): Collection
    {
        return $this->mapLabels(
            $this->distribution(clone $reportsInPeriod, 'jenis_kekerasan'),
            self::JENIS_KEKERASAN_LABELS,
        );
    }

    private function getPelaporDemografi(Collection $reportIdsInPeriod): array
    {
        $baseQuery = fn () => Report::query()
            ->whereIn('reports.id', $reportIdsInPeriod)
            ->join('reporters', 'reporters.id', '=', 'reports.reporter_id');

        return [
            'peran' => $this->mapLabels(
                $this->distribution($baseQuery(), 'reporters.status_civitas'),
                self::STATUS_CIVITAS_LABELS,
            ),
            'jurusan' => $this->distribution($baseQuery(), 'reporters.jurusan'),
        ];
    }

    private function getSubjectDemografi(Collection $reportIdsInPeriod, string $jenis): array
    {
        $peranLabels = $jenis === 'korban' ? self::STATUS_CIVITAS_LABELS : self::STATUS_TERLAPOR_LABELS;

        $baseQuery = fn () => ReportSubject::query()
            ->where('jenis', $jenis)
            ->whereIn('report_id', $reportIdsInPeriod);

        $peranGenderRows = $baseQuery()
            ->selectRaw('peran_akademik, jenis_kelamin, count(*) as total')
            ->whereNotNull('peran_akademik')
            ->whereNotNull('jenis_kelamin')
            ->groupBy('peran_akademik', 'jenis_kelamin')
            ->get();

        return [
            'peranGender' => $this->buildPeranGenderCrosstab($peranGenderRows, $peranLabels),
            'jurusan' => $this->distribution($baseQuery(), 'jurusan'),
            'disabilitas' => $baseQuery()->where('disabilitas', true)->count(),
        ];
    }

    private function getAvailableYears(): Collection
    {
        return Report::query()
            ->selectRaw('DISTINCT EXTRACT(YEAR FROM created_at) as year')
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn ($value) => (int) $value);
    }


    private function distribution(Builder $query, string $column): Collection
    {
        return $query
            ->selectRaw("{$column} as label, count(*) as total")
            ->whereNotNull($column)
            ->groupBy($column)
            ->orderByDesc('total')
            ->get();
    }

    private function buildPeranGenderCrosstab(Collection $rows, array $peranLabels): array
    {
        $genders = array_values(self::GENDER_LABELS);

        $totalPerPeranGender = $rows->reduce(function (array $carry, $row) use ($peranLabels) {
            $peran = $peranLabels[$row->peran_akademik] ?? $row->peran_akademik;
            $gender = self::GENDER_LABELS[$row->jenis_kelamin] ?? $row->jenis_kelamin;
            $carry[$peran][$gender] = ($carry[$peran][$gender] ?? 0) + (int) $row->total;

            return $carry;
        }, []);

        $peranList = $rows
            ->map(fn ($row) => $peranLabels[$row->peran_akademik] ?? $row->peran_akademik)
            ->unique()
            ->values()
            ->sortByDesc(fn ($peran) => array_sum($totalPerPeranGender[$peran] ?? []))
            ->values();

        return [
            'labels' => $peranList->all(),
            'series' => array_map(
                fn ($gender) => [
                    'label' => $gender,
                    'data' => $peranList->map(fn ($peran) => $totalPerPeranGender[$peran][$gender] ?? 0)->all(),
                ],
                $genders,
            ),
        ];
    }

    private function mapLabels(Collection $rows, array $labels): Collection
    {
        return $rows
            ->map(fn ($row) => [
                'label' => $labels[$row->label] ?? $row->label,
                'total' => (int) $row->total,
            ])
            ->groupBy('label')
            ->map(fn (Collection $group, string $label) => [
                'label' => $label,
                'total' => $group->sum('total'),
            ])
            ->sortByDesc('total')
            ->values();
    }
}
