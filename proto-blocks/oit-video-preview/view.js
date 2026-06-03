/**
 * OIT Video Preview -- click-to-play lightbox.
 *
 * Each .oit-video-preview block carries a self-contained modal at the
 * end of its DOM. The trigger button (the thumbnail) carries a
 * data-oit-video JSON payload describing { source, embedUrl, mp4Url }.
 *
 * On click: parse the payload, inject the correct player into the
 * modal stage, show the modal, lock body scroll. On close (button, ESC,
 * backdrop click): clear the stage (which stops playback) and unlock
 * scroll. One handler per section -- re-running init on the same node
 * is idempotent via dataset guard.
 */
(function () {
  'use strict';

  function buildPlayer(payload) {
    var stage = document.createElement('div');
    stage.className = 'w-full h-full';

    if (payload.source === 'mp4' && payload.mp4Url) {
      var video = document.createElement('video');
      video.src = payload.mp4Url;
      video.controls = true;
      video.autoplay = true;
      video.className = 'w-full h-full bg-black';
      stage.appendChild(video);
      return stage;
    }

    if (payload.embedUrl) {
      var iframe = document.createElement('iframe');
      iframe.src = payload.embedUrl;
      iframe.className = 'w-full h-full';
      iframe.setAttribute('frameborder', '0');
      iframe.setAttribute('allow', 'autoplay; fullscreen; picture-in-picture');
      iframe.setAttribute('allowfullscreen', '');
      stage.appendChild(iframe);
      return stage;
    }

    var msg = document.createElement('p');
    msg.className = 'text-white text-center p-8';
    msg.textContent = 'No video URL configured.';
    stage.appendChild(msg);
    return stage;
  }

  function init(section) {
    if (section.dataset.oitVpInit === '1') return;
    section.dataset.oitVpInit = '1';

    var trigger = section.querySelector('.oit-video-preview__trigger');
    var modal   = section.querySelector('.oit-video-preview__modal');
    var closeBtn = section.querySelector('.oit-video-preview__close');
    var stage   = section.querySelector('.oit-video-preview__stage');
    if (!trigger || !modal || !closeBtn || !stage) return;

    function open() {
      var raw = trigger.getAttribute('data-oit-video');
      if (!raw) return;
      var payload;
      try { payload = JSON.parse(raw); } catch (e) { return; }

      stage.innerHTML = '';
      stage.appendChild(buildPlayer(payload));

      modal.classList.remove('hidden');
      modal.classList.add('flex');
      document.body.style.overflow = 'hidden';
    }

    function close() {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      stage.innerHTML = '';
      document.body.style.overflow = '';
    }

    trigger.addEventListener('click', open);
    closeBtn.addEventListener('click', close);
    modal.addEventListener('click', function (e) {
      if (e.target === modal) close();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !modal.classList.contains('hidden')) close();
    });

    section.setAttribute('data-proto-animate', 'done');
  }

  function bootstrap() {
    document.querySelectorAll('.oit-video-preview').forEach(init);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootstrap);
  } else {
    bootstrap();
  }
})();
