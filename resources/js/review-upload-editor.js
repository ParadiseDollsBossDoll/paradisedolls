export function reviewFileError(file) {
    if (!/\.(jpe?g|png|webp)$/i.test(file.name)) return 'Use JPG, PNG or WEBP images.';
    if (file.size > 5 * 1024 * 1024) return 'Each image must be 5 MB or smaller.';
    return '';
}

export function reviewUploadEditor(nextIndex) {
    return {
        addedReviews: [],
        nextIndex,
        dragging: false,
        error: '',
        async addFiles(files) {
            const pending = [];
            const errors = [];
            for (const file of Array.from(files ?? [])) {
                const error = reviewFileError(file);
                if (error) {
                    errors.push(`${file.name}: ${error}`);
                    continue;
                }
                const index = this.nextIndex++;
                this.addedReviews.push(index);
                pending.push({ index, file });
            }
            await this.$nextTick?.();
            for (const { index, file } of pending) {
                try {
                    const transfer = new DataTransfer();
                    transfer.items.add(file);
                    this.$root.querySelector(`#review-image-${index}`).files = transfer.files;
                } catch {
                    errors.push(`${file.name}: Please select this photo using its new review row below.`);
                }
            }
            this.error = errors.join(' ');
        },
    };
}
