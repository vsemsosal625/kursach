<?php
$pageTitle = 'Главная';
require_once __DIR__ . '/includes/header.php';
?>

<div class="updates-container">
    <div class="updates-header">
        <h3 class="updates-title">Последние обновления</h3>
    </div>
    <p class="summary-text">
        Вышло обновление 7.41. Список изменений можно найти здесь. Крупное обновление игрового баланса, исправления ошибок и оптимизация сетевой части. Мы также внесли улучшения в систему подбора героев и интерфейс магазина.
    </p>
    <button class="toggle-btn" id="btnShow" onclick="toggleDetails()">
        <i class="fas fa-list-ul me-2"></i>Смотреть изменения
    </button>
    <div class="details-panel" id="detailsPanel">
        <div class="patches-carousel">
            <button class="carousel-nav prev" onclick="moveCarousel(-1)"><i class="fas fa-chevron-left"></i></button>
            <button class="carousel-nav next" onclick="moveCarousel(1)"><i class="fas fa-chevron-right"></i></button>
            <div class="patches-track" id="patchesTrack">
                <div class="patch-slide">
                    <div class="patch-card">
                        <span class="patch-badge">ОБНОВЛЕНИЕ 7.41</span>
                        <h4 class="patch-title">Крупное обновление игрового баланса</h4>
                        <div class="patch-content">
                            <p><strong>Изменения героев:</strong> переработаны способности 15 персонажей, изменены базовые характеристики.</p>
                            <p><strong>Изменения предметов:</strong> обновлены рецепты сборки, изменена стоимость 8 предметов.</p>
                            <p><strong>Игровые механики:</strong> новая система опыта, изменения в механике крипов.</p>
                            <p><strong>Карта:</strong> добавлены новые точки интереса, изменено расположение рун.</p>
                            <hr style="border-color: rgba(255,255,255,0.1); margin: 20px 0;">
                            <p>Когда герой применяет на союзника расходник или передаёт расходник союзнику в радиусе 1200 (например, Tango), над обоими героями отображается соответствующий индикатор.</p>
                            <p>В настройки добавлена опция, позволяющая зажать кнопку выбора героя, чтобы увидеть его текущую область обзора с учётом тумана войны.</p>
                            <p>Составляющие в рецептах предметов упорядочены от самых дорогих к самым дешёвым, чтобы быстрая покупка была более предсказуемой.</p>
                            <p>Исправлена ошибка, из-за которой некоторые эффекты от попадания по существу не работали должным образом после того, как в полёте одновременно оказывалось более 256 снарядов.</p>
                            <p style="color: #66c0f4; margin-top: 10px;">Мы отказываемся от поддержки видеокарт, не совместимых с DirectX 11 и Shader Model 5, поскольку эти API и функции необходимы для будущих возможностей графики.</p>
                        </div>
                    </div>
                </div>
                <div class="patch-slide">
                    <div class="patch-card style-a">
                        <span class="patch-badge style-a">ИСПРАВЛЕНИЯ 7.41a</span>
                        <h4 class="patch-title">Исправления ошибок и баланс</h4>
                        <div class="patch-content style-a-text">
                            <p><strong>Исправления стабильности:</strong> устранены различные причины сбоя клиента и серверов игры.</p>
                            <p><strong>Meepo:</strong> исправлены множественные ошибки взаимодействия с предметами Hand of Midas, Dust of Appearance, Radiance, Divine Rapier.</p>
                            <p><strong>Chen:</strong> исправлено взаимодействие Holy Persuasion с фанатиками и гранитными големами.</p>
                            <p><strong>Bloodseeker:</strong> исправлен урон Blood Rite и прохождение Bloodrage сквозь невосприимчивость к эффектам.</p>
                            <p><strong>Dragon Knight:</strong> исправлено двойное срабатывание Wyrm's Wrath и прохождение заморозки Elder Dragon Form.</p>
                            <p><strong>Drow Ranger:</strong> исправлено срабатывание Marksmanship на дополнительные атаки.</p>
                            <p><strong>Legion Commander:</strong> исправлено завершение Duel при победе над иллюзией.</p>
                            <p><strong>Сетевая часть:</strong> оптимизирована синхронизация снарядов и уменьшена задержка при использовании курьера.</p>
                            <p style="color: #34d399; margin-top: 15px;">Устранено более 50 ошибок игрового баланса и визуальных эффектов. Обновлено ядро matchmaking-системы.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <button class="toggle-btn" id="btnHide" onclick="toggleDetails()">
        <i class="fas fa-chevron-up me-2"></i>Скрыть
    </button>
</div>

<div class="popular-sections">
    <h2><i class="fas fa-fire" style="color: #f59e0b; margin-right: 10px;"></i>Популярные разделы</h2>
    <div class="sections-grid">
        <a href="<?= BASE_URL ?>/sections/roles/tactics.php?category=Функциональные+роли+игроков" class="section-card">
            <span class="icon"><i class="fas fa-chess-board" style="color: #3b82f6;"></i></span>
            <h4>Функциональные роли игроков</h4>
        </a>
        <a href="<?= BASE_URL ?>/sections/adaptation/objects.php" class="section-card">
            <span class="icon"><i class="fas fa-clock" style="color: #10b981;"></i></span>
            <h4>Адаптация и расчет времени</h4>
        </a>
        <a href="<?= BASE_URL ?>/sections/rating/rating.php" class="section-card">
            <span class="icon"><i class="fas fa-trophy" style="color: #fbbf24;"></i></span>
            <h4>Рейтинг героев</h4>
        </a>
        <a href="<?= BASE_URL ?>/sections/newbie/newbie.php" class="section-card">
            <span class="icon"><i class="fas fa-book-reader" style="color: #8b5cf6;"></i></span>
            <h4>Для новичков</h4>
        </a>
        <a href="<?= BASE_URL ?>/sections/synergy/synergy.php" class="section-card">
            <span class="icon"><i class="fas fa-link" style="color: #06b6d4;"></i></span>
            <h4>Синергия героев на линии</h4>
        </a>
        <a href="<?= BASE_URL ?>/sections/settings/settings.php" class="section-card">
            <span class="icon"><i class="fas fa-cog" style="color: #8b5cf6;"></i></span>
            <h4>Оптимальные настройки игры</h4>
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>