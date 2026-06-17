// Auto-detect whether a document is English or Gujarati.
//
// English (Latin) and Gujarati live in completely separate Unicode blocks,
// so we don't need a fuzzy ML language detector — we just read the actual
// characters and pick whichever script dominates. For mixed-language content
// the script with the most characters wins (the "mostly used" language).
//
// PDFs: text is read from the embedded text layer via pdf.js (reliable).
// Images: there is no text layer, so we OCR them with Tesseract.js (eng+guj).

// Gujarati Unicode block: U+0A80 – U+0AFF
const GUJARATI_RE = /[઀-૿]/g;
// Basic Latin letters (English)
const LATIN_RE = /[A-Za-z]/g;

/** How many real Gujarati Unicode characters are in the text. */
export function countGujarati(text) {
    return text ? (text.match(GUJARATI_RE) || []).length : 0;
}

/**
 * Decide the dominant language from a chunk of text.
 * @returns {'gu'|'en'|null} null when there isn't enough text to be sure.
 */
export function detectScriptFromText(text) {
    if (!text) return null;

    const gujaratiCount = (text.match(GUJARATI_RE) || []).length;
    const latinCount = (text.match(LATIN_RE) || []).length;

    // Not enough letters of either script to make a confident call.
    if (gujaratiCount + latinCount < 3) return null;

    // Mostly-used language wins. Ties fall back to Gujarati (the app default).
    return gujaratiCount >= latinCount ? 'gu' : 'en';
}

let _pdfjsLib = null;
async function loadPdfJs() {
    if (_pdfjsLib) return _pdfjsLib;
    _pdfjsLib = await import('pdfjs-dist/legacy/build/pdf.mjs');
    _pdfjsLib.GlobalWorkerOptions.workerSrc = '/pdf.worker.mjs';
    return _pdfjsLib;
}

/**
 * Pull the text layer out of a PDF File/Blob.
 * Scans up to `maxPages` pages — plenty to judge the language without
 * reading huge documents end to end.
 */
export async function extractPdfText(file, maxPages = 5) {
    const lib = await loadPdfJs();
    const buffer = await file.arrayBuffer();
    const loadingTask = lib.getDocument({ data: buffer });
    const pdf = await loadingTask.promise;

    try {
        const pageCount = Math.min(pdf.numPages, maxPages);
        let text = '';
        for (let i = 1; i <= pageCount; i++) {
            const page = await pdf.getPage(i);
            const content = await page.getTextContent();
            text += ' ' + content.items.map((item) => item.str).join(' ');
        }
        return text;
    } finally {
        // Always release the worker/document, otherwise a leaked task can
        // break the *next* getDocument() call (e.g. on a re-upload).
        await loadingTask.destroy();
    }
}

/**
 * Render the first page of a PDF to a canvas so it can be OCR'd.
 * Used for scanned PDFs that have no usable text layer.
 */
async function renderPdfPageToCanvas(file, attempts = 3) {
    const lib = await loadPdfJs();
    const buffer = await file.arrayBuffer();
    const loadingTask = lib.getDocument({ data: buffer });
    const pdf = await loadingTask.promise;

    try {
        const page = await pdf.getPage(1);
        const viewport = page.getViewport({ scale: 2 }); // upscale for better OCR
        const canvas = document.createElement('canvas');
        canvas.width = viewport.width;
        canvas.height = viewport.height;
        const ctx = canvas.getContext('2d');

        // pdf.js can throw "Dependent image isn't ready yet" when a page's
        // embedded images haven't finished decoding. It's transient — the
        // objects resolve a moment later, so retry a couple of times.
        for (let i = 0; i < attempts; i++) {
            try {
                await page.render({ canvasContext: ctx, viewport }).promise;
                return canvas;
            } catch (err) {
                if (i === attempts - 1) throw err;
                console.debug('[languageDetect] pdf render retry', i + 1, err?.message);
                await new Promise((r) => setTimeout(r, 250));
            }
        }
    } finally {
        await loadingTask.destroy();
    }
}

/**
 * OCR an image File/Blob. Loaded lazily so Tesseract (and its language
 * models) are only fetched when an image is actually uploaded.
 */
export async function extractImageText(file) {
    const Tesseract = (await import('tesseract.js')).default;
    const { data } = await Tesseract.recognize(file, 'eng+guj');
    const text = data?.text || '';
    console.debug('[languageDetect] OCR text sample:', text.slice(0, 120));
    return text;
}

/**
 * Auto-detect the language of an uploaded document.
 * @returns {Promise<'gu'|'en'|null>} null when detection isn't possible.
 */
export async function detectDocumentLanguage(file) {
    if (!file) return null;

    const type = file.type || '';
    const name = (file.name || '').toLowerCase();
    const isPdf = type === 'application/pdf' || name.endsWith('.pdf');
    const isImage = type.startsWith('image/') || /\.(png|jpe?g|webp|gif)$/.test(name);

    try {
        if (isPdf) {
            const text = await extractPdfText(file);

            // A text layer that contains real Gujarati Unicode is trustworthy —
            // use it directly (fast path, no OCR needed).
            if (countGujarati(text) >= 3) return 'gu';

            // No Gujarati Unicode found. This is EITHER a genuine English PDF,
            // OR a Gujarati PDF built with a non-Unicode font (very common for
            // invitations) whose text layer extracts as garbage Latin. We can't
            // tell those apart from the text, so OCR the rendered page — that
            // reads the real glyphs regardless of how the font is encoded.
            const fromText = detectScriptFromText(text);
            const canvas = await renderPdfPageToCanvas(file).catch(() => null);
            if (canvas) {
                const ocr = detectScriptFromText(await extractImageText(canvas).catch(() => ''));
                if (ocr) return ocr;
            }
            return fromText;
        }

        if (isImage) {
            return detectScriptFromText(await extractImageText(file));
        }

        return null;
    } catch (e) {
        console.warn('[languageDetect] detection failed:', e);
        return null;
    }
}
