document.addEventListener('DOMContentLoaded', () => {
  const openBtn = document.querySelector('[data-auth-open]');
  const drawer = document.querySelector('[data-auth-drawer]');
  const closeBtns = document.querySelectorAll('[data-auth-close]');

  if (!drawer) return;

  const views = drawer.querySelectorAll('[data-auth-view]');
  const showView = (name) => {
    views.forEach(v => v.hidden = (v.getAttribute('data-auth-view') !== name));
  };

  const open = () => {
    document.body.classList.add('auth-open');
    drawer.setAttribute('aria-hidden', 'false');
  };

  const close = () => {
    document.body.classList.remove('auth-open');
    drawer.setAttribute('aria-hidden', 'true');
  };

  if (openBtn) openBtn.addEventListener('click', open);
  closeBtns.forEach(btn => btn.addEventListener('click', close));

  drawer.querySelectorAll('[data-auth-switch]').forEach(btn => {
    btn.addEventListener('click', () => showView(btn.getAttribute('data-auth-switch')));
  });

  const params = new URLSearchParams(window.location.search);
  const auth = params.get('auth');
  if (auth) {
    open();
    if (auth === 'email_exists' || auth === 'register_error') showView('register');
    else showView('login');
  }
});