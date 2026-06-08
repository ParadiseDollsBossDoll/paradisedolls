import './bootstrap';
import './admin-course-form';
import './application-form';
import './community';
import './translator';

import Alpine from 'alpinejs';
import { pdfLessonViewer } from './pdf-viewer';

window.Alpine = Alpine;

Alpine.data('pdfLessonViewer', pdfLessonViewer);

Alpine.start();
