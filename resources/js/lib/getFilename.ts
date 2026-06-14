export function getFileName(filename: string): string {
    return filename.replace(/\.[^/.]+$/, '');
}
