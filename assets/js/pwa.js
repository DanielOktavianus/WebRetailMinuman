// PWA Helper - Service Worker Registration & Install Prompt

(function() {
  'use strict';

  const BASE = window.APP_BASE || '';

  let deferredPrompt;
  let isInstalled = false;

  // Check if app is already installed
  window.addEventListener('beforeinstallprompt', (e) => {
    console.log('[PWA] beforeinstallprompt event triggered');
    e.preventDefault();
    deferredPrompt = e;
    showInstallPrompt();
  });

  // Listen for app installation
  window.addEventListener('appinstalled', () => {
    console.log('[PWA] App installed successfully');
    isInstalled = true;
    hideInstallPrompt();
    localStorage.setItem('pwa-installed', 'true');
  });

  // Register Service Worker
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker
        .register(BASE + '/service-worker.php', { scope: BASE + '/' })
        .then((registration) => {
          console.log('[Service Worker] Registered successfully:', registration.scope);
          setInterval(() => registration.update(), 60000);
        })
        .catch((error) => {
          console.warn('[Service Worker] Registration failed:', error);
        });
    });

    navigator.serviceWorker.addEventListener('controllerchange', () => {
      console.log('[Service Worker] Controller changed - app updated');
      showUpdateNotification();
    });
  }

  function showInstallPrompt() {
    const banner = document.getElementById('pwa-install-banner');
    if (banner) {
      banner.style.display = 'flex';
      const installBtn = document.getElementById('pwa-install-btn');
      if (installBtn) installBtn.addEventListener('click', handleInstall);
      const closeBtn = document.getElementById('pwa-close-banner');
      if (closeBtn) closeBtn.addEventListener('click', () => { banner.style.display = 'none'; });
    }
  }

  function hideInstallPrompt() {
    const banner = document.getElementById('pwa-install-banner');
    if (banner) banner.style.display = 'none';
  }

  async function handleInstall() {
    if (!deferredPrompt) return;
    deferredPrompt.prompt();
    const { outcome } = await deferredPrompt.userChoice;
    console.log('[PWA] User response:', outcome);
    deferredPrompt = null;
  }

  function showUpdateNotification() {
    const notification = document.createElement('div');
    notification.style.cssText = `
      position:fixed;bottom:20px;left:20px;right:20px;
      background:linear-gradient(90deg,#4f46e5,#06b6d4);color:white;
      padding:16px;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,.2);
      z-index:9999;display:flex;align-items:center;justify-content:space-between;
      gap:12px;font-size:14px;animation:slideUp .3s ease;
    `;
    notification.innerHTML = `
      <span>Versi terbaru tersedia!</span>
      <button id="update-reload" style="background:white;color:#4f46e5;border:none;
        padding:6px 12px;border-radius:4px;cursor:pointer;font-weight:600;font-size:12px">
        Refresh
      </button>
    `;
    document.body.appendChild(notification);
    notification.querySelector('#update-reload').addEventListener('click', () => window.location.reload());
    setTimeout(() => {
      notification.style.animation = 'slideDown .3s ease';
      setTimeout(() => notification.remove(), 300);
    }, 10000);
  }

  const style = document.createElement('style');
  style.textContent = `
    @keyframes slideUp   { from { transform:translateY(100%); opacity:0 } to { transform:translateY(0); opacity:1 } }
    @keyframes slideDown { from { transform:translateY(0); opacity:1 }   to { transform:translateY(100%); opacity:0 } }
  `;
  document.head.appendChild(style);

  window.PWA = {
    install: handleInstall,
    isInstalled: () => isInstalled,
    unregister: () => {
      if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistrations().then((regs) => regs.forEach((r) => r.unregister()));
      }
    }
  };

  console.log('[PWA] Helper loaded (base=' + BASE + ')');
})();
