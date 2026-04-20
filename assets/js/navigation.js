document.addEventListener('DOMContentLoaded', function () {
  var menuToggle = document.querySelector('.menu-toggle');
  var navigation = document.querySelector('.primary-nav');
  var searchToggle = document.querySelector('[data-search-toggle]');
  var searchPanel = document.querySelector('[data-search-panel]');
  var backToTopButton = document.querySelector('[data-back-to-top]');
  var storyFilterLinks = document.querySelectorAll('[data-story-filter-link]');
  var backToTopOffset = 360;
  var storyArchiveScrollKey = 'cowmStoryArchiveScrollRestore';
  var pendingStoryArchiveScroll = null;

  function getStoryArchiveUrlKey(url) {
    return url.pathname + url.search;
  }

  function clearStoryArchiveScrollRestore() {
    try {
      window.sessionStorage.removeItem(storyArchiveScrollKey);
    } catch (error) {
      return;
    }
  }

  function readStoryArchiveScrollRestore() {
    var rawValue;
    var payload;
    var currentUrlKey;

    try {
      rawValue = window.sessionStorage.getItem(storyArchiveScrollKey);
    } catch (error) {
      return null;
    }

    if (!rawValue) {
      return null;
    }

    try {
      payload = JSON.parse(rawValue);
    } catch (error) {
      clearStoryArchiveScrollRestore();
      return null;
    }

    if (
      !payload ||
      typeof payload.urlKey !== 'string' ||
      typeof payload.scrollY !== 'number' ||
      typeof payload.timestamp !== 'number'
    ) {
      clearStoryArchiveScrollRestore();
      return null;
    }

    currentUrlKey = getStoryArchiveUrlKey(window.location);

    if (
      payload.urlKey !== currentUrlKey ||
      Date.now() - payload.timestamp > 10000
    ) {
      clearStoryArchiveScrollRestore();
      return null;
    }

    return payload;
  }

  function restoreStoryArchiveScroll(payload) {
    var root = document.documentElement;
    var previousScrollBehavior = root.style.scrollBehavior;

    if (!payload) {
      return;
    }

    root.style.scrollBehavior = 'auto';
    window.scrollTo(0, payload.scrollY);

    window.requestAnimationFrame(function () {
      root.style.scrollBehavior = previousScrollBehavior;
    });
  }

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

  if (storyFilterLinks.length) {
    pendingStoryArchiveScroll = readStoryArchiveScrollRestore();

    if (pendingStoryArchiveScroll) {
      restoreStoryArchiveScroll(pendingStoryArchiveScroll);

      window.addEventListener('load', function () {
        restoreStoryArchiveScroll(pendingStoryArchiveScroll);
        clearStoryArchiveScrollRestore();
        pendingStoryArchiveScroll = null;
      });
    }

    Array.prototype.forEach.call(storyFilterLinks, function (link) {
      link.addEventListener('click', function (event) {
        var targetUrl;

        if (
          event.defaultPrevented ||
          event.metaKey ||
          event.ctrlKey ||
          event.shiftKey ||
          event.altKey ||
          event.button > 0
        ) {
          return;
        }

        try {
          targetUrl = new URL(link.href, window.location.href);

          window.sessionStorage.setItem(
            storyArchiveScrollKey,
            JSON.stringify({
              urlKey: getStoryArchiveUrlKey(targetUrl),
              scrollY: window.scrollY,
              timestamp: Date.now(),
            })
          );
        } catch (error) {
          return;
        }
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
