(function () {
  'use strict';

  var cache = {};
  var queued = [];
  var flushTimer = null;

  function resolveContainer(el) {
    if (typeof el === 'string') {
      el = document.querySelector(el);
    }

    if (!el) {
      return null;
    }

    if (el.classList && el.classList.contains('slotslaunch-embed')) {
      return el;
    }

    return el.closest('.slotslaunch-embed');
  }

  function showError(container, message) {
    container.innerHTML = '';
    container.removeAttribute('hidden');
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

  function parseUrls(body) {
    var urls = (body.data && body.data.urls) || {};
    if (body.data && body.data.url && !Object.keys(urls).length) {
      return { _single: body.data.url };
    }
    return urls;
  }

  function flushQueue() {
    flushTimer = null;
    var batch = queued;
    queued = [];
    if (!batch.length) {
      return;
    }

    var ids = [];
    var seen = {};
    for (var i = 0; i < batch.length; i++) {
      var id = batch[i].id;
      if (!seen[id]) {
        seen[id] = true;
        ids.push(id);
      }
    }

    var params = new URLSearchParams({
      action: 'slotslaunch_embed_url',
      games: ids.join(','),
      nonce: slotslaunchEmbeds.nonce
    });

    fetch(slotslaunchEmbeds.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      cache: 'no-store',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
      },
      body: params.toString()
    })
      .then(function (res) {
        return res.json().then(function (body) {
          if (!res.ok || !body.success) {
            throw new Error((body.data && body.data.message) || 'Unable to load game');
          }
          return parseUrls(body);
        });
      })
      .then(function (urls) {
        for (var j = 0; j < batch.length; j++) {
          var item = batch[j];
          var url = urls[item.id];
          if (url) {
            item.resolve(url);
          } else {
            cache[item.id] = null;
            item.reject(new Error('Invalid response'));
          }
        }
      })
      .catch(function (err) {
        for (var k = 0; k < batch.length; k++) {
          cache[batch[k].id] = null;
          batch[k].reject(err);
        }
      });
  }

  function fetchGameUrl(gameId) {
    gameId = String(gameId || '');
    if (!gameId) {
      return Promise.reject(new Error('Slots Launch: missing game id.'));
    }

    if (cache[gameId]) {
      return cache[gameId];
    }

    cache[gameId] = new Promise(function (resolve, reject) {
      queued.push({ id: gameId, resolve: resolve, reject: reject });
      if (flushTimer == null) {
        flushTimer = setTimeout(flushQueue, 0);
      }
    });

    return cache[gameId];
  }

  function applyUrl(el, url) {
    var tag = el.tagName;
    if (tag === 'A' || tag === 'AREA') {
      el.setAttribute('href', url);
      el.setAttribute('data-sl-ready', '1');
      return;
    }
    if (tag === 'IFRAME' || tag === 'EMBED' || tag === 'SOURCE') {
      el.setAttribute('src', url);
      el.setAttribute('data-sl-ready', '1');
    }
  }

  function hydratePlaceholder(span) {
    var gameId = span.getAttribute('data-sl-game');
    fetchGameUrl(gameId)
      .then(function (url) {
        var host = span.closest('a, area, iframe, embed');
        if (host) {
          applyUrl(host, url);
          return;
        }
        span.removeAttribute('hidden');
        span.textContent = url;
      })
      .catch(function (err) {
        showError(span, err && err.message ? err.message : 'Unable to load game.');
      });
  }

  function hydrateAttr(el) {
    var gameId = el.getAttribute('data-sl-game-url');
    fetchGameUrl(gameId)
      .then(function (url) {
        applyUrl(el, url);
        if (!el.hasAttribute('data-sl-ready')) {
          el.textContent = url;
        }
      })
      .catch(function (err) {
        showError(el, err && err.message ? err.message : 'Unable to load game.');
      });
  }

  function loadEmbed(container) {
    if (container.getAttribute('data-sl-booted') === '1') {
      return;
    }
    container.setAttribute('data-sl-booted', '1');

    var gameId = container.getAttribute('data-sl-game');
    var height = container.getAttribute('data-sl-height') || '600px';

    fetchGameUrl(gameId)
      .then(function (url) {
        mountIframe(container, url, height);
      })
      .catch(function (err) {
        container.removeAttribute('data-sl-booted');
        showError(container, err && err.message ? err.message : 'Unable to load game.');
      });
  }

  function openUrl(el, url) {
    var target = el.getAttribute('target') || '';
    if (target === '_blank') {
      window.open(url, '_blank', 'noopener,noreferrer');
      return;
    }
    window.location.href = url;
  }

  function gameIdFromLink(link) {
    if (link.hasAttribute('data-sl-game-url')) {
      return link.getAttribute('data-sl-game-url');
    }
    var span = link.querySelector('.slotslaunch-url[data-sl-game]');
    return span ? span.getAttribute('data-sl-game') : '';
  }

  function boot() {
    var placeholders = document.querySelectorAll('.slotslaunch-url[data-sl-game]');
    for (var i = 0; i < placeholders.length; i++) {
      hydratePlaceholder(placeholders[i]);
    }

    var attrs = document.querySelectorAll('[data-sl-game-url]');
    for (var j = 0; j < attrs.length; j++) {
      hydrateAttr(attrs[j]);
    }

    var nodes = document.querySelectorAll('.slotslaunch-embed[data-sl-game]');
    for (var k = 0; k < nodes.length; k++) {
      if (nodes[k].getAttribute('data-sl-autoload') !== '0') {
        loadEmbed(nodes[k]);
      } else {
        fetchGameUrl(nodes[k].getAttribute('data-sl-game'));
      }
    }

    document.addEventListener('click', function (event) {
      var play = event.target.closest('[data-sl-play]');
      if (play) {
        var container = resolveContainer(play);
        if (!container || container.getAttribute('data-sl-autoload') !== '0') {
          return;
        }
        event.preventDefault();
        loadEmbed(container);
        return;
      }

      var link = event.target.closest('a');
      if (!link || link.getAttribute('data-sl-ready') === '1') {
        return;
      }
      var gameId = gameIdFromLink(link);
      if (!gameId) {
        return;
      }

      event.preventDefault();
      fetchGameUrl(gameId)
        .then(function (url) {
          applyUrl(link, url);
          openUrl(link, url);
        })
        .catch(function (err) {
          showError(link, err && err.message ? err.message : 'Unable to load game.');
        });
    });
  }

  window.SlotsLaunchEmbeds = {
    load: function (el) {
      var container = resolveContainer(el);
      if (container) {
        loadEmbed(container);
      }
    }
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
