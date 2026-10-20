import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { createRequire } from 'node:module';
import path from 'node:path';
import test from 'node:test';

test('PDF viewer is locked to a patched PDF.js release', () => {
    const lock = JSON.parse(readFileSync(new URL('../package-lock.json', import.meta.url)));
    const version = lock.packages['node_modules/pdfjs-dist'].version;
    const [major, minor, patch] = version.split('.').map(Number);
    assert.ok(major > 6 || (major === 6 && (minor > 2 || (minor === 2 && patch >= 108))), version);
});

test('upgraded PDF.js parses text and renders a nonblank page with the viewer API', async () => {
    const require = createRequire(import.meta.url);
    const pdfRequire = createRequire(require.resolve('pdfjs-dist/package.json'));
    const { createCanvas } = pdfRequire('@napi-rs/canvas');
    const pdfjs = await import('pdfjs-dist/legacy/build/pdf.mjs');
    const stream = '0 0 1 rg 10 10 80 80 re f\nBT /F1 12 Tf 10 110 Td (PDF smoke test) Tj ET\n';
    const objects = [
        '<< /Type /Catalog /Pages 2 0 R >>',
        '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
        '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 200 160] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>',
        `<< /Length ${Buffer.byteLength(stream)} >>\nstream\n${stream}endstream`,
        '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
    ];
    let pdf = '%PDF-1.4\n';
    const offsets = [0];
    objects.forEach((object, index) => {
        offsets.push(Buffer.byteLength(pdf));
        pdf += `${index + 1} 0 obj\n${object}\nendobj\n`;
    });
    const xref = Buffer.byteLength(pdf);
    pdf += `xref\n0 6\n0000000000 65535 f \n${offsets.slice(1).map(offset => `${String(offset).padStart(10, '0')} 00000 n \n`).join('')}`;
    pdf += `trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n${xref}\n%%EOF\n`;

    const task = pdfjs.getDocument({
        data: new Uint8Array(Buffer.from(pdf)),
        standardFontDataUrl: path.join(path.dirname(require.resolve('pdfjs-dist/package.json')), 'standard_fonts') + '/',
        verbosity: 0,
    });
    try {
        const document = await task.promise;
        assert.equal(document.numPages, 1);
        const page = await document.getPage(1);
        const text = await page.getTextContent();
        assert.ok(text.items.some(item => item.str === 'PDF smoke test'));
        const viewport = page.getViewport({ scale: 1 });
        const canvas = createCanvas(viewport.width, viewport.height);
        const context = canvas.getContext('2d');
        await page.render({ canvas: null, canvasContext: context, viewport }).promise;
        const pixel = context.getImageData(40, 110, 1, 1).data;
        assert.ok(pixel[2] > 200 && pixel[0] < 50 && pixel[1] < 50, `Expected blue PDF content: ${pixel}`);
    } finally {
        await task.destroy();
    }
});
