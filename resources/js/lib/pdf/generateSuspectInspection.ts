import jsPDF from 'jspdf';
import { formatDate, today } from '@/lib/formatDate';

export const generateSuspectInspection = (report: any, form: any) => {
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
            'BERITA ACARA & NOTULENSI PEMERIKSAAN TERLAPOR',
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

    drawHeader();

    drawSectionTitle('I. INFORMASI LAPORAN & KASUS');
    drawDataRow('Nomor Kasus', report.case_number);
    drawDataRow('Tanggal Masuk Laporan', formatDate(report.created_at, false));
    drawDataRow('Tanggal Pemeriksaan', today);
    drawDataRow('Jenis Kekerasan', form.terlapor.jenisKekerasan);

    y += 3;

    drawSectionTitle('II. IDENTITAS TERLAPOR');
    drawDataRow('Nama Lengkap', form.terlapor.nama);
    drawDataRow('Jenis Kelamin', form.terlapor.status);
    drawDataRow('Civitas/Peran', form.terlapor.civitas);
    drawDataRow('Nomor WhatsApp', form.terlapor.whatsapp);
    drawDataRow('Jurusan', form.terlapor.jurusan);
    drawDataRow('Program Studi', form.terlapor.prodi);
    drawDataRow('Domisili', form.terlapor.domisili);
    drawDataRow('Kontak Pihak Lain', form.terlapor.kontakLain);

    y += 3;

    drawSectionTitle('III. KRONOLOGI PERISTIWA (VERSI TERLAPOR)');
    drawMultiLineText(form.kronologi);
    y += 6;

    drawSectionTitle('IV. PIHAK YANG TELAH DIHUBUNGI');
    drawMultiLineText(form.pihakDihubungi);
    y += 6;

    drawSectionTitle('V. KETERLIBATAN PIHAK LAIN');
    drawMultiLineText(form.pihakLain);
    y += 6;

    drawSectionTitle('VI. TIM PENANGANAN KASUS');
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
