<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
</head>

<body style="font-family: Arial, Helvetica, sans-serif; color:#333; line-height:1.6">

    <p>Halo {{ $user->name }},</p>

    @if ($isKetua)
        <p>
            Telah masuk <strong>laporan baru</strong> pada SIKPK.
            Mohon segera melakukan peninjauan dan menentukan tindak lanjut yang diperlukan.
        </p>
    @else
        <p>
            Telah masuk <strong>laporan baru</strong> pada SIKPK.
            Mohon segera menginformasikan kepada Ketua Satgas agar laporan dapat segera ditindaklanjuti.
        </p>
    @endif

    <table cellpadding="6" cellspacing="0">
        <tr>
            <td><strong>Nomor Laporan</strong></td>
            <td>{{ $report->case_number }}</td>
        </tr>
        <tr>
            <td><strong>Tanggal</strong></td>
            <td>{{ $report->created_at->format('d M Y H:i') }}</td>
        </tr>
    </table>

    <p style="margin-top:24px;">
        <a href="{{ $loginUrl }}"
            style="background:#2563eb;color:#fff;text-decoration:none;padding:12px 18px;border-radius:6px;">
            Login ke Sistem
        </a>
    </p>

    <p>Atau buka tautan berikut:</p>

    <p>{{ $loginUrl }}</p>

    <p>Terima kasih.</p>

</body>

</html>
