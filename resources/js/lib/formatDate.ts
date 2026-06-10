export const formatDate = (date: string, withTime: boolean = true) => {
    return new Date(date).toLocaleString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        ...(withTime && {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false,
        }),
    });
};
