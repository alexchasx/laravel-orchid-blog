(() => {
  // =========================================================================
  // Cookie-баннер: ВЫЗЫВАЕТСЯ В САМОМ НАЧАЛЕ загрузки страницы — раньше любых
  // трекинговых скриптов (функция объявлена в resources/js/cookie-banner.js,
  // который подключён в @vite первым). Решение читается из localStorage
  // синхронно, поэтому аналитику можно гейтить прямо здесь.
  // =========================================================================
  const cookieConsent = window.initCookieBanner
    ? window.initCookieBanner()
    : { status: 'undecided', decided: false };

  // ---------------------------------------------------------------------------
  // ТОЧКА ПОДКЛЮЧЕНИЯ АНАЛИТИКИ.
  // ВСЕ трекинговые скрипты (Google Analytics / gtag, Яндекс.Метрика, пиксели)
  // должны загружаться ТОЛЬКО при разрешённой категории 'analytics'.
  //
  // 1) Для уже принятого решения (повторный визит) — проверка синхронно:
  if (cookieConsent.categories.analytics) {
    // Ваши скрипты аналитики можно записать здесь, например:
    //   (function() { var s = document.createElement('script');
    //     s.async = true; s.src = '<URL скрипта>'; document.head.appendChild(s); })();
  }

  // 2) Для нового решения (первый визит — пользователь нажал кнопку в баннере):
  document.addEventListener('cookies-consent', (e) => {
    if (e.detail.categories.analytics) {
      // Здесь — то же самое подключение аналитики, что и в пункте 1.
    }
  });
  // ---------------------------------------------------------------------------

  const root = document.documentElement;
  const menu = document.querySelector('.menu-toggle');
  const nav = document.querySelector('.nav-links');
  const themeButtons = document.querySelectorAll('.theme-toggle');
  const topButton = document.querySelector('.to-top');
  const modals = document.querySelectorAll('.modal');

  const closeModal = (modal) => {
    modal.classList.remove('is-open');
    document.body.style.overflow = '';
    document.body.classList.remove('modal-open');
  };

  const openModal = (modal) => {
    modal.classList.add('is-open');
    document.body.style.overflow = 'hidden';
    document.body.classList.add('modal-open');
    modal.querySelector('[data-modal-close], button')?.focus();
  };

  // Тема: восстановить сохранённую
  const savedTheme = localStorage.getItem('techlog-theme');
  if (savedTheme === 'light') root.classList.add('light');

  // Выпадающее меню «Темы» в хедере
  const dropdowns = document.querySelectorAll('.nav-dropdown');

  const closeDropdowns = () => {
    dropdowns.forEach(dropdown => {
      dropdown.querySelector('.nav-dropdown-menu')?.classList.remove('open');
      dropdown.querySelector('.nav-dropdown-toggle')?.setAttribute('aria-expanded', 'false');
    });
  };

  dropdowns.forEach(dropdown => {
    const toggle = dropdown.querySelector('.nav-dropdown-toggle');
    const dropdownMenu = dropdown.querySelector('.nav-dropdown-menu');

    toggle?.addEventListener('click', () => {
      const willOpen = !dropdownMenu.classList.contains('open');
      closeDropdowns();
      if (willOpen) {
        dropdownMenu.classList.add('open');
        toggle.setAttribute('aria-expanded', 'true');
      }
    });

    dropdownMenu?.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', closeDropdowns);
    });
  });

  document.addEventListener('click', e => {
    if (!e.target.closest?.('.nav-dropdown')) closeDropdowns();
  });

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeDropdowns();
  });

  // Мобильное меню
  menu?.addEventListener('click', () => {
    const open = nav.classList.toggle('open');
    menu.setAttribute('aria-expanded', String(open));
    closeDropdowns();
  });

  // Закрыть меню при клике на ссылку
  nav?.querySelectorAll('a').forEach(a => a.addEventListener('click', () => {
    nav.classList.remove('open');
    menu?.setAttribute('aria-expanded', 'false');
  }));

  // Переключение темы
  themeButtons.forEach(button => button.addEventListener('click', () => {
    root.classList.toggle('light');
    localStorage.setItem('techlog-theme', root.classList.contains('light') ? 'light' : 'dark');
  }));

  // Scroll reveal
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12 });
  document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

  // Кнопка «Наверх»
  window.addEventListener('scroll', () => {
    topButton?.classList.toggle('visible', window.scrollY > 600);
  }, { passive: true });
  topButton?.addEventListener('click', () => {
    scrollTo({ top: 0, behavior: 'smooth' });
  });

  // Newsletter form: подписка на новые статьи (отправка на backend)
  const newsletterModal = document.querySelector('#newsletter-modal');
  const newsletterFormView = newsletterModal?.querySelector('.newsletter-form-view');
  const newsletterResultView = newsletterModal?.querySelector('.newsletter-result-view');
  const newsletterModalIcon = newsletterResultView?.querySelector('.modal-icon');
  const newsletterModalTitle = newsletterResultView?.querySelector('.modal-title');
  const newsletterModalText = newsletterResultView?.querySelector('.modal-text');

  const resetNewsletterModal = () => {
    if (newsletterFormView) newsletterFormView.hidden = false;
    if (newsletterResultView) newsletterResultView.hidden = true;
  };

  const showNewsletterModal = (success, message) => {
    if (!newsletterModal) return;
    if (newsletterFormView) newsletterFormView.hidden = true;
    if (newsletterResultView) newsletterResultView.hidden = false;
    if (newsletterModalIcon) {
      newsletterModalIcon.className = `modal-icon ${success ? 'modal-icon--ok' : 'modal-icon--error'}`;
      newsletterModalIcon.innerHTML = success ? '&#10003;' : '!';
    }
    if (newsletterModalTitle) newsletterModalTitle.textContent = success ? 'Подписка оформлена' : 'Не получилось';
    if (newsletterModalText) newsletterModalText.textContent = message;
    openModal(newsletterModal);
  };

  // Кнопки «Подписаться» открывают модалку с формой
  document.querySelectorAll('[data-modal-open="newsletter"]').forEach(trigger => {
    trigger.addEventListener('click', () => {
      if (!newsletterModal) return;
      resetNewsletterModal();
      openModal(newsletterModal);
    });
  });

  const emailForm = document.querySelector('[data-newsletter-form]');
  emailForm?.addEventListener('submit', async e => {
    e.preventDefault();

    const messageEl = emailForm.querySelector('.form-message');
    const submitBtn = emailForm.querySelector('button[type="submit"]');
    const originalText = submitBtn?.textContent;

    messageEl.classList.remove('success', 'error');
    messageEl.textContent = '';

    if (!emailForm.checkValidity()) {
      messageEl.textContent = 'Введите корректный email.';
      messageEl.classList.add('error');
      emailForm.email.focus();
      return;
    }

    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = 'Подписываем…';
    }

    try {
      const response = await fetch(emailForm.action, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
        },
        body: new FormData(emailForm),
      });

      let data = {};
      try {
        data = await response.json();
      } catch (_) {
        // если сервер вернул не JSON — обработаем по статусу
      }

      if (response.ok && data.success) {
        emailForm.reset();
        showNewsletterModal(true, data.message || 'Подписка оформлена!');
      } else {
        const firstError = data.errors
          ? Object.values(data.errors)[0][0]
          : (data.message || 'Не удалось оформить подписку. Попробуйте ещё раз.');
        showNewsletterModal(false, firstError);
      }
    } catch (_) {
      showNewsletterModal(false, 'Не удалось оформить подписку. Проверьте соединение и попробуйте ещё раз.');
    } finally {
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
      }
    }
  });

  // Contact form: отправка на backend
  const contactForm = document.querySelector('[data-contact-form]');
  contactForm?.addEventListener('submit', async e => {
    e.preventDefault();

    const messageEl = contactForm.querySelector('.form-message');
    const submitBtn = contactForm.querySelector('button[type="submit"]');
    const originalText = submitBtn?.textContent;

    messageEl.classList.remove('success', 'error');
    messageEl.textContent = '';

    if (!contactForm.checkValidity()) {
      messageEl.textContent = 'Проверьте заполнение полей формы.';
      messageEl.classList.add('error');
      contactForm.reportValidity();
      return;
    }

    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = 'Отправляем…';
    }

    try {
      const response = await fetch(contactForm.action, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
        },
        body: new FormData(contactForm),
      });

      let data = {};
      try {
        data = await response.json();
      } catch (_) {
        // если сервер вернул не JSON — обработаем по статусу
      }

      if (response.ok && data.success) {
        messageEl.textContent = data.message || 'Сообщение отправлено!';
        messageEl.classList.add('success');
        contactForm.reset();
      } else {
        const firstError = data.errors
          ? Object.values(data.errors)[0][0]
          : (data.message || 'Не удалось отправить сообщение. Попробуйте ещё раз.');
        messageEl.textContent = firstError;
        messageEl.classList.add('error');
      }
    } catch (_) {
      messageEl.textContent = 'Не удалось отправить сообщение. Проверьте соединение и попробуйте ещё раз.';
      messageEl.classList.add('error');
    } finally {
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
      }
    }
  });

// Модальные окна: привязка обработчиков закрытия
  modals.forEach(modal => {
    // Элементы закрытия: крестик, оверлей, кнопка «Понятно»
    modal.querySelectorAll('[data-modal-close]').forEach(el => {
      el.addEventListener('click', () => closeModal(modal));
    });

    // Клик за пределами карточки (по оверлею)
    modal.addEventListener('click', e => {
      if (e.target === modal) closeModal(modal);
    });
  });

  // Escape закрывает открытую модалку
  document.addEventListener('keydown', e => {
    if (e.key !== 'Escape') return;
    modals.forEach(modal => {
      if (modal.classList.contains('is-open')) closeModal(modal);
    });
  });

  // Модалка, открытая сервером (redirect с flash/ошибкой), блокирует скролл
  modals.forEach(modal => {
    if (modal.classList.contains('is-open')) openModal(modal);
  });
})();
