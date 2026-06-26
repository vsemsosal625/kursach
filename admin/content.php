<?php
require_once __DIR__ . '/../config/init.php';
requireAdmin();

$pdo = getDB();

function buildSynergyContent($lane, $hero1, $pos1, $hero2, $pos2, $desc) {
    return "Тип линии: {$lane}\n"
         . "• {$hero1} ({$pos1} позиция)\n"
         . "• {$hero2} ({$pos2} позиция)\n"
         . "Описание взаимодействия: {$desc}";
}

function parseSynergyContent($content) {
    $res = ['lane' => 'Легкая линия', 'hero1' => '', 'pos1' => 1, 'hero2' => '', 'pos2' => 5, 'desc' => ''];
    if (preg_match('/Тип линии:\s*(.+)/u', $content, $m)) $res['lane'] = trim($m[1]);
    if (preg_match_all('/•\s*(.+?)\s*\((\d+)\s*позиция\)/u', $content, $mm, PREG_SET_ORDER)) {
        if (isset($mm[0])) { $res['hero1'] = trim($mm[0][1]); $res['pos1'] = (int)$mm[0][2]; }
        if (isset($mm[1])) { $res['hero2'] = trim($mm[1][1]); $res['pos2'] = (int)$mm[1][2]; }
    }
    if (preg_match('/Описание взаимодействия:\s*(.+)/su', $content, $m)) $res['desc'] = trim($m[1]);
    return $res;
}

$sections = [
    'mechanics'  => ['label'=>'Игровые механики', 'icon'=>'fa-gears', 'accent'=>'#8b5cf6', 'categories'=>['Виды контроля','Типы урона','Защитные механики']],
    'roles'      => ['label'=>'Функциональные роли игроков', 'icon'=>'fa-users-gear', 'accent'=>'#f59e0b', 'categories'=>['Основа (1-3 позиция)','Поддержка (4-5 позиция)']],
    'adaptation' => ['label'=>'Адаптация и расчёт времени', 'icon'=>'fa-hourglass-half', 'accent'=>'#10b981', 'categories'=>['Объекты']],
    'newbie'     => ['label'=>'Для новичков', 'icon'=>'fa-graduation-cap', 'accent'=>'#8b5cf6', 'categories'=>['Крипы','Игровые цели','Командные постройки']],
    'synergy'    => ['label'=>'Синергия героев на линии', 'icon'=>'fa-link', 'accent'=>'#8b5cf6', 'categories'=>['Синергия героев на линии']],
    'settings'   => ['label'=>'Оптимальные настройки игры', 'icon'=>'fa-sliders', 'accent'=>'#8b5cf6', 'categories'=>['Способности','Предметы','Курьер','Автоатака','Камера','Прочее']],
];

$section = $_GET['section'] ?? 'mechanics';
if (!isset($sections[$section])) $section = 'mechanics';
$cfg = $sections[$section];
$isSynergy = ($section === 'synergy');
$lanes = ['Легкая линия', 'Сложная линия'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create' || $action === 'update') {
        if ($isSynergy) {
            $lane  = $_POST['lane'] ?? $lanes[0];
            if (!in_array($lane, $lanes, true)) $lane = $lanes[0];
            $hero1 = trim($_POST['hero1'] ?? '');
            $pos1  = (int)($_POST['pos1'] ?? 0);
            $hero2 = trim($_POST['hero2'] ?? '');
            $pos2  = (int)($_POST['pos2'] ?? 0);
            $desc  = trim($_POST['desc'] ?? '');
            if ($pos1 < 1 || $pos1 > 5) $pos1 = 1;
            if ($pos2 < 1 || $pos2 > 5) $pos2 = 5;
            $title = $hero1 . ' + ' . $hero2;
            $category = 'Синергия героев на линии';
            $content = buildSynergyContent($lane, $hero1, $pos1, $hero2, $pos2, $desc);
            $valid = ($hero1 !== '' && $hero2 !== '');
        } else {
            $title = trim($_POST['title'] ?? '');
            $category = $_POST['category'] ?? '';
            $content = trim($_POST['content'] ?? '');
            $valid = ($title !== '' && $content !== '' && in_array($category, $cfg['categories']));
        }
        if ($valid) {
            if ($action === 'create') {
                $stmt = $pdo->prepare("INSERT INTO game_mechanic (title, category, content, created_date) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$title, $category, $content]);
            } else {
                $gid = (int)($_POST['id'] ?? 0);
                $stmt = $pdo->prepare("UPDATE game_mechanic SET title = ?, category = ?, content = ? WHERE id_game_mechanic = ?");
                $stmt->execute([$title, $category, $content, $gid]);
            }
        }
    } elseif ($action === 'delete') {
        $gid = (int)($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM game_mechanic WHERE id_game_mechanic = ?");
        $stmt->execute([$gid]);
    }
    header('Location: ' . BASE_URL . '/admin/content.php?section=' . urlencode($section));
    exit;
}

$place = implode(',', array_fill(0, count($cfg['categories']), '?'));
$stmt = $pdo->prepare("SELECT * FROM game_mechanic WHERE category IN ($place) ORDER BY category, id_game_mechanic");
$stmt->execute($cfg['categories']);
$rows = $stmt->fetchAll();

$pageTitle = $cfg['label'];
$currentPage = 'admin';

require_once __DIR__ . '/../includes/header.php';
?>

<style>
.ct-wrap { max-width:1000px; margin:0 auto; }
.ct-back { display:inline-block; color:#fbbf24; text-decoration:none; margin-bottom:16px; }
.ct-head { display:flex; align-items:center; gap:12px; margin-bottom:18px; }
.ct-head i { font-size:26px; color:<?= $cfg['accent'] ?>; }
.ct-head h1 { margin:0; color:#fff; font-size:26px; }
.ct-tabs { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:22px; }
.ct-tab { padding:7px 14px; border-radius:20px; background:#1b2838; border:1px solid #36414d; color:#c7d0d8; text-decoration:none; font-size:13px; }
.ct-form-box { background:linear-gradient(135deg,#1b2838,#2a475e); border:1px solid #36414d; border-radius:12px; padding:22px; margin-bottom:28px; }
.ct-form-box h2 { margin:0 0 16px; color:#fff; font-size:18px; }
.ct-field { margin-bottom:14px; }
.ct-field label { display:block; color:#c7d0d8; font-size:13px; margin-bottom:6px; }
.ct-field input, .ct-field select, .ct-field textarea { width:100%; background:#0f1923; border:1px solid #36414d; border-radius:8px; color:#e0e0e0; padding:10px; box-sizing:border-box; }
.ct-field textarea { min-height:120px; resize:vertical; }
.ct-grid2 { display:grid; grid-template-columns:1fr 130px; gap:12px; }
.ct-plus { text-align:center; font-size:26px; font-weight:700; color:#8b5cf6; margin:4px 0 10px; }
.ct-hint { background:rgba(139,92,246,0.1); border:1px solid rgba(139,92,246,0.3); border-radius:8px; padding:10px 14px; color:#c7d0d8; font-size:13px; margin-bottom:14px; white-space:pre-wrap; line-height:1.5; }
.ct-actions { display:flex; gap:10px; align-items:center; }
.btn-primary2 { background:#fbbf24; color:#1b2838; border:none; border-radius:8px; padding:10px 20px; font-weight:600; cursor:pointer; }
.btn-cancel { background:transparent; color:#8f98a0; border:1px solid #36414d; border-radius:8px; padding:10px 16px; cursor:pointer; display:none; }
.ct-cat-group { margin-bottom:24px; }
.ct-cat-title { color:#fbbf24; font-size:15px; font-weight:600; margin-bottom:10px; border-bottom:1px solid #36414d; padding-bottom:6px; }
.ct-row { background:#1b2838; border:1px solid #36414d; border-radius:10px; padding:14px 16px; margin-bottom:10px; display:flex; justify-content:space-between; gap:14px; align-items:flex-start; }
.ct-row-main { flex:1; min-width:0; }
.ct-row h4 { margin:0 0 6px; color:#fff; font-size:15px; }
.ct-row p { margin:0; color:#8f98a0; font-size:13px; line-height:1.5; max-height:60px; overflow:hidden; white-space:pre-wrap; }
.ct-row-btns { display:flex; gap:8px; flex-shrink:0; }
.btn-edit { background:transparent; color:#fbbf24; border:1px solid #fbbf24; border-radius:8px; padding:7px 12px; cursor:pointer; font-size:13px; }
.btn-del2 { background:transparent; color:#f87171; border:1px solid #f87171; border-radius:8px; padding:7px 12px; cursor:pointer; font-size:13px; }
.ct-empty { color:#8f98a0; padding:14px; }
</style>

<div class="ct-wrap">
    <a class="ct-back" href="<?= BASE_URL ?>/admin/index.php"><i class="fas fa-arrow-left"></i> Назад в админ-панель</a>
    <div class="ct-head"><i class="fas <?= $cfg['icon'] ?>"></i><h1><?= htmlspecialchars($cfg['label']) ?></h1></div>

    <div class="ct-tabs">
        <?php foreach ($sections as $k => $s): ?>
            <a class="ct-tab" href="<?= BASE_URL ?>/admin/content.php?section=<?= $k ?>"<?= $k === $section ? ' style="background:' . $s['accent'] . ';border-color:' . $s['accent'] . ';color:#1b2838;font-weight:600;"' : '' ?>><?= htmlspecialchars($s['label']) ?></a>
        <?php endforeach; ?>
    </div>

    <div class="ct-form-box">
        <h2 id="form-title"><?= $isSynergy ? 'Добавить связку' : 'Добавить запись' ?></h2>

        <?php if ($isSynergy): ?>
            <form method="post">
                <input type="hidden" name="action" id="form-action" value="create">
                <input type="hidden" name="id" id="form-id" value="">
                <div class="ct-field"><label>Тип линии</label>
                    <select name="lane" id="f-lane">
                        <?php foreach ($lanes as $ln): ?>
                            <option value="<?= htmlspecialchars($ln, ENT_QUOTES) ?>"><?= htmlspecialchars($ln) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="ct-grid2">
                    <div class="ct-field"><label>Герой 1</label><input type="text" name="hero1" id="f-hero1" placeholder="Имя героя" required></div>
                    <div class="ct-field"><label>Позиция</label>
                        <select name="pos1" id="f-pos1">
                            <option>1</option><option>2</option><option>3</option><option>4</option><option>5</option>
                        </select>
                    </div>
                </div>
                <div class="ct-plus">+</div>
                <div class="ct-grid2">
                    <div class="ct-field"><label>Герой 2</label><input type="text" name="hero2" id="f-hero2" placeholder="Имя героя" required></div>
                    <div class="ct-field"><label>Позиция</label>
                        <select name="pos2" id="f-pos2">
                            <option>1</option><option>2</option><option>3</option><option>4</option><option selected>5</option>
                        </select>
                    </div>
                </div>
                <div class="ct-field"><label>Описание взаимодействия</label><textarea name="desc" id="f-desc" placeholder="Как эти герои взаимодействуют на линии..."></textarea></div>
                <div class="ct-actions">
                    <button class="btn-primary2" type="submit" id="submit-btn">Добавить</button>
                    <button class="btn-cancel" type="button" id="cancel-edit" onclick="cancelEdit()">Отмена</button>
                </div>
            </form>
        <?php else: ?>
            <form method="post">
                <input type="hidden" name="action" id="form-action" value="create">
                <input type="hidden" name="id" id="form-id" value="">
                <div class="ct-field"><label>Заголовок</label><input type="text" name="title" id="f-title" required></div>
                <div class="ct-field"><label>Категория</label>
                    <select name="category" id="f-category">
                        <?php foreach ($cfg['categories'] as $cat): ?>
                            <option value="<?= htmlspecialchars($cat, ENT_QUOTES) ?>"><?= htmlspecialchars($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="ct-field"><label>Содержание</label><textarea name="content" id="f-content" required></textarea></div>
                <div class="ct-actions">
                    <button class="btn-primary2" type="submit" id="submit-btn">Добавить</button>
                    <button class="btn-cancel" type="button" id="cancel-edit" onclick="cancelEdit()">Отмена</button>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <?php foreach ($cfg['categories'] as $cat):
        $catRows = array_filter($rows, fn($r) => ($r['category'] ?? '') === $cat); ?>
        <div class="ct-cat-group">
            <div class="ct-cat-title"><?= htmlspecialchars($cat) ?> (<?= count($catRows) ?>)</div>
            <?php if (empty($catRows)): ?>
                <div class="ct-empty">Пока нет записей в этой категории.</div>
            <?php else: foreach ($catRows as $r): ?>
                <div class="ct-row">
                    <div class="ct-row-main">
                        <h4><?= htmlspecialchars($r['title']) ?></h4>
                        <p><?= htmlspecialchars($r['content']) ?></p>
                    </div>
                    <div class="ct-row-btns">
                        <?php if ($isSynergy): $sd = parseSynergyContent($r['content']); ?>
                            <button class="btn-edit"
                                data-id="<?= $r['id_game_mechanic'] ?>"
                                data-lane="<?= htmlspecialchars($sd['lane'], ENT_QUOTES) ?>"
                                data-hero1="<?= htmlspecialchars($sd['hero1'], ENT_QUOTES) ?>"
                                data-pos1="<?= (int)$sd['pos1'] ?>"
                                data-hero2="<?= htmlspecialchars($sd['hero2'], ENT_QUOTES) ?>"
                                data-pos2="<?= (int)$sd['pos2'] ?>"
                                data-desc="<?= htmlspecialchars($sd['desc'], ENT_QUOTES) ?>"
                                onclick="editSynergyRow(this)"><i class="fas fa-pen"></i></button>
                        <?php else: ?>
                            <button class="btn-edit" data-id="<?= $r['id_game_mechanic'] ?>" data-title="<?= htmlspecialchars($r['title'], ENT_QUOTES) ?>" data-category="<?= htmlspecialchars($r['category'], ENT_QUOTES) ?>" data-content="<?= htmlspecialchars($r['content'], ENT_QUOTES) ?>" onclick="editRow(this)"><i class="fas fa-pen"></i></button>
                        <?php endif; ?>
                        <form method="post" onsubmit="return confirm('Удалить эту запись?');" style="margin:0;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $r['id_game_mechanic'] ?>">
                            <button class="btn-del2" type="submit"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<script>
var IS_SYNERGY = <?= json_encode($isSynergy) ?>;

function editRow(btn) {
    document.getElementById('form-action').value = 'update';
    document.getElementById('form-id').value = btn.dataset.id;
    document.getElementById('f-title').value = btn.dataset.title;
    document.getElementById('f-category').value = btn.dataset.category;
    document.getElementById('f-content').value = btn.dataset.content;
    document.getElementById('form-title').textContent = 'Редактировать запись #' + btn.dataset.id;
    document.getElementById('submit-btn').textContent = 'Сохранить изменения';
    document.getElementById('cancel-edit').style.display = 'inline-block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function editSynergyRow(btn) {
    document.getElementById('form-action').value = 'update';
    document.getElementById('form-id').value = btn.dataset.id;
    document.getElementById('f-lane').value = btn.dataset.lane;
    document.getElementById('f-hero1').value = btn.dataset.hero1;
    document.getElementById('f-pos1').value = btn.dataset.pos1;
    document.getElementById('f-hero2').value = btn.dataset.hero2;
    document.getElementById('f-pos2').value = btn.dataset.pos2;
    document.getElementById('f-desc').value = btn.dataset.desc;
    document.getElementById('form-title').textContent = 'Редактировать связку #' + btn.dataset.id;
    document.getElementById('submit-btn').textContent = 'Сохранить изменения';
    document.getElementById('cancel-edit').style.display = 'inline-block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function cancelEdit() {
    document.getElementById('form-action').value = 'create';
    document.getElementById('form-id').value = '';
    if (IS_SYNERGY) {
        document.getElementById('f-hero1').value = '';
        document.getElementById('f-hero2').value = '';
        document.getElementById('f-desc').value = '';
        document.getElementById('f-pos1').value = '1';
        document.getElementById('f-pos2').value = '5';
        document.getElementById('f-lane').selectedIndex = 0;
    } else {
        document.getElementById('f-title').value = '';
        document.getElementById('f-content').value = '';
    }
    document.getElementById('form-title').textContent = IS_SYNERGY ? 'Добавить связку' : 'Добавить запись';
    document.getElementById('submit-btn').textContent = 'Добавить';
    document.getElementById('cancel-edit').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
