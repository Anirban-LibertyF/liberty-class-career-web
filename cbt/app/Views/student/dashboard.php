<section class="page student-page" data-dashboard-explorer>
 <div class="student-hero dashboard-hero">
  <div class="hero-copy"><span>READY TO PRACTISE?</span><h1>Prepare smarter. Perform better.</h1><p>Search and filter secure mock tests created by your admin.</p><a class="btn yellow-btn explore-all-btn" href="/cbt/tests"><span>Explore all tests</span><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"></path></svg></a></div>
  <div class="week-summary"><b>Your week</b><div><article><strong><?=e((int)$week['completed'])?></strong><span>Tests completed</span></article><article><strong><?=e($week['avg_score'])?>%</strong><span>Average score</span></article></div></div>
  <form class="hero-search-panel" action="/cbt/tests" method="get" data-dashboard-search-form>
   <label class="dashboard-search"><span>Search tests</span><span class="dashboard-search-control"><input type="search" name="q" placeholder="Search by test title, exam or subject…" data-explore-search autocomplete="off"><button type="submit" title="Search tests" aria-label="Search tests" data-dashboard-search-submit><svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="11" cy="11" r="6.5"></circle><path d="m16 16 4 4"></path></svg></button></span></label>
   <div class="hero-filter-part"><b>Exams</b><label><span>Choose exam</span><select name="exam" data-dashboard-filter><option value="">All exams</option><?php foreach($exams as $exam):?><option value="<?=e($exam)?>"><?=e($exam)?></option><?php endforeach;?></select></label></div>
   <div class="hero-filter-part"><b>Subjects</b><label><span>Choose subject</span><select name="subject" data-dashboard-filter><option value="">All subjects</option><?php foreach($subjects as $id=>$subject):?><option value="<?=e($id)?>"><?=e($subject)?></option><?php endforeach;?></select></label></div>
  </form>
 </div>
 <div class="section-head dashboard-section-head"><div><span class="eyebrow">POPULAR THIS WEEK</span><h2>Trending mock tests</h2><p>Current week’s most-enrolled tests, updated from real enrollment data.</p></div><a class="view-all-tests" href="/cbt/tests">View all tests <span>→</span></a></div>
 <?php if(!$trending):?><div class="empty card">No trending tests are available yet.</div><?php else:?><div class="test-grid"><?php foreach($trending as $test)require __DIR__.'/test-card.php';?></div><?php endif;?>
 <section class="explore-tests explore-section" data-explore-section="exam">
  <div class="section-head dashboard-section-head"><div><span class="eyebrow">EXPLORE BY EXAM</span><h2>Exam-wise mock tests</h2><p>Choose an admin-saved exam to view its available tests.</p></div></div>
  <div class="filter-rail-block"><b>Exams</b><div class="filter-rail"><button class="active" type="button" data-explore-chip="exam" data-value="">All exams</button><?php foreach($exams as $exam):?><button type="button" data-explore-chip="exam" data-value="<?=e($exam)?>"><?=e($exam)?></button><?php endforeach;?></div></div>
  <p class="explore-count"><b data-explore-count><?=e(count($tests))?></b> tests found</p>
  <div class="test-grid explore-grid"><?php foreach($tests as $test)require __DIR__.'/test-card.php';?></div><div class="empty card" data-explore-empty hidden>No tests match this exam.</div>
 </section>
 <section class="explore-tests explore-section" data-explore-section="subject">
  <div class="section-head dashboard-section-head"><div><span class="eyebrow">EXPLORE BY SUBJECT</span><h2>Subject-wise mock tests</h2><p>Choose an admin-saved subject to practise topic-relevant tests.</p></div></div>
  <div class="filter-rail-block"><b>Subjects</b><div class="filter-rail"><button class="active" type="button" data-explore-chip="subject" data-value="">All subjects</button><?php foreach($subjects as $subject):?><button type="button" data-explore-chip="subject" data-value="<?=e($subject)?>"><?=e($subject)?></button><?php endforeach;?></div></div>
  <p class="explore-count"><b data-explore-count><?=e(count($tests))?></b> tests found</p>
  <div class="test-grid explore-grid"><?php foreach($tests as $test)require __DIR__.'/test-card.php';?></div><div class="empty card" data-explore-empty hidden>No tests match this subject.</div>
 </section>
</section>
