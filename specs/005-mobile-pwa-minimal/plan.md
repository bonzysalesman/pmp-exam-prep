# Implementation Plan: Minimal PWA

**Branch**: `005-mobile-pwa-minimal` | **Date**: September 20, 2025

## Summary
Add essential PWA functionality: service worker for offline lesson caching, web manifest for home screen installation, and offline detection UI.

## Technical Context
**Language/Version**: JavaScript ES2020+, PHP 8.1+  
**Primary Dependencies**: WordPress, Workbox (service worker library)  
**Storage**: Browser Cache API, localStorage  
**Testing**: Manual testing with DevTools  
**Target Platform**: Mobile browsers (iOS Safari, Chrome Android)  
**Project Type**: Web application (WordPress theme)  
**Performance Goals**: < 3s load on 3G, 90+ Lighthouse PWA score  
**Constraints**: < 5MB cache size, works offline  
**Scale/Scope**: 10 cached lessons, basic PWA features only

## Project Structure
```
wp-content/themes/pmp-dashboard/
├── pwa/
│   ├── service-worker.js     # Main service worker
│   ├── manifest.json         # Web app manifest
│   └── install-prompt.js     # Installation UI
├── assets/
│   └── icons/               # PWA icons (192px, 512px)
└── inc/
    └── pwa-init.php         # WordPress PWA integration
```

## Phase 1: Service Worker (Week 1)
1. Create service worker with Workbox
2. Implement lesson caching strategy
3. Add offline fallback pages
4. Register service worker in WordPress

## Phase 2: Web Manifest (Week 1)
1. Create manifest.json with app metadata
2. Generate PWA icons (192px, 512px)
3. Add manifest to WordPress head
4. Test installation flow

## Phase 3: Offline UI (Week 2)
1. Add offline detection
2. Create offline indicator
3. Show cached lessons when offline
4. Add install prompt

## Success Criteria
- Lighthouse PWA score > 90
- Lessons accessible offline
- Home screen installation works
- < 3s load time on 3G
