document.addEventListener('DOMContentLoaded', function () {

  // Page loader: hide once everything is ready
  const loader = document.getElementById('pageLoader');
  window.addEventListener('load', function () {
    if (loader) setTimeout(() => loader.classList.add('loaded'), 250);
  });
  // Safety fallback in case 'load' fires slowly
  setTimeout(() => { if (loader) loader.classList.add('loaded'); }, 2500);

  // AOS init
  if (window.AOS) { AOS.init({ duration: 800, once: true, offset: 60 }); }

  // Navbar shrink on scroll
  const nav = document.querySelector('.main-navbar');
  const backToTop = document.getElementById('backToTop');
  window.addEventListener('scroll', function () {
    if (nav) nav.classList.toggle('scrolled', window.scrollY > 40);
    if (backToTop) backToTop.classList.toggle('show', window.scrollY > 400);
  });
  if (backToTop) {
    backToTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  }

  // Hero rotating word typewriter (used on homepage hero)
  const rotatingEl = document.querySelector('.rotating-word');
  if (rotatingEl) {
    const words = (rotatingEl.dataset.words || 'India').split(',');
    let wIndex = 0, chIndex = 0, deleting = false;
    function tick() {
      const word = words[wIndex].trim();
      if (!deleting) {
        chIndex++;
        rotatingEl.textContent = word.substring(0, chIndex);
        if (chIndex === word.length) { deleting = true; setTimeout(tick, 1400); return; }
      } else {
        chIndex--;
        rotatingEl.textContent = word.substring(0, chIndex);
        if (chIndex === 0) { deleting = false; wIndex = (wIndex + 1) % words.length; }
      }
      setTimeout(tick, deleting ? 60 : 110);
    }
    tick();
  }

  // Wishlist heart toggle (visual only until connected to backend AJAX)
  document.querySelectorAll('.tile-fav').forEach(btn => {
    btn.addEventListener('click', function (e) {
      const icon = this.querySelector('i');
      if (icon.classList.contains('bi-heart')) {
        icon.classList.remove('bi-heart');
        icon.classList.add('bi-heart-fill');
      }
    });
  });

});
