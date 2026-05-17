import * as pdfjsLib from 'pdfjs-dist';

pdfjsLib.GlobalWorkerOptions.workerSrc = new URL(
    'pdfjs-dist/build/pdf.worker.min.mjs',
    import.meta.url,
).toString();

export function pdfLessonViewer(pdfUrl) {
    // _pdfDoc lives in the closure. Alpine's reactive Proxy never wraps it.
    // pdf.js class instances use native private class fields, which do not
    // tolerate proxy-wrapped method calls.
    let _pdfDoc = null;
    let _resizeObserver = null;
    let _resizeTimer = null;
    let _renderToken = 0;
    let _needsRerender = false;
    let _lastWidth = 0;

    return {
        totalPages: 0,
        loading: true,
        error: null,
        rendering: false,

        async init() {
            if (!pdfUrl) {
                this.loading = false;
                return;
            }

            try {
                const task = pdfjsLib.getDocument({ url: pdfUrl, verbosity: 0 });
                _pdfDoc = await task.promise;
                this.totalPages = _pdfDoc.numPages;
                this.loading = false;

                await this.$nextTick();
                this._watchResize();
                await this.renderAllPages();
            } catch (err) {
                console.error('[PdfViewer] init:', err);
                this.error = err.message || 'Could not load this PDF.';
                this.loading = false;
            }
        },

        destroy() {
            if (_resizeObserver) _resizeObserver.disconnect();
            if (_resizeTimer) clearTimeout(_resizeTimer);
            _renderToken++;
        },

        _watchResize() {
            if (!this.$refs.wrap || typeof ResizeObserver === 'undefined') return;

            _resizeObserver = new ResizeObserver(() => {
                if (_resizeTimer) clearTimeout(_resizeTimer);

                _resizeTimer = setTimeout(() => {
                    const width = this.$refs.wrap?.clientWidth || 0;
                    if (!width || Math.abs(width - _lastWidth) < 24) return;
                    this.renderAllPages();
                }, 180);
            });

            _resizeObserver.observe(this.$refs.wrap);
        },

        async renderAllPages() {
            if (!_pdfDoc || !this.$refs.pages || !this.$refs.wrap) return;

            if (this.rendering) {
                _needsRerender = true;
                return;
            }

            const token = ++_renderToken;
            this.rendering = true;
            this.error = null;

            try {
                const pagesRoot = this.$refs.pages;
                const wrapWidth = this.$refs.wrap.clientWidth || this.$el.clientWidth || 760;
                const available = Math.max(wrapWidth - 32, 240);
                const pixelRatio = Math.min(window.devicePixelRatio || 1, 2);

                _lastWidth = wrapWidth;
                pagesRoot.replaceChildren();

                for (let pageNum = 1; pageNum <= _pdfDoc.numPages; pageNum++) {
                    if (token !== _renderToken) return;

                    const page = await _pdfDoc.getPage(pageNum);
                    const baseViewport = page.getViewport({ scale: 1 });
                    const scale = available >= baseViewport.width
                        ? Math.min(1.35, available / baseViewport.width)
                        : available / baseViewport.width;
                    const viewport = page.getViewport({ scale });

                    const canvas = document.createElement('canvas');
                    const context = canvas.getContext('2d');
                    if (!context) throw new Error('2D context unavailable');

                    const width = Math.floor(viewport.width);
                    const height = Math.floor(viewport.height);

                    canvas.width = Math.floor(width * pixelRatio);
                    canvas.height = Math.floor(height * pixelRatio);
                    canvas.style.width = `${width}px`;
                    canvas.style.height = `${height}px`;
                    canvas.className = 'block max-w-full rounded-sm shadow-2xl shadow-black/60';
                    canvas.setAttribute('aria-label', `PDF page ${pageNum} of ${_pdfDoc.numPages}`);

                    context.setTransform(pixelRatio, 0, 0, pixelRatio, 0, 0);

                    const pageWrap = document.createElement('div');
                    pageWrap.className = 'flex w-full justify-center';
                    pageWrap.appendChild(canvas);
                    pagesRoot.appendChild(pageWrap);

                    await page.render({ canvasContext: context, viewport }).promise;
                }
            } catch (err) {
                if (token === _renderToken) {
                    console.error('[PdfViewer] render:', err);
                    this.error = 'PDF render failed - ' + (err.message || 'unknown');
                }
            } finally {
                if (token === _renderToken) {
                    this.rendering = false;

                    if (_needsRerender) {
                        _needsRerender = false;
                        await this.renderAllPages();
                    }
                }
            }
        },
    };
}
