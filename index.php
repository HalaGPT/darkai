<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    header('Content-Type: application/json; charset=utf-8');
    
    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $error_message = 'Upload failed with error code ' . $_FILES['image']['error'];
        echo json_encode(["error" => $error_message]);
        exit;
    }

    $uploadDir = __DIR__ . "/uploads/";
    
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            echo json_encode(["error" => "Failed to create uploads directory"]);
            exit;
        }
    }
    
    $fileName = time() . "_" . basename($_FILES['image']['name']);
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        $url = (isset($_SERVER['HTTPS']) ? "https" : "http") .
               "://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/uploads/".$fileName;
        echo json_encode(["url" => $url]);
        exit;
    }
    
    echo json_encode(["error" => "Error moving the uploaded file"]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>DarkAI</title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<style>
:root{
--glass-bg: rgba(255,255,255,0.06);
--accent: #0af;
--muted: rgba(255,255,255,0.12);
--text: #ffffff;
--sidebar-w: 260px;
--anim-speed: 320ms;
}
*{box-sizing:border-box;-webkit-tap-highlight-color:transparent;}
html,body{
height:100%;
margin:0;
padding:0;
background: transparent; 
font-family:Inter,Segoe UI,Arial,'Noto Naskh Arabic',sans-serif;
color:var(--text);
-webkit-text-size-adjust:100%;
}
body{overflow-y:auto; -webkit-user-select:none; user-select:none;}
.bg-video-wrap{
position:fixed;
inset:0; 
width:100%;
height:100vh;
overflow:hidden;
z-index:-1;
pointer-events:none;
display:block;
background-color:#000;
}
.bg-video-wrap video{
position:absolute;
left:50%;
top:50%;
transform:translate(-50%,-50%);
width:100%;
height:100%;
object-fit:cover;
filter: blur(4px) brightness(.95) saturate(.98);
-webkit-filter: blur(4px) brightness(.95) saturate(.98);
will-change: transform;
}
.top-btn{
position:fixed; top:18px; left:18px; width:52px; height:52px; border-radius:14px;
background: rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.12);
display:flex; align-items:center; justify-content:center; z-index:60; cursor:pointer;
box-shadow:0 6px 18px rgba(0,0,0,0.35); backdrop-filter: blur(8px);
transition: transform var(--anim-speed) cubic-bezier(.2,.9,.2,1), background var(--anim-speed);
}
.top-btn.moved{ transform: translateX(calc(var(--sidebar-w) + 12px)); } 
.top-btn svg{ width:22px; height:22px; fill:var(--text); transition: transform .18s ease; }
.sidebar{
position:fixed; top:0; left:0; height:100vh; width:var(--sidebar-w);
background: linear-gradient(180deg, rgba(8,8,8,0.98), rgba(10,10,10,0.95));
transform: translateX(-120%); transition: transform var(--anim-speed) cubic-bezier(.2,.9,.2,1);
z-index:55; padding:22px 14px; box-shadow: 10px 12px 50px rgba(0,0,0,0.6);
}
.sidebar.open{ transform: translateX(0); }
.sidebar h3{ margin:6px 0 18px; font-size:16px; }
.nav-item{ display:flex; gap:12px; align-items:center; padding:10px 8px; border-radius:10px; cursor:pointer; color:var(--text); margin-bottom:8px; transition:background .18s; }
.nav-item:hover{ background: rgba(255,255,255,0.02); }
.nav-item.active{ background: rgba(0,170,255,0.12); border:1px solid rgba(10,170,255,0.12); }
.lang-select{ margin-top:18px; display:flex; gap:8px; align-items:center; }
.lang-select select{ background:transparent; color:var(--text); border:1px solid var(--muted); padding:8px 10px; border-radius:8px; }
.container{ width:94%; max-width:980px; margin:0 auto; position:relative; z-index:10; padding-top:12vh; display:flex; justify-content:center; transition: padding-top calc(var(--anim-speed) * 1.1); }
.glass-box{ background: var(--glass-bg); backdrop-filter:blur(12px); border-radius:20px; padding:20px; width:100%; box-shadow:0 14px 50px rgba(0,0,0,0.55); overflow:hidden; }
.header-row{ display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:12px; }
.page-title{ font-size:18px; font-weight:700; letter-spacing:.3px; }
.hint{ font-size:13px; color:#d6d6d6; }
.upload-area{ border:2px dashed rgba(255,255,255,0.12); border-radius:12px; padding:14px; min-height:140px; display:flex; align-items:center; justify-content:center; margin-bottom:12px; background: rgba(255,255,255,0.01); }
.upload-area img{ max-width:140px; max-height:140px; border-radius:10px; }
.remove-btn{ position:absolute; top:10px; right:12px; background: rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.12); padding:6px 10px; border-radius:8px; color:#fff; cursor:pointer; }
textarea, input[type="text"]{ width:100%; padding:14px; border-radius:12px; border:none; outline:none; font-size:16px; background: rgba(255,255,255,0.04); color:var(--text); }
.actions{ display:flex; gap:12px; margin-top:12px; flex-wrap:wrap; }
.btn{ background:var(--accent); color:#000; padding:10px 18px; border-radius:12px; border:none; cursor:pointer; font-weight:700; display:inline-flex; align-items:center; gap:8px; }
.btn.ghost{ background:transparent; color:var(--text); border:1px solid var(--muted); }
.result{ margin-top:14px; }
.result img, .result video{ width:100%; border-radius:12px; box-shadow:0 8px 30px rgba(0,0,0,0.6); }
.result-group{ display:flex; flex-wrap:wrap; gap:10px; align-items:center; }
.result-group .result-item{ width:calc(50% - 5px); }
.result-group img, .result-group video{ width:100%; height:auto; border-radius:12px; }
.download-btn{ background:rgba(255,255,255,0.06); color:var(--text); border:1px solid rgba(255,255,255,0.12); padding:8px 14px; border-radius:10px; margin-top:6px; cursor:pointer; display:inline-flex; align-items:center; gap:6px; font-size:14px; }
.dev-note{ margin-top:10px; font-size:13px; color:#dcdcdc; background:rgba(255,255,255,0.02); padding:8px 10px; border-radius:8px; display:inline-block; }
.tabs{ display:flex; gap:8px; margin-bottom:12px; }
.tab{ padding:8px 12px; border-radius:10px; cursor:pointer; border:1px solid transparent; }
.tab.active{ background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.06); }
.spinner{ border:3px solid rgba(255,255,255,0.18); border-top:3px solid #fff; border-radius:50%; width:18px; height:18px; animation:spin .9s linear infinite; display:inline-block; vertical-align:middle; }
@keyframes spin{100%{transform:rotate(360deg)}} 
.page{ position:relative; opacity:0; transform:translateX(18px); transition: opacity var(--anim-speed) ease, transform var(--anim-speed) ease; pointer-events:none; }
.page.show{ opacity:1; transform:translateX(0); pointer-events:auto; }
.page.hide{ opacity:0; transform:translateX(-12px); pointer-events:none; }
.fade-out{ opacity:0 !important; transform:scale(.98); transition: opacity 260ms ease, transform 260ms ease, height 260ms ease, margin 260ms ease; height:0 !important; margin:0 !important; padding:0 !important; overflow:hidden; }
@media (max-width:720px){ .sidebar{ width:84%; } .top-btn{ left:12px; } .top-btn.moved{ transform: translateX(calc(84% + 12px)); } }
.home-content{ text-align:center; padding-top:20px; }
.home-content .media-container{ max-width:300px; margin:0 auto 20px; }
.home-content .media-container img, .home-content .media-container video{ width:100%; height:auto; border-radius:12px; box-shadow: 0 8px 24px rgba(0,0,0,0.5); }
.home-content .media-container video{ margin-top:15px; }
.home-text-box{ background:rgba(255,255,255,0.03); border-radius:12px; padding:18px; margin-bottom:20px; text-align:center; }
.home-links{ display:flex; flex-wrap:wrap; justify-content:center; gap:12px; }
.home-links .btn{ font-size:14px; padding:10px 14px; }
</style>
</head>
<body>
<div class="bg-video-wrap">
<video id="bgVideo" autoplay muted loop playsinline preload="auto" poster="back.jpg" aria-hidden="true">
<source src="data/back.mp4" type="video/mp4">
Your browser does not support the video element
</video>
</div>
<button id="topBtn" class="top-btn" aria-label="Open menu" title="القائمة">
<svg id="topIcon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18v2H3V6zm0 5h18v2H3v-2zm0 5h18v2H3v-2z"/></svg>
</button>
<nav id="sidebar" class="sidebar" aria-hidden="true">
<h3 id="sidebarTitle">DarkAI</h3>
<div class="nav-item" data-page="home" id="nav-home"><strong>Home</strong></div>
<div class="nav-item" data-page="image-to-video" id="nav-img2vid"><strong>Image → Video</strong></div>
<div class="nav-item" data-page="text-to-video" id="nav-txt2vid"><strong>Text → Video</strong></div>
<div class="nav-item" data-page="image-tools" id="nav-img-tools"><strong>Image Tools</strong></div>
<div class="lang-select">
<label for="language" id="languageLabel" style="font-size:13px;color:#ddd;margin-right:6px;">Language</label>
<select id="language">
<option value="en">English</option>
<option value="ar">العربية</option>
<option value="ru">Русский</option>
<option value="zh">中文</option>
</select>
</div>
</nav>
<div class="container">
<div class="glass-box" id="app">
<div class="header-row">
<div>
<div class="page-title" id="pageTitle">Home</div>
<div class="hint" id="pageHint">Your AI Creation Hub</div>
</div>
</div>
<div id="page-home" class="page show" data-page="home">
<div class="home-content">
<div class="media-container">
<img src="data/ai.png" alt="AI Image">
<video autoplay loop muted playsinline preload="auto" src="data/ai.mp4" id="aiVideo"></video>
</div>
<div class="home-text-box">
<p id="homeText">مرحباً في DarkAI — منصتك المتكاملة لإنشاء الفيديوهات والصور باستخدام الذكاء الاصطناعي. استكشف أدواتنا المبتكرة وحوّل أفكارك إلى واقع مرئي.</p>
</div>
<div class="home-links">
<button class="btn" id="goImg2Vid">
<span class="btn-label" id="goImg2VidText">صورة → فيديو</span>
</button>
<button class="btn" id="goTxt2Vid">
<span class="btn-label" id="goTxt2VidText">نص → فيديو</span>
</button>
<button class="btn" id="goImgTools">
<span class="btn-label" id="goImgToolsText">أدوات الصور</span>
</button>
</div>
</div>
</div>
<div id="page-image-to-video" class="page" data-page="image-to-video" style="display:block;">
<div class="upload-area" id="uploadAreaImg2Vid"><span id="uploadHintImg2Vid">📂 اضغط لرفع صورة</span>
<input type="file" id="imageInputImg2Vid" accept="image/*" style="display:none;">
</div>
<textarea id="img2vidText" placeholder="اكتب الوصف..."></textarea>
<div class="actions">
<button class="btn" id="img2vidBtn" data-label="Generate Video"><span class="btn-label">Generate Video</span></button>
<button class="btn ghost" id="img2vidClear">Clear</button>
</div>
<div class="result" id="img2vidResult"></div>
</div>
<div id="page-text-to-video" class="page" data-page="text-to-video">
<textarea id="txt2vidText" placeholder="اكتب النص..."></textarea>
<div class="actions">
<button class="btn" id="txt2vidBtn" data-label="Generate Video"><span class="btn-label">Generate Video</span></button>
<button class="btn ghost" id="txt2vidClear">Clear</button>
</div>
<div class="result" id="txt2vidResult"></div>
</div>
<div id="page-image-tools" class="page" data-page="image-tools">
<div class="tabs"><div class="tab active" data-tab="edit" id="tabEditLabel">Edit (gemini)</div><div class="tab" data-tab="flux" id="tabFluxLabel">Generate (flux)</div></div>
<div id="tab-edit">
<div class="upload-area" id="uploadAreaGemini"><span id="uploadHintGemini">📂 اضغط لرفع صورة</span>
<input type="file" id="imageInputGemini" accept="image/*" style="display:none;">
</div>
<input type="text" id="geminiText" placeholder='وصف (مثال: make it cinematic)'>
<div class="actions">
<button class="btn" id="geminiBtn" data-label="Generate Image (gemini)"><span class="btn-label">Generate Image (gemini)</span></button>
<button class="btn ghost" id="geminiClear">Clear</button>
</div>
<div class="result" id="geminiResult"></div>
</div>
<div id="tab-flux" style="display:none;">
<input type="text" id="fluxText" placeholder='مثال: GOLD word "DARK" On the wooden wall'>
<div class="actions">
<button class="btn" id="fluxBtn" data-label="Generate Images (flux)"><span class="btn-label">Generate Images (flux)</span></button>
<button class="btn ghost" id="fluxClear">Clear</button>
</div>
<div class="result" id="fluxResult"></div>
</div>
</div>
</div>
</div>
<script>
const sidebar = document.getElementById('sidebar');
const topBtn = document.getElementById('topBtn');
const topIcon = document.getElementById('topIcon');
let sidebarOpen = false;
function setTopIconToX(){
topIcon.innerHTML = '<path d="M18.3 5.71L12 12l6.3 6.29-1.41 1.42L10.59 13.41 4.29 19.71 2.88 18.29 9.18 12 2.88 5.71 4.29 4.29 10.59 10.59 16.88 4.29z"/>';
}
function setTopIconToHamburger(){
topIcon.innerHTML = '<path d="M3 6h18v2H3V6zm0 5h18v2H3v-2zm0 5h18v2H3v-2z"/>';
}
function openSidebar(){
sidebar.classList.add('open');
sidebar.setAttribute('aria-hidden','false');
topBtn.classList.add('moved');
setTopIconToX();
sidebarOpen = true;
}
function closeSidebar(){
sidebar.classList.remove('open');
sidebar.setAttribute('aria-hidden','true');
topBtn.classList.remove('moved');
setTopIconToHamburger();
sidebarOpen = false;
}
topBtn.addEventListener('click', ()=>{
if(sidebarOpen) closeSidebar(); else openSidebar();
});
document.addEventListener('click', (e)=>{
if(!sidebarOpen) return;
if(!sidebar.contains(e.target) && !topBtn.contains(e.target)){
closeSidebar();
}
});
const pages = document.querySelectorAll('.page');
const navItems = document.querySelectorAll('.nav-item');
let currentPage = document.querySelector('.page.show')?.dataset.page || 'home';
function updateNavActive(page){
navItems.forEach(n => n.classList.toggle('active', n.dataset.page === page));
}
navItems.forEach(n => n.addEventListener('click', ()=> {
goTo(n.dataset.page);
}));
function goTo(page){
if(page === currentPage) { closeSidebar(); return; }
const prevEl = document.querySelector(`.page[data-page="${currentPage}"]`);
const nextEl = document.querySelector(`.page[data-page="${page}"]`);
if(!nextEl) return;
if(prevEl){
prevEl.classList.remove('show');
prevEl.classList.add('hide');
setTimeout(()=>{ prevEl.style.display = 'none'; prevEl.classList.remove('hide'); }, 320);
}
nextEl.style.display = 'block';
void nextEl.offsetWidth;
nextEl.classList.add('show');
currentPage = page;
updateNavActive(page);
document.getElementById('pageTitle').textContent = translations[currentLang][page+'Title'] || page;
document.getElementById('pageHint').textContent = translations[currentLang][page+'Hint'] || '';
applyTextTranslationsForPage(currentLang);
closeSidebar();
history.replaceState(null, '', '#'+page);
}
window.addEventListener('load', ()=>{
const initial = location.hash.replace('#','') || 'home';
pages.forEach(p => { if(p.dataset.page !== initial){ p.style.display = 'none'; p.classList.remove('show'); } else { p.style.display = 'block'; p.classList.add('show'); } });
currentPage = initial;
updateNavActive(initial);
applyTextTranslationsForPage(currentLang);
});
const translations = {
en: {
homeTitle:'Home', homeHint:'Your AI Creation Hub', homeText:'Welcome to DarkAI — your all-in-one platform for creating videos and images using artificial intelligence. Explore our innovative tools and transform your ideas into visual reality.',
homeSubText:'Image to Video: A demonstration of AI magic',
'image-to-videoTitle':'Image → Video','image-to-videoHint':'Convert an image to a short video',
'text-to-videoTitle':'Text → Video','text-to-videoHint':'Create a video from a text prompt',
'image-toolsTitle':'Image Tools','image-toolsHint':'Edit or generate images (gemini / flux)',
uploadHintImg2Vid:'📂 Click to upload an image', img2vidPlaceholder:'Write a description...', img2vidBtn:'Generate Video', img2vidClear:'Clear',
txt2vidPlaceholder:'Write your text...', txt2vidBtn:'Generate Video', txt2vidClear:'Clear',
uploadHintGemini:'📂 Click to upload an image', geminiPlaceholder:'Description (e.g. make it cinematic)', geminiBtn:'Generate Image', geminiClear:'Clear',
fluxPlaceholder:'Example: GOLD word "DARK" On the wooden wall', fluxBtn:'Generate Images', fluxClear:'Clear',
tabEdit:'Edit (gemini)', tabFlux:'Generate (flux)', sidebarTitle:'DarkAI', languageLabel:'Language',
navHome:'Home', navImg2Vid:'Image → Video', navTxt2Vid:'Text → Video', navImgTools:'Image Tools',
uploading:'Uploading...', uploadFail:'Upload failed. Please try again.', uploadError:'Upload error. Connection might be weak.', unexpected:'Unexpected response', connFail:'Connection failed',
removeBtn:'Remove', generateImage:'Generate Image', generateVideo:'Generate Video',
goImg2VidText:'Image → Video', goTxt2VidText:'Text → Video', goImgToolsText:'Image Tools', downloadBtn:'Download'
},
ar: {
homeTitle:'الرئيسية', homeHint:'مركز إبداعك بالذكاء الاصطناعي', homeText:'مرحباً في DarkAI — منصتك المتكاملة لإنشاء الفيديوهات والصور باستخدام الذكاء الاصطناعي. استكشف أدواتنا المبتكرة وحوّل أفكارك إلى واقع مرئي.',
homeSubText:'تجربة تحويل من صورة إلى فيديو',
'image-to-videoTitle':'صورة → فيديو','image-to-videoHint':'حوّل الصورة إلى فيديو قصير',
'text-to-videoTitle':'نص → فيديو','text-to-videoHint':'انشئ فيديو من وصف نصي',
'image-toolsTitle':'أدوات الصور','image-toolsHint':'تعديل أو إنشاء صور (gemini / flux)',
uploadHintImg2Vid:'📂 اضغط لرفع صورة', img2vidPlaceholder:'اكتب الوصف...', img2vidBtn:'انشئ فيديو', img2vidClear:'مسح',
txt2vidPlaceholder:'اكتب النص...', txt2vidBtn:'انشئ فيديو', txt2vidClear:'مسح',
uploadHintGemini:'📂 اضغط لرفع صورة', geminiPlaceholder:'وصف (مثال: اجعله سينمائي)', geminiBtn:'انشئ صورة', geminiClear:'مسح',
fluxPlaceholder:'مثال: GOLD word "DARK" On the wooden wall', fluxBtn:'انشئ صور', fluxClear:'مسح',
tabEdit:'تعديل (gemini)', tabFlux:'إنشاء (flux)', sidebarTitle:'داك اِيه آي', languageLabel:'اللغة',
navHome:'الرئيسية', navImg2Vid:'صورة → فيديو', navTxt2Vid:'نص → فيديو', navImgTools:'أدوات الصور',
uploading:'جارِ الرفع...', uploadFail:'فشل الرفع. الرجاء المحاولة مجدداً.', uploadError:'خطأ في الرفع. قد يكون الاتصال ضعيفاً.', unexpected:'استجابة غير متوقعة', connFail:'فشل الاتصال',
removeBtn:'إزالة', generateImage:'انشئ صورة', generateVideo:'انشئ فيديو',
goImg2VidText:'صورة → فيديو', goTxt2VidText:'نص → فيديو', goImgToolsText:'أدوات الصور', downloadBtn:'تحميل'
},
ru: {
homeTitle:'Главная', homeHint:'Ваш центр ИИ-творчества', homeText:'Добро пожаловать в DarkAI — вашу универсальную платформу для создания видео и изображений с помощью искусственного интеллекта. Изучите наши инновационные инструменты и превратите свои идеи в визуальную реальность.',
homeSubText:'Изображение в видео: демонстрация магии ИИ',
'image-to-videoTitle':'Изображение → Видео','image-to-videoHint':'Преобразовать изображение в видео',
'text-to-videoTitle':'Текст → Видео','text-to-videoHint':'Создать видео из текста',
'image-toolsTitle':'Инструменты изображений','image-toolsHint':'Редактирование и генерация изображений',
uploadHintImg2Vid:'📂 Нажмите, чтобы загрузить изображение', img2vidPlaceholder:'Введите описание...', img2vidBtn:'Создать видео', img2vidClear:'Очистить',
txt2vidPlaceholder:'Введите текст...', txt2vidBtn:'Создать видео', txt2vidClear:'Очистить',
uploadHintGemini:'📂 Нажмите, чтобы загрузить изображение', geminiPlaceholder:'Описание (например: сделать кинематографичным)', geminiBtn:'Создать изображение', geminiClear:'Очистить',
fluxPlaceholder:'Пример: GOLD word "DARK" On the wooden wall', fluxBtn:'Создать изображения', fluxClear:'Очистить',
tabEdit:'Редактирование (gemini)', tabFlux:'Генерация (flux)', sidebarTitle:'DarkAI', languageLabel:'Язык',
navHome:'Главная', navImg2Vid:'Изображение → Видео', navTxt2Vid:'Текст → Видео', navImgTools:'Инструменты',
uploading:'Загрузка...', uploadFail:'Ошибка загрузки. Пожалуйста, попробуйте снова.', uploadError:'Ошибка загрузки. Возможно, слабое соединение.', unexpected:'Неожиданный ответ', connFail:'Ошибка подключения',
removeBtn:'Удалить', generateImage:'Создать изображение', generateVideo:'Создать видео',
goImg2VidText:'Изображение → Видео', goTxt2VidText:'Текст → Видео', goImgToolsText:'Инструменты изображений', downloadBtn:'Скачать'
},
zh: {
homeTitle:'主页', homeHint:'您的AI创作中心', homeText:'欢迎使用 DarkAI — 您使用人工智能创建视频和图像的一体化平台。探索我们的创新工具，将您的想法变为视觉现实。',
homeSubText:'图片转视频：AI魔法的展示',
'image-to-videoTitle':'图片 → 视频','image-to-videoHint':'将图片转换为视频',
'text-to-videoTitle':'文本 → 视频','text-to-videoHint':'从文本生成视频',
'image-toolsTitle':'图像工具','image-toolsHint':'编辑或生成图像 (gemini / flux)',
uploadHintImg2Vid:'📂 点击上传图片', img2vidPlaceholder:'写下描述...', img2vidBtn:'生成视频', img2vidClear:'清除',
txt2vidPlaceholder:'写下文本...', txt2vidBtn:'生成视频', txt2vidClear:'清除',
uploadHintGemini:'📂 点击上传图片', geminiPlaceholder:'描述 (例如: 使其有电影感)', geminiBtn:'生成图像', geminiClear:'清除',
fluxPlaceholder:'示例: GOLD word "DARK" On the wooden wall', fluxBtn:'生成图像', fluxClear:'清除',
tabEdit:'编辑 (gemini)', tabFlux:'生成 (flux)', sidebarTitle:'DarkAI', languageLabel:'语言',
navHome:'主页', navImg2Vid:'图片 → 视频', navTxt2Vid:'文本 → 视频', navImgTools:'图像工具',
uploading:'正在上传...', uploadFail:'上传失败。请重试。', uploadError:'上传错误。网络可能较弱。', unexpected:'意外的响应', connFail:'连接失败',
removeBtn:'移除', generateImage:'生成图像', generateVideo:'生成视频',
goImg2VidText:'图片 → 视频', goTxt2VidText:'文本 → 视频', goImgToolsText:'图像工具', downloadBtn:'下载'
}
};
const languageSelect = document.getElementById('language');
let currentLang = localStorage.getItem('darkai_lang') || 'ar';
languageSelect.value = currentLang;
function applyLanguage(lang){
currentLang = lang;
localStorage.setItem('darkai_lang', lang);
document.getElementById('sidebarTitle').textContent = translations[lang].sidebarTitle || 'DarkAI';
document.getElementById('nav-home').querySelector('strong').textContent = translations[lang].navHome || 'Home';
document.getElementById('nav-img2vid').querySelector('strong').textContent = translations[lang].navImg2Vid || 'Image → Video';
document.getElementById('nav-txt2vid').querySelector('strong').textContent = translations[lang].navTxt2Vid || 'Text → Video';
document.getElementById('nav-img-tools').querySelector('strong').textContent = translations[lang].navImgTools || 'Image Tools';
const langLabel = document.getElementById('languageLabel');
if(langLabel) langLabel.textContent = translations[lang].languageLabel || 'Language';
document.getElementById('pageTitle').textContent = translations[lang][currentPage+'Title'] || translations[lang].homeTitle || currentPage;
document.getElementById('pageHint').textContent = translations[lang][currentPage+'Hint'] || '';
document.getElementById('homeText').textContent = translations[lang].homeText || '';
document.getElementById('goImg2VidText').textContent = translations[lang].goImg2VidText || 'Image → Video';
document.getElementById('goTxt2VidText').textContent = translations[lang].goTxt2VidText || 'Text → Video';
document.getElementById('goImgToolsText').textContent = translations[lang].goImgToolsText || 'Image Tools';
document.getElementById('uploadHintImg2Vid').textContent = translations[lang].uploadHintImg2Vid || '';
const uploadAreaImg2Vid = document.getElementById('uploadAreaImg2Vid');
if(uploadAreaImg2Vid.querySelector('img')) {
const removeBtn = uploadAreaImg2Vid.querySelector('.remove-btn');
if(removeBtn) removeBtn.textContent = translations[lang].removeBtn || 'Remove';
}
document.getElementById('uploadHintGemini').textContent = translations[lang].uploadHintGemini || '';
const uploadAreaGemini = document.getElementById('uploadAreaGemini');
if(uploadAreaGemini.querySelector('img')) {
const removeBtn = uploadAreaGemini.querySelector('.remove-btn');
if(removeBtn) removeBtn.textContent = translations[lang].removeBtn || 'Remove';
}
const img2 = document.getElementById('img2vidText'); if(img2) img2.placeholder = translations[lang].img2vidPlaceholder || '';
const txt2 = document.getElementById('txt2vidText'); if(txt2) txt2.placeholder = translations[lang].txt2vidPlaceholder || '';
const gem = document.getElementById('geminiText'); if(gem) gem.placeholder = translations[lang].geminiPlaceholder || '';
const flux = document.getElementById('fluxText'); if(flux) flux.placeholder = translations[lang].fluxPlaceholder || '';
const setBtnLabel = (id, text) => {
const btn = document.getElementById(id);
if(!btn) return;
const span = btn.querySelector('.btn-label');
if(span) span.textContent = text;
else btn.textContent = text;
btn.setAttribute('data-label', text);
};
setBtnLabel('img2vidBtn', translations[lang].img2vidBtn || 'Generate Video');
setBtnLabel('img2vidClear', translations[lang].img2vidClear || 'Clear');
setBtnLabel('txt2vidBtn', translations[lang].txt2vidBtn || 'Generate Video');
setBtnLabel('txt2vidClear', translations[lang].txt2vidClear || 'Clear');
setBtnLabel('geminiBtn', translations[lang].geminiBtn || 'Generate Image');
setBtnLabel('geminiClear', translations[lang].geminiClear || 'Clear');
setBtnLabel('fluxBtn', translations[lang].fluxBtn || 'Generate Images');
setBtnLabel('fluxClear', translations[lang].fluxClear || 'Clear');
const tE = document.getElementById('tabEditLabel');
const tF = document.getElementById('tabFluxLabel');
if(tE) tE.textContent = translations[lang].tabEdit || 'Edit';
if(tF) tF.textContent = translations[lang].tabFlux || 'Generate';
applyTextTranslationsForPage(lang);
}
languageSelect.addEventListener('change', (e)=> applyLanguage(e.target.value));
function applyTextTranslationsForPage(lang){
document.getElementById('pageTitle').textContent = translations[lang][currentPage+'Title'] || document.getElementById('pageTitle').textContent;
document.getElementById('pageHint').textContent = translations[lang][currentPage+'Hint'] || '';
const img2vidBtn = document.getElementById('img2vidBtn');
if(img2vidBtn) img2vidBtn.setAttribute('data-label', translations[lang].img2vidBtn);
const txt2vidBtn = document.getElementById('txt2vidBtn');
if(txt2vidBtn) txt2vidBtn.setAttribute('data-label', translations[lang].txt2vidBtn);
const geminiBtn = document.getElementById('geminiBtn');
if(geminiBtn) geminiBtn.setAttribute('data-label', translations[lang].geminiBtn);
const fluxBtn = document.getElementById('fluxBtn');
if(fluxBtn) fluxBtn.setAttribute('data-label', translations[lang].fluxBtn);
}
applyLanguage(currentLang);
function setLoading(btn, loading){
if(!btn) return;
if(loading){
if(!btn.dataset.orig) btn.dataset.orig = btn.querySelector('.btn-label') ? btn.querySelector('.btn-label').textContent : btn.textContent;
btn.disabled = true;
btn.innerHTML = '<span class="spinner" aria-hidden="true"></span>';
} else {
btn.disabled = false;
const orig = btn.dataset.orig || btn.getAttribute('data-label') || 'Action';
btn.innerHTML = `<span class="btn-label">${orig}</span>`;
}
}
function smoothClear(elements, { delay=0 } = {}){
const els = Array.isArray(elements) ? elements : [elements];
els.forEach((el, i) => {
if(!el) return;
el.classList.add('fade-out');
setTimeout(() => {
if(el.tagName === 'TEXTAREA' || el.tagName === 'INPUT') {
el.value = '';
el.dir = 'ltr';
el.style.textAlign = 'start';
} else {
el.innerHTML = '';
}
el.classList.remove('fade-out');
}, 300 + delay * i);
});
}
function autoDir(elem){
const arabic = /[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF]/;
elem.addEventListener('input', e=>{
const v = e.target.value;
if(arabic.test(v)){ elem.dir='rtl'; elem.style.textAlign='right'; }
else if(v.trim().length===0){ elem.dir='ltr'; elem.style.textAlign='start'; }
else { elem.dir='ltr'; elem.style.textAlign='left'; }
});
}
['img2vidText','txt2vidText','geminiText','fluxText'].forEach(id => { const el = document.getElementById(id); if(el) autoDir(el); });
let uploadedUrlImg2Vid = null;
const uploadAreaImg2Vid = document.getElementById('uploadAreaImg2Vid');
const imageInputImg2Vid = document.getElementById('imageInputImg2Vid');
uploadAreaImg2Vid.addEventListener('click', ()=> imageInputImg2Vid.click());
imageInputImg2Vid.addEventListener('change', async ()=>{
if(imageInputImg2Vid.files.length===0) return;
const hint = document.getElementById('uploadHintImg2Vid');
hint.innerHTML = `<span class="spinner"></span> ${translations[currentLang].uploading || 'Uploading...'}`;
const fd = new FormData(); fd.append('image', imageInputImg2Vid.files[0]);
try{
const res = await fetch('', { method:'POST', body:fd });
const data = await res.json();
if(data.url){
uploadedUrlImg2Vid = data.url;
uploadAreaImg2Vid.innerHTML = `<img src="${uploadedUrlImg2Vid}" alt="preview"><button class="remove-btn" id="removeImg2Vid">${translations[currentLang].removeBtn || 'Remove'}</button>`;
document.getElementById('removeImg2Vid').onclick = ()=>{
uploadedUrlImg2Vid = null; imageInputImg2Vid.value=''; uploadAreaImg2Vid.innerHTML = `<span id="uploadHintImg2Vid">${translations[currentLang].uploadHintImg2Vid || ''}</span>`;
};
} else { hint.textContent = data.error || (translations[currentLang].uploadFail || 'فشل الرفع'); }
}catch(e){ hint.textContent = (translations[currentLang].uploadError || 'خطأ في الرفع'); }
});
document.getElementById('img2vidBtn').addEventListener('click', async ()=>{
const text = document.getElementById('img2vidText').value.trim();
const result = document.getElementById('img2vidResult'); result.innerHTML='';
const btn = document.getElementById('img2vidBtn');
setLoading(btn, true);
let apiUrl = uploadedUrlImg2Vid ? `https://sii3.moayman.top/api/veo3.php?text=${encodeURIComponent(text)}&link=${encodeURIComponent(uploadedUrlImg2Vid)}` : `https://sii3.moayman.top/api/veo3.php?text=${encodeURIComponent(text)}`;
try{
const r = await fetch(apiUrl);
const data = await r.json();
let videoUrl = data.url || data.video || (data.data && (data.data.url||data.data.video));
if(videoUrl){
result.innerHTML = `<video controls autoplay loop src="${videoUrl}"></video>
<button class="download-btn" onclick="downloadVideo('${videoUrl}')">${translations[currentLang].downloadBtn || 'تحميل'}</button>`;
if(data.dev) showDevNote(result, data.dev);
} else {
result.innerHTML = `<div class="dev-note">${translations[currentLang].unexpected || 'استجابة غير متوقعة'}</div>`;
console.warn('img2vid response', data);
}
} catch(err){
result.innerHTML = `<div class="dev-note">${translations[currentLang].connFail || 'فشل الاتصال'}</div>`;
}
setLoading(btn, false);
});
document.getElementById('img2vidClear').addEventListener('click', ()=>{
smoothClear([document.getElementById('img2vidText'), document.getElementById('img2vidResult'), uploadAreaImg2Vid]);
uploadedUrlImg2Vid = null;
imageInputImg2Vid.value = '';
setTimeout(()=> { uploadAreaImg2Vid.innerHTML = `<span id="uploadHintImg2Vid">${translations[currentLang].uploadHintImg2Vid || ''}</span>`; applyLanguage(currentLang); }, 360);
});
document.getElementById('txt2vidBtn').addEventListener('click', async ()=>{
const text = document.getElementById('txt2vidText').value.trim();
const result = document.getElementById('txt2vidResult'); result.innerHTML='';
const btn = document.getElementById('txt2vidBtn');
setLoading(btn, true);
const apiUrl = `https://sii3.moayman.top/api/veo3.php?text=${encodeURIComponent(text)}`;
try{
const r = await fetch(apiUrl);
const data = await r.json();
let videoUrl = data.url || data.video || (data.data && (data.data.url||data.data.video));
if(videoUrl){
result.innerHTML = `<video controls autoplay loop src="${videoUrl}"></video>
<button class="download-btn" onclick="downloadVideo('${videoUrl}')">${translations[currentLang].downloadBtn || 'تحميل'}</button>`;
if(data.dev) showDevNote(result, data.dev);
} else {
result.innerHTML = `<div class="dev-note">${translations[currentLang].unexpected || 'استجابة غير متوقعة'}</div>`;
console.warn('txt2vid response', data);
}
}catch(e){
result.innerHTML = `<div class="dev-note">${translations[currentLang].connFail || 'فشل الاتصال'}</div>`;
}
setLoading(btn, false);
});
document.getElementById('txt2vidClear').addEventListener('click', ()=>{
smoothClear([document.getElementById('txt2vidText'), document.getElementById('txt2vidResult')]);
});
let uploadedUrlGemini = null;
const uploadAreaGemini = document.getElementById('uploadAreaGemini');
const imageInputGemini = document.getElementById('imageInputGemini');
uploadAreaGemini.addEventListener('click', ()=> imageInputGemini.click());
imageInputGemini.addEventListener('change', async ()=>{
if(imageInputGemini.files.length===0) return;
const hint = document.getElementById('uploadHintGemini');
hint.innerHTML = `<span class="spinner"></span> ${translations[currentLang].uploading || 'Uploading...'}`;
const fd = new FormData(); fd.append('image', imageInputGemini.files[0]);
try{
const res = await fetch('', { method:'POST', body:fd });
const data = await res.json();
if(data.url){
uploadedUrlGemini = data.url;
uploadAreaGemini.innerHTML = `<img src="${uploadedUrlGemini}" alt="preview"><button class="remove-btn" id="removeGemini">${translations[currentLang].removeBtn || 'Remove'}</button>`;
document.getElementById('removeGemini').onclick = ()=>{
uploadedUrlGemini = null; imageInputGemini.value=''; uploadAreaGemini.innerHTML = `<span id="uploadHintGemini">${translations[currentLang].uploadHintGemini || ''}</span>`;
};
} else { hint.textContent = data.error || (translations[currentLang].uploadFail || 'فشل الرفع'); }
}catch(e){ hint.textContent=(translations[currentLang].uploadError || 'خطأ في الرفع'); }
});
document.getElementById('geminiBtn').addEventListener('click', async ()=>{
const text = document.getElementById('geminiText').value.trim();
const result = document.getElementById('geminiResult'); result.innerHTML='';
const btn = document.getElementById('geminiBtn');
setLoading(btn, true);
try{
const body = new URLSearchParams(); body.append('text', text); body.append('link', uploadedUrlGemini || '');
const r = await fetch('https://sii3.moayman.top/api/gemini-img.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: body.toString() });
const data = await r.json();
const image = data.image || data.image1 || null;
if(image){
result.innerHTML = `<img src="${image}?t=${new Date().getTime()}" alt="generated">
<button class="download-btn" onclick="downloadImage('${image}')">${translations[currentLang].downloadBtn || 'تحميل'}</button>`;
if(data.dev) showDevNote(result, data.dev);
} else {
result.innerHTML = `<div class="dev-note">${translations[currentLang].unexpected || 'استجابة غير متوقعة'}</div>`;
console.warn('gemini response', data);
}
}catch(e){
result.innerHTML = `<div class="dev-note">${translations[currentLang].connFail || 'فشل الاتصال'}</div>`;
}
setLoading(btn, false);
});
document.getElementById('geminiClear').addEventListener('click', ()=>{
smoothClear([document.getElementById('geminiText'), document.getElementById('geminiResult'), uploadAreaGemini]);
uploadedUrlGemini = null;
imageInputGemini.value = '';
setTimeout(()=> { uploadAreaGemini.innerHTML = `<span id="uploadHintGemini">${translations[currentLang].uploadHintGemini || ''}</span>`; applyLanguage(currentLang); }, 360);
});
document.getElementById('fluxBtn').addEventListener('click', async ()=>{
const text = document.getElementById('fluxText').value.trim();
const result = document.getElementById('fluxResult'); result.innerHTML='';
const btn = document.getElementById('fluxBtn');
setLoading(btn, true);
try{
const body = new URLSearchParams(); body.append('v', text);
const r = await fetch('https://sii3.moayman.top/api/flux.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: body.toString() });
const data = await r.json();
const imgs = [data.image1, data.image2, data.image3, data.image4].filter(Boolean);
if(imgs.length){
result.innerHTML = '<div class="result-group">' + imgs.map(u=>`
<div class="result-item">
<img src="${u}?t=${new Date().getTime()}" alt="flux">
<button class="download-btn" onclick="downloadImage('${u}')">${translations[currentLang].downloadBtn || 'تحميل'}</button>
</div>
`).join('') + '</div>';
if(data.note) showDevNote(result, data.note);
} else {
result.innerHTML = `<div class="dev-note">${translations[currentLang].unexpected || 'استجابة غير متوقعة'}</div>`;
console.warn('flux response', data);
}
}catch(e){
result.innerHTML = `<div class="dev-note">${translations[currentLang].connFail || 'فشل الاتصال'}</div>`;
}
setLoading(btn, false);
});
document.getElementById('fluxClear').addEventListener('click', ()=>{
smoothClear([document.getElementById('fluxText'), document.getElementById('fluxResult')]);
});
const tabs = document.querySelectorAll('.tab');
tabs.forEach(t => t.addEventListener('click', ()=>{
tabs.forEach(x => x.classList.toggle('active', x===t));
document.getElementById('tab-edit').style.display = t.dataset.tab==='edit' ? 'block' : 'none';
document.getElementById('tab-flux').style.display = t.dataset.tab==='flux' ? 'block' : 'none';
}));
function showDevNote(targetEl, text){ if(!text) return; const d = document.createElement('div'); d.className='dev-note'; d.textContent = text; targetEl.appendChild(d); d.scrollIntoView({behavior:'smooth', block:'end'}); }
const bgVideo = document.getElementById('bgVideo');
if (bgVideo) {
bgVideo.play().catch(()=> {
console.warn('bg video autoplay blocked');
bgVideo.muted = true;
bgVideo.play().catch(()=>{});
});
bgVideo.addEventListener('error', ()=> {
document.querySelector('.bg-video-wrap').classList.add('video-error');
});
}
function downloadImage(url){
const a = document.createElement('a');
a.href = url;
a.download = `darkai_image_${new Date().getTime()}`;
document.body.appendChild(a);
a.click();
document.body.removeChild(a);
}
function downloadVideo(url){
const a = document.createElement('a');
a.href = url;
a.download = `darkai_video_${new Date().getTime()}`;
document.body.appendChild(a);
a.click();
document.body.removeChild(a);
}
console.log('DarkAI UI ready');
document.getElementById('goImg2Vid').addEventListener('click', ()=> goTo('image-to-video'));
document.getElementById('goTxt2Vid').addEventListener('click', ()=> goTo('text-to-video'));
document.getElementById('goImgTools').addEventListener('click', ()=> goTo('image-tools'));
</script>
</body>
</html>
