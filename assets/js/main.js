document.addEventListener('DOMContentLoaded', function () {
  var header = document.querySelector('header.site-header');
  if (header) {
    var onScroll = function () {
      header.classList.toggle('scrolled', window.scrollY > 40);
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  var toggleBtn = document.querySelector('.nav-toggle');
  var nav = document.querySelector('.main-nav');
  if (toggleBtn && nav && header) {
    toggleBtn.addEventListener('click', function () {
      var isOpen = nav.classList.toggle('open');
      header.classList.toggle('menu-open', isOpen);
    });
  }

  var revealEls = document.querySelectorAll('.reveal');
  if ('IntersectionObserver' in window && revealEls.length) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
    revealEls.forEach(function (el) { io.observe(el); });
  } else {
    revealEls.forEach(function (el) { el.classList.add('is-visible'); });
  }

  // Split word-reveal titles into animated spans
  document.querySelectorAll('.word-reveal').forEach(function (el) {
    var words = el.textContent.trim().split(/\s+/);
    el.innerHTML = words.map(function (w, i) {
      return '<span style="animation-delay:' + (0.15 + i * 0.09) + 's">' + w + '&nbsp;</span>';
    }).join('');
  });

  // Hero slider
  var slider = document.querySelector('.hero-slider');
  if (slider) {
    var slides = Array.prototype.slice.call(slider.querySelectorAll('.slide'));
    var dotsWrap = slider.querySelector('.slider-dots');
    var idx = 0;
    var timer;

    if (dotsWrap) {
      slides.forEach(function (_, i) {
        var b = document.createElement('button');
        if (i === 0) b.classList.add('active');
        b.addEventListener('click', function () { goTo(i); resetTimer(); });
        dotsWrap.appendChild(b);
      });
    }

    function render() {
      slides.forEach(function (s, i) { s.classList.toggle('active', i === idx); });
      if (dotsWrap) {
        Array.prototype.forEach.call(dotsWrap.children, function (d, i) {
          d.classList.toggle('active', i === idx);
        });
      }
    }

    function goTo(i) { idx = (i + slides.length) % slides.length; render(); }
    function next() { goTo(idx + 1); }
    function prev() { goTo(idx - 1); }
    function resetTimer() { clearInterval(timer); timer = setInterval(next, 6000); }

    var nextBtn = slider.querySelector('.slider-next');
    var prevBtn = slider.querySelector('.slider-prev');
    if (nextBtn) nextBtn.addEventListener('click', function () { next(); resetTimer(); });
    if (prevBtn) prevBtn.addEventListener('click', function () { prev(); resetTimer(); });

    render();
    resetTimer();
  }

  // Lightbox popup for gallery images
  var lightbox = document.getElementById('lightbox');
  if (lightbox) {
    var lbImg = lightbox.querySelector('img');
    var lbCaption = lightbox.querySelector('figcaption');
    var lbClose = lightbox.querySelector('.lightbox-close');
    var lbPrev = lightbox.querySelector('.lightbox-prev');
    var lbNext = lightbox.querySelector('.lightbox-next');
    var currentGroup = [];
    var currentIndex = 0;

    function showAt(i) {
      currentIndex = (i + currentGroup.length) % currentGroup.length;
      var link = currentGroup[currentIndex];
      lbImg.src = link.getAttribute('href');
      lbImg.alt = link.getAttribute('data-caption') || '';
      lbCaption.textContent = link.getAttribute('data-caption') || '';
    }

    function openLightbox(link) {
      var grid = link.closest('.gallery-grid');
      currentGroup = grid
        ? Array.prototype.slice.call(grid.querySelectorAll('.lightbox-link'))
        : [link];
      showAt(currentGroup.indexOf(link));
      lightbox.classList.add('open');
      document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
      lightbox.classList.remove('open');
      document.body.style.overflow = '';
    }

    document.querySelectorAll('.lightbox-link').forEach(function (link) {
      link.addEventListener('click', function (e) {
        e.preventDefault();
        openLightbox(link);
      });
    });

    lbClose.addEventListener('click', closeLightbox);
    lbNext.addEventListener('click', function () { showAt(currentIndex + 1); });
    lbPrev.addEventListener('click', function () { showAt(currentIndex - 1); });

    lightbox.addEventListener('click', function (e) {
      if (e.target === lightbox) closeLightbox();
    });

    document.addEventListener('keydown', function (e) {
      if (!lightbox.classList.contains('open')) return;
      if (e.key === 'Escape') closeLightbox();
      if (e.key === 'ArrowRight') showAt(currentIndex + 1);
      if (e.key === 'ArrowLeft') showAt(currentIndex - 1);
    });
  }
});
