<?php
require_once __DIR__ . '/../config/init.php';
requireAdmin();
require_once __DIR__ . '/../config/game_content.php';

$pdo = getDB();
gc_ensureTables($pdo);

$type = $_GET['type'] ?? 'hero';
if (!in_array($type, ['hero', 'item'], true)) $type = 'hero';

$attrs = ['strength' => '🔴 Сила', 'agility' => '🟢 Ловкость', 'intelligence' => '🔵 Интеллект', 'universal' => '🟡 Универсальный'];
$cats = ['Артефакт', 'Оружие', 'Расходники', 'Поддержка'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ptype = $_POST['type'] ?? 'hero';
    if (!in_array($ptype, ['hero', 'item'], true)) $ptype = 'hero';
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $rid = (int)($_POST['id'] ?? 0);
        $tbl = $ptype === 'hero' ? 'hero_catalog' : 'item_catalog';
        $stmt = $pdo->prepare("DELETE FROM $tbl WHERE id = ?");
        $stmt->execute([$rid]);
    } elseif ($action === 'create' || $action === 'update') {
        if ($ptype === 'hero') {
            $name = trim($_POST['name'] ?? '');
            $attr = $_POST['attr'] ?? 'strength';
            if (!isset($attrs[$attr])) $attr = 'strength';
            $fields = [
                'name' => $name,
                'attr' => $attr,
                'attr_name' => gc_heroAttrName($attr),
                'attack' => trim($_POST['attack'] ?? ''),
                'roles' => trim($_POST['roles'] ?? ''),
                'base_str' => (int)($_POST['base_str'] ?? 0),
                'base_agi' => (int)($_POST['base_agi'] ?? 0),
                'base_int' => (int)($_POST['base_int'] ?? 0),
                'gain_str' => trim($_POST['gain_str'] ?? '0'),
                'gain_agi' => trim($_POST['gain_agi'] ?? '0'),
                'gain_int' => trim($_POST['gain_int'] ?? '0'),
                'abilities' => trim($_POST['abilities'] ?? ''),
                'image_url' => trim($_POST['image_url'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'tips' => trim($_POST['tips'] ?? ''),
            ];
            if ($name !== '') {
                if ($action === 'create') {
                    $cols = implode(', ', array_keys($fields));
                    $ph = implode(', ', array_fill(0, count($fields), '?'));
                    $stmt = $pdo->prepare("INSERT INTO hero_catalog ($cols) VALUES ($ph)");
                    $stmt->execute(array_values($fields));
                } else {
                    $rid = (int)($_POST['id'] ?? 0);
                    $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($fields)));
                    $stmt = $pdo->prepare("UPDATE hero_catalog SET $set WHERE id = ?");
                    $stmt->execute(array_merge(array_values($fields), [$rid]));
                }
            }
        } else {
            $name = trim($_POST['name'] ?? '');
            $category = trim($_POST['category'] ?? '');
            if (!in_array($category, $cats, true)) $category = 'Артефакт';
            $fields = [
                'name' => $name,
                'category' => $category,
                'cost' => trim($_POST['cost'] ?? ''),
                'components' => trim($_POST['components'] ?? ''),
                'bonuses' => trim($_POST['bonuses'] ?? ''),
                'effects' => trim($_POST['effects'] ?? ''),
                'strong_against' => trim($_POST['strong_against'] ?? ''),
                'image_url' => trim($_POST['image_url'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'tips' => trim($_POST['tips'] ?? ''),
            ];
            if ($name !== '') {
                if ($action === 'create') {
                    $cols = implode(', ', array_keys($fields));
                    $ph = implode(', ', array_fill(0, count($fields), '?'));
                    $stmt = $pdo->prepare("INSERT INTO item_catalog ($cols) VALUES ($ph)");
                    $stmt->execute(array_values($fields));
                } else {
                    $rid = (int)($_POST['id'] ?? 0);
                    $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($fields)));
                    $stmt = $pdo->prepare("UPDATE item_catalog SET $set WHERE id = ?");
                    $stmt->execute(array_merge(array_values($fields), [$rid]));
                }
            }
        }
    }
    header('Location: ' . BASE_URL . '/admin/heroes_items.php?type=' . urlencode($ptype));
    exit;
}

$tbl = $type === 'hero' ? 'hero_catalog' : 'item_catalog';
$rows = $pdo->query("SELECT * FROM $tbl ORDER BY id")->fetchAll();

$pageTitle = $type === 'hero' ? 'Управление героями' : 'Управление предметами';
$currentPage = 'admin';

require_once __DIR__ . '/../includes/header.php';
?>

<style>
.hi-wrap { max-width:1000px; margin:0 auto; }
.hi-back { display:inline-block; color:#fbbf24; text-decoration:none; margin-bottom:16px; }
.hi-head { display:flex; align-items:center; gap:12px; margin-bottom:18px; }
.hi-head i { font-size:26px; color:#fbbf24; }
.hi-head h1 { margin:0; color:#fff; font-size:26px; }
.hi-tabs { display:flex; gap:8px; margin-bottom:22px; }
.hi-tab { padding:9px 20px; border-radius:20px; background:#1b2838; border:1px solid #36414d; color:#c7d0d8; text-decoration:none; font-size:14px; }
.hi-tab.active { background:#fbbf24; border-color:#fbbf24; color:#1b2838; font-weight:600; }
.hi-form-box { background:linear-gradient(135deg,#1b2838,#2a475e); border:1px solid #36414d; border-radius:12px; padding:22px; margin-bottom:28px; }
.hi-form-box h2 { margin:0 0 16px; color:#fff; font-size:18px; }
.hi-field { margin-bottom:14px; }
.hi-field label { display:block; color:#c7d0d8; font-size:13px; margin-bottom:6px; }
.hi-field input, .hi-field select, .hi-field textarea { width:100%; background:#0f1923; border:1px solid #36414d; border-radius:8px; color:#e0e0e0; padding:10px; box-sizing:border-box; }
.hi-field textarea { min-height:80px; resize:vertical; }
.hi-grid3 { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; }
.hi-actions { display:flex; gap:10px; align-items:center; margin-top:6px; }
.btn-primary2 { background:#fbbf24; color:#1b2838; border:none; border-radius:8px; padding:10px 20px; font-weight:600; cursor:pointer; }
.btn-cancel { background:transparent; color:#8f98a0; border:1px solid #36414d; border-radius:8px; padding:10px 16px; cursor:pointer; display:none; }
.hi-row { background:#1b2838; border:1px solid #36414d; border-radius:10px; padding:12px 16px; margin-bottom:10px; display:flex; justify-content:space-between; gap:14px; align-items:center; }
.hi-row-main { flex:1; min-width:0; display:flex; align-items:center; gap:14px; }
.hi-thumb { width:54px; height:54px; border-radius:8px; background:#0f1419; flex-shrink:0; display:flex; align-items:center; justify-content:center; overflow:hidden; border:1px solid #36414d; }
.hi-thumb img { width:100%; height:100%; object-fit:cover; }
.hi-thumb i { color:#8f98a0; }
.hi-row h4 { margin:0 0 4px; color:#fff; font-size:15px; }
.hi-row .meta { color:#8f98a0; font-size:13px; }
.hi-row-btns { display:flex; gap:8px; flex-shrink:0; }
.btn-edit { background:transparent; color:#fbbf24; border:1px solid #fbbf24; border-radius:8px; padding:7px 12px; cursor:pointer; font-size:13px; }
.btn-del2 { background:transparent; color:#f87171; border:1px solid #f87171; border-radius:8px; padding:7px 12px; cursor:pointer; font-size:13px; }
.hi-empty { color:#8f98a0; padding:14px; }
.hi-hint { background:rgba(251,191,36,0.08); border:1px solid rgba(251,191,36,0.3); border-radius:8px; padding:10px 14px; color:#c7d0d8; font-size:13px; margin-bottom:14px; line-height:1.5; }
</style>

<div class="hi-wrap">
    <a class="hi-back" href="<?= BASE_URL ?>/admin/index.php"><i class="fas fa-arrow-left"></i> Назад в админ-панель</a>
    <div class="hi-head"><i class="fas <?= $type === 'hero' ? 'fa-dragon' : 'fa-gem' ?>"></i><h1><?= htmlspecialchars($pageTitle) ?></h1></div>

    <div class="hi-tabs">
        <a class="hi-tab <?= $type === 'hero' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/heroes_items.php?type=hero">🐉 Герои</a>
        <a class="hi-tab <?= $type === 'item' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/heroes_items.php?type=item">💎 Предметы</a>
    </div>

    <div class="hi-form-box">
        <h2 id="form-title"><?= $type === 'hero' ? 'Добавить героя' : 'Добавить предмет' ?></h2>
        <div class="hi-hint"><i class="fas fa-image"></i> Фото задаётся ссылкой (URL картинки) в поле «Ссылка на фото».</div>
        <form method="post">
            <input type="hidden" name="type" value="<?= $type ?>">
            <input type="hidden" name="action" id="form-action" value="create">
            <input type="hidden" name="id" id="form-id" value="">

            <?php if ($type === 'hero'): ?>
                <div class="hi-field"><label>Имя героя</label><input type="text" name="name" id="f-name" required></div>
                <div class="hi-field"><label>Атрибут</label>
                    <select name="attr" id="f-attr">
                        <?php foreach ($attrs as $k => $lbl): ?>
                            <option value="<?= $k ?>"><?= $lbl ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="hi-field"><label>Ссылка на фото (URL)</label><input type="text" name="image_url" id="f-image_url" placeholder="https://..."></div>
                <div class="hi-field"><label>Тип атаки</label><input type="text" name="attack" id="f-attack" placeholder="Ближняя / Дальняя"></div>
                <div class="hi-field"><label>Роли</label><input type="text" name="roles" id="f-roles" placeholder="Напр.: Инициатор, Танк"></div>
                <div class="hi-grid3">
                    <div class="hi-field"><label>Базовая Сила</label><input type="number" name="base_str" id="f-base_str" value="0"></div>
                    <div class="hi-field"><label>Базовая Ловкость</label><input type="number" name="base_agi" id="f-base_agi" value="0"></div>
                    <div class="hi-field"><label>Базовый Интеллект</label><input type="number" name="base_int" id="f-base_int" value="0"></div>
                </div>
                <div class="hi-grid3">
                    <div class="hi-field"><label>Прирост Силы</label><input type="text" name="gain_str" id="f-gain_str" value="0"></div>
                    <div class="hi-field"><label>Прирост Ловкости</label><input type="text" name="gain_agi" id="f-gain_agi" value="0"></div>
                    <div class="hi-field"><label>Прирост Интеллекта</label><input type="text" name="gain_int" id="f-gain_int" value="0"></div>
                </div>
                <div class="hi-field"><label>Способности (по одной на строку)</label><textarea name="abilities" id="f-abilities"></textarea></div>
                <div class="hi-field"><label>Описание</label><textarea name="description" id="f-description"></textarea></div>
                <div class="hi-field"><label>Советы</label><textarea name="tips" id="f-tips"></textarea></div>
            <?php else: ?>
                <div class="hi-field"><label>Название предмета</label><input type="text" name="name" id="f-name" required></div>
                <div class="hi-field"><label>Категория</label>
                    <select name="category" id="f-category">
                        <?php foreach ($cats as $c): ?>
                            <option value="<?= htmlspecialchars($c, ENT_QUOTES) ?>"><?= htmlspecialchars($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="hi-field"><label>Ссылка на фото (URL)</label><input type="text" name="image_url" id="f-image_url" placeholder="https://..."></div>
                <div class="hi-field"><label>Стоимость</label><input type="text" name="cost" id="f-cost" placeholder="Напр.: 4050 золота"></div>
                <div class="hi-field"><label>Компоненты</label><textarea name="components" id="f-components"></textarea></div>
                <div class="hi-field"><label>Бонусы</label><textarea name="bonuses" id="f-bonuses"></textarea></div>
                <div class="hi-field"><label>Эффекты</label><textarea name="effects" id="f-effects"></textarea></div>
                <div class="hi-field"><label>Эффективен против</label><textarea name="strong_against" id="f-strong_against"></textarea></div>
                <div class="hi-field"><label>Описание</label><textarea name="description" id="f-description"></textarea></div>
                <div class="hi-field"><label>Советы</label><textarea name="tips" id="f-tips"></textarea></div>
            <?php endif; ?>

            <div class="hi-actions">
                <button class="btn-primary2" type="submit" id="submit-btn">Добавить</button>
                <button class="btn-cancel" type="button" id="cancel-edit" onclick="cancelEdit()">Отмена</button>
            </div>
        </form>
    </div>

    <?php if (empty($rows)): ?>
        <div class="hi-empty">Пока нет записей.</div>
    <?php else: foreach ($rows as $r): ?>
        <div class="hi-row">
            <div class="hi-row-main">
                <div class="hi-thumb">
                    <?php if (!empty($r['image_url'])): ?>
                        <img src="<?= htmlspecialchars($r['image_url']) ?>" alt="<?= htmlspecialchars($r['name']) ?>">
                    <?php else: ?>
                        <i class="fas <?= $type === 'hero' ? 'fa-user' : 'fa-gem' ?>"></i>
                    <?php endif; ?>
                </div>
                <div>
                    <h4><?= htmlspecialchars($r['name']) ?></h4>
                    <div class="meta">#<?= $r['id'] ?> · <?= $type === 'hero' ? htmlspecialchars($r['attr_name']) : htmlspecialchars($r['category']) ?></div>
                </div>
            </div>
            <div class="hi-row-btns">
                <?php if ($type === 'hero'): ?>
                    <button class="btn-edit" onclick="editRow(this)"
                        data-id="<?= $r['id'] ?>"
                        data-name="<?= htmlspecialchars($r['name'], ENT_QUOTES) ?>"
                        data-attr="<?= htmlspecialchars($r['attr'], ENT_QUOTES) ?>"
                        data-attack="<?= htmlspecialchars($r['attack'], ENT_QUOTES) ?>"
                        data-roles="<?= htmlspecialchars($r['roles'], ENT_QUOTES) ?>"
                        data-base_str="<?= htmlspecialchars($r['base_str'], ENT_QUOTES) ?>"
                        data-base_agi="<?= htmlspecialchars($r['base_agi'], ENT_QUOTES) ?>"
                        data-base_int="<?= htmlspecialchars($r['base_int'], ENT_QUOTES) ?>"
                        data-gain_str="<?= htmlspecialchars($r['gain_str'], ENT_QUOTES) ?>"
                        data-gain_agi="<?= htmlspecialchars($r['gain_agi'], ENT_QUOTES) ?>"
                        data-gain_int="<?= htmlspecialchars($r['gain_int'], ENT_QUOTES) ?>"
                        data-abilities="<?= htmlspecialchars($r['abilities'], ENT_QUOTES) ?>"
                        data-image_url="<?= htmlspecialchars($r['image_url'], ENT_QUOTES) ?>"
                        data-description="<?= htmlspecialchars($r['description'], ENT_QUOTES) ?>"
                        data-tips="<?= htmlspecialchars($r['tips'], ENT_QUOTES) ?>"><i class="fas fa-pen"></i></button>
                <?php else: ?>
                    <button class="btn-edit" onclick="editRow(this)"
                        data-id="<?= $r['id'] ?>"
                        data-name="<?= htmlspecialchars($r['name'], ENT_QUOTES) ?>"
                        data-category="<?= htmlspecialchars($r['category'], ENT_QUOTES) ?>"
                        data-cost="<?= htmlspecialchars($r['cost'], ENT_QUOTES) ?>"
                        data-components="<?= htmlspecialchars($r['components'], ENT_QUOTES) ?>"
                        data-bonuses="<?= htmlspecialchars($r['bonuses'], ENT_QUOTES) ?>"
                        data-effects="<?= htmlspecialchars($r['effects'], ENT_QUOTES) ?>"
                        data-strong_against="<?= htmlspecialchars($r['strong_against'], ENT_QUOTES) ?>"
                        data-image_url="<?= htmlspecialchars($r['image_url'], ENT_QUOTES) ?>"
                        data-description="<?= htmlspecialchars($r['description'], ENT_QUOTES) ?>"
                        data-tips="<?= htmlspecialchars($r['tips'], ENT_QUOTES) ?>"><i class="fas fa-pen"></i></button>
                <?php endif; ?>
                <form method="post" onsubmit="return confirm('Удалить эту запись?');" style="margin:0;">
                    <input type="hidden" name="type" value="<?= $type ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <button class="btn-del2" type="submit"><i class="fas fa-trash"></i></button>
                </form>
            </div>
        </div>
    <?php endforeach; endif; ?>
</div>

<script>
var HI_TYPE = <?= json_encode($type) ?>;
var HI_FIELDS = HI_TYPE === 'hero'
    ? ['name','attr','attack','roles','base_str','base_agi','base_int','gain_str','gain_agi','gain_int','abilities','image_url','description','tips']
    : ['name','category','cost','components','bonuses','effects','strong_against','image_url','description','tips'];

function editRow(btn) {
    document.getElementById('form-action').value = 'update';
    document.getElementById('form-id').value = btn.dataset.id;
    HI_FIELDS.forEach(function(f) {
        var el = document.getElementById('f-' + f);
        if (el) el.value = btn.dataset[f] !== undefined ? btn.dataset[f] : '';
    });
    document.getElementById('form-title').textContent = (HI_TYPE === 'hero' ? 'Редактировать героя #' : 'Редактировать предмет #') + btn.dataset.id;
    document.getElementById('submit-btn').textContent = 'Сохранить изменения';
    document.getElementById('cancel-edit').style.display = 'inline-block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
function cancelEdit() {
    document.getElementById('form-action').value = 'create';
    document.getElementById('form-id').value = '';
    HI_FIELDS.forEach(function(f) {
        var el = document.getElementById('f-' + f);
        if (el) el.value = (f.indexOf('base_') === 0) ? '0' : ((f.indexOf('gain_') === 0) ? '0' : '');
    });
    document.getElementById('form-title').textContent = (HI_TYPE === 'hero' ? 'Добавить героя' : 'Добавить предмет');
    document.getElementById('submit-btn').textContent = 'Добавить';
    document.getElementById('cancel-edit').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
