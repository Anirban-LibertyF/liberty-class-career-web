# UI Guide

## Tokens
```css
--brand-red:#C9342E; --brand-orange:#F26A3D; --brand-yellow:#D9A900;
--off-white:#FFF9EF; --text:#191919; --success:#2E8B57; --review:#356FA3;
```
- Use `public/assets/images/logo.png`; simple/elegant radius, spacing, shadow and restrained motion.
- Prefer off-white; questions/answers black. Visible focus and contrast mandatory.

## Global
- Admin topbar remains fixed at the top and the mobile sidebar becomes bottom navigation; desktop sidebar must not squeeze content; mobile stacks/collapses safely.
- Modal: centered, viewport max-height, internal scroll, top-right close, reachable actions/focus.
- Icon-label spacing 8-12 px; hover is enhancement, never the only cue.

## Login
- No role toggle/register. Large one-line red `LIBERTY CLASS AND CAREER (CBT)` brand name when possible with subtle orange glow.
- The supplied Liberty Foundation since-2008 image is anchored inside the red panel at its upper-left corner; keep it proportionate, visible and non-distorted, separate from the centered main logo.
- Password field uses one modern accessible outline SVG eye icon and suppresses browser-native duplicate reveal controls; versioned asset URLs prevent stale login CSS/JavaScript after deployment; the small checkbox has a clear 10 px text gap and the functional Remember Me row has a 30 px gap before the login button; rating uses visible star icons.

## Admin
- Create Test exam and subject use native HTML datalist editable comboboxes. Typing filters browser-native saved suggestions and a new typed value is added through the normal Save flow; category remains a dropdown.
- Authenticated inner pages show a consistent top-left Back control with dashboard fallback.
- Create Test begins with a drag/drop JPG/JPEG/PNG thumbnail field, browser preview/compression and a 1 MB limit; stored output is WebP.
- Prosenjit Roy profile; no dot; click -> <=1 MB JPEG/JPG/PNG drag/drop.
- Dashboard follows the approved reference: `Admin Portal` topbar, greeting/Create action, three spacious colored stats with real trend subtitles, icon-led activity rows, grouped last-five modal and two prominent quick-action cards. It remains responsive without squeezing the fixed sidebar.
- Builder/options/actions spacing with existing-question prefill; preview all questions before a separate Publish action. Question media and each A-D optional image field place View and Remove icon buttons side by side. Each option visually separates text from image with `OR`, and option images render in Preview, Live Exam and Result. Full-width aligned paginated test table; Draft/Published status filtering, edit/delete controls and a labelled eye-icon Result action; Archive is not exposed. Organized results retain average score and Download top-right.
- Admin sidebar and profile-modal Log out actions use an accessible red treatment with smooth hover/focus feedback.
- Question Builder uses radio controls for Single answer correct-option selection and checkboxes only for Multiple answer; Publish test stays on one line.
- Create Test thumbnail uses a visible native Choose File input with separately clickable View/Remove icons; no full-zone invisible file-input overlay is used.
- All user-managed upload fields use adjacent outline View and Remove icons; stored test thumbnails expose the same actions in All Tests. Preview question/option images are compact 86 × 62 px thumbnails rather than full-width media. A/B/C/D correct-selection controls align on the same baseline as their letters. Publish is disabled until Preview is opened after the latest edit.
- Question Builder uses a contained 220 px desktop palette/action panel; its one-column Preview/Publish group ends just below the buttons without overflow and collapses to one column on smaller screens. Previous opens the prior serial, saved serials are green, and locally persisted drafts restore unsaved question text, type and answers.
- Admin Logout uses a faded golden background, red text and smooth hover/focus feedback.

## Student
- Navbar `LIBERTY CLASS AND CAREER` and `Computer Based Test` are strongly bold. Desktop student links are centered, the current page is bold, and hover/current states use a smoothly animated underline. The profile modal separates Upload and Log out actions. Dashboard hero contains a red-panel debounced search with separate exam/subject groups, current-week completion/average score with a golden `Your week` heading and real enrollment-ranked trending cards. Exam-wise and Subject-wise discovery are independent bordered sections with their own horizontal chips, counts, cards and empty state; category filters are omitted.
- Anirban Hazra profile; same upload; no dot/search navbar icon.
- Full-width low-height red hero, yellow READY TO PRACTISE?, accessible animated weekly stats.
- Trending/subject/catalogue share cards; search plus exam/category/subject filters; readable schedule and contextual Enroll/View/Result/disabled-upcoming action.
- Enrollment modal scrolls and uses separated fee/start/end/duration rows + deadline note. Its paid Continue action closes the native enrollment dialog and opens Razorpay Checkout directly in the browser top layer without navigating through an intermediate payment page; dismissing Checkout restores the enrollment dialog.
- My Tests uses Live & ready, Upcoming and Completed tabs. Live rows distinguish LIVE NOW from IN PROGRESS; resumable rows show saved-question progress, a monospaced remaining-time display and Resume Exam, while unstarted rows show Start Exam.

## Exam/result
- Instructions use a dedicated test-context header, metric cards, numbered rules, highlighted Special Note and separated Back/Start actions.
- Live Exam uses a dedicated horizontal brand/test/timer bar without the normal student navbar. Timer = stopwatch left; label over countdown right.
- Desktop: question left/palette right; mobile stack. Current question includes marks and autosave status; selected answers have a clear checked state; palette includes answered progress, four-state legend and contained Final Submit.
- Answered green, unanswered yellow, review blue/purple plus text/shape; rounded marks; serial visible.
- Result uses a completed-test greeting/PDF header, high-contrast score summary, clear active filters/legend and detailed per-question state, marks, selected answer and correct answer.

## Responsive acceptance
Check 360, 768 and >=1280 px: no page overflow/clipping, modal actions reachable, touch targets ~44 px, keyboard/focus and reduced-motion functional.
- Create Test numeric values are placeholders. Start precedes End, and the duration-derived minimum End is shown with an inline branded warning that blocks submission.
- Application failures use branded alert components/pages rather than raw status-number pages.
- Form success, validation and error messages open as a prominent top popup with an explicit OK button.
- Admin image input may be up to 10 MB; the stored WebP remains at most 1 MB.

- Instructions navbar branding matches the student dashboard: approved logo, `LIBERTY CLASS AND CAREER`, and `Computer Based Test`. Its summary displays Correct answer immediately before Negative marking.

- Live Exam places a proper `Mark for review` toggle button exactly between Previous and Save & Next; its active purple state follows the current question.

- Payment uses branded Razorpay Test Mode Standard Checkout. Result PDF mirrors the webpage summary and detailed answer analysis. Selected MCQ ticks stay at the far-right edge of each option row.

- Dashboard hero search has a right-aligned magnifying-glass submit button. Search, Exam and Subject controls open All Tests with the selected GET filter; Subject sends its catalogue ID. Typing still debounces the dashboard preview.

- The dashboard magnifier is a yellow, dark-red icon button fully inset inside the search input at every responsive width.

- Student test-card exam/category metadata uses a readable golden pill. The dashboard search magnifier is a compact 34 px yellow button centered inside the input. Admin All Tests shows no thumbnail View/Remove icons.
# Public CBT catalogue

- `/` and `/catalogue` use the approved CBT colors, cards and responsive typography.
- Anonymous visitors see available exams, subjects, schedules and fees plus a Student login action.
- The catalogue/filter layout must remain usable at 360 px, 768 px and 1280 px.
