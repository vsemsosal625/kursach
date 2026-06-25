<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header('Location: auth.php'); exit; }

$pageTitle = 'Настройки сайта';
$currentPage = '';

require_once 'includes/header.php';
?>

<style>
.site-settings { --ss-card: linear-gradient(135deg,#1b2838 0%,#2a475e 100%); --ss-txt:#e0e0e0; --ss-txt2:#acb2b8; --ss-bd:#36414d; --ss-acc:#8b5cf6; padding-bottom: 30px; }
.site-settings.light { --ss-card: linear-gradient(135deg,#ffffff 0%,#eef0f3 100%); --ss-txt:#1a1a1a; --ss-txt2:#5b6470; --ss-bd:#d4d9e0; --ss-acc:#6d28d9; }
.settings-section { background: var(--ss-card); border: 1px solid var(--ss-bd); border-radius: 12px; padding: 25px; margin-bottom: 25px; transition: all 0.3s; }
.settings-section:hover { border-color: var(--ss-acc); }
.settings-section .section-head { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid var(--ss-bd); }
.settings-section .section-head i { font-size: 24px; color: var(--ss-acc); }
.settings-section .section-head h2 { color: var(--ss-txt); font-size: 20px; font-weight: 600; margin: 0; }
.setting-item { display: flex; justify-content: space-between; align-items: center; padding: 15px 0; border-bottom: 1px solid var(--ss-bd); gap: 20px; }
.setting-item:last-child { border-bottom: none; }
.setting-label { color: var(--ss-txt); font-size: 15px; font-weight: 500; }
.setting-description { color: var(--ss-txt2); font-size: 13px; margin-top: 4px; }
.toggle-switch { position: relative; width: 50px; height: 26px; flex-shrink: 0; }
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.toggle-slider { position: absolute; cursor: pointer; inset: 0; background-color: var(--ss-bd); transition: .4s; border-radius: 26px; }
.toggle-slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 4px; bottom: 4px; background-color: #fff; transition: .4s; border-radius: 50%; }
input:checked + .toggle-slider { background-color: var(--ss-acc); }
input:checked + .toggle-slider:before { transform: translateX(24px); }
.setting-select { background: var(--ss-card); border: 2px solid var(--ss-bd); color: var(--ss-txt); padding: 8px 16px; border-radius: 8px; font-size: 14px; cursor: pointer; }
.setting-select:focus { outline: none; border-color: var(--ss-acc); }
.setting-select:disabled { opacity: 0.5; cursor: not-allowed; }
.reset-btn { background: rgba(239,68,68,0.15); border: 2px solid #ef4444; color: #fca5a5; padding: 10px 24px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px; }
.reset-btn:hover { background: rgba(239,68,68,0.3); color: #fff; }
</style>
