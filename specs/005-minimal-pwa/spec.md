# Minimal PWA Enhancement

**Feature ID**: 005-minimal-pwa  
**Priority**: High  
**Status**: Specification  
**Created**: September 20, 2025

## Problem
Students need offline access to lessons during commutes and unreliable internet connections.

## Solution
Add essential PWA features: service worker for offline caching, web manifest for installation, and basic offline lesson access.

## User Stories
- **As a student**, I want to access downloaded lessons when offline
- **As a student**, I want to install the app on my phone's home screen
- **As a student**, I want the app to load quickly on slow connections

## Requirements

### Core PWA Features
1. **Service Worker**: Cache lessons and core app files
2. **Web Manifest**: Enable home screen installation
3. **Offline Fallback**: Show cached lessons when offline

### Technical Specs
- Cache 10 most recent lessons automatically
- Offline indicator when no connection
- Install prompt after 2 visits
- < 3s load time on 3G

## Success Metrics
- 90+ Lighthouse PWA score
- 20% of mobile users install PWA
- 50% faster repeat visits

## Implementation
1. Create service worker with lesson caching
2. Add web manifest with app icons
3. Implement offline detection UI
4. Add install prompt

**Scope**: Essential PWA functionality only - no push notifications or complex sync.
