let currentFolder = '';

// Funkce pro získání překladu
function t(key, fallback = '') {
    const keys = key.split('.');
    let value = translations;
    
    for (const k of keys) {
        if (value && value[k]) {
            value = value[k];
        } else {
            return fallback || key;
        }
    }
    
    return value;
}

// Funkce pro ovládání jazykového dropdownu
function toggleLanguageDropdown(event) {
    event.preventDefault();
    const menu = document.getElementById('languageDropdownMenu');
    const isVisible = menu.style.display === 'block';
    
    // Zavřít všechny ostatní dropdowny
    document.querySelectorAll('.dropdown-menu').forEach(m => m.style.display = 'none');
    
    // Přepnout viditelnost
    menu.style.display = isVisible ? 'none' : 'block';
}

// Zavřít dropdown při kliknutí mimo něj
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('languageDropdown');
    const menu = document.getElementById('languageDropdownMenu');
    
    if (!dropdown.contains(event.target) && !menu.contains(event.target)) {
        menu.style.display = 'none';
    }
});

// Funkce pro změnu jazyka
function changeLanguage(lang) {
    // Zavřít dropdown
    document.getElementById('languageDropdownMenu').style.display = 'none';
    
    fetch('save_language.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ language: lang })
    })
    .then(res => {
        if (!res.ok) {
            throw new Error('Chyba při změně jazyka');
        }
        return res.text();
    })
    .then(() => {
        location.reload();
    })
    .catch(err => {
        console.error(err);
        showFlash('danger', 'Nepodařilo se změnit jazyk.');
    });
}

// Fulltext
document.getElementById('search').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    let visibleCount = 0;
    
    document.querySelectorAll('.project').forEach(el => {
        const name = el.dataset.name;
        const tags = el.dataset.tags;
        const isVisible = name.includes(q) || tags.includes(q);
        el.style.display = isVisible ? '' : 'none';
        if (isVisible) visibleCount++;
    });
    
    // Zobrazit/skrýt zprávu o žádných výsledcích
    showNoResultsMessage(q !== '' && visibleCount === 0);
});

// Funkce pro zobrazení zprávy o žádných výsledcích
function showNoResultsMessage(show) {
    let noResultsEl = document.getElementById('no-results-message');
    
    if (show && !noResultsEl) {
        noResultsEl = document.createElement('div');
        noResultsEl.id = 'no-results-message';
        noResultsEl.className = 'col-12 text-center py-5';
        noResultsEl.innerHTML = `
            <div class="empty">
                <div class="empty-icon">
                    <i class="ti ti-search icon"></i>
                </div>
                <p class="empty-title">${t('app.search_no_results')}</p>
            </div>
        `;
        document.getElementById('projects').appendChild(noResultsEl);
    } else if (!show && noResultsEl) {
        noResultsEl.remove();
    }
}

// Filtrace podle skupiny
document.querySelectorAll('#groupTabs .btn').forEach(btn => {
  btn.addEventListener('click', function (e) {
    e.preventDefault();

    const group = this.dataset.group;

    // Správně přepnout aktivní stav
    document.querySelectorAll('#groupTabs .btn').forEach(b => b.classList.remove('active'));
    this.classList.add('active');

    // Filtrovat projekty podle skupiny
    document.querySelectorAll('.project').forEach(card => {
      card.style.display = (group === 'all' || card.dataset.group === group) ? '' : 'none';
    });
  });
});

function submitEdit() {
    const title = document.getElementById('edit-title').value.trim();
    if (title === '') {
        showFlash('danger', t('messages.name_required'));
        return;
    }

    fetch('save.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            folder: currentFolder,
            title,
            description: document.getElementById('edit-description').value,
            tags: document.getElementById('edit-tags').value.split(',').map(t => t.trim()).filter(Boolean),
            group: document.getElementById('edit-group').value,
            entry: document.getElementById('edit-entry').value
        })
    })
        .then(res => {
            if (!res.ok) {
                showFlash('danger', t('messages.error'), t('messages.save_error'));
                throw new Error(t('messages.save_error'));
            }
            return res.text();
        })
        .then(() => {
            showFlash('success', t('messages.save_success'));
            const modal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
            modal.hide();
            setTimeout(() => location.reload(), 5000);
        })
        .catch(err => {
            console.error(err);
            showFlash('danger', t('messages.save_failed'));
        });
}
function editProject(folder) {
    const data = config[folder] || { title: folder, description: '', tags: [], group: '', entry: '' };
    currentFolder = folder;
    document.getElementById('edit-project-name').textContent = folder;
    document.getElementById('edit-folder').value = folder;
    document.getElementById('edit-title').value = data.title;
    document.getElementById('edit-description').value = data.description || '';
    document.getElementById('edit-tags').value = Array.isArray(data.tags) ? data.tags.join(', ') : '';
    document.getElementById('edit-group').value = data.group || '';
    document.getElementById('edit-entry').value = data.entry || '';
    new bootstrap.Modal(document.getElementById('editModal')).show();
}
function showFlash(type, message) {
    const icons = {
        success: `
      <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
        <path d="M5 12l5 5l10 -10"></path>
      </svg>`,
        danger: `
      <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
        <circle cx="12" cy="12" r="9" />
        <line x1="12" y1="8" x2="12" y2="12" />
        <line x1="12" y1="16" x2="12.01" y2="16" />
      </svg>`,
        warning: `
      <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 9v4" />
        <path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" />
        <path d="M12 16h.01" />
      </svg>`,
        info: `
      <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="9" />
        <line x1="12" y1="8" x2="12.01" y2="8" />
        <line x1="11" y1="12" x2="13" y2="12" />
        <line x1="12" y1="12" x2="12" y2="16" />
      </svg>`
    };

    const alert = document.createElement('div');
    alert.className = `alert alert-important alert-${type} alert-dismissible mb-3`;
    alert.setAttribute('role', 'alert');
    alert.innerHTML = `
    <div class="d-flex">
      <div>${icons[type]}</div>
      <div class="ms-2">${message}</div>
    </div>
    <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
  `;

    const container = document.getElementById('flash-container');
    container.appendChild(alert);

    setTimeout(() => {
        alert.remove();
    }, 5000);
}

function applyTheme(theme) {
  document.body.setAttribute('data-bs-theme', theme);
  document.documentElement.setAttribute('data-bs-theme', theme);
  localStorage.setItem('theme', theme);
}


document.addEventListener('DOMContentLoaded', () => {
  const savedTheme = localStorage.getItem('theme');
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  const theme = savedTheme || (prefersDark ? 'dark' : 'light');

  applyTheme(theme)

  document.getElementById('setDark')?.addEventListener('click', e => {
    e.preventDefault();
    applyTheme('dark');
  });

  document.getElementById('setLight')?.addEventListener('click', e => {
    e.preventDefault();
    applyTheme('light');
  });

  const dropdownElementList = document.querySelectorAll('.dropdown-toggle');
  const dropdownList = [...dropdownElementList].map(dropdownToggleEl => new bootstrap.Dropdown(dropdownToggleEl));
});





document.getElementById('sort').addEventListener('change', function () {
  const sortType = this.value;
  const container = document.getElementById('projects');
  const cards = Array.from(container.querySelectorAll('.project'));

  cards.sort((a, b) => {
    const nameA = a.dataset.name;
    const nameB = b.dataset.name;
    const timeA = parseInt(a.dataset.modified || '0');
    const timeB = parseInt(b.dataset.modified || '0');

    if (sortType === 'az') {
      return nameA.localeCompare(nameB);
    } else if (sortType === 'za') {
      return nameB.localeCompare(nameA);
    } else if (sortType === 'modified') {
      return timeB - timeA;
    }
    return 0;
  });

  // Přerenderuj
  cards.forEach(card => container.appendChild(card));
});
