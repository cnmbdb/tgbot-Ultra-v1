/**
 * Pixel Blast style background - Canvas implementation
 * Inspired by https://reactbits.dev/backgrounds/pixel-blast (Bayer dithering + FBM noise)
 * Draws at low resolution then scales up for performance.
 */
(function () {
  'use strict';

  var PIXEL_SIZE = 4;
  var COLOR = '#16a34a';
  var PATTERN_SCALE = 2;
  var PATTERN_DENSITY = 0.5;
  var SPEED = 0.5;
  var EDGE_FADE = 0.25;
  var LOGIC_SCALE = 0.35; /* draw at 35% resolution then scale up */

  function bayer8(x, y) {
    x = Math.floor(x) % 8;
    y = Math.floor(y) % 8;
    var b2 = function (a, b) {
      return ((a / 2 + b * b * 0.75) % 1);
    };
    return (b2(x / 4, y / 4) * 0.25 + b2(x / 2, y / 2) * 0.25 + b2(x, y)) / 1.25;
  }

  function hash11(n) {
    var s = Math.sin(n * 43758.5453);
    return (s - Math.floor(s));
  }

  function vnoise(x, y, t) {
    var ix = Math.floor(x), iy = Math.floor(y), it = Math.floor(t);
    var fx = x - ix, fy = y - iy, ft = t - it;
    function smooth(v) {
      return v * v * v * (v * (v * 6 - 15) + 10);
    }
    fx = smooth(fx);
    fy = smooth(fy);
    ft = smooth(ft);
    function dot(i, j, k) {
      return hash11((ix + i) * 1 + (iy + j) * 57 + (it + k) * 113) * 2 - 1;
    }
    var n000 = dot(0, 0, 0), n100 = dot(1, 0, 0), n010 = dot(0, 1, 0), n110 = dot(1, 1, 0);
    var n001 = dot(0, 0, 1), n101 = dot(1, 0, 1), n011 = dot(0, 1, 1), n111 = dot(1, 1, 1);
    var x00 = n000 + fx * (n100 - n000), x10 = n010 + fx * (n110 - n010);
    var x01 = n001 + fx * (n101 - n001), x11 = n011 + fx * (n111 - n011);
    var y0 = x00 + fy * (x10 - x00), y1 = x01 + fy * (x11 - x01);
    return (y0 + ft * (y1 - y0)) * 0.5 + 0.5;
  }

  function fbm2(uvx, uvy, t) {
    var amp = 1, freq = 1, sum = 0;
    for (var i = 0; i < 5; i++) {
      sum += amp * vnoise(uvx * freq, uvy * freq, t * freq);
      freq *= 1.25;
      amp *= 1;
    }
    return (sum / 5) * 0.5 + 0.5;
  }

  function init(container) {
    if (!container) return;
    var canvas = document.createElement('canvas');
    canvas.className = 'pixel-blast-canvas';
    canvas.style.cssText = 'position:absolute;top:0;left:0;width:100%;height:100%;display:block;z-index:0;';
    container.insertBefore(canvas, container.firstChild);

    var ctx = canvas.getContext('2d');
    if (!ctx) return;

    var timeOffset = Math.random() * 1000;
    var raf = 0;
    var lw = 0, lh = 0;
    var off = document.createElement('canvas');
    var octx = off.getContext('2d');
    if (!octx) return;

    function resize() {
      lw = Math.max(1, Math.floor((container.clientWidth || 1) * LOGIC_SCALE));
      lh = Math.max(1, Math.floor((container.clientHeight || 1) * LOGIC_SCALE));
      off.width = lw;
      off.height = lh;
      canvas.width = container.clientWidth || 1;
      canvas.height = container.clientHeight || 1;
      canvas.style.width = '100%';
      canvas.style.height = '100%';
    }

    function draw() {
      var t = timeOffset + (Date.now() / 1000) * SPEED;
      var pw = Math.max(1, Math.floor(PIXEL_SIZE * LOGIC_SCALE));
      var w = lw, h = lh;
      var aspect = w / h;
      var cellSize = Math.max(1, 8 * pw);
      var baseDensity = 0.5 + (PATTERN_DENSITY - 0.5) * 0.3;

      octx.fillStyle = '#f9fafb';
      octx.fillRect(0, 0, w, h);

      for (var py = 0; py < h + pw; py += pw) {
        for (var px = 0; px < w + pw; px += pw) {
          var cellX = Math.floor(px / cellSize) * cellSize;
          var cellY = Math.floor(py / cellSize) * cellSize;
          var uvx = (cellX / w) * aspect;
          var uvy = cellY / h;
          var base = fbm2(uvx, uvy, t * 0.05);
          base = base * 0.5 - 0.65;
          var feed = base + baseDensity;

          var bx = px / pw;
          var by = py / pw;
          var b = bayer8(bx, by) - 0.5;
          var bw = feed + b > 0.5 ? 1 : 0;
          if (bw <= 0) continue;

          var nx = px / w;
          var ny = py / h;
          var edge = Math.min(nx, ny, 1 - nx, 1 - ny);
          var fade = edge <= 0 ? 0 : (edge < EDGE_FADE ? edge / EDGE_FADE : 1);
          if (fade <= 0) continue;

          octx.globalAlpha = fade;
          octx.fillStyle = COLOR;
          octx.fillRect(px, py, pw + 0.5, pw + 0.5);
        }
      }
      octx.globalAlpha = 1;

      ctx.imageSmoothingEnabled = false;
      ctx.msImageSmoothingEnabled = false;
      ctx.drawImage(off, 0, 0, lw, lh, 0, 0, canvas.width, canvas.height);

      raf = requestAnimationFrame(draw);
    }

    resize();
    window.addEventListener('resize', resize);
    draw();

    return function destroy() {
      window.removeEventListener('resize', resize);
      cancelAnimationFrame(raf);
      if (canvas.parentNode) canvas.parentNode.removeChild(canvas);
    };
  }

  var container = document.querySelector('.login-wrapper');
  if (container) {
    init(container);
  }
})();
