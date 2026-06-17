import jsPDF from 'jspdf';
import { formatDate, today } from '@/lib/formatDate';

export const generateReporterInspection = (report: any, form: any) => {
    const pdf = new jsPDF({
        orientation: 'p',
        unit: 'mm',
        format: 'a4',
    });

    let y = 20;
    const pageWidth = pdf.internal.pageSize.width;
    const margin = 15;
    const contentWidth = pageWidth - margin * 2;

    const checkPageBounds = (neededHeight: number) => {
        if (y + neededHeight > 280) {
            pdf.addPage();
            y = 20;
            drawHeader();
        }
    };

    const drawHeader = () => {
        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(14);
        pdf.text(
            'SATUAN TUGAS PENCEGAHAN & PENANGANAN KEKERASAN',
            pageWidth / 2,
            y,
            { align: 'center' },
        );

        y += 6;
        pdf.setFontSize(11);
        pdf.setFont('helvetica', 'bold');
        pdf.text(
            'BERITA ACARA & NOTULENSI PEMERIKSAAN PELAPOR',
            pageWidth / 2,
            y,
            { align: 'center' },
        );

        y += 4;
        pdf.setLineWidth(0.6);
        pdf.line(margin, y, pageWidth - margin, y);
        pdf.setLineWidth(0.2);
        pdf.line(margin, y + 1, pageWidth - margin, y + 1);
        y += 8;
    };

    const drawSectionTitle = (title: string) => {
        checkPageBounds(12);
        pdf.setFillColor(245, 245, 245);
        pdf.rect(margin, y, contentWidth, 7, 'F');

        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(10);
        pdf.setTextColor(50, 50, 50);
        pdf.text(title, margin + 3, y + 5);

        pdf.setTextColor(0, 0, 0);
        y += 11;
    };

    const drawDataRow = (label: string, value: string, labelWidth = 55) => {
        checkPageBounds(7);
        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(10);
        pdf.text(label, margin + 2, y);

        pdf.setFont('helvetica', 'normal');
        pdf.text(':', margin + labelWidth - 3, y);

        const splitValue = pdf.splitTextToSize(
            value || '-',
            contentWidth - labelWidth,
        );
        pdf.text(splitValue, margin + labelWidth, y);

        y += splitValue.length * 5 + 2;
    };

    const drawMultiLineText = (text: string) => {
        if (text) {
            pdf.setFont('helvetica', 'normal');
            pdf.setFontSize(10);

            const splitNotes = pdf.splitTextToSize(text, contentWidth - 4);

            splitNotes.forEach((line: string) => {
                checkPageBounds(6);
                pdf.text(line, margin + 2, y);
                y += 5.5;
            });
        } else {
            drawDataRow('Keterangan', 'Tidak ada catatan yang diisi.');
        }
    };

    // Helper untuk mapping label
    const formatOptions = (
        selectedValues: string[],
        otherValue: string,
        optionsMap: Record<string, string>,
    ) => {
        if (!selectedValues || selectedValues.length === 0) return '-';
        return selectedValues
            .map((val) => {
                if (val === 'lainnya')
                    return otherValue ? `Lainnya (${otherValue})` : 'Lainnya';
                return optionsMap[val] || val;
            })
            .join(', ');
    };

    // Mapping berdasarkan konstanta options di file Vue Anda
    const alasanMap: Record<string, string> = {
        keadilan: 'Mencari keadilan',
        keberulangan: 'Mencegah keberulangan',
        trauma: 'Pemulihan trauma',
        sanksi: 'Sanksi terhadap pelaku',
        nama_baik: 'Pengembalian nama baik',
    };

    const kebutuhanMap: Record<string, string> = {
        keadilan: 'Pendampingan psikologis',
        keberulangan: 'Pendampingan hukum',
        trauma: 'Pemulihan akademik',
        sanksi: 'Perlindungan keamanan',
        nama_baik: 'Pemulihan nama baik / Mediasi',
    };

    drawHeader();

    drawSectionTitle('I. INFORMASI LAPORAN & KASUS');
    drawDataRow('Nomor Kasus', report.case_number);
    drawDataRow('Tanggal Masuk Laporan', formatDate(report.created_at, false));
    drawDataRow('Tanggal Pemeriksaan', today);
    drawDataRow(
        'Jenis Kekerasan',
        form.jenisKekerasan || form.pelapor.jenisKekerasan,
    );

    y += 3;

    drawSectionTitle('II. IDENTITAS PELAPOR');
    drawDataRow('Nama Lengkap', form.pelapor.nama);
    drawDataRow(
        'Status',
        form.pelapor.status === 'korban' ? 'Korban' : 'Saksi',
    );
    drawDataRow('Civitas/Peran', form.pelapor.civitas);
    drawDataRow('Nomor WhatsApp', form.pelapor.whatsapp);
    drawDataRow('Jurusan', form.pelapor.jurusan);
    drawDataRow('Program Studi', form.pelapor.prodi);
    drawDataRow('Domisili', form.pelapor.domisili);
    drawDataRow('Kontak Pihak Lain', form.pelapor.kontakLain);

    y += 3;

    drawSectionTitle('III. IDENTITAS TERLAPOR');
    if (form.terlapor && form.terlapor.nama) {
        drawDataRow('Nama Lengkap', form.terlapor.nama);
        drawDataRow('Status', form.terlapor.status);
        drawDataRow('Civitas/Peran', form.terlapor.civitas);
        drawDataRow('Jurusan', form.terlapor.jurusan);
        drawDataRow('Program Studi', form.terlapor.prodi);
    } else {
        drawDataRow('Keterangan', 'Identitas terlapor belum/tidak diisi.');
    }

    y += 3;

    drawSectionTitle('IV. KRONOLOGI PERISTIWA');
    drawMultiLineText(form.kronologi);
    y += 6;

    drawSectionTitle('V. CIRI FISIK PADA SAAT KEJADIAN');
    drawMultiLineText(form.ciriFisik);
    y += 6;

    drawSectionTitle('VI. ALASAN & KEBUTUHAN KORBAN');
    const alasanText = formatOptions(
        form.alasan,
        form.alasanLainnya,
        alasanMap,
    );
    const kebutuhanText = formatOptions(
        form.kebutuhanKorban,
        form.kebutuhanKorbanLainnya,
        kebutuhanMap,
    );

    drawDataRow('Alasan Pengaduan', alasanText);
    drawDataRow('Kebutuhan Korban', kebutuhanText);

    y += 6;

    drawSectionTitle('VII. TIM PENANGANAN KASUS');
    drawDataRow(
        'Nomor Tim Penyelidik',
        `Tim Kelompok Kerja Ke-${report.team_number || '-'}`,
    );

    if (report.members && report.members.length > 0) {
        const memberNames = report.members.map((m: any) => m.name).join(', ');
        drawDataRow('Anggota Pemeriksa', memberNames);
    }

    y += 10;

    checkPageBounds(35);
    y += 5;

    const signatureX = pageWidth - margin - 60;
    pdf.setFont('helvetica', 'normal');
    pdf.setFontSize(10);
    pdf.text('Dibuat & disahkan oleh,', signatureX, y);

    y += 20;
    pdf.setFont('helvetica', 'bold');
    pdf.text('Satgas Penanganan Kasus', signatureX, y);
    pdf.setLineWidth(0.3);
    pdf.line(signatureX, y + 1, signatureX + 50, y + 1);

    return pdf;
};
