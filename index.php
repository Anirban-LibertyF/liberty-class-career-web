<?php
declare(strict_types=1);
$requestPath=(string)(parse_url($_SERVER['REQUEST_URI']??'/',PHP_URL_PATH)?:'/');
if($requestPath==='/cbt'||str_starts_with($requestPath,'/cbt/')){
    require __DIR__.'/cbt/public/index.php';
    exit;
}
require_once __DIR__.'/cbt/vendor/autoload.php';
if (class_exists(Dotenv\Dotenv::class) && is_file(__DIR__ . '/.env')) {
    Dotenv\Dotenv::createImmutable(__DIR__)->safeLoad();
}
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
if (($_ENV['APP_ENV'] ?? 'production') === 'production') {
    ini_set('session.cookie_secure', '1');
}
session_name($_ENV['SESSION_NAME'] ?? 'lcc_cbt_session');
session_start();
\App\Core\Auth::restoreRemembered();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$studentUser = isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? null) === 'student'
    ? $_SESSION['user']
    : null;
$studentPhone = '';
if ($studentUser && !empty($studentUser['id'])) {
    try {
        $phoneQuery = \App\Core\Database::connection()->prepare('SELECT phone FROM students WHERE id=? AND deleted_at IS NULL');
        $phoneQuery->execute([(int) $studentUser['id']]);
        $studentPhone = (string) ($phoneQuery->fetchColumn() ?: '');
    } catch (Throwable $ignored) {}
}
$studentAvatar = $studentUser && !empty($studentUser['profile_photo'])
    ? '/cbt/media/' . ltrim((string) $studentUser['profile_photo'], '/')
    : '/assets/images/brand/default-student-avatar.webp';
if (empty($_SESSION['_csrf'])) {
    $_SESSION['_csrf'] = bin2hex(random_bytes(32));
}
$portalCssVersion = (string) (@filemtime(__DIR__ . '/assets/css/php-portal.css') ?: 1);
$portalJsVersion = (string) (@filemtime(__DIR__ . '/assets/js/app.js') ?: 1);
$homeLoginError = !$studentUser ? ($_SESSION['_flash']['error'] ?? null) : null;
if ($homeLoginError !== null) {
    unset($_SESSION['_flash']['error']);
}

$courses = [
    ['slug'=>'ai-machine-learning','image'=>'assets/images/courses/ai-machine-learning.svg','title'=>'AI & Machine Learning','cat'=>'Technology','weeks'=>24,'rating'=>'4.9','students'=>126,'color'=>'blue','icon'=>'AI','courseFee'=>1200,'videoFee'=>399,'materialFee'=>299],
    ['slug'=>'web-development','image'=>'assets/images/courses/web-development.svg','title'=>'Web Development','cat'=>'Development','weeks'=>24,'rating'=>'4.8','students'=>214,'color'=>'red','icon'=>'&lt;/&gt;','courseFee'=>1200,'videoFee'=>399,'materialFee'=>299],
    ['slug'=>'share-trading','image'=>'assets/images/courses/share-trading.svg','title'=>'Share Trading','cat'=>'Finance','weeks'=>24,'rating'=>'4.7','students'=>98,'color'=>'gold','icon'=>'↗','courseFee'=>1200,'videoFee'=>399,'materialFee'=>299],
    ['slug'=>'digital-marketing','image'=>'assets/images/courses/digital-marketing.svg','title'=>'Digital Marketing','cat'=>'Marketing','weeks'=>24,'rating'=>'4.8','students'=>184,'color'=>'red','icon'=>'DM','courseFee'=>1200,'videoFee'=>399,'materialFee'=>299],
    ['slug'=>'content-creation','image'=>'assets/images/courses/content-creation.svg','title'=>'Content Creation & Editing','cat'=>'Creative','weeks'=>24,'rating'=>'4.7','students'=>142,'color'=>'gold','icon'=>'CC','courseFee'=>1200,'videoFee'=>399,'materialFee'=>299],
    ['slug'=>'app-web-development','image'=>'assets/images/courses/app-development.svg','title'=>'App Development','cat'=>'Development','weeks'=>24,'rating'=>'4.9','students'=>117,'color'=>'blue','icon'=>'APP','courseFee'=>1200,'videoFee'=>399,'materialFee'=>299],
];
$fallbackCourses = $courses;

function skill_key(string $value): string {
    return strtolower((string) preg_replace('/[^a-z0-9]+/i', '', $value));
}

function admin_skill_courses(array $fallbackCourses): array {
    $base = rtrim((string) ($_ENV['LCC_ADMIN_API_BASE_URL'] ?? getenv('LCC_ADMIN_API_BASE_URL') ?: ''), '/');
    if ($base === '') return [];

    $context = stream_context_create(['http' => [
        'method' => 'GET',
        'header' => "Accept: application/json\r\nConnection: close\r\n",
        'timeout' => 2.5,
        'ignore_errors' => true,
    ]]);
    $json = @file_get_contents($base . '/public_skills.php', false, $context);
    if (!is_string($json) || $json === '') return [];
    $payload = json_decode($json, true);
    $rows = $payload['data']['skills'] ?? null;
    if (($payload['status'] ?? '') !== 'success' || !is_array($rows) || !$rows) return [];

    $defaults = [];
    foreach ($fallbackCourses as $course) {
        $defaults[(string) $course['slug']] = $course;
        $defaults[skill_key((string) $course['title'])] = $course;
    }
    $aliases = [
        'aiml' => 'aimachinelearning',
        'appwebdevelopment' => 'appdevelopment',
        'contentcreation' => 'contentcreationediting',
    ];
    $slugImages = [
        'ai-machine-learning'=>'assets/images/courses/ai-machine-learning.svg','share-trading'=>'assets/images/courses/share-trading.svg',
        'content-creation'=>'assets/images/courses/content-creation.svg','digital-marketing'=>'assets/images/courses/digital-marketing.svg',
        'app-web-development'=>'assets/images/courses/app-development.svg','private-job-preparation'=>'assets/images/courses/private-job-preparation.svg',
        'toefl'=>'assets/images/courses/toefl.svg','ielts'=>'assets/images/courses/ielts.svg',
        'government-job-preparation'=>'assets/images/courses/government-job-preparation.svg','dmlt'=>'assets/images/courses/dmlt.svg',
        'dott'=>'assets/images/courses/dott.svg','jee-neet'=>'assets/images/courses/jee-neet.svg',
    ];
    $generic = $fallbackCourses[0];
    $courses = [];
    foreach ($rows as $row) {
        if (!is_array($row) || trim((string) ($row['name'] ?? '')) === '') continue;
        $key = skill_key((string) ($row['title'] ?? $row['name']));
        $slug = trim((string) ($row['slug'] ?? ''));
        $match = $defaults[$slug] ?? $defaults[$key] ?? $defaults[$aliases[$key] ?? ''] ?? $generic;
        $fallbackImage = $slugImages[$slug] ?? $match['image'];
        $adminImage = filter_var((string) ($row['thumbnail_url'] ?? ''), FILTER_VALIDATE_URL) ?: '';
        $syllabusUrl = filter_var((string) ($row['syllabus_pdf_url'] ?? ''), FILTER_VALIDATE_URL) ?: '';
        $courseFee = max(0, (float) ($row['course_fee'] ?? 0));
        $videoFee = max(0, (float) ($row['recorded_video_fee'] ?? 0));
        $materialFee = max(0, (float) ($row['material_fee'] ?? 0));
        $courses[] = array_merge($match, [
            'id' => max(0, (int) ($row['id'] ?? 0)),
            'slug' => $slug,
            'title' => trim((string) ($row['title'] ?? $row['name'])),
            'cat' => trim((string) ($row['category'] ?? '')) ?: $match['cat'],
            'subtitle' => trim((string) ($row['short_description'] ?? $row['subtitle'] ?? '')),
            'duration' => trim((string) ($row['duration'] ?? '')) ?: ((int) $match['weeks'] . ' weeks'),
            'color' => in_array(($row['color'] ?? ''), ['red','blue','green','gold','purple'], true) ? $row['color'] : $match['color'],
            'image' => $adminImage ?: $fallbackImage,
            'fallbackImage' => $fallbackImage,
            'syllabusUrl' => $syllabusUrl,
            'courseFee' => $courseFee,
            'videoFee' => $videoFee,
            'materialFee' => $materialFee,
        ]);
    }
    return $courses;
}

$adminCourses = admin_skill_courses($fallbackCourses);
if ($adminCourses) $courses = $adminCourses;
$homeCourses = array_slice($courses, 0, 6);
$listingCourses = $adminCourses ? $courses : array_merge($courses, $courses);
$degrees = [
    ['image'=>'https://www.eduvow.com/images/college/186_EduvowIIMTUniversity.jpeg','name'=>'Online BA','university'=>'IIMT University Meerut — CDOE','duration'=>'3 Years','fee'=>'₹27,000','tuition'=>'₹7,000 per year','examFee'=>'₹2,000 per year','registration'=>'₹2,000 one time','eligibility'=>'As per university admission rules','syllabus'=>'Economics, Hindi, English, Political Science, Sociology or Psychology'],
    ['image'=>'https://www.eduvow.com/images/college/186_EduvowIIMTUniversity.jpeg','name'=>'Online BA (JMC)','university'=>'IIMT University Meerut — CDOE','duration'=>'3 Years','fee'=>'₹33,000','tuition'=>'₹9,000 per year','examFee'=>'₹2,000 per year','registration'=>'₹2,000 one time','eligibility'=>'As per university admission rules','syllabus'=>'Journalism and Mass Communication'],
    ['image'=>'https://www.eduvow.com/images/college/186_EduvowIIMTUniversity.jpeg','name'=>'Online B.Com (Hons.)','university'=>'IIMT University Meerut — CDOE','duration'=>'3 Years','fee'=>'₹36,000','tuition'=>'₹10,000 per year','examFee'=>'₹2,000 per year','registration'=>'₹2,000 one time','eligibility'=>'As per university admission rules','syllabus'=>'B.Com Honours'],
    ['image'=>'https://www.eduvow.com/images/college/186_EduvowIIMTUniversity.jpeg','name'=>'Online MBA — General','university'=>'IIMT University Meerut — CDOE','duration'=>'2 Years','fee'=>'₹54,000','tuition'=>'₹25,000 per year','examFee'=>'₹2,000 per year','registration'=>'₹2,000 one time','eligibility'=>'As per university admission rules','syllabus'=>'General Management'],
    ['image'=>'https://www.eduvow.com/images/college/186_EduvowIIMTUniversity.jpeg','name'=>'Online MBA — Marketing','university'=>'IIMT University Meerut — CDOE','duration'=>'2 Years','fee'=>'₹54,000','tuition'=>'₹25,000 per year','examFee'=>'₹2,000 per year','registration'=>'₹2,000 one time','eligibility'=>'As per university admission rules','syllabus'=>'Marketing Management'],
    ['image'=>'https://www.eduvow.com/images/college/186_EduvowIIMTUniversity.jpeg','name'=>'Online MBA — HRM','university'=>'IIMT University Meerut — CDOE','duration'=>'2 Years','fee'=>'₹54,000','tuition'=>'₹25,000 per year','examFee'=>'₹2,000 per year','registration'=>'₹2,000 one time','eligibility'=>'As per university admission rules','syllabus'=>'Human Resource Management'],
    ['image'=>'https://www.eduvow.com/images/college/186_EduvowIIMTUniversity.jpeg','name'=>'Online MBA — Banking & Finance','university'=>'IIMT University Meerut — CDOE','duration'=>'2 Years','fee'=>'₹54,000','tuition'=>'₹25,000 per year','examFee'=>'₹2,000 per year','registration'=>'₹2,000 one time','eligibility'=>'As per university admission rules','syllabus'=>'Banking and Finance'],
];
$institutes = [
    ['name'=>'IIF Business School','type'=>'Business School','image'=>'https://www.collegebatch.com/static/clg-gallery/iif-college-of-commerce-management-studies-greater-noida-234665.jpg','courses'=>['MBA / MBA+++ — 2 Years','B.Voc. Banking, Financial Services & Insurance — 3 Years']],
    ['name'=>'M.G. Institute of Management & Technology','type'=>'Engineering & Management Institute','image'=>'https://cdn.collegevidya.com/mobile_banners/mg-institute-of-management-and-technology-mobile-banner.webp','courses'=>['B.Tech Civil Engineering — 4 Years','B.Tech Computer Science Engineering — 4 Years','B.Tech CSE (AI & ML) — 4 Years','B.Tech Electrical Engineering — 4 Years','B.Tech Mechanical Engineering — 4 Years','B.Tech Biotechnology — 4 Years','Polytechnic Electrical Engineering — 3 Years','Polytechnic Civil Engineering — 3 Years','Polytechnic Mechanical Engineering — 3 Years','Polytechnic Computer Science — 3 Years']],
    ['name'=>'B.R. Gautam Polytechnic','type'=>'Polytechnic Institute','image'=>'https://cache.careers360.mobi/media/colleges/social-media/media-gallery/41559/2023/11/25/Campus%20Entrance%20View%20of%20BR%20Gautam%20Polytechnic%20Institute%20Jaunpur_Campus-View.png','courses'=>['Diploma Mechanical Engineering (Production) — 3 Years','Diploma Mechanical Engineering (Automobile) — 3 Years','Diploma Civil Engineering — 3 Years','Diploma Electrical Engineering — 3 Years','Diploma Computer Science & Engineering — 3 Years']],
    ['name'=>'Islamia College of Commerce – GIDA Campus','type'=>'College','image'=>'https://www.islamiacollegeofcommerce.in/images/college-image.jpg','courses'=>['BBA — 3 Years','BCA — 3 Years','B.Com — 3 Years']],
    ['name'=>'Shobhit Institute of Engineering & Technology (Shobhit University)','type'=>'University','image'=>'https://www.collegebatch.com/static/clg-gallery/shobhit-deemed-university-meerut-221886.jpg','courses'=>['B.Tech Computer Science Engineering — 4 Years','B.Tech Agriculture Engineering — 4 Years','B.Tech Biomedical Engineering — 4 Years','B.Tech Biotechnology — 4 Years','B.Sc. (Hons.) Agriculture — 4 Years','B.Sc. (Hons.) Nutrition & Dietetics — 3 Years','B.Sc. (Hons.) Biotechnology — 3 Years','B.Sc. (Hons.) Microbiology — 3 Years','B.Sc. (Hons.) Biomedical Science — 3 Years','BA LL.B. — 5 Years','BCA / BCA AI & ML / Cyber Security / Data Science — 3 Years','BBA Programmes — 3 Years','M.Tech Programmes — 2 Years','MCA Specialisations — 2 Years','M.Com — 2 Years','MBA Specialisations — 2 Years','PG Diploma Programmes','Ph.D. Programmes']],
];
function h(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function course_card(array $course, bool $duplicate = false): void { ?>
<article class="course skill-slide-card" data-course='<?= h(json_encode($course, JSON_UNESCAPED_UNICODE)) ?>'>
  <div class="course-art <?= h($course['color']) ?>"><img src="<?= h($course['image']) ?>" alt="<?= h($course['title']) ?> professional course thumbnail" loading="lazy"<?php if (!empty($course['fallbackImage'])): ?> onerror="this.onerror=null;this.src='<?= h($course['fallbackImage']) ?>'"<?php endif; ?>><i><?= h($course['cat']) ?></i></div>
  <div class="course-body"><div class="rating"><i data-lucide="star"></i> <?= h($course['rating']) ?> <span>(<?= (int)$course['students'] ?>)</span></div>
  <h3><?= h($course['title']) ?></h3><div class="meta"><span><i data-lucide="clock-3"></i><?= h((string) ($course['duration'] ?? ((int)$course['weeks'] . ' weeks'))) ?></span><span><i data-lucide="video"></i>Live + Video</span></div>
  <div class="price"><span>Starting from <b>₹299</b></span><button class="open-course">Know more <i data-lucide="arrow-right"></i></button></div></div>
</article><?php }
function degree_card(array $degree): void { ?>
<article class="home-degree-card" data-degree='<?= h(json_encode($degree, JSON_UNESCAPED_UNICODE)) ?>'>
  <div class="home-degree-label"><span>ONLINE DEGREE</span><i>UGC Approved</i></div>
  <h3><?= h($degree['name']) ?></h3>
  <div class="university-image-slot"><img src="<?= h($degree['image']) ?>" alt="<?= h($degree['university']) ?> campus" loading="lazy" referrerpolicy="no-referrer"></div>
  <p><?= h($degree['university']) ?></p>
  <div class="home-degree-facts"><span><i data-lucide="clock-3"></i><?= h($degree['duration']) ?></span><b><small>Total fee</small><?= h($degree['fee']) ?></b></div>
  <button class="open-degree">View & Apply <i data-lucide="arrow-right"></i></button>
</article><?php }
function institute_card(array $institute, string $button = 'Apply', bool $showCourseCount = false): void { ?>
<article class="associate-card" data-institute='<?= h(json_encode($institute, JSON_UNESCAPED_UNICODE)) ?>'>
  <div class="associate-image"><img src="<?= h($institute['image']) ?>" alt="<?= h($institute['name']) ?> campus" loading="lazy" referrerpolicy="no-referrer"><span><?= h($institute['type']) ?></span></div>
  <div class="associate-card-body"><small><?= h($institute['type']) ?></small><h3><?= h($institute['name']) ?></h3><?php if ($showCourseCount): ?><p><?= count($institute['courses']) ?> courses available</p><?php endif; ?><button class="open-institute"><?= h($button) ?> <i data-lucide="arrow-right"></i></button></div>
</article><?php }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="<?= h($_SESSION['csrf_token']) ?>">
  <title>Liberty Class and Career — Student Portal</title>
  <meta name="description" content="Skill training, admission guidance, online degrees, learning and CBT for Liberty students.">
  <link rel="icon" type="image/png" href="assets/images/brand/liberty-icon.png">
  <link rel="stylesheet" href="assets/css/style.css?v=20260831-13">
  <link rel="stylesheet" href="assets/css/php-portal.css?v=<?= h($portalCssVersion) ?>">
</head>
<body data-student-logged-in="<?= $studentUser ? '1' : '0' ?>">
<main>
  <div class="topline"></div>
  <header class="public-header">
    <button class="menu-button" id="menuButton" aria-label="Open menu"><i data-lucide="menu"></i></button>
    <div class="logo"><img src="assets/images/brand/liberty-class-career-logo.jpg" alt="Liberty Class and Career — Since 2008"></div>
    <nav class="nav" id="mainNav">
      <button class="active" data-page="home"><i data-lucide="home"></i>Home</button>
      <div class="services-menu"><button class="services-trigger"><i data-lucide="briefcase-business"></i>Our Services<i data-lucide="chevron-down"></i></button>
        <div class="services-dropdown"><button data-page="skill-training"><i data-lucide="book-open"></i>Skill Training</button><button data-page="admission"><i data-lucide="briefcase-business"></i>Admission</button><button data-page="online-degree"><i data-lucide="graduation-cap"></i>Online Degree</button></div>
      </div>
      <button data-page="my-courses"><i data-lucide="play"></i>My Courses</button>
      <a class="external-cbt" href="/cbt"><i data-lucide="clipboard-check"></i>CBT</a>
    </nav>
    <div class="header-actions"><button class="icon-btn" aria-label="Search"><i data-lucide="search"></i></button><button class="icon-btn" aria-label="Notifications"><i data-lucide="bell"></i><i></i></button>
      <a class="icon-btn whatsapp-support" href="https://wa.me/918276015376?text=Hi%2C%20I%20need%20help" target="_blank" rel="noopener noreferrer" aria-label="Chat with us on WhatsApp" title="Chat with us on WhatsApp"><svg width="26" height="26" viewBox="0 0 32 32" aria-hidden="true"><path fill="#25D366" d="M16.004 3C8.822 3 3 8.82 3 16c0 2.292.598 4.53 1.734 6.5L3 29l6.668-1.75A12.95 12.95 0 0 0 16.004 29C23.184 29 29 23.18 29 16S23.184 3 16.004 3Zm0 23.8a10.77 10.77 0 0 1-5.49-1.5l-.393-.234-3.957 1.038 1.055-3.858-.256-.398A10.77 10.77 0 0 1 5.204 16c0-5.956 4.844-10.8 10.8-10.8 5.954 0 10.796 4.844 10.796 10.8s-4.842 10.8-10.796 10.8Zm5.923-8.087c-.325-.163-1.923-.948-2.221-1.057-.298-.108-.515-.163-.732.163-.216.325-.84 1.057-1.03 1.273-.19.217-.38.244-.705.081-.325-.162-1.372-.505-2.613-1.61-.966-.86-1.619-1.923-1.808-2.248-.19-.325-.02-.5.143-.662.146-.145.325-.38.488-.569.162-.19.216-.325.325-.542.108-.216.054-.406-.027-.569-.081-.162-.732-1.76-1.003-2.41-.264-.634-.532-.548-.732-.558l-.623-.011c-.217 0-.57.081-.868.406-.298.325-1.138 1.111-1.138 2.71 0 1.598 1.165 3.142 1.327 3.36.163.216 2.292 3.499 5.553 4.906.776.334 1.381.534 1.853.684.779.247 1.488.212 2.048.129.625-.093 1.923-.786 2.194-1.544.271-.758.271-1.408.19-1.544-.081-.135-.298-.216-.623-.379Z"/></svg></a>
      <?php if ($studentUser): ?>
      <div class="profile-menu-wrap"><button class="profile-trigger" id="profileTrigger" aria-expanded="false" aria-label="Open <?= h((string) $studentUser['name']) ?>'s profile menu" title="Profile"><img class="profile-avatar" src="<?= h($studentAvatar) ?>" alt=""><span><?= h((string) $studentUser['name']) ?></span><i data-lucide="chevron-down"></i></button>
        <div class="profile-dropdown" id="profileDropdown"><div class="profile-dropdown-head"><img class="profile-dropdown-avatar" src="<?= h($studentAvatar) ?>" alt="<?= h((string) $studentUser['name']) ?>"><div><b><?= h((string) $studentUser['name']) ?></b><small>Student Account</small></div></div><button data-page="my-courses"><i data-lucide="play"></i>My Learning</button><button type="button" data-profile-settings-open><i data-lucide="settings"></i>Account Settings</button><div class="profile-dropdown-divider"></div><form class="profile-logout-form" method="post" action="/cbt/logout"><input type="hidden" name="_csrf" value="<?= h($_SESSION['_csrf']) ?>"><input type="hidden" name="return" value="/"><button class="logout-item" type="submit"><i data-lucide="log-out"></i>Logout</button></form></div>
      </div>
      <?php else: ?>
      <button class="student-avatar-login" type="button" data-home-login-open aria-label="Student login" title="Student login"><img src="/assets/images/brand/default-student-avatar.webp" alt=""></button>
      <?php endif; ?>
    </div>
  </header>

  <section class="portal-page" id="page-home">
    <section class="welcome-shell"><div class="welcome">
      <div class="hero-slider"><div class="hero-slide-stage">
        <button class="hero-slide thumbnail-skills active" data-target="skill-training"><div class="thumbnail-icon"><i data-lucide="book-open"></i></div><div class="thumbnail-copy"><span>LIBERTY SKILL TRAINING</span><h1>Learn today. Lead tomorrow.</h1><p>Build Practical, Job-Ready Skills</p><div class="thumbnail-topics"><i>Share Trading</i><i>AI & ML</i><i>Content Creation & Editing</i><i>Web/App Development</i><i>Digital Marketing</i></div></div><i class="thumbnail-arrow" data-lucide="arrow-right"></i></button>
        <button class="hero-slide thumbnail-admission" data-target="admission"><div class="thumbnail-icon"><i data-lucide="briefcase-business"></i></div><div class="thumbnail-copy"><span>LIBERTY COLLEGE & CAREER</span><h1>The right admission. A stronger future.</h1><p>College, Course & Admission Guidance</p><div class="thumbnail-topics"><i>Admission Support</i><i>College Selection</i><i>Course Guidance</i></div></div><i class="thumbnail-arrow" data-lucide="arrow-right"></i></button>
        <button class="hero-slide thumbnail-degree" data-target="online-degree"><div class="thumbnail-icon"><i data-lucide="graduation-cap"></i></div><div class="thumbnail-copy"><span>LIBERTY ONLINE DEGREE</span><h1>Your degree. Your time. Your future.</h1><p>Flexible Online Learning from Anywhere</p><div class="thumbnail-topics"><i>UG & PG Programmes</i><i>Flexible Learning</i><i>Career Growth</i></div></div><i class="thumbnail-arrow" data-lucide="arrow-right"></i></button>
        <a class="hero-slide thumbnail-app" href="https://liberty-class-career-ui.libertyfoundation4ne.chatgpt.site/?download=app"><div class="thumbnail-app-qr"><img src="assets/images/brand/liberty-app-qr.jpg" alt="App download QR"></div><div class="thumbnail-copy"><span>LIBERTY CLASS AND CAREER APP</span><h1>Learn. Practise. Grow—anywhere.</h1><p>Government & Private Job Updates</p><strong class="app-ai-highlight">24×7 AI-Powered Student Assistance</strong><div class="thumbnail-topics"><i>Government Jobs</i><i>Private Jobs</i><i>Live Classes</i><i>CBT</i></div></div><i class="thumbnail-arrow" data-lucide="arrow-right"></i></a>
      </div><div class="hero-dots"><button class="active"></button><button></button><button></button><button></button></div>
      <div class="hero-fixed-actions"><button class="primary open-contact">Contact Us <i data-lucide="arrow-right"></i></button><button class="secondary" data-page="my-courses"><i data-lucide="play"></i>Your Learning</button></div></div>
      <div class="hero-visual animated-learning"><div class="learning-ring ring-one"></div><div class="learning-ring ring-two"></div><div class="student-card logo-card"><img src="assets/images/brand/liberty-icon.png" alt="Liberty logo"></div><div class="hero-ai-assistance">24×7 AI Powered</div><div class="orbit-pill ai">AI</div><div class="orbit-pill stock">Stock Market</div><div class="orbit-pill degree">Online Degree</div><div class="orbit-pill live"><span class="live-dot"></span>Live Class</div><div class="orbit-pill college">College Admission</div><div class="orbit-pill skill">Skill Training</div></div>
    </div></section>
    <section class="content"><div class="quick-grid"><article class="quick"><div class="qicon red"><i data-lucide="book-open"></i></div><div><strong>04</strong><span>Enrolled courses</span></div><i data-lucide="arrow-right"></i></article><article class="quick"><div class="qicon blue"><i data-lucide="clock-3"></i></div><div><strong>18h</strong><span>Learning time</span></div><i data-lucide="arrow-right"></i></article><article class="quick"><div class="qicon gold"><i data-lucide="calendar-days"></i></div><div><strong>02</strong><span>Upcoming classes</span></div><i data-lucide="arrow-right"></i></article><article class="quick"><div class="qicon green"><i data-lucide="star"></i></div><div><strong>03</strong><span>Certificates</span></div><i data-lucide="arrow-right"></i></article></div>
      <div class="section-head"><div><span class="eyebrow">BUILD YOUR FUTURE</span><h2>Popular skill trainings</h2></div><button class="view-all-institutes" data-page="skill-training">View all courses <i data-lucide="arrow-right"></i></button></div>
      <div class="course-slider-window"><div class="course-grid animated-course-group" id="homeCourseGrid"><?php foreach ($homeCourses as $course) course_card($course); ?></div></div>
      <section class="home-degrees"><div class="section-head degree-section-head"><div><span class="eyebrow">STUDY FROM ANYWHERE</span><h2>Online Degree Programmes</h2></div><button class="view-all-degrees" data-page="online-degree">View All Courses <i data-lucide="arrow-right"></i></button></div><div class="degree-marquee"><div class="degree-marquee-track"><?php foreach (array_merge($degrees,$degrees) as $degree) degree_card($degree); ?></div></div></section>
      <section class="associate-section"><div class="section-head"><div><span class="eyebrow">ADMISSION · COLLEGE AND CAREER</span><h2>Our Associate Institute / College / University</h2></div><button class="view-all-institutes" data-page="admission">View All Institutes <i data-lucide="arrow-right"></i></button></div><div class="associate-grid animated-institute-grid" id="homeInstituteGrid"><?php foreach ($institutes as $institute) institute_card($institute); ?></div></section>
      <section class="home-cbt-trending" data-cbt-trending data-endpoint="/cbt/api/public/trending" data-base=""><div class="section-head"><div><span class="eyebrow">POPULAR THIS WEEK</span><h2>Trending CBT Tests</h2><p>Popular mock tests selected from current student enrollments.</p></div><a class="view-all-cbt" href="/cbt">View All Tests <i data-lucide="arrow-right"></i></a></div><div class="home-cbt-grid" data-cbt-grid aria-live="polite"><article class="home-cbt-loading"><i data-lucide="loader-circle"></i><span>Loading trending tests...</span></article></div><p class="home-cbt-unavailable" data-cbt-unavailable hidden>Trending tests are temporarily unavailable. <a href="/cbt">View all CBT tests</a></p></section>
    </section>
  </section>

  <section class="portal-page page content hidden-page" id="page-skill-training"><div class="page-title"><span class="eyebrow">SKILL TRAINING</span><h1>Choose a skill. Build your career.</h1><p>Learn from industry experts with videos, downloadable materials and interactive live classes.</p></div><div class="filterbar"><label><i data-lucide="search"></i><input id="courseSearch" placeholder="Search courses..."></label><label class="filter-select"><span>Category</span><select id="courseCategoryFilter" aria-label="Filter courses by category"><option value="all">All categories</option><option value="Technology">Technology</option><option value="Development">Development</option><option value="Finance">Finance</option><option value="Marketing">Marketing</option><option value="Creative">Creative</option><option value="Career">Career</option><option value="Language">Language</option><option value="Competitive Exams">Competitive Exams</option><option value="Healthcare">Healthcare</option></select><i data-lucide="chevron-down"></i></label><label class="filter-select"><span>Sort</span><select id="courseSort" aria-label="Sort courses"><option value="popular">Most popular</option><option value="rating">Highest rated</option><option value="title-asc">Title A–Z</option><option value="title-desc">Title Z–A</option></select><i data-lucide="chevron-down"></i></label></div><div class="course-grid" id="allCourses"><?php foreach ($listingCourses as $course) course_card($course); ?></div></section>

  <section class="portal-page page content hidden-page online-degree-page" id="page-online-degree"><div class="page-title"><span class="eyebrow">ONLINE DEGREE</span><h1>Recognised degrees, flexible learning.</h1><p>Compare all online degree programmes, universities, fees and duration in one place.</p></div><div class="home-degree-grid online-degree-all-grid"><?php foreach ($degrees as $degree) degree_card($degree); ?></div></section>

  <section class="portal-page page content hidden-page admission-institutes-page" id="page-admission"><div class="page-title"><span class="eyebrow">ADMISSION · COLLEGE AND CAREER</span><h1>Find the right institute for your future.</h1><p>Explore our associate institutes, colleges and universities. Select an institute to view all available courses and request admission guidance.</p></div><div class="admission-page-summary"><span><i data-lucide="graduation-cap"></i>Associate Institutes</span><b><?= count($institutes) ?> Options</b></div><div class="associate-grid admission-all-grid"><?php foreach ($institutes as $institute) institute_card($institute, 'View Details & Apply', true); ?></div></section>

  <section class="portal-page page content hidden-page" id="page-my-courses"><div class="page-title"><span class="eyebrow">MY LEARNING</span><h1>Continue where you left off.</h1><p>Your enrolled courses, live schedules and learning resources.</p></div><div class="learning-list"><?php foreach (array_slice($courses,0,2) as $i=>$course): ?><article><div class="mini-art <?= h($course['color']) ?>"><?= $course['icon'] ?></div><div class="learn-main"><span><?= h($course['cat']) ?></span><h3><?= h($course['title']) ?></h3><div class="bar"><i style="width:<?= $i ? '38':'72' ?>%"></i></div><small><?= $i ? '38':'72' ?>% completed</small></div><div class="learn-actions"><button class="primary open-learning" data-learning-title="<?= h($course['title']) ?>" data-learning-category="<?= h($course['cat']) ?>"><i data-lucide="play"></i>Continue</button><button class="open-learning" data-learning-title="<?= h($course['title']) ?>" data-learning-category="<?= h($course['cat']) ?>" data-learning-target="materials"><i data-lucide="download"></i>Materials</button></div></article><?php endforeach; ?></div></section>

  <section class="portal-page page content hidden-page course-learning-page" id="page-course-learning">
    <button class="learning-back" data-page="my-courses"><i data-lucide="arrow-left"></i>Back to My Courses</button>
    <div class="learning-course-hero"><div><span class="eyebrow" id="learningCourseCategory">COURSE LEARNING</span><h1 id="learningCourseTitle">AI &amp; Machine Learning</h1><p>Complete each lesson and tick the checkbox to track your progress.</p></div><div class="learning-progress"><b id="learningProgressText">0 of 5 completed</b><div><i id="learningProgressBar"></i></div></div></div>

    <section class="learning-resource-section live-resource-section" id="learningLive"><div class="learning-section-heading"><span><i data-lucide="radio"></i></span><div><small>STEP 01</small><h2>Live Class Link</h2><p>Join the scheduled interactive class with your trainer.</p></div></div><article class="learning-resource-row featured"><label class="learning-check"><input type="checkbox" class="resource-check"><span><i data-lucide="check"></i></span></label><div class="resource-type red"><i data-lucide="video"></i></div><div class="resource-copy"><small>NEXT LIVE CLASS</small><h3>Weekly Mentor Live Session</h3><p>Sunday · 7:00 PM · Demo class link</p></div><button class="learning-link demo-resource-action" data-message="Demo live-class link opened."><i data-lucide="external-link"></i>Join Live Class</button></article></section>

    <section class="learning-resource-section" id="learningVideos"><div class="learning-section-heading"><span><i data-lucide="play-circle"></i></span><div><small>STEP 02</small><h2>Video Lessons</h2><p>Watch the lessons in sequence at your own pace.</p></div></div><div class="learning-resource-list"><article class="learning-resource-row"><label class="learning-check"><input type="checkbox" class="resource-check"><span><i data-lucide="check"></i></span></label><div class="resource-video-thumb blue"><i data-lucide="play"></i></div><div class="resource-copy"><small>VIDEO · 12 MIN</small><h3>Introduction &amp; Course Roadmap</h3><p>Understand the learning path, projects and outcomes.</p></div><button class="resource-open demo-resource-action" data-message="Demo video lesson is ready to play."><i data-lucide="play"></i>Play</button></article><article class="learning-resource-row"><label class="learning-check"><input type="checkbox" class="resource-check"><span><i data-lucide="check"></i></span></label><div class="resource-video-thumb gold"><i data-lucide="play"></i></div><div class="resource-copy"><small>VIDEO · 18 MIN</small><h3>Core Concepts — Guided Lesson</h3><p>Learn the key concepts through a practical demonstration.</p></div><button class="resource-open demo-resource-action" data-message="Demo video lesson is ready to play."><i data-lucide="play"></i>Play</button></article></div></section>

    <section class="learning-resource-section" id="learningMaterials"><div class="learning-section-heading"><span><i data-lucide="files"></i></span><div><small>STEP 03</small><h2>Study Materials</h2><p>Use the notes and practice worksheet after each lesson.</p></div></div><div class="learning-resource-list"><article class="learning-resource-row"><label class="learning-check"><input type="checkbox" class="resource-check"><span><i data-lucide="check"></i></span></label><div class="resource-type navy"><i data-lucide="file-text"></i></div><div class="resource-copy"><small>PDF NOTES · DEMO</small><h3>Module 01 — Quick Revision Notes</h3><p>Important definitions, examples and lesson summary.</p></div><button class="resource-open demo-resource-action" data-message="Demo study material opened."><i data-lucide="eye"></i>Open</button></article><article class="learning-resource-row"><label class="learning-check"><input type="checkbox" class="resource-check"><span><i data-lucide="check"></i></span></label><div class="resource-type green"><i data-lucide="clipboard-check"></i></div><div class="resource-copy"><small>WORKSHEET · DEMO</small><h3>Module 01 — Practice Worksheet</h3><p>Short exercises to check your understanding.</p></div><button class="resource-open demo-resource-action" data-message="Demo practice worksheet opened."><i data-lucide="eye"></i>Open</button></article></div></section>
  </section>

  <section class="portal-page page content hidden-page cbt-page" id="page-cbt"><div class="page-title"><span class="eyebrow">COMPUTER BASED TEST</span><h1>Practise. Perform. Progress.</h1><p>Attempt timed mock tests, improve accuracy and prepare confidently for your exams.</p></div><div class="cbt-summary"><div><i data-lucide="clipboard-check"></i><span><b>08</b><small>Tests attempted</small></span></div><div><i data-lucide="star"></i><span><b>76%</b><small>Average score</small></span></div><div><i data-lucide="clock-3"></i><span><b>03</b><small>Available tests</small></span></div></div><div class="section-head cbt-head"><div><span class="eyebrow">AVAILABLE NOW</span><h2>Choose your test</h2></div></div><div class="cbt-grid"><?php $tests=[['General Aptitude Mock Test','Competitive Exam',50,60,100,'Available'],['Computer Fundamentals Test','Computer & IT',30,30,60,'Available'],['English Practice Test','Language Skills',25,25,50,'Upcoming']]; foreach($tests as $i=>$test): ?><article class="test-card"><div class="test-icon t<?= $i ?>"><i data-lucide="clipboard-check"></i></div><span class="test-status <?= strtolower($test[5]) ?>"><?= h($test[5]) ?></span><small><?= h($test[1]) ?></small><h3><?= h($test[0]) ?></h3><div class="test-meta"><span><?= $test[2] ?><small>Questions</small></span><span><?= $test[3] ?> min<small>Duration</small></span><span><?= $test[4] ?><small>Marks</small></span></div><button class="<?= $test[5]==='Available'?'primary':'secondary' ?>" <?= $test[5]!=='Available'?'disabled':'' ?>><?= $test[5]==='Available'?'Start Test':'Coming Soon' ?> <i data-lucide="arrow-right"></i></button></article><?php endforeach; ?></div></section>

  <footer class="site-footer"><div class="footer-main"><div class="footer-brand"><img src="assets/images/brand/liberty-class-career-logo.jpg" alt="Liberty Class and Career"><div class="powered-by-brand"><span>Powered by</span><img src="assets/images/brand/liberty-foundation-powered-by.jpg" alt="Liberty Foundation — Since 2008"></div><p>Learn today. Lead tomorrow. Skill training, admission guidance and online learning—all in one place.</p></div><div class="footer-links"><h3>Quick Links</h3><button data-page="home">Home</button><button data-page="skill-training">Skill Training</button><button data-page="admission">Admission</button><button data-page="online-degree">Online Degree</button></div><div class="footer-links"><h3>Student Support</h3><button data-page="my-courses">Your Learning</button><a class="external-cbt" href="/cbt" target="_blank" rel="noopener noreferrer">CBT</a><button class="open-contact">Contact Us</button><span>libertyfoundation4news@gmail.com</span></div><div class="footer-app" id="download-app"><div><span>GET THE APP</span><h3>Liberty Class and Career</h3><p>Scan the QR code or use the button to download the student app.</p></div><div class="app-download-row"><img src="assets/images/brand/liberty-app-qr.jpg" alt="App QR"><a href="https://liberty-class-career-ui.libertyfoundation4ne.chatgpt.site/?download=app"><i data-lucide="download"></i><span><small>DOWNLOAD THE</small>Liberty App</span></a></div></div></div><div class="footer-bottom"><span>© 2026 Liberty Class and Career. All rights reserved.</span><span>Since 2008 · Your Door to Future Success</span></div></footer>

  <?php if ($studentUser): ?><div class="overlay app-modal profile-settings-overlay" id="profileSettingsModal" hidden><div class="profile-settings-modal"><button class="close close-modal" type="button" aria-label="Close profile settings"><i data-lucide="x"></i></button><img class="profile-settings-avatar" src="<?= h($studentAvatar) ?>" alt="<?= h((string) $studentUser['name']) ?>" data-profile-settings-preview><span class="eyebrow">ACCOUNT SETTINGS</span><h2>Profile image</h2><p>Upload a JPG or PNG image. Maximum size 10 MB.</p><form action="/cbt/profile-photo" method="post" enctype="multipart/form-data" data-home-profile-form><input type="hidden" name="_csrf" value="<?= h($_SESSION['_csrf']) ?>"><input type="hidden" name="return" value="/"><label class="profile-settings-picker"><span>Choose profile image</span><input type="file" name="photo" accept=".jpeg,.jpg,.png,image/jpeg,image/png" required data-home-profile-input></label><div class="profile-settings-actions"><button class="secondary" type="submit" name="remove_photo" value="1" formnovalidate>Remove image</button><button class="primary" type="submit">Upload image</button></div></form></div></div><?php endif; ?>

  <?php if (!$studentUser): ?><div class="overlay app-modal home-login-overlay" id="homeLoginModal" hidden><div class="home-login-modal"><button class="close close-modal" type="button" aria-label="Close login"><i data-lucide="x"></i></button><img src="/assets/images/brand/default-student-avatar.webp" alt=""><span class="eyebrow">STUDENT ACCOUNT</span><h2>Welcome back</h2><p>Login to access your learning and CBT account.</p><?php if ($homeLoginError): ?><p class="home-login-error" role="alert"><?= h((string) $homeLoginError) ?></p><?php endif; ?><form action="/cbt/login" method="post"><input type="hidden" name="_csrf" value="<?= h($_SESSION['_csrf']) ?>"><input type="hidden" name="return" value="/"><input type="hidden" name="login_context" value="home"><label>Email or phone<input name="identity" required autocomplete="username" value="<?= h((string) ($_SESSION['_old']['identity'] ?? '')) ?>" placeholder="Enter email or phone"></label><label>Password<input name="password" type="password" required autocomplete="current-password" placeholder="Enter password"></label><label class="home-login-remember"><input type="checkbox" name="remember" value="1"><span>Remember me</span></label><button class="primary full" type="submit">Login</button></form></div></div><?php unset($_SESSION['_old']['identity']); endif; ?>

  <div class="overlay app-modal" id="courseModal" hidden><div class="modal"><button class="close close-modal"><i data-lucide="x"></i></button><div class="modal-art blue" id="courseArt"><img id="courseModalThumbnail" src="assets/images/courses/ai-machine-learning.svg" alt=""><small>6 month programme</small></div><div class="modal-body"><span class="eyebrow" id="courseCategory"></span><h2 id="courseTitle"></h2><p>Practical lessons, real projects and guided support designed to make you job-ready.</p><h4>Choose your access</h4><div class="plans" id="plans"><button data-access-type="video" data-plan="Videos only" data-amount="₹399"><i></i><span>Videos only</span><b>₹399</b></button><button data-access-type="material" data-plan="Materials only" data-amount="₹299"><i></i><span>Materials only</span><b>₹299</b></button><button class="selected" data-access-type="full_bundle" data-plan="Live Class (Full Bundle)" data-amount="₹1,200"><i><i data-lucide="check"></i></i><span>Live Class (Full Bundle)<small>MOST POPULAR</small></span><b>₹1,200</b></button></div><p class="form-error" id="enrollError" hidden></p><button class="primary full" id="enrollButton">Enroll now · ₹1,200 <i data-lucide="arrow-right"></i></button><a class="secondary full" id="courseSyllabus" target="_blank" rel="noopener" hidden>View / Download Syllabus <i data-lucide="download"></i></a></div></div></div>
  <div class="overlay app-modal call-modal-overlay" id="skillLeadModal" hidden><div class="call-back-modal"><button class="close close-modal"><i data-lucide="x"></i></button><div class="form-state"><span class="eyebrow">SKILL TRAINING</span><h2>Complete your interest</h2><p class="call-context"><b id="skillLeadCourse"></b><span id="skillLeadPlan"></span></p><form id="skillLeadForm" class="call-request-form"><label><span>Student Name <b>*</b></span><input name="studentName" required maxlength="120" value="<?=h((string)($studentUser['name']??''))?>" placeholder="Enter student name"></label><label><span>Phone Number <b>*</b></span><input name="phone" required inputmode="tel" maxlength="20" value="<?=h($studentPhone)?>" placeholder="Enter phone number"></label><p class="form-error" hidden></p><button class="primary">Continue to WhatsApp <i data-lucide="arrow-right"></i></button></form></div><div class="success-state" role="status" aria-live="polite" hidden><div class="call-success"><i data-lucide="check"></i><div><b>Interest saved successfully!</b><span>Opening WhatsApp with your details…</span></div></div></div></div></div>

  <div class="overlay app-modal degree-detail-overlay" id="degreeModal" hidden><div class="degree-detail-modal"><button class="close close-modal"><i data-lucide="x"></i></button><div class="degree-modal-icon"><i data-lucide="graduation-cap"></i></div><span class="eyebrow">ONLINE DEGREE PROGRAMME</span><h2 id="degreeTitle"></h2><p class="degree-university-name" id="degreeUniversity"></p><div class="degree-written-details" id="degreeDetails"></div><div class="syllabus-box" id="syllabusBox" hidden><b>Programme Syllabus</b><p id="degreeSyllabus"></p></div><div class="degree-modal-actions"><button class="secondary" id="syllabusButton">View Syllabus</button><button class="primary open-contact">Apply Now <i data-lucide="arrow-right"></i></button></div></div></div>

  <div class="overlay app-modal institute-overlay" id="instituteModal" hidden><div class="institute-modal"><button class="close close-modal"><i data-lucide="x"></i></button><span class="eyebrow">ASSOCIATE UNIVERSITY</span><h2 id="instituteTitle"></h2><div class="institute-modal-image"><img id="instituteImage" alt="Institute campus" referrerpolicy="no-referrer"></div><h3>All Courses Offered</h3><div class="offered-courses" id="offeredCourses"></div><div class="admission-note"><b>Need the best affordable course fee?</b><p>To know the best affordable course fee or any available concession or discount, please click the button below now.</p></div><button class="primary get-call-button" id="getCallButton">Get a Call <i data-lucide="arrow-right"></i></button></div></div>

  <div class="overlay app-modal call-modal-overlay" id="callModal" hidden><div class="call-back-modal"><button class="close close-modal"><i data-lucide="x"></i></button><div class="form-state"><span class="eyebrow">ADMISSION SUPPORT</span><h2>Request a Call Back</h2><p class="call-context"><b id="callInstitute"></b><span id="callCourses"></span></p><form id="callForm" class="call-request-form"><label>Student Name <b>*</b><input name="studentName" required placeholder="Enter student name"></label><label>Phone Number <b>*</b><input name="phone" required inputmode="tel" placeholder="Enter phone number"></label><p class="form-error" hidden></p><button class="primary">Submit Request <i data-lucide="arrow-right"></i></button></form></div><div class="success-state" hidden><div class="call-success"><i data-lucide="check"></i><div><b>Call request submitted</b><span>Our admission team will contact you shortly.</span></div></div></div></div></div>

  <div class="overlay app-modal contact-overlay" id="contactModal" hidden><div class="contact-modal"><button class="close close-modal"><i data-lucide="x"></i></button><div class="form-state"><div class="contact-title"><span class="eyebrow">GET IN TOUCH</span><h2>How can we help you?</h2><p>Complete the form and our student counsellor will contact you.</p></div><form class="contact-form" id="contactForm"><div class="two"><label>Student Name <b>*</b><input name="studentName" required placeholder="Enter student name"></label><label>Mobile Number <b>*</b><input name="phone" required inputmode="tel" placeholder="Enter mobile number"></label></div><label>Email ID <b>*</b><input name="email" required type="email" placeholder="Enter email address"></label><label>Address<input name="address" placeholder="Enter address"></label><div class="two"><label>Class / Diploma / Degree<input name="qualification" placeholder="If any"></label><label>College / University / Institute<input name="institute" placeholder="If any"></label></div><label>Interested In <b>*</b><select name="interestedIn" required><option value="">Select an option</option><option>Skills Training</option><option>College and Career</option><option>Online Learning</option></select></label><label>Tell us more<textarea name="message" placeholder="Tell us how we can help..."></textarea></label><p class="form-error" hidden></p><button class="primary full">Submit Details <i data-lucide="arrow-right"></i></button></form></div><div class="contact-success success-state" hidden><i data-lucide="check"></i><h2>Thank you!</h2><p>Your details have been submitted successfully. Our team will contact you shortly.</p><button class="primary close-modal">Done</button></div></div></div>
</main>
<script src="https://unpkg.com/lucide@0.468.0/dist/umd/lucide.min.js"></script>
<script src="assets/js/app.js?v=<?= h($portalJsVersion) ?>"></script>
</body>
</html>
