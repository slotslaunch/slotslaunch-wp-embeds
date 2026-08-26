(function () {
  'use strict';

  function showError(container, message) {
    container.innerHTML = '';
    var notice = document.createElement('div');
    notice.className = 'slotslaunch-embed-notice';
    notice.style.cssText = 'display:flex;align-items:center;justify-content:center;padding:16px;font:14px/1.4 system-ui,sans-serif;color:#666;text-align:center;min-height:120px;border:1px solid #ddd;background:#f9f9f9;';
    notice.textContent = message;
    container.appendChild(notice);
  }

  function mountIframe(container, url, height) {
    container.innerHTML = '';
    var iframe = document.createElement('iframe');
    iframe.src = url;
    iframe.title = container.getAttribute('data-sl-title') || 'Slots Launch game';
    iframe.setAttribute('allowfullscreen', '');
    iframe.setAttribute('loading', 'lazy');
    iframe.style.cssText = 'border:0;width:100%;height:' + height + ';display:block;max-width:100%;';
    container.appendChild(iframe);
    container.classList.remove('slotslaunch-embed--loading');
  }

  function loadEmbed(container) {
    if (container.getAttribute('data-sl-booted') === '1') {
      return;
    }
    container.setAttribute('data-sl-booted', '1');

    var gameId = container.getAttribute('data-sl-game');
    var height = container.getAttribute('data-sl-height') || '600px';

    if (!gameId) {
      showError(container, 'Slots Launch: missing game id.');
      return;
    }

    var params = new URLSearchParams({
      action: 'slotslaunch_embed_url',
      game: gameId,
      nonce: slotslaunchEmbeds.nonce
    });

    fetch(slotslaunchEmbeds.ajaxUrl + '?' + params.toString(), {
      method: 'GET',
      credentials: 'same-origin',
      cache: 'no-store'
    })
      .then(function (res) {
        return res.json().then(function (body) {
          if (!res.ok || !body.success) {
            throw new Error((body.data && body.data.message) || 'Unable to load game');
          }
          return body.data;
        });
      })
      .then(function (data) {
        if (!data || !data.url) {
          throw new Error('Invalid response');
        }
        mountIframe(container, data.url, height);
      })
      .catch(function (err) {
        showError(container, err && err.message ? err.message : 'Unable to load game.');
      });
  }

  function boot() {
    var nodes = document.querySelectorAll('.slotslaunch-embed[data-sl-game]');
    for (var i = 0; i < nodes.length; i++) {
      loadEmbed(nodes[i]);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
