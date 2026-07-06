const STORAGE_KEY = 'paradise_dolls_language';
const SKIPPED_TAGS = new Set(['SCRIPT', 'STYLE', 'NOSCRIPT', 'TEMPLATE', 'SVG', 'CANVAS', 'IFRAME', 'VIDEO', 'AUDIO']);
const SKIPPED_CONTROL_TAGS = new Set(['INPUT', 'TEXTAREA', 'SELECT']);
const TRANSLATABLE_ATTRIBUTES = ['placeholder', 'aria-label', 'title', 'alt'];
const BATCH_SIZE = 80;
const FLAG_CDN_BASE = 'https://flagcdn.com/w40/';
const LANGUAGE_FLAG_COUNTRIES = {
    ab: 'ge',
    ace: 'id',
    ach: 'ug',
    af: 'za',
    ak: 'gh',
    alz: 'ug',
    am: 'et',
    ar: 'sa',
    as: 'in',
    awa: 'in',
    ay: 'bo',
    az: 'az',
    ba: 'ru',
    ban: 'id',
    bbc: 'id',
    be: 'by',
    bem: 'zm',
    bew: 'id',
    bg: 'bg',
    bho: 'in',
    bik: 'ph',
    bm: 'ml',
    bn: 'bd',
    br: 'fr',
    bs: 'ba',
    bts: 'id',
    btx: 'id',
    bua: 'ru',
    ca: 'es',
    ceb: 'ph',
    cgg: 'ug',
    chm: 'ru',
    ckb: 'iq',
    cnh: 'mm',
    co: 'fr',
    crh: 'ua',
    crs: 'sc',
    cs: 'cz',
    cv: 'ru',
    cy: 'gb',
    da: 'dk',
    de: 'de',
    din: 'ss',
    doi: 'in',
    dov: 'zw',
    dv: 'mv',
    dz: 'bt',
    ee: 'gh',
    el: 'gr',
    en: 'gb',
    eo: 'pl',
    es: 'es',
    et: 'ee',
    eu: 'es',
    fa: 'ir',
    ff: 'sn',
    fi: 'fi',
    fj: 'fj',
    fr: 'fr',
    fy: 'nl',
    ga: 'ie',
    gaa: 'gh',
    gd: 'gb',
    gl: 'es',
    gn: 'py',
    gom: 'in',
    gu: 'in',
    ha: 'ng',
    haw: 'us',
    he: 'il',
    hi: 'in',
    hil: 'ph',
    hmn: 'la',
    hr: 'hr',
    hrx: 'br',
    ht: 'ht',
    hu: 'hu',
    hy: 'am',
    id: 'id',
    ig: 'ng',
    ilo: 'ph',
    is: 'is',
    it: 'it',
    iw: 'il',
    ja: 'jp',
    jv: 'id',
    jw: 'id',
    ka: 'ge',
    kk: 'kz',
    km: 'kh',
    kn: 'in',
    ko: 'kr',
    kri: 'sl',
    ktu: 'cd',
    ku: 'tr',
    ky: 'kg',
    la: 'va',
    lb: 'lu',
    lg: 'ug',
    li: 'nl',
    lij: 'it',
    lmo: 'it',
    ln: 'cd',
    lo: 'la',
    lt: 'lt',
    ltg: 'lv',
    luo: 'ke',
    lus: 'in',
    lv: 'lv',
    mai: 'in',
    mak: 'id',
    mg: 'mg',
    mi: 'nz',
    min: 'id',
    mk: 'mk',
    ml: 'in',
    mn: 'mn',
    'mni-mtei': 'in',
    mr: 'in',
    ms: 'my',
    mt: 'mt',
    my: 'mm',
    ne: 'np',
    new: 'np',
    nl: 'nl',
    no: 'no',
    nr: 'za',
    nso: 'za',
    nus: 'ss',
    ny: 'mw',
    oc: 'fr',
    om: 'et',
    or: 'in',
    pa: 'in',
    pag: 'ph',
    pam: 'ph',
    pap: 'cw',
    pl: 'pl',
    ps: 'af',
    pt: 'br',
    qu: 'pe',
    rn: 'bi',
    ro: 'ro',
    rom: 'ro',
    ru: 'ru',
    rw: 'rw',
    sa: 'in',
    scn: 'it',
    sd: 'pk',
    sg: 'cf',
    shn: 'mm',
    si: 'lk',
    sk: 'sk',
    sl: 'si',
    sm: 'ws',
    sn: 'zw',
    so: 'so',
    sq: 'al',
    sr: 'rs',
    ss: 'sz',
    st: 'ls',
    su: 'id',
    sv: 'se',
    sw: 'tz',
    szl: 'pl',
    ta: 'in',
    te: 'in',
    tet: 'tl',
    tg: 'tj',
    th: 'th',
    ti: 'er',
    tk: 'tm',
    tl: 'ph',
    tn: 'bw',
    tr: 'tr',
    ts: 'za',
    tt: 'ru',
    ug: 'cn',
    uk: 'ua',
    ur: 'pk',
    uz: 'uz',
    vi: 'vn',
    xh: 'za',
    yi: 'il',
    yo: 'ng',
    yua: 'mx',
    yue: 'hk',
    zh: 'cn',
    zu: 'za',
    'fr-ca': 'ca',
    'ms-arab': 'my',
    'pa-arab': 'pk',
    'pt-pt': 'pt',
    'zh-cn': 'cn',
    'zh-hans': 'cn',
    'zh-hant': 'tw',
    'zh-tw': 'tw',
};

const textOriginals = new WeakMap();
const attributeOriginals = new WeakMap();

let activeLanguage = 'en';
let backendEnabled = false;
let translationRoot = null;
let translationConfig = null;
let mutationTimer = null;

export function initPublicTranslator() {
    translationConfig = window.ParadiseTranslatorConfig;
    translationRoot = document.querySelector('[data-pd-translation-root]');

    if (!translationConfig || !translationRoot) {
        return;
    }

    const selectors = getLanguageSelectors();

    if (selectors.length === 0) {
        return;
    }

    loadLanguages()
        .then(({ enabled, languages }) => {
            backendEnabled = enabled;
            renderLanguageOptions(languages);
            setSelectorAvailability(backendEnabled);

            const availableCodes = new Set(languages.map((language) => language.code));
            const storedLanguage = getStoredLanguage();

            activeLanguage = backendEnabled && availableCodes.has(storedLanguage) ? storedLanguage : 'en';
            setSelectValues(activeLanguage);
            setDocumentLanguage(activeLanguage);

            if (backendEnabled && activeLanguage !== 'en') {
                translatePage(activeLanguage);
            }
        })
        .catch(() => {
            backendEnabled = false;
            setSelectorAvailability(false);
            setSelectValues('en');
        });

    bindLanguageSelectors(selectors);

    observeNewPublicContent();
}

function getLanguageSelectors() {
    return Array.from(document.querySelectorAll('[data-pd-language-selector]'));
}

async function loadLanguages() {
    const response = await fetch(translationConfig.languagesUrl, {
        headers: { Accept: 'application/json' },
    });

    if (!response.ok) {
        throw new Error('Could not load languages.');
    }

    const payload = await response.json();
    const languages = Array.isArray(payload.languages) && payload.languages.length > 0
        ? payload.languages
        : fallbackLanguages();

    return {
        enabled: Boolean(payload.enabled),
        languages,
    };
}

function fallbackLanguages() {
    return [
        { code: 'en', name: 'English', priority: true, flagCountry: 'gb' },
        { code: 'es', name: 'Spanish', priority: true, flagCountry: 'es' },
        { code: 'pt', name: 'Portuguese', priority: true, flagCountry: 'br' },
        { code: 'fr', name: 'French', priority: true, flagCountry: 'fr' },
        { code: 'de', name: 'German', priority: true, flagCountry: 'de' },
        { code: 'ru', name: 'Russian', priority: true, flagCountry: 'ru' },
        { code: 'th', name: 'Thai', priority: true, flagCountry: 'th' },
    ];
}

function bindLanguageSelectors(selectors) {
    selectors.forEach((selector) => {
        const button = selector.querySelector('[data-pd-language-button]');
        const search = selector.querySelector('[data-pd-language-search]');

        button?.addEventListener('click', () => {
            toggleLanguageMenu(selector);
        });

        search?.addEventListener('input', () => {
            filterLanguageOptions(selector, search.value);
        });

        selector.addEventListener('click', (event) => {
            const option = event.target.closest('[data-pd-language-option]');

            if (!option) {
                return;
            }

            setLanguage(option.dataset.value || 'en');
            closeLanguageMenus();
        });
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('[data-pd-language-selector]')) {
            closeLanguageMenus();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeLanguageMenus();
        }
    });
}

function toggleLanguageMenu(selector) {
    const menu = selector.querySelector('[data-pd-language-menu]');
    const button = selector.querySelector('[data-pd-language-button]');

    if (!menu || !button) {
        return;
    }

    const willOpen = menu.classList.contains('hidden');

    closeLanguageMenus();

    if (willOpen) {
        menu.classList.remove('hidden');
        button.setAttribute('aria-expanded', 'true');
        window.requestAnimationFrame(() => selector.querySelector('[data-pd-language-search]')?.focus());
    }
}

function closeLanguageMenus() {
    getLanguageSelectors().forEach((selector) => {
        selector.querySelector('[data-pd-language-menu]')?.classList.add('hidden');
        selector.querySelector('[data-pd-language-button]')?.setAttribute('aria-expanded', 'false');

        const search = selector.querySelector('[data-pd-language-search]');
        if (search) {
            search.value = '';
            filterLanguageOptions(selector, '');
        }
    });
}

function renderLanguageOptions(languages) {
    const priorityCodes = new Set(translationConfig.priority || ['en', 'es', 'pt', 'fr', 'de', 'ru', 'th']);
    const enriched = languages.map(enrichLanguage);
    const priority = enriched.filter((language) => priorityCodes.has(language.code));
    const rest = enriched.filter((language) => !priorityCodes.has(language.code));

    getLanguageSelectors().forEach((selector) => {
        const menu = selector.querySelector('[data-pd-language-options]');

        if (!menu) {
            return;
        }

        menu.innerHTML = '';
        priority.forEach((language) => menu.appendChild(languageOption(language)));

        if (rest.length > 0) {
            const divider = document.createElement('div');
            divider.dataset.pdLanguageDivider = '';
            divider.className = 'my-1 border-t border-boss-rose/15';
            menu.appendChild(divider);
            rest.forEach((language) => menu.appendChild(languageOption(language)));
        }

        const empty = document.createElement('p');
        empty.dataset.pdLanguageEmpty = '';
        empty.className = 'hidden px-3 py-6 text-center text-[0.75rem] text-boss-dark/45';
        empty.textContent = 'No languages found';
        menu.appendChild(empty);

        updateSelectorDisplay(selector, activeLanguage);
    });
}

function filterLanguageOptions(selector, query) {
    const normalizedQuery = String(query || '').trim().toLocaleLowerCase();
    const options = Array.from(selector.querySelectorAll('[data-pd-language-option]'));
    let visibleCount = 0;

    options.forEach((option) => {
        const searchableText = `${option.dataset.name || ''} ${option.dataset.value || ''}`.toLocaleLowerCase();
        const visible = normalizedQuery === '' || searchableText.includes(normalizedQuery);
        option.classList.toggle('hidden', !visible);
        visibleCount += visible ? 1 : 0;
    });

    selector.querySelector('[data-pd-language-divider]')?.classList.toggle('hidden', normalizedQuery !== '');
    selector.querySelector('[data-pd-language-empty]')?.classList.toggle('hidden', visibleCount !== 0);
}

function languageOption(language) {
    const option = document.createElement('button');
    option.type = 'button';
    option.role = 'option';
    option.dataset.pdLanguageOption = '';
    option.dataset.value = language.code;
    option.dataset.name = language.name;
    option.dataset.flagCountry = language.flagCountry;
    option.dataset.flagUrl = language.flagUrl;
    option.className = 'flex w-full items-center gap-3 px-3 py-2 text-left text-[0.78rem] transition hover:bg-boss-muted aria-selected:bg-boss-muted';
    option.innerHTML = `
        <img src="${escapeHtml(language.flagUrl)}" alt="" class="h-3.5 w-5 shrink-0 rounded-[2px] object-cover shadow-sm" aria-hidden="true" loading="lazy">
        <span class="min-w-0 flex-1 truncate">${escapeHtml(language.name)}</span>
        <span class="text-[0.62rem] font-semibold uppercase tracking-[0.12em] text-boss-dark/35">${escapeHtml(language.shortCode)}</span>
    `;

    return option;
}

function enrichLanguage(language) {
    const flagCountry = language.flagCountry || language.flag_country || flagCountryForLanguage(language.code);

    return {
        ...language,
        flagCountry,
        flagUrl: language.flagUrl || language.flag_url || flagUrl(flagCountry),
        shortCode: language.code.replace('zh-', '').toUpperCase(),
    };
}

function flagCountryForLanguage(code) {
    const normalized = String(code || '').trim().toLowerCase();
    const base = normalized.split('-')[0];

    return LANGUAGE_FLAG_COUNTRIES[normalized] || LANGUAGE_FLAG_COUNTRIES[base] || 'gb';
}

function flagUrl(country) {
    return `${FLAG_CDN_BASE}${String(country || 'gb').toLowerCase()}.png`;
}

function escapeHtml(value) {
    const element = document.createElement('span');
    element.textContent = value;

    return element.innerHTML;
}

async function setLanguage(language) {
    activeLanguage = language || 'en';
    setSelectValues(activeLanguage);
    setDocumentLanguage(activeLanguage);
    storeLanguage(activeLanguage);

    if (!backendEnabled || activeLanguage === 'en') {
        restoreEnglish();
        return;
    }

    await translatePage(activeLanguage);
}

function setSelectValues(language) {
    getLanguageSelectors().forEach((selector) => {
        updateSelectorDisplay(selector, language);
    });
}

function updateSelectorDisplay(selector, language) {
    const fallback = enrichLanguage({ code: 'en', name: 'English' });
    const option = selector.querySelector(`[data-pd-language-option][data-value="${CSS.escape(language)}"]`);
    const flagUrl = option?.dataset.flagUrl || fallback.flagUrl;
    const flagCountry = option?.dataset.flagCountry || fallback.flagCountry;
    const code = (option?.dataset.value || fallback.code).replace('zh-', '').toUpperCase();
    const flag = selector.querySelector('[data-pd-language-flag]');

    if (flag instanceof HTMLImageElement) {
        flag.src = flagUrl;
        flag.dataset.flagCountry = flagCountry;
    } else if (flag) {
        flag.textContent = '';
    }

    selector.querySelector('[data-pd-language-code]').textContent = code;

    selector.querySelectorAll('[data-pd-language-option]').forEach((languageOptionElement) => {
        languageOptionElement.setAttribute(
            'aria-selected',
            languageOptionElement.dataset.value === language ? 'true' : 'false'
        );
    });
}

function setSelectorAvailability(active) {
    getLanguageSelectors().forEach((selector) => {
        const button = selector.querySelector('[data-pd-language-button]');

        selector.dataset.pdTranslationActive = active ? 'true' : 'false';
        button?.setAttribute(
            'title',
            active ? 'Language' : 'Add translation API credentials to activate translation.'
        );
    });
}

function getStoredLanguage() {
    try {
        return window.localStorage.getItem(STORAGE_KEY) || translationConfig.defaultLanguage || 'en';
    } catch {
        return translationConfig.defaultLanguage || 'en';
    }
}

function storeLanguage(language) {
    try {
        window.localStorage.setItem(STORAGE_KEY, language);
    } catch {
        // Ignore storage failures; translation still works for the current page.
    }
}

function setDocumentLanguage(language) {
    document.documentElement.lang = language === 'en' ? 'en' : language;
    document.documentElement.dataset.pdLanguage = language;
}

async function translatePage(language) {
    const items = collectTranslatableItems();

    if (items.length === 0) {
        return;
    }

    const sourceTexts = Array.from(new Set(items.map((item) => item.source)));
    const translatedTexts = await translateTexts(sourceTexts, language);
    const translatedBySource = new Map(sourceTexts.map((text, index) => [text, translatedTexts[index] || text]));

    items.forEach((item) => {
        const translated = translatedBySource.get(item.source) || item.source;

        if (item.type === 'text') {
            item.node.nodeValue = item.leading + translated + item.trailing;
            return;
        }

        item.element.setAttribute(item.attribute, translated);
    });
}

async function translateTexts(texts, language) {
    const translated = [];

    for (let index = 0; index < texts.length; index += BATCH_SIZE) {
        const chunk = texts.slice(index, index + BATCH_SIZE);

        try {
            const response = await fetch(translationConfig.translateUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': translationConfig.csrfToken,
                },
                body: JSON.stringify({
                    target: language,
                    texts: chunk,
                }),
            });

            if (!response.ok) {
                translated.push(...chunk);
                continue;
            }

            const payload = await response.json();
            translated.push(...(Array.isArray(payload.translations) ? payload.translations : chunk));
        } catch {
            translated.push(...chunk);
        }
    }

    return translated;
}

function restoreEnglish() {
    collectTranslatableItems().forEach((item) => {
        if (item.type === 'text') {
            item.node.nodeValue = item.original;
            return;
        }

        item.element.setAttribute(item.attribute, item.original);
    });
}

function collectTranslatableItems() {
    return [
        ...collectTextNodes(),
        ...collectAttributes(),
    ];
}

function collectTextNodes() {
    const items = [];
    const walker = document.createTreeWalker(
        translationRoot,
        NodeFilter.SHOW_TEXT,
        {
            acceptNode(node) {
                const element = node.parentElement;
                const original = textOriginals.get(node);
                const value = original || node.nodeValue || '';

                if (!element || shouldSkipElement(element) || !hasTranslatableLetters(value)) {
                    return NodeFilter.FILTER_REJECT;
                }

                return NodeFilter.FILTER_ACCEPT;
            },
        }
    );

    let node = walker.nextNode();

    while (node) {
        const original = textOriginals.get(node) || node.nodeValue || '';
        textOriginals.set(node, original);

        const source = original.trim();

        if (source !== '') {
            items.push({
                type: 'text',
                node,
                original,
                source,
                leading: original.match(/^\s*/)?.[0] || '',
                trailing: original.match(/\s*$/)?.[0] || '',
            });
        }

        node = walker.nextNode();
    }

    return items;
}

function collectAttributes() {
    const items = [];
    const selector = TRANSLATABLE_ATTRIBUTES.map((attribute) => `[${attribute}]`).join(',');

    translationRoot.querySelectorAll(selector).forEach((element) => {
        if (shouldSkipElement(element, false)) {
            return;
        }

        TRANSLATABLE_ATTRIBUTES.forEach((attribute) => {
            if (!element.hasAttribute(attribute)) {
                return;
            }

            const original = getOriginalAttribute(element, attribute);

            if (!hasTranslatableLetters(original)) {
                return;
            }

            items.push({
                type: 'attribute',
                element,
                attribute,
                original,
                source: original.trim(),
            });
        });
    });

    return items;
}

function getOriginalAttribute(element, attribute) {
    const originals = attributeOriginals.get(element) || {};

    if (!(attribute in originals)) {
        originals[attribute] = element.getAttribute(attribute) || '';
        attributeOriginals.set(element, originals);
    }

    return originals[attribute];
}

function shouldSkipElement(element, skipControls = true) {
    if (element.closest('[data-translate-ignore], [data-pd-language-selector], [data-no-translate]')) {
        return true;
    }

    if (SKIPPED_TAGS.has(element.tagName)) {
        return true;
    }

    if (skipControls && SKIPPED_CONTROL_TAGS.has(element.tagName)) {
        return true;
    }

    return element.isContentEditable;
}

function hasTranslatableLetters(value) {
    return /[A-Za-z]/.test((value || '').trim());
}

function observeNewPublicContent() {
    const observer = new MutationObserver(() => {
        if (!backendEnabled || activeLanguage === 'en') {
            return;
        }

        clearTimeout(mutationTimer);
        mutationTimer = setTimeout(() => translatePage(activeLanguage), 250);
    });

    observer.observe(translationRoot, {
        childList: true,
        subtree: true,
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPublicTranslator);
} else {
    initPublicTranslator();
}
