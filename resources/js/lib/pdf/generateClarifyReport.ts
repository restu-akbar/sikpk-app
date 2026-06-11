import jsPDF from 'jspdf';
import { formatDate } from '@/lib/formatDate';

export const generateClarifyReport = (report: any, form: any) => {
    const pdf = new jsPDF();

    let y = 10;

    const addText = (text: string) => {
        pdf.text(text, 10, y);
        y += 7;
    };

    pdf.setFontSize(16);
    addText('LAPORAN KLARIFIKASI PELAPOR');

    pdf.setFontSize(11);
    y += 3;

    addText(`No Laporan: ${report.id}`);
    addText(`Tanggal Lapor: ${formatDate(report.created_at)}`);
    addText(`Tanggal Klarifikasi: ${new Date().toLocaleDateString()}`);

    y += 5;

    pdf.setFontSize(13);
    addText('IDENTITAS PELAPOR');

    pdf.setFontSize(11);
    addText(`Nama: ${form.nama}`);
    addText(`Status: ${form.statusPelapor}`);
    addText(`Civitas: ${form.statusCivitas}`);
    addText(`WhatsApp: ${form.whatsapp}`);
    addText(`Jurusan: ${form.jurusan}`);
    addText(`Prodi: ${form.prodi}`);

    y += 5;

    pdf.setFontSize(13);
    addText('JENIS KEKERASAN');
    pdf.setFontSize(11);
    addText(form.jenisKekerasan);

    y += 5;

    pdf.setFontSize(13);
    addText('NOTULENSI KLARIFIKASI');
    pdf.setFontSize(11);

    const splitText = pdf.splitTextToSize(form.catatanKlarifikasi, 180);
    pdf.text(splitText, 10, y);

    y += splitText.length * 6;

    // TIM
    y += 5;
    pdf.setFontSize(13);
    addText('TIM PENANGANAN');

    pdf.setFontSize(11);
    report.members.forEach((m: any) => {
        addText(`- ${m.name}`);
    });

    return pdf;
};
