document.addEventListener('DOMContentLoaded', function () {
  var menuToggle = document.querySelector('.menu-toggle');
  var navigation = document.querySelector('.primary-nav');
  var searchToggle = document.querySelector('[data-search-toggle]');
  var searchPanel = document.querySelector('[data-search-panel]');
  var backToTopButton = document.querySelector('[data-back-to-top]');
  var backToTopOffset = 360;

  function syncBackToTopButton() {
    if (!backToTopButton) {
      return;
    }

    var isVisible = window.scrollY > backToTopOffset;
    backToTopButton.classList.toggle('is-visible', isVisible);
    backToTopButton.setAttribute('aria-hidden', String(!isVisible));
    backToTopButton.tabIndex = isVisible ? 0 : -1;
  }

  if (menuToggle && navigation) {
    menuToggle.addEventListener('click', function () {
      var isExpanded = menuToggle.getAttribute('aria-expanded') === 'true';
      menuToggle.setAttribute('aria-expanded', String(!isExpanded));
      navigation.classList.toggle('is-open');
    });
  }

  if (searchToggle && searchPanel) {
    searchToggle.addEventListener('click', function () {
      var isExpanded = searchToggle.getAttribute('aria-expanded') === 'true';
      searchToggle.setAttribute('aria-expanded', String(!isExpanded));
      searchPanel.hidden = isExpanded;
    });
  }

  if (backToTopButton) {
    syncBackToTopButton();

    window.addEventListener('scroll', syncBackToTopButton, { passive: true });

    backToTopButton.addEventListener('click', function () {
      var scrollBehavior = 'smooth';

      if (
        window.matchMedia &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches
      ) {
        scrollBehavior = 'auto';
      }

      window.scrollTo({
        top: 0,
        behavior: scrollBehavior,
      });
    });
  }

  document.addEventListener('keyup', function (event) {
    if (event.key !== 'Escape') {
      return;
    }

    if (navigation && navigation.classList.contains('is-open') && menuToggle) {
      navigation.classList.remove('is-open');
      menuToggle.setAttribute('aria-expanded', 'false');
    }

    if (searchPanel && !searchPanel.hidden && searchToggle) {
      searchPanel.hidden = true;
      searchToggle.setAttribute('aria-expanded', 'false');
    }
  });
});
