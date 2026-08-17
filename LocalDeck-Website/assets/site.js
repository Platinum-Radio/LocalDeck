(() => {
  const menuButton = document.querySelector('.menu-button');
  const navigation = document.querySelector('.main-nav');
  menuButton?.addEventListener('click', () => {
    const open = navigation?.classList.toggle('open') ?? false;
    menuButton.setAttribute('aria-expanded', String(open));
  });

  const search = document.querySelector('[data-wiki-search]');
  const topics = [...document.querySelectorAll('[data-search-label]')];
  search?.addEventListener('input', event => {
    const query = event.target.value.trim().toLocaleLowerCase();
    topics.forEach(topic => {
      topic.hidden = query !== '' && !topic.dataset.searchLabel.includes(query);
    });
  });

  const topicSelect = document.querySelector('[data-topic-select]');
  document.querySelectorAll('[data-topic-type]').forEach(link => {
    link.addEventListener('click', () => {
      if (topicSelect) topicSelect.value = link.dataset.topicType;
    });
  });

  const tourTabs = [...document.querySelectorAll('[data-tour-tab]')];
  const tourPanels = [...document.querySelectorAll('[data-tour-panel]')];
  const selectTourTab = selected => {
    tourTabs.forEach(tab => {
      const active = tab === selected;
      tab.classList.toggle('active', active);
      tab.setAttribute('aria-selected', String(active));
      tab.tabIndex = active ? 0 : -1;
    });
    tourPanels.forEach(panel => {
      panel.hidden = panel.dataset.tourPanel !== selected.dataset.tourTab;
    });
  };
  tourTabs.forEach((tab, index) => {
    tab.addEventListener('click', () => selectTourTab(tab));
    tab.addEventListener('keydown', event => {
      if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
      event.preventDefault();
      const nextIndex = event.key === 'Home' ? 0 : event.key === 'End' ? tourTabs.length - 1 : (index + (event.key === 'ArrowRight' ? 1 : -1) + tourTabs.length) % tourTabs.length;
      selectTourTab(tourTabs[nextIndex]);
      tourTabs[nextIndex].focus();
    });
  });

  document.querySelectorAll('[data-copy]').forEach(button => {
    button.addEventListener('click', async () => {
      const original = button.textContent;
      try {
        await navigator.clipboard.writeText(button.dataset.copy || '');
        button.textContent = document.documentElement.lang === 'nl' ? 'Gekopieerd' : 'Copied';
      } catch {
        button.textContent = document.documentElement.lang === 'nl' ? 'Kopiëren mislukt' : 'Copy failed';
      }
      window.setTimeout(() => { button.textContent = original; }, 1800);
    });
  });

  document.querySelector('[data-doc-helpful]')?.addEventListener('click', event => {
    event.currentTarget.textContent = document.documentElement.lang === 'nl' ? 'Bedankt!' : 'Thank you!';
    event.currentTarget.disabled = true;
  });
})();
