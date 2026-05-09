const phoneDigitCount = (value) => value.replace(/\D/g, '').length;
const maxPhotoCount = 6;
const photoTypes = new Set(['image/jpeg', 'image/png', 'image/webp']);

const photoKey = (file) => `${file.name}-${file.size}-${file.lastModified}`;
const photoSize = (file) => {
    const sizeInMb = file.size / 1024 / 1024;

    if (sizeInMb >= 1) {
        return `${sizeInMb.toFixed(1)} MB`;
    }

    return `${Math.max(1, Math.round(file.size / 1024))} KB`;
};

const isAcceptedPhoto = (file) => {
    const extension = file.name.split('.').pop()?.toLowerCase();

    return photoTypes.has(file.type) || ['jpg', 'jpeg', 'png', 'webp'].includes(extension);
};

document.querySelectorAll('[data-application-form]').forEach((form) => {
    const country = form.querySelector('[data-phone-country]');
    const email = form.querySelector('[data-email-address]');
    const emailFeedback = form.querySelector('[data-email-feedback]');
    const number = form.querySelector('[data-phone-number]');
    const photoDropzone = form.querySelector('[data-photo-dropzone]');
    const photoError = form.querySelector('[data-photo-error]');
    const photoInput = form.querySelector('[data-photo-input]');
    const photoPreview = form.querySelector('[data-photo-preview]');
    const photoSummary = form.querySelector('[data-photo-summary]');
    let selectedPhotos = [];
    let previewUrls = [];

    if (!country || !email || !number) {
        return;
    }

    const showEmailFeedback = (message = '') => {
        if (!emailFeedback) {
            return;
        }

        emailFeedback.textContent = message;
        emailFeedback.classList.toggle('hidden', message === '');
    };

    const validateEmail = () => {
        const rawEmail = email.value.trim();
        const message = 'Please enter a valid email address, like name@example.com.';

        email.setCustomValidity('');
        showEmailFeedback();

        if (rawEmail === '') {
            return true;
        }

        if (!email.validity.valid) {
            email.setCustomValidity(message);
            showEmailFeedback(message);

            return false;
        }

        return true;
    };

    const validatePhone = () => {
        const rawNumber = number.value.trim();

        country.setCustomValidity('');
        number.setCustomValidity('');

        if (rawNumber === '') {
            return true;
        }

        if (!/^[0-9\s().-]+$/.test(rawNumber)) {
            number.setCustomValidity('Use digits, spaces, dashes, or parentheses for your phone number.');

            return false;
        }

        const digits = phoneDigitCount(rawNumber);

        if (digits < 6 || digits > 15) {
            number.setCustomValidity('Enter a valid phone number with 6 to 15 digits after the country code.');

            return false;
        }

        if (!country.value) {
            country.setCustomValidity('Choose a country code for your phone number.');

            return false;
        }

        return true;
    };

    const showPhotoError = (message = '') => {
        if (!photoError) {
            return;
        }

        photoError.textContent = message;
        photoError.classList.toggle('hidden', message === '');
    };

    const syncPhotoInput = () => {
        if (!photoInput || typeof DataTransfer === 'undefined') {
            return;
        }

        const transfer = new DataTransfer();
        selectedPhotos.forEach((file) => transfer.items.add(file));
        photoInput.files = transfer.files;
    };

    const renderPhotoPreviews = () => {
        if (!photoPreview || !photoSummary) {
            return;
        }

        previewUrls.forEach((url) => URL.revokeObjectURL(url));
        previewUrls = [];
        photoPreview.replaceChildren();

        if (selectedPhotos.length === 0) {
            photoSummary.textContent = 'No photos selected';
            return;
        }

        photoSummary.textContent = selectedPhotos.length === 1
            ? '1 photo selected'
            : `${selectedPhotos.length} photos selected`;

        selectedPhotos.forEach((file, index) => {
            const url = URL.createObjectURL(file);
            previewUrls.push(url);

            const item = document.createElement('div');
            item.className = 'flex items-center gap-3 border border-boss-pink bg-white p-2 shadow-sm';

            const image = document.createElement('img');
            image.className = 'h-16 w-16 shrink-0 object-cover';
            image.src = url;
            image.alt = file.name;

            const details = document.createElement('div');
            details.className = 'min-w-0 flex-1';

            const name = document.createElement('p');
            name.className = 'truncate text-[0.82rem] font-medium text-boss-dark';
            name.textContent = file.name;

            const size = document.createElement('p');
            size.className = 'mt-1 text-[0.7rem] text-boss-dark/42';
            size.textContent = photoSize(file);

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'shrink-0 px-2 py-1 text-[0.68rem] uppercase tracking-[0.12em] text-red-600 transition-colors hover:bg-red-50';
            remove.textContent = 'Remove';
            remove.addEventListener('click', () => {
                selectedPhotos = selectedPhotos.filter((_, photoIndex) => photoIndex !== index);
                showPhotoError();
                syncPhotoInput();
                renderPhotoPreviews();
            });

            details.append(name, size);
            item.append(image, details, remove);
            photoPreview.append(item);
        });
    };

    const addPhotos = (files) => {
        if (!files || !photoInput) {
            return;
        }

        showPhotoError();

        const incoming = Array.from(files);
        const existingKeys = new Set(selectedPhotos.map(photoKey));
        const accepted = [];

        for (const file of incoming) {
            if (!isAcceptedPhoto(file)) {
                showPhotoError('Only JPG, PNG, or WEBP photos can be uploaded.');
                continue;
            }

            const key = photoKey(file);

            if (existingKeys.has(key)) {
                continue;
            }

            accepted.push(file);
            existingKeys.add(key);
        }

        if (selectedPhotos.length + accepted.length > maxPhotoCount) {
            showPhotoError(`Upload up to ${maxPhotoCount} photos. Remove a photo before adding more.`);
            selectedPhotos = selectedPhotos.concat(accepted).slice(0, maxPhotoCount);
        } else {
            selectedPhotos = selectedPhotos.concat(accepted);
        }

        syncPhotoInput();
        renderPhotoPreviews();
    };

    country.addEventListener('change', validatePhone);
    email.addEventListener('input', validateEmail);
    email.addEventListener('invalid', validateEmail);
    number.addEventListener('input', validatePhone);

    if (photoInput && photoDropzone) {
        photoInput.addEventListener('change', () => {
            addPhotos(photoInput.files);
        });

        ['dragenter', 'dragover'].forEach((eventName) => {
            photoDropzone.addEventListener(eventName, (event) => {
                event.preventDefault();
                photoDropzone.classList.add('border-boss-gold', 'bg-boss-cream');
            });
        });

        ['dragleave', 'drop'].forEach((eventName) => {
            photoDropzone.addEventListener(eventName, () => {
                photoDropzone.classList.remove('border-boss-gold', 'bg-boss-cream');
            });
        });

        photoDropzone.addEventListener('drop', (event) => {
            event.preventDefault();
            addPhotos(event.dataTransfer.files);
        });
    }

    form.addEventListener('submit', (event) => {
        const emailIsValid = validateEmail();
        const phoneIsValid = validatePhone();

        if (!emailIsValid || !phoneIsValid) {
            event.preventDefault();
            form.reportValidity();
        }
    });
});
