import test from 'node:test';
import assert from 'node:assert/strict';
import { reviewUploadEditor, reviewFileError } from '../resources/js/review-upload-editor.js';

test('review images retain the existing type and 5 MB limit', () => {
    assert.equal(reviewFileError({ name: 'review.JPG', size: 5 * 1024 * 1024 }), '');
    assert.match(reviewFileError({ name: 'review.svg', size: 200 }), /JPG/);
    assert.match(reviewFileError({ name: 'review.png', size: 5 * 1024 * 1024 + 1 }), /5 MB/);
});

test('one selection creates separate rows and file inputs without replacing earlier rows', async (t) => {
    const previous = globalThis.DataTransfer;
    globalThis.DataTransfer = class {
        files = [];
        items = { add: (file) => this.files.push(file) };
    };
    t.after(() => { globalThis.DataTransfer = previous; });
    const inputs = new Map();
    const editor = reviewUploadEditor(11);
    editor.$nextTick = async () => {};
    editor.$root = { querySelector: (selector) => {
        if (!inputs.has(selector)) inputs.set(selector, { files: [] });
        return inputs.get(selector);
    } };
    const first = ['one.jpg', 'two.png', 'three.webp'].map((name) => ({ name, size: 100 }));
    await editor.addFiles(first);
    await editor.addFiles([{ name: 'four.jpeg', size: 100 }, { name: 'unsafe.svg', size: 100 }]);
    assert.deepEqual(editor.addedReviews, [11, 12, 13, 14]);
    assert.equal(editor.nextIndex, 15);
    assert.equal(inputs.get('#review-image-11').files[0], first[0]);
    assert.equal(inputs.get('#review-image-14').files[0].name, 'four.jpeg');
    assert.match(editor.error, /unsafe.svg/);
});

test('an empty selection does not create a row', async () => {
    const editor = reviewUploadEditor(11);
    await editor.addFiles([]);
    assert.deepEqual(editor.addedReviews, []);
    assert.equal(editor.nextIndex, 11);
});
