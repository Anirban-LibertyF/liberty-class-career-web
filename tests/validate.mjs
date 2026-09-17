import assert from 'node:assert/strict';
import { access, readFile } from 'node:fs/promises';
import test from 'node:test';

const root = new URL('../', import.meta.url);
const read = path => readFile(new URL(path, root), 'utf8');

test('uses the requested PHP MySQL stack only', async () => {
  const index = await read('index.php');
  assert.match(index, /<!doctype html>/i);
  assert.doesNotMatch(index, /cdn\.tailwindcss\.com/);
  assert.match(index, /assets\/js\/app\.js/);
  assert.doesNotMatch(index, /React|Next\.js|useState|\.tsx/);
});

test('locks reference typography, layout reset and animation timings', async () => {
  const css = await read('assets/css/style.css');
  const portalCss = await read('assets/css/php-portal.css');
  const js = await read('assets/js/app.js');
  assert.match(css, /tailwindcss v4\.2\.1/);
  assert.match(css, /font-family:Inter,Arial,sans-serif/);
  assert.match(css, /animation:22s linear infinite ringRotate/);
  assert.match(css, /animation:28s linear infinite ringRotateReverse/);
  assert.match(css, /transition:opacity \.7s,transform \.7s cubic-bezier/);
  assert.match(js, /5200/);
  assert.match(js, /3000/);
  assert.doesNotMatch(portalCss, /\.changing|fade-cycle/);
});

test('includes working skill category and sort controls', async () => {
  const index = await read('index.php');
  const js = await read('assets/js/app.js');
  for (const id of ['courseSearch','courseCategory','courseSort']) assert.ok(index.includes(`id="${id}"`));
  assert.match(js, /refreshCourseResults/);
  assert.match(js, /courseCategory.*addEventListener\('change'/s);
  assert.match(js, /courseSort.*addEventListener\('change'/s);
});

test('contains all student pages and modal flows', async () => {
  const index = await read('index.php');
  for (const id of ['page-home','page-skill-training','page-online-degree','page-admission','page-my-courses','page-cbt','courseModal','degreeModal','instituteModal','callModal','contactModal']) assert.match(index, new RegExp(`id="${id}"`));
  assert.doesNotMatch(index, /Admin Portal|AdminPortal/);
});

test('shows database-driven trending CBT cards at the bottom of the dashboard', async () => {
  const index = await read('index.php');
  const js = await read('assets/js/app.js');
  const css = await read('assets/css/php-portal.css');
  assert.match(index, /data-cbt-trending/);
  assert.match(index, /View All Tests/);
  assert.match(js, /api\/public\/trending|dataset\.endpoint/);
  assert.match(css, /\.home-cbt-grid/);
});

test('renders IIMT online programmes with complete fee breakdown and real campus media', async () => {
  const index = await read('index.php');
  for (const programme of ['Online BA','Online BA (JMC)','Online B.Com (Hons.)','Online MBA — General','Online MBA — Marketing','Online MBA — HRM','Online MBA — Banking & Finance']) assert.ok(index.includes(programme));
  for (const fee of ['₹27,000','₹33,000','₹36,000','₹54,000','₹2,000 one time']) assert.ok(index.includes(fee));
  assert.ok(index.includes('IIMTUniversity.jpeg'));
});

test('uses the five institutes and offered courses from the supplied workbook', async () => {
  const index = await read('index.php');
  for (const institute of ['IIF Business School','M.G. Institute of Management & Technology','B.R. Gautam Polytechnic','Islamia College of Commerce','Shobhit Institute of Engineering & Technology']) assert.ok(index.includes(institute));
  for (const course of ['MBA / MBA+++','B.Tech Civil Engineering','Diploma Civil Engineering','BCA — 3 Years','B.Tech Computer Science Engineering']) assert.ok(index.includes(course));
});

test('contains protected PHP APIs and complete MySQL schema', async () => {
  const bootstrap = await read('api/bootstrap.php');
  const contact = await read('api/contact-inquiries.php');
  const enrollment = await read('api/enrollments.php');
  const schema = await read('database/schema.sql');
  assert.match(bootstrap, /require_csrf/);
  assert.match(contact, /prepare\(/);
  assert.match(enrollment, /prepare\(/);
  for (const table of ['students','courses','online_degrees','institutes','institute_courses','contact_inquiries','course_enrollments','cbt_tests']) assert.match(schema, new RegExp(`CREATE TABLE IF NOT EXISTS ${table}`));
});

test('includes all supplied brand and admission assets', async () => {
  for (const path of ['assets/images/brand/liberty-class-career-logo.jpg','assets/images/brand/liberty-foundation-logo.jpg','assets/images/brand/liberty-foundation-powered-by.jpg','assets/images/brand/liberty-icon.png','assets/images/brand/liberty-app-qr.jpg','assets/images/courses/ai-machine-learning.svg','assets/images/courses/web-development.svg','assets/images/courses/share-trading.svg','assets/images/courses/digital-marketing.svg','assets/images/courses/content-creation.svg','assets/images/courses/app-development.svg','assets/images/admission/campus-1.webp','assets/images/admission/campus-2.webp']) await access(new URL(path, root));
});
