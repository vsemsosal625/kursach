<?php
require_once __DIR__ . '/../config/init.php';
requireLogin();

$pageTitle = 'Настройки сайта';
$currentPage = '';

require_once __DIR__ . '/../includes/header.php';
?>

<style>
.settings-section { background: linear-gradient(135deg,#1b2838 0%,#2a475e 100%); border: 1px solid #36414d; border-radius: 12px; padding: 25px; margin-bottom: 25px; transition: all 0.3s; }
.settings-section:hover { border-color: #8b5cf6; }
.settings-section .section-head { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #36414d; }
.settings-section .section-head i { font-size: 24px; color: #8b5cf6; }
.settings-section .section-head h2 { color: #fff; font-size: 20px; font-weight: 600; margin: 0; }
.setting-item { display: flex; justify-content: space-between; align-items: center; padding: 15px 0; border-bottom: 1px solid #36414d; gap: 20px; }
.setting-item:last-child { border-bottom: none; }
.setting-label { color: #e0e0e0; font-size: 15px; font-weight: 500; }
.setting-description { color: #acb2b8; font-size: 13px; margin-top: 4px; }
.toggle-switch { position: relative; width: 50px; height: 26px; flex-shrink: 0; }
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.toggle-slider { position: absolute; cursor: pointer; inset: 0; background-color: #36414d; transition: .4s; border-radius: 26px; }
.toggle-slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 4px; bottom: 4px; background-color: #fff; transition: .4s; border-radius: 50%; }
input:checked + .toggle-slider { background-color: #8b5cf6; }
input:checked + .toggle-slider:before { transform: translateX(24px); }
.setting-select { background: rgba(27,40,56,0.8); border: 2px solid #36414d; color: #e0e0e0; padding: 8px 16px; border-radius: 8px; font-size: 14px; cursor: pointer; }
.setting-select:focus { outline: none; border-color: #8b5cf6; }
.setting-select:disabled { opacity: 0.5; cursor: not-allowed; }
.reset-btn { background: rgba(239,68,68,0.15); border: 2px solid #ef4444; color: #fca5a5; padding: 10px 24px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px; }
.reset-btn:hover { background: rgba(239,68,68,0.3); color: #fff; }
</style>

<div class="page-header">
    <h1 class="page-title" style="border-left-color:#8b5cf6;">⚙️ Настройки сайта</h1>
</div>

<div class="settings-section">
    <div class="section-head"><i class="fas fa-palette"></i><h2>Внешний вид</h2></div>
    <div class="setting-item">
        <div>
            <div class="setting-label">Тёмная тема оформления</div>
            <div class="setting-description">Включите для тёмной темы, выключите для светлой. Применяется ко всему сайту.</div>
        </div>
        <label class="toggle-switch"><input type="checkbox" id="themeToggle"><span class="toggle-slider"></span></label>
    </div>
</div>

<div class="settings-section">
    <div class="section-head"><i class="fas fa-language"></i><h2>Язык интерфейса</h2></div>
    <div class="setting-item">
        <div>
            <div class="setting-label">Выберите язык отображения</div>
            <div class="setting-description">Русский доступен сейчас, английский — в разработке</div>
        </div>
        <select class="setting-select" id="language">
            <option value="ru" selected>Русский</option>
            <option value="en" disabled>English (Скоро)</option>
        </select>
    </div>
</div>

<div style="text-align:center; margin-top:30px;">
    <button type="button" class="reset-btn" onclick="resetSettings()"><i class="fas fa-undo"></i> Сбросить настройки</button>
</div>

<script>
    var themeToggle = document.getElementById('themeToggle');
    function isLight(){ return document.documentElement.classList.contains('light-theme'); }
    function setTheme(light){
        if(light){ document.documentElement.classList.add('light-theme'); try{localStorage.setItem('siteTheme','light');}catch(e){} }
        else { document.documentElement.classList.remove('light-theme'); try{localStorage.setItem('siteTheme','dark');}catch(e){} }
        if(themeToggle) themeToggle.checked = !light;
    }
    if(themeToggle){
        themeToggle.checked = !isLight();
        themeToggle.addEventListener('change', function(){ setTheme(!this.checked); });
    }
    function resetSettings(){ if(confirm('Вы уверены, что хотите сбросить все настройки?')){ setTheme(false); } }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>