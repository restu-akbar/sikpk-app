export function readRecoveryFile(file: File): Promise<string> {
    return new Promise((resolve, reject) => {
        if (!file.name.endsWith('.txt')) {
            reject(new Error('File harus berformat .txt'));
            return;
        }

        const reader = new FileReader();

        reader.onload = (e) => {
            const text = (e.target?.result as string).trim();

            if (!text) {
                reject(new Error('File recovery kosong'));

                return;
            }

            resolve(text);
        };

        reader.onerror = () => reject(new Error('Gagal membaca file'));
        reader.readAsText(file);
    });
}
