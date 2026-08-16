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
})();

