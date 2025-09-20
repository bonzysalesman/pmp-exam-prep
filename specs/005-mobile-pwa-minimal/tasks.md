# Tasks: Minimal PWA Enhancement

**Feature**: 005-mobile-pwa-minimal  
**Goal**: Add essential PWA functionality for offline lesson access

## Phase 1: Service Worker Foundation
- [ ] T001 Create service worker file at `wp-content/themes/pmp-dashboard/pwa/service-worker.js`
- [ ] T002 Add Workbox CDN import for caching strategies
- [ ] T003 Implement lesson caching with cache-first strategy
- [ ] T004 Add offline fallback page caching
- [ ] T005 Register service worker in WordPress theme

## Phase 2: Web App Manifest
- [ ] T006 [P] Create manifest.json with app metadata
- [ ] T007 [P] Generate PWA icons (192px, 512px) in assets/icons/
- [ ] T008 Add manifest link to WordPress wp_head
- [ ] T009 Set theme colors and display mode

## Phase 3: WordPress Integration
- [ ] T010 Create PWA initialization class in `inc/pwa-init.php`
- [ ] T011 Enqueue service worker registration script
- [ ] T012 Add offline detection JavaScript
- [ ] T013 Include PWA files in theme functions.php

## Phase 4: Offline Experience
- [ ] T014 Create offline indicator UI component
- [ ] T015 Show cached lessons when offline
- [ ] T016 Add install prompt after 2 visits
- [ ] T017 Test offline functionality in DevTools

## Phase 5: Validation
- [ ] T018 Run Lighthouse PWA audit (target: 90+)
- [ ] T019 Test installation on iOS and Android
- [ ] T020 Verify offline lesson access works
- [ ] T021 Measure load time on 3G simulation

## Dependencies
- T001-T005 must complete before T010-T013
- T006-T009 can run parallel with T001-T005
- T014-T017 require T010-T013 complete
- T018-T021 are final validation

## Success Criteria
- ✅ Lighthouse PWA score > 90
- ✅ Lessons accessible when offline
- ✅ Home screen installation works
- ✅ Load time < 3s on 3G
**CRITICAL: These tests MUST be written and MUST FAIL before ANY implementation**
- [ ] T004 [P] Contract test POST /api/users in tests/contract/test_users_post.py
- [ ] T005 [P] Contract test GET /api/users/{id} in tests/contract/test_users_get.py
- [ ] T006 [P] Integration test user registration in tests/integration/test_registration.py
- [ ] T007 [P] Integration test auth flow in tests/integration/test_auth.py

## Phase 3.3: Core Implementation (ONLY after tests are failing)
- [ ] T008 [P] User model in src/models/user.py
- [ ] T009 [P] UserService CRUD in src/services/user_service.py
- [ ] T010 [P] CLI --create-user in src/cli/user_commands.py
- [ ] T011 POST /api/users endpoint
- [ ] T012 GET /api/users/{id} endpoint
- [ ] T013 Input validation
- [ ] T014 Error handling and logging

## Phase 3.4: Integration
- [ ] T015 Connect UserService to DB
- [ ] T016 Auth middleware
- [ ] T017 Request/response logging
- [ ] T018 CORS and security headers

## Phase 3.5: Polish
- [ ] T019 [P] Unit tests for validation in tests/unit/test_validation.py
- [ ] T020 Performance tests (<200ms)
- [ ] T021 [P] Update docs/api.md
- [ ] T022 Remove duplication
- [ ] T023 Run manual-testing.md

## Dependencies
- Tests (T004-T007) before implementation (T008-T014)
- T008 blocks T009, T015
- T016 blocks T018
- Implementation before polish (T019-T023)

## Parallel Example
```
# Launch T004-T007 together:
Task: "Contract test POST /api/users in tests/contract/test_users_post.py"
Task: "Contract test GET /api/users/{id} in tests/contract/test_users_get.py"
Task: "Integration test registration in tests/integration/test_registration.py"
Task: "Integration test auth in tests/integration/test_auth.py"
```

## Notes
- [P] tasks = different files, no dependencies
- Verify tests fail before implementing
- Commit after each task
- Avoid: vague tasks, same file conflicts

## Task Generation Rules
*Applied during main() execution*

1. **From Contracts**:
   - Each contract file → contract test task [P]
   - Each endpoint → implementation task
   
2. **From Data Model**:
   - Each entity → model creation task [P]
   - Relationships → service layer tasks
   
3. **From User Stories**:
   - Each story → integration test [P]
   - Quickstart scenarios → validation tasks

4. **Ordering**:
   - Setup → Tests → Models → Services → Endpoints → Polish
   - Dependencies block parallel execution

## Validation Checklist
*GATE: Checked by main() before returning*

- [ ] All contracts have corresponding tests
- [ ] All entities have model tasks
- [ ] All tests come before implementation
- [ ] Parallel tasks truly independent
- [ ] Each task specifies exact file path
- [ ] No task modifies same file as another [P] task