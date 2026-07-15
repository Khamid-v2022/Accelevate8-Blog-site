/**
 * Homepage hero scroll / entrance motion.
 */
(function () {
  var hero = document.querySelector(".ml-home-hero");
  if (!hero) {
    return;
  }

  var media = hero.querySelector("[data-ml-hero-media]");
  var reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  requestAnimationFrame(function () {
    hero.classList.add("is-ready");
  });

  if (reduce || !media) {
    return;
  }

  var ticking = false;

  function update() {
    ticking = false;
    var rect = hero.getBoundingClientRect();
    var height = hero.offsetHeight || 1;
    var progress = Math.min(1, Math.max(0, -rect.top / height));
    media.style.transform =
      "translate3d(0," + progress * 12 + "%,0) scale(" + (1 + progress * 0.06) + ")";
    hero.style.setProperty("--ml-hero-fade", String(1 - progress * 0.85));
  }

  window.addEventListener(
    "scroll",
    function () {
      if (!ticking) {
        ticking = true;
        requestAnimationFrame(update);
      }
    },
    { passive: true }
  );

  update();
})();
