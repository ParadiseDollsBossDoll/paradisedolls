import './bootstrap';
import './admin-course-form';
import './application-form';
import './chatter-time-tracker';
import './community';
import './translator';

import Alpine from 'alpinejs';
import { pdfLessonViewer } from './pdf-viewer';
import { reviewUploadEditor } from './review-upload-editor';

window.Alpine = Alpine;

Alpine.data('pdfLessonViewer', pdfLessonViewer);
Alpine.data('reviewUploadEditor', reviewUploadEditor);

Alpine.start();
