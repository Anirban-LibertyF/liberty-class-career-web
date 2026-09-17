(() => {
  'use strict';
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const $ = (selector, root = document) => root.querySelector(selector);
  const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];
  const iconRefresh = () => window.lucide?.createIcons();

  function toast(message, error = false) {
    const item = document.createElement('div');
    item.className = `toast${error ? ' error' : ''}`;
    item.textContent = message;
    document.body.appendChild(item);
    window.setTimeout(() => item.remove(), 3200);
  }

  function showPage(name) {
    $$('.portal-page').forEach(page => page.classList.add('hidden-page'));
    const page = $(`#page-${name}`) || $('#page-home');
    page.classList.remove('hidden-page');
    $$('[data-page]').forEach(button => button.classList.toggle('active', button.dataset.page === name));
    $('#mainNav')?.classList.remove('open');
    $('#profileDropdown')?.classList.remove('open');
    $('#profileTrigger')?.setAttribute('aria-expanded', 'false');
    $('#profileTrigger [data-lucide="chevron-down"]')?.classList.remove('rotated');
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  $$('[data-page]').forEach(button => button.addEventListener('click', event => {
    event.preventDefault();
    showPage(button.dataset.page);
  }));
  $('#menuButton')?.addEventListener('click', () => $('#mainNav')?.classList.toggle('open'));
  $('#profileTrigger')?.addEventListener('click', event => {
    event.stopPropagation();
    const isOpen = $('#profileDropdown')?.classList.toggle('open') || false;
    $('#profileTrigger')?.setAttribute('aria-expanded', String(isOpen));
    $('#profileTrigger [data-lucide="chevron-down"]')?.classList.toggle('rotated', isOpen);
  });
  document.addEventListener('click', event => {
    if (!event.target.closest('.profile-menu-wrap')) {
      $('#profileDropdown')?.classList.remove('open');
      $('#profileTrigger')?.setAttribute('aria-expanded', 'false');
      $('#profileTrigger [data-lucide="chevron-down"]')?.classList.remove('rotated');
    }
  });
  $('#logoutButton')?.addEventListener('click', () => { showPage('home'); toast('Demo student session logged out.'); });

  const slides = $$('.hero-slide');
  const dots = $$('.hero-dots button');
  let slideIndex = 0;
  function setSlide(index) {
    slideIndex = (index + slides.length) % slides.length;
    slides.forEach((slide, i) => {
      slide.classList.toggle('active', i === slideIndex);
      slide.setAttribute('aria-hidden', i === slideIndex ? 'false' : 'true');
    });
    dots.forEach((dot, i) => dot.classList.toggle('active', i === slideIndex));
  }
  dots.forEach((dot, i) => dot.addEventListener('click', () => setSlide(i)));
  slides.forEach(slide => {
    if (slide.dataset.target) slide.addEventListener('click', () => showPage(slide.dataset.target));
  });
  if (slides.length) window.setInterval(() => setSlide(slideIndex + 1), 5200);

  const homeCourseGrid = $('#homeCourseGrid');
  const homeCourseCards = homeCourseGrid ? $$('.skill-slide-card', homeCourseGrid) : [];
  let courseGroup = 0;
  function showCourseGroup() {
    const groups = Math.ceil(homeCourseCards.length / 3);
    homeCourseCards.forEach((card, i) => card.classList.toggle('slide-hidden', Math.floor(i / 3) !== courseGroup));
    homeCourseGrid?.classList.remove('animated-course-group');
    void homeCourseGrid?.offsetWidth;
    homeCourseGrid?.classList.add('animated-course-group');
    courseGroup = (courseGroup + 1) % groups;
  }
  if (homeCourseCards.length) { showCourseGroup(); window.setInterval(showCourseGroup, 3000); }

  const instituteGrid = $('#homeInstituteGrid');
  if (instituteGrid) window.setInterval(() => {
    const first = instituteGrid.firstElementChild;
    if (first) instituteGrid.appendChild(first);
    instituteGrid.classList.remove('animated-institute-grid');
    void instituteGrid.offsetWidth;
    instituteGrid.classList.add('animated-institute-grid');
  }, 3000);

  function openModal(modal) {
    if (!modal) return;
    modal.hidden = false;
    modal.scrollTop = 0;
    modal.classList.add('is-open');
    document.body.classList.add('modal-open');
    iconRefresh();
  }
  function closeModal(modal) {
    if (!modal) return;
    modal.classList.remove('is-open');
    modal.hidden = true;
    if (!$('.app-modal.is-open')) document.body.classList.remove('modal-open');
  }
  $$('.close-modal').forEach(button => button.addEventListener('click', () => closeModal(button.closest('.app-modal'))));
  $$('.app-modal').forEach(modal => modal.addEventListener('mousedown', event => { if (event.target === modal) closeModal(modal); }));
  document.addEventListener('keydown', event => { if (event.key === 'Escape') $$('.app-modal.is-open').forEach(closeModal); });

  let currentCourse = null;
  let selectedPlan = { name: 'Live Class (Full Bundle)', amount: '₹5,500' };
  $$('.open-course').forEach(button => button.addEventListener('click', () => {
    currentCourse = JSON.parse(button.closest('[data-course]').dataset.course);
    $('#courseTitle').textContent = currentCourse.title;
    $('#courseCategory').textContent = currentCourse.cat;
    $('#courseIcon').innerHTML = currentCourse.icon;
    $('#courseArt').className = `modal-art ${currentCourse.color}`;
    $('#enrollButton').disabled = false;
    $('#enrollButton').innerHTML = `Enroll now · ${selectedPlan.amount} <i data-lucide="arrow-right"></i>`;
    $('#enrollError').hidden = true;
    openModal($('#courseModal'));
  }));
  $$('#plans button').forEach(button => button.addEventListener('click', () => {
    $$('#plans button').forEach(item => item.classList.remove('selected'));
    button.classList.add('selected');
    selectedPlan = { name: button.dataset.plan, amount: button.dataset.amount };
    $('#enrollButton').innerHTML = `Enroll now · ${selectedPlan.amount} <i data-lucide="arrow-right"></i>`;
    iconRefresh();
  }));
  $('#enrollButton')?.addEventListener('click', async () => {
    const button = $('#enrollButton');
    button.disabled = true; button.textContent = 'Submitting...';
    try {
      const response = await api('api/enrollments.php', { studentName: 'Prosenjit Roy', courseName: currentCourse.title, accessPlan: selectedPlan.name, amount: selectedPlan.amount });
      button.textContent = response.message;
      toast(response.message);
    } catch (error) {
      button.disabled = false;
      button.innerHTML = `Enroll now · ${selectedPlan.amount} <i data-lucide="arrow-right"></i>`;
      $('#enrollError').textContent = error.message; $('#enrollError').hidden = false; iconRefresh();
    }
  });

  let currentDegree = null;
  $$('.open-degree').forEach(button => button.addEventListener('click', () => {
    currentDegree = JSON.parse(button.closest('[data-degree]').dataset.degree);
    $('#degreeTitle').textContent = currentDegree.name;
    $('#degreeUniversity').textContent = currentDegree.university;
    $('#degreeDetails').innerHTML = `<p><b>University / College:</b> ${escapeHtml(currentDegree.university)}</p><p><b>Programme Duration:</b> ${escapeHtml(currentDegree.duration)}</p><p><b>Total Programme Fee:</b> ${escapeHtml(currentDegree.fee)}</p><p><b>Eligibility:</b> ${escapeHtml(currentDegree.eligibility)}</p><p><b>Learning Mode:</b> Online learning with digital study support</p>`;
    $('#degreeSyllabus').textContent = currentDegree.syllabus;
    $('#syllabusBox').hidden = true; $('#syllabusButton').textContent = 'View Syllabus';
    openModal($('#degreeModal'));
  }));
  $('#syllabusButton')?.addEventListener('click', () => {
    const box = $('#syllabusBox'); box.hidden = !box.hidden;
    $('#syllabusButton').textContent = box.hidden ? 'View Syllabus' : 'Hide Syllabus';
  });

  let currentInstitute = null;
  let selectedCourses = [];
  $$('.open-institute').forEach(button => button.addEventListener('click', () => {
    currentInstitute = JSON.parse(button.closest('[data-institute]').dataset.institute);
    selectedCourses = currentInstitute.courses.slice(0, 1);
    $('#instituteTitle').textContent = currentInstitute.name;
    $('#instituteImage').src = currentInstitute.image;
    $('#offeredCourses').innerHTML = currentInstitute.courses.map(course => `<button type="button" aria-pressed="${selectedCourses.includes(course)}" class="${selectedCourses.includes(course) ? 'selected' : ''}" data-course-name="${escapeHtml(course)}"><span>${escapeHtml(course)}</span><i data-lucide="check"></i></button>`).join('');
    $$('#offeredCourses button').forEach(item => item.addEventListener('click', () => {
      const name = item.dataset.courseName;
      selectedCourses = selectedCourses.includes(name) ? selectedCourses.filter(value => value !== name) : [...selectedCourses, name];
      item.classList.toggle('selected');
      item.setAttribute('aria-pressed', String(item.classList.contains('selected')));
    }));
    iconRefresh();
    openModal($('#instituteModal'));
  }));
  $('#getCallButton')?.addEventListener('click', () => {
    $('#callInstitute').textContent = currentInstitute.name;
    $('#callCourses').textContent = selectedCourses.length ? selectedCourses.join(' · ') : 'Course guidance';
    closeModal($('#instituteModal'));
    resetFormModal($('#callModal'));
    openModal($('#callModal'));
  });

  $$('.open-contact').forEach(button => button.addEventListener('click', () => {
    $$('.app-modal.is-open').forEach(closeModal);
    resetFormModal($('#contactModal'));
    openModal($('#contactModal'));
  }));
  $('#contactForm')?.addEventListener('submit', event => submitForm(event, 'api/contact-inquiries.php', $('#contactModal')));
  $('#callForm')?.addEventListener('submit', event => submitForm(event, 'api/contact-inquiries.php', $('#callModal'), {
    email: '', interestedIn: 'College and Career', institute: currentInstitute?.name || '', message: `Call request for ${selectedCourses.join(', ') || 'Course guidance'}`
  }));

  async function submitForm(event, url, modal, extras = {}) {
    event.preventDefault();
    const form = event.currentTarget;
    const error = $('.form-error', form); const button = $('button[type="submit"], button.primary', form);
    error.hidden = true; button.disabled = true; const original = button.innerHTML; button.textContent = 'Submitting...';
    try {
      const payload = { ...Object.fromEntries(new FormData(form).entries()), ...extras };
      const response = await api(url, payload);
      $('.form-state', modal).hidden = true; $('.success-state', modal).hidden = false;
      toast(response.message); iconRefresh();
    } catch (failure) {
      error.textContent = failure.message; error.hidden = false;
    } finally { button.disabled = false; button.innerHTML = original; iconRefresh(); }
  }
  function resetFormModal(modal) {
    $('.form-state', modal).hidden = false; $('.success-state', modal).hidden = true;
    $('.form-error', modal)?.setAttribute('hidden', '');
  }
  async function api(url, payload) {
    const response = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf }, body: JSON.stringify(payload) });
    const data = await response.json().catch(() => ({ message: 'Invalid server response.' }));
    if (!response.ok || !data.success) throw new Error(data.message || 'Request failed.');
    return data;
  }
  function escapeHtml(value) {
    return String(value).replace(/[&<>'"]/g, char => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', "'":'&#39;', '"':'&quot;' }[char]));
  }

  const cbtTrending = $('[data-cbt-trending]');
  if (cbtTrending) {
    const grid = $('[data-cbt-grid]', cbtTrending);
    const unavailable = $('[data-cbt-unavailable]', cbtTrending);
    const base = cbtTrending.dataset.base.replace(/\/$/, '');
    const money = value => Number(value) > 0 ? `₹${Number(value).toLocaleString('en-IN', { maximumFractionDigits: 2 })}` : 'FREE';
    const shortDate = value => new Intl.DateTimeFormat('en-IN', { day:'2-digit', month:'short', hour:'numeric', minute:'2-digit' }).format(new Date(String(value).replace(' ', 'T')));
    fetch(cbtTrending.dataset.endpoint, { headers: { Accept: 'application/json' } }).then(response => {
      if (!response.ok) throw new Error('CBT catalogue unavailable');
      return response.json();
    }).then(data => {
      const tests = Array.isArray(data.tests) ? data.tests : [];
      if (!tests.length) throw new Error('No trending CBT tests');
      grid.innerHTML = tests.map(test => `<article class="home-cbt-card"><a class="home-cbt-image" href="${base}${escapeHtml(test.url)}">${test.thumbnail ? `<img src="${base}${escapeHtml(test.thumbnail)}" alt="${escapeHtml(test.title)} thumbnail">` : '<span><i data-lucide="clipboard-check"></i></span>'}<em>${escapeHtml(test.subject)}</em></a><div class="home-cbt-body"><small>${escapeHtml(test.exam)} · ${escapeHtml(test.category)}</small><h3>${escapeHtml(test.title)}</h3><div class="home-cbt-meta"><span><b>${Number(test.questions)}</b> Questions</span><span><b>${Number(test.duration)}</b> Minutes</span><span><b>${Number(test.marks)}</b> Marks</span></div><p>Available until <b>${escapeHtml(shortDate(test.ends_at))}</b></p><div class="home-cbt-foot"><strong>${money(test.fee)}</strong><a href="${base}${escapeHtml(test.url)}">View &amp; Enroll <i data-lucide="arrow-right"></i></a></div></div></article>`).join('');
      iconRefresh();
    }).catch(() => {
      grid.hidden = true;
      unavailable.hidden = false;
    });
  }

  const courseGrid = $('#allCourses');
  const courseSearch = $('#courseSearch');
  const courseCategory = $('#courseCategoryFilter');
  const courseSort = $('#courseSort');
  const readCourse = card => {
    try { return JSON.parse(card.dataset.course); }
    catch { return {}; }
  };
  function refreshCourseResults() {
    if (!courseGrid) return;
    const query = (courseSearch?.value || '').trim().toLowerCase();
    const category = courseCategory?.value || 'all';
    const sort = courseSort?.value || 'popular';
    const cards = $$('.course', courseGrid);
    cards.forEach(card => {
      const course = readCourse(card);
      const matchesQuery = !query || [course.title, course.cat].some(value => String(value || '').toLowerCase().includes(query));
      const matchesCategory = category === 'all' || course.cat === category;
      card.hidden = !(matchesQuery && matchesCategory);
    });
    const compare = {
      popular: (a, b) => Number(readCourse(b).students || 0) - Number(readCourse(a).students || 0),
      rating: (a, b) => Number(readCourse(b).rating || 0) - Number(readCourse(a).rating || 0) || Number(readCourse(b).students || 0) - Number(readCourse(a).students || 0),
      'title-asc': (a, b) => String(readCourse(a).title || '').localeCompare(String(readCourse(b).title || '')),
      'title-desc': (a, b) => String(readCourse(b).title || '').localeCompare(String(readCourse(a).title || ''))
    }[sort];
    [...cards].sort(compare).forEach(card => courseGrid.appendChild(card));
  }
  courseSearch?.addEventListener('input', refreshCourseResults);
  courseCategory?.addEventListener('change', refreshCourseResults);
  courseSort?.addEventListener('change', refreshCourseResults);
  const learningChecks = $$('.resource-check');
  function updateLearningProgress() {
    const completed = learningChecks.filter(check => check.checked).length;
    const total = learningChecks.length;
    if ($('#learningProgressText')) $('#learningProgressText').textContent = `${completed} of ${total} completed`;
    if ($('#learningProgressBar')) $('#learningProgressBar').style.width = `${total ? (completed / total) * 100 : 0}%`;
  }
  $$('.open-learning').forEach(button => button.addEventListener('click', () => {
    if ($('#learningCourseTitle')) $('#learningCourseTitle').textContent = button.dataset.learningTitle || 'Your Course';
    if ($('#learningCourseCategory')) $('#learningCourseCategory').textContent = button.dataset.learningCategory || 'COURSE LEARNING';
    showPage('course-learning');
    if (button.dataset.learningTarget === 'materials') {
      window.setTimeout(() => $('#learningMaterials')?.scrollIntoView({ behavior: 'smooth', block: 'start' }), 180);
    }
  }));
  learningChecks.forEach(check => check.addEventListener('change', updateLearningProgress));
  $$('.demo-resource-action').forEach(button => button.addEventListener('click', () => toast(button.dataset.message || 'Demo resource opened.')));
  updateLearningProgress();

  refreshCourseResults();
  iconRefresh();
  setSlide(0);
})();
