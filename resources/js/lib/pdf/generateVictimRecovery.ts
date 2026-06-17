import jsPDF from 'jspdf';
import { formatDate, today } from '@/lib/formatDate';
import { Report } from '@/types/reports';

const bentukPemulihanMap: Record<string, string> = {
    konseling: 'Konseling Psikologis',
    pendampingan_akademik: 'Pendampingan Akademik',
    pendampingan_medis: 'Pendampingan Medis',
    perlindungan: 'Perlindungan Hukum',
    relokasi: 'Relokasi Kelas',
    pemulihan: 'Pemulihan Finansial',
};

export const generateVictimRecovery = (report: Report | any, form: any) => {
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
        pdf.text('DOKUMEN PEMULIHAN KORBAN', pageWidth / 2, y, {
            align: 'center',
        });

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

    const drawDataRow = (label: string, value: string, labelWidth = 65) => {
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

            // Memisahkan baris berdasarkan karakter newline agar format list tetap rapi
            const rawLines = text.split('\n');

            rawLines.forEach((rawLine) => {
                const splitNotes = pdf.splitTextToSize(
                    rawLine,
                    contentWidth - 4,
                );
                splitNotes.forEach((line: string) => {
                    checkPageBounds(6);
                    pdf.text(line, margin + 2, y);
                    y += 5.5;
                });
            });
        } else {
            drawDataRow('Keterangan', 'Tidak ada catatan yang diisi.', 30);
        }
    };

    const formatStatus = (status: string) => {
        if (status === 'terbukti') return 'Terbukti';
        if (status === 'tidakTerbukti') return 'Tidak Terbukti';
        return status || '-';
    };

    const getBentukPemulihanText = () => {
        if (!form.bentukPemulihan || form.bentukPemulihan.length === 0)
            return '-';

        const items = form.bentukPemulihan.map((val: string) => {
            if (val === 'lainnya')
                return `- Lainnya: ${form.bentukPemulihanLainnya}`;
            return `- ${bentukPemulihanMap[val] || val}`;
        });

        return items.join('\n');
    };

    // 1. Render Header
    drawHeader();

    // 2. Render Informasi Laporan
    drawSectionTitle('I. INFORMASI LAPORAN & KASUS');
    drawDataRow('Nomor Kasus', report.case_number);
    drawDataRow('Tanggal Masuk Laporan', formatDate(report.created_at, false));
    drawDataRow('Tanggal Dokumen', today);
    drawDataRow('Status Kasus', formatStatus(form.status));
    y += 3;

    // 3. Render Bentuk Pemulihan
    drawSectionTitle('II. BENTUK PEMULIHAN');
    drawMultiLineText(getBentukPemulihanText());
    y += 4;

    // 4. Render Pemulihan Akan Dilakukan
    drawSectionTitle('III. PEMULIHAN YANG AKAN DILAKUKAN');
    drawMultiLineText(form.pemulihanAkan);
    y += 6;

    // 5. Render Pemulihan Sudah Dilakukan
    drawSectionTitle('IV. PEMULIHAN YANG SUDAH DILAKUKAN');
    drawMultiLineText(form.pemulihanSudah);
    y += 6;

    // 6. Render Catatan Pemulihan
    drawSectionTitle('V. CATATAN PEMULIHAN KORBAN');
    drawMultiLineText(form.catatanPemulihan);
    y += 6;

    // 7. Render Hasil Pemantauan
    drawSectionTitle('VI. HASIL PEMANTAUAN PEMULIHAN');
    drawMultiLineText(form.hasilPemantauan);
    y += 6;

    // 8. Render Tim Penanganan
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

    // 9. Render Tanda Tangan Pengesahan
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
