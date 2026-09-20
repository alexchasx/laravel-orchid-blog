/**
 * Cookie-баннер: согласие на использование cookies и аналитики.
 *
 * Подключается ПЕРВЫМ (раньше techlog.js), чтобы решение о согласии можно
 * было проверить синхронно в самом начале загрузки страницы — ДО того, как
 * будут загружены какие-либо трекинговые скрипты.
 *
 * Публичный API:
 *   window.initCookieBanner(options?) — инициализация баннера, вызывается в начале загрузки.
 *       Возвращает объект согласия { status, categories, decided }.
 *   window.getCookieConsent()        — синхронный доступ к текущему решению.
 *   событие document 'cookies-consent' — срабатывает при каждом выборе пользователя.
 *
 * Логика: при первом визите баннер показывается; решение сохраняется в localStorage
 * (по умолчанию на 365 дней) и повторно не показывается, пока не истечёт срок.
 */
(() => {
  'use strict';

  const STORAGE_KEY = 'techlog-cookie-consent';
  const STORAGE_VERSION = 1;
  const DEFAULT_MAX_AGE_DAYS = 365;
  const ALL_CATEGORIES = ['analytics'];

  // Актуальное решение (синхронно читается из localStorage при инициализации).
  let state = {
    status: 'undecided', // 'accepted' | 'declined' | 'customized' | 'undecided'
    categories: { analytics: false },
    savedAt: null,
  };

  // ---------------------------------------------------------------------------
  // Хранилище localStorage
  // ---------------------------------------------------------------------------

  /** Прочитать решение из localStorage с проверкой версии и срока действия. */
  function readStored(maxAgeDays) {
    let raw;
    try {
      raw = localStorage.getItem(STORAGE_KEY);
    } catch (_) {
      return null; // localStorage недоступен (приватный режим) — действуем без сохранения
    }
    if (!raw) return null;

    let parsed;
    try {
      parsed = JSON.parse(raw);
    } catch (_) {
      return null;
    }

    if (parsed.version !== STORAGE_VERSION) return null;

    const savedAt = Number(parsed.savedAt);
    const expiresAt = savedAt + maxAgeDays * 24 * 60 * 60 * 1000;
    if (!savedAt || Date.now() > expiresAt) return null; // срок истёк — показать снова

    return parsed;
  }

  /** Сохранить решение в localStorage (с меткой времени для срока действия). */
  function persist(next) {
    state = next;
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify({ ...next, version: STORAGE_VERSION }));
    } catch (_) {
      // Не критично: баннер всё равно обработает выбор в рамках сессии.
    }
  }

  // ---------------------------------------------------------------------------
  // Событие согласия (для условной загрузки аналитики)
  // ---------------------------------------------------------------------------

  /** Сообщить остальному коду о новом решении пользователя. */
  function dispatchConsent() {
    document.dispatchEvent(new CustomEvent('cookies-consent', {
      detail: { status: state.status, categories: { ...state.categories } },
    }));
  }

  // ---------------------------------------------------------------------------
  // Контроль показа баннера
  // ---------------------------------------------------------------------------

  function getBanner() {
    return document.querySelector('[data-cookie-banner]');
  }

  let previousFocus = null;

  function showBanner(banner, focusEl) {
    previousFocus = document.activeElement;
    banner.hidden = false;
    // Требование «фокус при открытии»: переносим фокус на кнопку «Принять».
    (focusEl || banner.querySelector('[data-cookie-accept]'))?.focus();
    document.addEventListener('keydown', onKeydown);
  }

  function hideBanner(banner, restoreFocus = true) {
    banner.hidden = true;
    document.removeEventListener('keydown', onKeydown);
    if (restoreFocus) {
      // Возвращаем фокус туда, где он был до открытия баннера.
      if (previousFocus && document.contains(previousFocus)) {
        previousFocus.focus({ preventScroll: true });
      }
      previousFocus = null;
    }
  }

  // Перенос фокуса по Tab внутри диалога (корректная навигация клавиатурой).
  function onKeydown(e) {
    const banner = getBanner();
    if (!banner || banner.hidden) return;

    if (e.key === 'Escape') {
      e.preventDefault();
      hideBanner(banner);
      return;
    }

    if (e.key !== 'Tab') return;
    const focusable = banner.querySelectorAll('button, input[type="checkbox"], a[href]');
    if (!focusable.length) return;

    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    if (e.shiftKey && document.activeElement === first) {
      e.preventDefault();
      last.focus();
    } else if (!e.shiftKey && document.activeElement === last) {
      e.preventDefault();
      first.focus();
    }
  }

  // ---------------------------------------------------------------------------
  // Действия пользователя
  // ---------------------------------------------------------------------------

  /** Общий путь выбора: сохранить решение, скрыть баннер, сообщить подписчикам. */
  function decide(status, enabledCategories) {
    const categories = { analytics: false, ...state.categories };
    ALL_CATEGORIES.forEach((cat) => {
      categories[cat] = status === 'accepted' || (enabledCategories && enabledCategories[cat]);
    });
    persist({ status, categories, savedAt: Date.now() });
    dispatchConsent();
  }

  function onAccept(banner) {
    decide('accepted');
    hideBanner(banner);
  }

  function onDecline(banner) {
    decide('declined', {});
    hideBanner(banner);
  }

  function onCustomizeToggle(banner) {
    const panel = banner.querySelector('#cookie-banner-settings');
    const btn = banner.querySelector('[data-cookie-customize]');
    const open = panel.hidden;
    panel.hidden = !open;
    btn.setAttribute('aria-expanded', String(open));
    if (open) banner.querySelector('[data-cookie-cat="analytics"]')?.focus();
  }

  function onSaveCustom(banner) {
    const enabled = {};
    banner.querySelectorAll('[data-cookie-cat]').forEach((input) => {
      enabled[input.dataset.cookieCat] = input.checked;
    });
    decide('customized', enabled);
    hideBanner(banner);
  }

  /** Закрыть баннер крестиком (решение не сохраняется — показать в следующий раз). */
  function onClose(banner) {
    hideBanner(banner);
  }

  // ---------------------------------------------------------------------------
  // Инициализация
  // ---------------------------------------------------------------------------

  /**
   * Инициализировать cookie-баннер. Вызывается в самом начале загрузки страницы,
   * ДО подключения трекинговых скриптов.
   *
   * @param {Object} [options]
   * @param {number} [options.maxAgeDays]     Срок действия решения, дни (по умолчанию 365)
   * @param {Function} [options.onConsent]    Колбэк при выборе (status, categories)
   * @returns {{status: string, categories: Object, decided: boolean}}
   */
  function initCookieBanner(options = {}) {
    const maxAgeDays = options.maxAgeDays ?? DEFAULT_MAX_AGE_DAYS;

    const stored = readStored(maxAgeDays);
    const decided = Boolean(stored);

    if (stored) {
      // Решение уже принято и не устарело — восстанавливаем его и НЕ показываем баннер.
      state = {
        status: stored.status,
        categories: { ...ALL_CATEGORIES.reduce((acc, c) => (acc[c] = Boolean(stored.categories[c]), acc), {}) },
        savedAt: stored.savedAt,
      };
      window.__cookieConsent = getState();
      return getState();
    }

    const banner = getBanner();
    if (!banner) return getState();

    // Показываем баннер только после разбора всего DOM (banner размещён перед </body>).
    requestAnimationFrame(() => {
      if (readStored(maxAgeDays)) return; // могли успеть решить где-то ещё
      showBanner(banner);
    });

    banner.querySelector('[data-cookie-accept]')?.addEventListener('click', () => onAccept(banner));
    banner.querySelector('[data-cookie-decline]')?.addEventListener('click', () => onDecline(banner));
    banner.querySelector('[data-cookie-customize]')?.addEventListener('click', () => onCustomizeToggle(banner));
    banner.querySelector('[data-cookie-save]')?.addEventListener('click', () => onSaveCustom(banner));
    banner.querySelector('[data-cookie-close]')?.addEventListener('click', () => onClose(banner));

    // Порог согласия хранится в state — после init он доступен извне.
    window.__cookieConsent = getState();

    // Гейтинг аналитики: подписчики на 'cookies-consent' узнают о выборе мгновенно.
    document.addEventListener('cookies-consent', (e) => {
      options.onConsent?.(e.detail.status, e.detail.categories);
    });

    return getState();
  }

  /** Синхронный доступ к текущему решению. */
  function getState() {
    return JSON.parse(JSON.stringify(state));
  }

  /**
   * Разрешить ли категорию трекинга.
   * @param {string} category Ключ категории ('analytics' и т.п.)
   */
  function isAllowed(category) {
    return Boolean(state.categories[category]);
  }

  // Публичное API.
  window.initCookieBanner = initCookieBanner;
  window.getCookieConsent = getState;
  window.isCookieCategoryAllowed = isAllowed;
  window.__cookieConsent = getState(); // синхронный снимок для самого раннего гейтинга
})();