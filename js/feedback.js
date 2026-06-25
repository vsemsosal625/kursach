// js/feedback.js — пагинация истории обращений и удаление

document.addEventListener('DOMContentLoaded', function () {
    const PER_PAGE = 3;
    const list = document.getElementById('feedbackList');
    const pager = document.getElementById('feedbackPager');
    if (!list) return;

    let currentPage = 1;

    function items() {
        return Array.from(list.querySelectorAll('.feedback-item'));
    }

    function totalPages() {
        return Math.max(1, Math.ceil(items().length / PER_PAGE));
    }

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
            (function (page) {
                b.addEventListener('click', function () { currentPage = page; render(); });
            })(p);
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
        fd.append('id', id);
        fetch('delete_feedback.php', { method: 'POST', body: fd })
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