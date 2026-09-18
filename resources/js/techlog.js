(() => {
  const root = document.documentElement;
  const menu = document.querySelector('.menu-toggle');
  const nav = document.querySelector('.nav-links');
  const themeButtons = document.querySelectorAll('.theme-toggle');
  const topButton = document.querySelector('.to-top');

  // Тема: восстановить сохранённую
  const savedTheme = localStorage.getItem('techlog-theme');
  if (savedTheme === 'light') root.classList.add('light');

  // Мобильное меню
  menu?.addEventListener('click', () => {
    const open = nav.classList.toggle('open');
    menu.setAttribute('aria-expanded', String(open));
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

  // Newsletter form validation
  const emailForm = document.querySelector('[data-validate]');
  emailForm?.addEventListener('submit', e => {
    e.preventDefault();
    const email = emailForm.email.value.trim();
    const message = emailForm.querySelector('.form-message');
    if (!emailForm.email.checkValidity()) {
      message.textContent = 'Введите корректный email.';
      emailForm.email.focus();
      return;
    }
    message.textContent = 'Готово — подписка оформлена. Проверьте почту для подтверждения.';
    emailForm.reset();
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

  // Модальное окно: результат отправки комментария
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
