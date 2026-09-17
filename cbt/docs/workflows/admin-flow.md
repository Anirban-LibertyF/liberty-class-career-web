# Admin Flow

## Exam and Subject entry
Create Test exposes native HTML datalist editable comboboxes: typing filters active saved suggestions, while any valid new typed name is accepted and saved through the normal test creation flow.
1. Shared login resolves the admin role.
2. Dashboard loads real statistics and authorized private media. Admin profile, test thumbnail, question image and option-image fields provide adjacent View/Remove controls.
3. Create a validated draft with placeholder-only numeric fields; End must be at least Start plus Duration and failures return as in-form alerts.
4. Save ordered questions and private question/option images using adjacent View/Remove controls; options may use text, image or both. Single answer exposes one-select radios while Multiple answer exposes multi-select checkboxes, matching server validation. Preview renders every position once, saved serials turn green immediately after Save & Next, Previous loads the preceding saved question, and unsaved per-question text/selections persist in browser local storage during editing.
5. Preview renders compact media and every question once; Publish stays disabled until Preview is opened after the latest builder edit, then server validation still permits only a complete valid test.
6. Manage Draft/Published tests with edit, labelled Result and safe delete actions; Archive is not exposed in the admin UI.

Every write requires admin session, CSRF and server validation. Tests and questions receive hidden collision-checked eight-character database keys.
Validation failures return to the form and open a top alert popup with an explicit OK acknowledgement.

- All Tests omits thumbnail media controls. Delete requires browser confirmation and soft-deletes regardless of existing attempts; historical attempt/result/payment records are retained.

- Dashboard totals, live counts and recent activity represent non-deleted tests only; soft-deleted test history remains in storage but no longer contributes to current dashboard data.
