# PWA Installation Testing Guide

## T019: Test Installation on iOS/Android

### iOS Safari Testing
1. **Open Safari** on iPhone/iPad
2. **Navigate to** your PWA URL
3. **Tap Share button** (square with arrow)
4. **Select "Add to Home Screen"**
5. **Verify** app icon appears on home screen
6. **Tap icon** to launch in standalone mode
7. **Check** no browser UI visible (status bar only)

### Android Chrome Testing  
1. **Open Chrome** on Android device
2. **Navigate to** your PWA URL
3. **Wait for install banner** or tap menu → "Add to Home screen"
4. **Confirm installation**
5. **Verify** app appears in app drawer
6. **Launch app** and check standalone mode

### Expected Results
✅ App installs without app store  
✅ Launches in fullscreen/standalone mode  
✅ Shows custom icon and splash screen  
✅ Works offline after installation  

## T020: Verify Offline Lesson Access

### Test Steps
1. **Load PWA** with internet connection
2. **Navigate to lessons** (let service worker cache)
3. **Turn off WiFi/mobile data**
4. **Refresh page** - should show offline indicator
5. **Navigate to cached lessons** - should load from cache
6. **Try uncached content** - should show offline fallback
7. **Turn internet back on** - should sync automatically

### Expected Results
✅ Offline indicator appears when disconnected  
✅ Cached lessons load without internet  
✅ Offline fallback shows for uncached content  
✅ Auto-sync when connection restored  

## Validation Checklist
- [ ] iOS installation works
- [ ] Android installation works  
- [ ] Standalone mode launches correctly
- [ ] Offline lessons accessible
- [ ] Offline indicator functions
- [ ] Auto-sync on reconnection
