document.addEventListener('DOMContentLoaded', function () {
  var menuToggle = document.querySelector('.menu-toggle');
  var navigation = document.querySelector('.primary-nav');
  var searchToggle = document.querySelector('[data-search-toggle]');
  var searchPanel = document.querySelector('[data-search-panel]');
  var backToTopButton = document.querySelector('[data-back-to-top]');
  var backToTopOffset = 360;
  var storyArchiveScrollKey = 'cowmStoryArchiveScrollRestore';
  var pendingStoryArchiveScroll = null;

  function normalizeFilterText(value) {
    var text = (value || '').toString().toLowerCase();

    if (text.normalize) {
      text = text.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }

    return text.trim();
  }

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

  // Multi-tag filter bar.
  var multiTagBar = document.querySelector('[data-multi-tag-bar]');

  if (multiTagBar) {
    var archiveBaseUrl = multiTagBar.getAttribute('data-archive-url') || window.location.pathname;
    var extraArgsRaw = multiTagBar.getAttribute('data-extra-args') || '{}';
    var extraArgs = {};

    try {
      extraArgs = JSON.parse(extraArgsRaw);
    } catch (e) {
      extraArgs = {};
    }

    // Read active tags from URL.
    function getActiveTagIds() {
      var params = new URLSearchParams(window.location.search);
      var raw = params.get('story_tag') || '';

      if (!raw) {
        return [];
      }

      return raw
        .split(',')
        .map(function (v) { return parseInt(v, 10); })
        .filter(function (v) { return v > 0; });
    }

    function buildUrl(tagIds) {
      var url = new URL(archiveBaseUrl, window.location.origin);

      // Apply extra args (e.g. story_category).
      var key;
      for (key in extraArgs) {
        if (extraArgs.hasOwnProperty(key)) {
          url.searchParams.set(key, extraArgs[key]);
        }
      }

      if (tagIds.length > 0) {
        url.searchParams.set('story_tag', tagIds.join(','));
      }

      return url.toString();
    }

    function saveScrollRestore(targetUrl) {
      try {
        var parsed = new URL(targetUrl, window.location.href);

        window.sessionStorage.setItem(
          storyArchiveScrollKey,
          JSON.stringify({
            urlKey: getStoryArchiveUrlKey(parsed),
            scrollY: window.scrollY,
            timestamp: Date.now(),
          })
        );
      } catch (error) {
        return;
      }
    }

    // Restore scroll on load if coming from a filter toggle.
    pendingStoryArchiveScroll = readStoryArchiveScrollRestore();

    if (pendingStoryArchiveScroll) {
      restoreStoryArchiveScroll(pendingStoryArchiveScroll);

      window.addEventListener('load', function () {
        restoreStoryArchiveScroll(pendingStoryArchiveScroll);
        clearStoryArchiveScrollRestore();
        pendingStoryArchiveScroll = null;
      });
    }

    // Reset button ("Tất cả").
    var resetBtn = multiTagBar.querySelector('[data-tag-reset]');

    if (resetBtn) {
      resetBtn.addEventListener('click', function () {
        var url = buildUrl([]);
        saveScrollRestore(url);
        window.location.href = url;
      });
    }

    // Tag toggle buttons.
    var tagButtons = multiTagBar.querySelectorAll('[data-tag-id]');

    Array.prototype.forEach.call(tagButtons, function (btn) {
      btn.addEventListener('click', function () {
        var tagId = parseInt(btn.getAttribute('data-tag-id'), 10);

        if (!tagId) {
          return;
        }

        var activeIds = getActiveTagIds();
        var index = activeIds.indexOf(tagId);

        if (index > -1) {
          // Remove tag (deselect).
          activeIds.splice(index, 1);
        } else {
          // Add tag (select).
          activeIds.push(tagId);
        }

        var url = buildUrl(activeIds);
        saveScrollRestore(url);
        window.location.href = url;
      });
    });
  }

  // Homepage profiling board filters.
  var profileBoards = document.querySelectorAll('[data-profile-board]');

  Array.prototype.forEach.call(profileBoards, function (board) {
    var searchInput = board.querySelector('[data-profile-search-input]');
    var filterButtons = board.querySelectorAll('[data-profile-filter-button]');
    var cards = board.querySelectorAll('[data-profile-card]');
    var emptyState = board.querySelector('[data-profile-empty]');
    var resultCount = board.querySelector('[data-profile-result-count]');
    var activeFilter = 'all';

    function updateFilterButtons() {
      Array.prototype.forEach.call(filterButtons, function (button) {
        var isActive = (button.getAttribute('data-profile-filter') || 'all') === activeFilter;

        button.classList.toggle('is-active', isActive);
        button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
      });
    }

    function applyBoardFilters() {
      var query = normalizeFilterText(searchInput ? searchInput.value : '');
      var visibleCount = 0;
      var seenStoryIds = Object.create(null);

      Array.prototype.forEach.call(cards, function (card) {
        var cardFilter = card.getAttribute('data-profile-filter-value') || '';
        var storyId = card.getAttribute('data-profile-story-id') || '';
        var cardSearchText = normalizeFilterText(card.getAttribute('data-profile-search') || '');
        var matchesFilter = activeFilter === 'all' || cardFilter === activeFilter;
        var matchesSearch = !query || cardSearchText.indexOf(query) !== -1;
        var isVisible = matchesFilter && matchesSearch;

        if (isVisible && activeFilter === 'all' && storyId) {
          if (seenStoryIds[storyId]) {
            isVisible = false;
          } else {
            seenStoryIds[storyId] = true;
          }
        }

        card.hidden = !isVisible;
        card.classList.toggle('is-hidden', !isVisible);

        if (isVisible) {
          visibleCount += 1;
        }
      });

      if (emptyState) {
        emptyState.hidden = visibleCount > 0;
      }

      if (resultCount) {
        resultCount.textContent = visibleCount + ' hồ sơ đang mở trên bàn';
      }
    }

    Array.prototype.forEach.call(filterButtons, function (button) {
      button.addEventListener('click', function () {
        activeFilter = button.getAttribute('data-profile-filter') || 'all';
        updateFilterButtons();
        applyBoardFilters();
      });
    });

    if (searchInput) {
      searchInput.addEventListener('input', applyBoardFilters);
    }

    updateFilterButtons();
    applyBoardFilters();
  });

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
