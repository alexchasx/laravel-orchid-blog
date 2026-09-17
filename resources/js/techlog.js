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

  // Contact form validation
  const contactForm = document.querySelector('[data-contact-form]');
  contactForm?.addEventListener('submit', e => {
    e.preventDefault();
    const message = contactForm.querySelector('.form-message');
    if (!contactForm.checkValidity()) {
      message.textContent = 'Проверьте заполнение полей формы.';
      contactForm.reportValidity();
      return;
    }
    message.textContent = 'Сообщение принято. В рабочей интеграции здесь будет отправка на backend.';
    contactForm.reset();
  });
})();
