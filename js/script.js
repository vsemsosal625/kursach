// js/script.js - Общие скрипты сайта

// Функция раскрытия/скрытия деталей обновлений
function toggleDetails() {
    const panel = document.getElementById('detailsPanel');
    const btnShow = document.getElementById('btnShow');
    const btnHide = document.getElementById('btnHide');
    if (panel && btnShow && btnHide) {
        if (panel.classList.contains('active')) {
            panel.classList.remove('active');
            btnShow.classList.remove('hidden');
            btnHide.classList.remove('visible');
        } else {
            panel.classList.add('active');
            btnShow.classList.add('hidden');
            btnHide.classList.add('visible');
        }
    }
}

// Карусель патчей
let currentSlide = 0;
const totalSlides = 2;

function moveCarousel(direction) {
    const track = document.getElementById('patchesTrack');
    if (!track) return;
    currentSlide += direction;
    if (currentSlide < 0) currentSlide = totalSlides - 1;
    if (currentSlide >= totalSlides) currentSlide = 0;
    track.style.transform = `translateX(-${currentSlide * 50}%)`;
}

// Свайп для мобильных
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.querySelector('.patches-carousel');
    if (carousel) {
        let startX = 0;
        carousel.addEventListener('touchstart', e => { startX = e.touches[0].clientX; }, {passive: true});
        carousel.addEventListener('touchend', e => {
            const diff = startX - e.changedTouches[0].clientX;
            if (Math.abs(diff) > 50) moveCarousel(diff > 0 ? 1 : -1);
        }, {passive: true});
    }
});
// ===== СКРИПТЫ СТРАНИЦЫ ГЕРОЕВ =====

// Фильтр героев
function toggleFilter() {
    const filterOptions = document.getElementById('filterOptions');
    if (filterOptions) {
        filterOptions.classList.toggle('show');
    }
}

function applyFilter(attr) {
    const url = new URL(window.location);
    if (attr) {
        url.searchParams.set('attr', attr);
        const names = {strength:'Сила', agility:'Ловкость', intelligence:'Интеллект', universal:'Универсальный'};
        const emojis = {strength:'🔴', agility:'🟢', intelligence:'🔵', universal:'🟡'};
        const currentFilter = document.getElementById('currentFilter');
        if (currentFilter) {
            currentFilter.textContent = (emojis[attr] || '') + ' ' + (names[attr] || 'Все');
        }
    } else {
        url.searchParams.delete('attr');
        const currentFilter = document.getElementById('currentFilter');
        if (currentFilter) {
            currentFilter.textContent = '🌐 Все';
        }
    }
    url.searchParams.delete('search');
    window.location.href = url;
}

// Поиск героев с подсветкой
function initHeroesSearch() {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.toLowerCase().trim();
        searchTimeout = setTimeout(() => {
            const url = new URL(window.location);
            if (query) {
                url.searchParams.set('search', query);
            } else {
                url.searchParams.delete('search');
            }
            document.querySelectorAll('.hero-card').forEach(card => {
                const heroName = card.getAttribute('data-name');
                if (query && heroName.includes(query)) {
                    card.classList.add('highlighted');
                } else {
                    card.classList.remove('highlighted');
                }
            });
        }, 300);
    });
}

// Инициализация подсветки при загрузке
function initHeroesHighlight() {
    const searchQuery = document.body.getAttribute('data-search-query') || '';
    if (searchQuery) {
        document.querySelectorAll('.hero-card').forEach(card => {
            const heroName = card.getAttribute('data-name');
            if (heroName.includes(searchQuery)) {
                card.classList.add('highlighted');
            }
        });
    }
}

// Закрытие фильтра при клике вне
document.addEventListener('click', function(e) {
    if (!e.target.closest('.filter-dropdown')) {
        const filterOptions = document.getElementById('filterOptions');
        if (filterOptions) {
            filterOptions.classList.remove('show');
        }
    }
});

// ===== СКРИПТЫ СТРАНИЦЫ ПРЕДМЕТОВ =====
function applyItemFilter(category) {
    const url = new URL(window.location);
    if (category) {
        url.searchParams.set('category', category);
    } else {
        url.searchParams.delete('category');
    }
    url.searchParams.delete('search');
    window.location.href = url;
}

function initItemsSearch() {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.toLowerCase().trim();
        searchTimeout = setTimeout(() => {
            const url = new URL(window.location);
            if (query) {
                url.searchParams.set('search', query);
            } else {
                url.searchParams.delete('search');
            }
            document.querySelectorAll('.item-card').forEach(card => {
                const itemName = card.getAttribute('data-name');
                if (query && itemName.includes(query)) {
                    card.classList.add('highlighted');
                } else {
                    card.classList.remove('highlighted');
                }
            });
        }, 300);
    });
}

function initItemsHighlight() {
    const searchQuery = document.body.getAttribute('data-search-query') || '';
    if (searchQuery) {
        document.querySelectorAll('.item-card').forEach(card => {
            const itemName = card.getAttribute('data-name');
            if (itemName.includes(searchQuery)) {
                card.classList.add('highlighted');
            }
        });
    }
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    initHeroesSearch();
    initHeroesHighlight();
    initItemsSearch();
    initItemsHighlight();
});
// ===== КНОПКА ИЗБРАННОГО =====
function toggleFavorite(type, id) {
    const formData = new FormData();
    formData.append('item_type', type);
    formData.append('item_id', id);
    fetch((window.BASE_URL || '') + '/api/add_favorite.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const btn = document.getElementById('favBtn');
            if (!btn) return;
            if (data.action === 'added') {
                btn.innerHTML = '<i class="fas fa-star"></i> В избранном';
                btn.style.background = 'rgba(251,191,36,0.3)';
                btn.style.borderColor = '#fbbf24';
                btn.style.color = '#fbbf24';
            } else {
                btn.innerHTML = '<i class="fas fa-bookmark"></i> Добавить в избранное';
                btn.style.background = 'rgba(251,191,36,0.15)';
                btn.style.borderColor = '#fbbf24';
                btn.style.color = '#fbbf24';
            }
        } else if (data.error === 'Не авторизован') {
            // Гость пытается добавить в избранное — предлагаем войти
            if (confirm('Чтобы добавлять в избранное, нужно войти в систему. Перейти на страницу входа?')) {
                window.location.href = (window.BASE_URL || '') + '/auth/auth.php';
            }
        }
    })
    .catch(err => console.error('Ошибка:', err));
}
// ===== СКРИПТЫ СТРАНИЦЫ МЕХАНИК =====
function toggleMechanicFilter() { 
    const filterOptions = document.getElementById('filterOptions');
    const sortOptions = document.getElementById('sortOptions');
    if(filterOptions) filterOptions.classList.toggle('show');
    if(sortOptions) sortOptions.classList.remove('show'); 
}

function toggleSort() { 
    const sortOptions = document.getElementById('sortOptions');
    const filterOptions = document.getElementById('filterOptions');
    if(sortOptions) sortOptions.classList.toggle('show'); 
    if(filterOptions) filterOptions.classList.remove('show'); 
}

function applyMechanicFilter(cat) { 
    const url = new URL(window.location);
    if(cat) {
        url.searchParams.set('category', cat);
    } else {
        url.searchParams.delete('category');
    }
    url.searchParams.delete('search');
    window.location.href = url; 
}

function applySort(sort) { 
    const url = new URL(window.location);
    url.searchParams.set('sort', sort); 
    window.location.href = url; 
}

document.addEventListener('click', function(e) { 
    if (!e.target.closest('.filter-dropdown') && !e.target.closest('.sort-dropdown')) { 
        const filterOptions = document.getElementById('filterOptions');
        const sortOptions = document.getElementById('sortOptions');
        if(filterOptions) filterOptions.classList.remove('show'); 
        if(sortOptions) sortOptions.classList.remove('show'); 
    } 
});

// Инициализация поиска для механик
document.addEventListener('DOMContentLoaded', function() {
    const searchInputMech = document.getElementById('searchInputMechanics'); 
    if(searchInputMech) {
        let searchTimeoutMech;
        searchInputMech.addEventListener('input', function() { 
            clearTimeout(searchTimeoutMech); 
            const query = this.value.trim(); 
            searchTimeoutMech = setTimeout(() => { 
                const url = new URL(window.location); 
                if(query) url.searchParams.set('search', query); 
                else url.searchParams.delete('search');
                window.location.href = url; 
            }, 500); 
        });
    }
});

// ===== ОБРАТНАЯ СВЯЗЬ: пагинация истории и удаление (раздел feedback.php) =====
document.addEventListener('DOMContentLoaded', function () {
    const PER_PAGE = 3;
    const list = document.getElementById('feedbackList');
    const pager = document.getElementById('feedbackPager');
    if (!list) return;
    let currentPage = 1;
    function items() { return Array.from(list.querySelectorAll('.feedback-item')); }
    function totalPages() { return Math.max(1, Math.ceil(items().length / PER_PAGE)); }
    function render() {
        const all = items();
        const pages = totalPages();
        if (currentPage > pages) currentPage = pages;
        all.forEach(function (el, i) {
            const page = Math.floor(i / PER_PAGE) + 1;
            el.style.display = (page === currentPage) ? '' : 'none';
        });
        renderPager(pages);
    }
    function renderPager(pages) {
        if (!pager) return;
        pager.innerHTML = '';
        if (pages <= 1) { pager.style.display = 'none'; return; }
        pager.style.display = 'flex';
        const prev = document.createElement('button');
        prev.className = 'pager-btn';
        prev.innerHTML = '<i class="fas fa-chevron-left"></i>';
        prev.disabled = currentPage === 1;
        prev.addEventListener('click', function () { if (currentPage > 1) { currentPage--; render(); } });
        pager.appendChild(prev);
        for (let p = 1; p <= pages; p++) {
            const b = document.createElement('button');
            b.className = 'pager-btn' + (p === currentPage ? ' active' : '');
            b.textContent = p;
            (function (page) { b.addEventListener('click', function () { currentPage = page; render(); }); })(p);
            pager.appendChild(b);
        }
        const next = document.createElement('button');
        next.className = 'pager-btn';
        next.innerHTML = '<i class="fas fa-chevron-right"></i>';
        next.disabled = currentPage === pages;
        next.addEventListener('click', function () { if (currentPage < pages) { currentPage++; render(); } });
        pager.appendChild(next);
    }
    window.deleteFeedback = function (id, btn) {
        if (!confirm('Удалить это обращение? Действие необратимо.')) return;
        const fd = new FormData();
        fd.append('ajax', 'delete');
        fd.append('id', id);
        fetch((window.BASE_URL || '') + '/account/feedback.php', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    const card = btn.closest('.feedback-item');
                    if (card) card.remove();
                    render();
                } else {
                    alert(data.error || 'Не удалось удалить обращение');
                }
            })
            .catch(function () { alert('Ошибка сети при удалении'); });
    };
    render();
});