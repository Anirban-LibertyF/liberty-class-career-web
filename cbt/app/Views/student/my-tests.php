<section class="page student-page my-tests-page" data-my-tests data-server-now="<?=e($serverNow)?>">
 <span class="eyebrow">YOUR LIBRARY</span><h1>My Tests</h1><p>Everything you have enrolled in, all in one place.</p>
 <nav class="tabs my-test-tabs" aria-label="My test status">
  <a class="<?=$tab==='live'?'active':''?>" href="?tab=live"<?=$tab==='live'?' aria-current="page"':''?>>Live &amp; ready <b><?=e($counts['live'])?></b></a>
  <a class="<?=$tab==='upcoming'?'active':''?>" href="?tab=upcoming"<?=$tab==='upcoming'?' aria-current="page"':''?>>Upcoming <b><?=e($counts['upcoming'])?></b></a>
  <a class="<?=$tab==='completed'?'active':''?>" href="?tab=completed"<?=$tab==='completed'?' aria-current="page"':''?>>Completed <b><?=e($counts['completed'])?></b></a>
 </nav>
 <div class="my-test-list"><?php foreach($tests as $test)require __DIR__.'/my-test-card.php';?></div>
 <?php if(!$tests):?><div class="empty card">No tests in this section.</div><?php endif;?>
</section>
