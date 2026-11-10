// content-zoom-slider.js (Vanilla JS v2)
// Replaces jQuery plugin
// Updated for Bootstrap 5 (me-*, ms-*)

function initContentZoomSlider(targetSelector, options = {}) {
  const target = document.querySelector(targetSelector);
  if (!target) return;

  const settings = Object.assign({
    toolContainer: "#tool-container",
    step: 5,
    min: 50,
    max: 200,
    zoom: 100
  }, options);

  const toolContainer = document.querySelector(settings.toolContainer);
  if (!toolContainer) return;

  // Helper: set zoom
  function setZoom(val) {
    // Clamp
    if (val < settings.min) val = settings.min;
    if (val > settings.max) val = settings.max;

    const scale = val / 100;

    // Apply Zoom
    if ('zoom' in document.documentElement.style) {
      target.style.zoom = scale;
    } else {
      // Firefox fallback
      target.style.transform = `scale(${scale})`;
      target.style.transformOrigin = '50% 0';
    }

    // Update UI
    const range = toolContainer.querySelector('.ranger');
    if (range) range.value = scale;

    const label = toolContainer.querySelector('.zoom-value');
    if (label) label.textContent = Math.round(val) + "%";

    settings.zoom = val;
  }

  // Render Toolbar (BS5 Classes: mr->me, ml->ms)
  toolContainer.innerHTML = `
        <div class="row">
            <div class="col-sm-12 p-1 text-center zoominout d-flex flex-row align-items-center justify-content-center">
                <span class="me-2">Zoom</span>
                <button type="button" class="zoom-out btn"><i class="bi bi-minus m-1 text-primary"></i></button>
                <input class="ranger" type="range" value="${settings.zoom / 100}" step="${settings.step / 100}"
                    min="${settings.min / 100}"
                    max="${settings.max / 100}" style="width: 100px; margin: 0 10px;">
                <button type="button" class="zoom-in btn"><i class="bi bi-plus m-1 text-primary"></i></button>
                <span class="zoom-value ms-2">${settings.zoom}%</span>
            </div>
        </div>
    `;

  // Events
  const btnOut = toolContainer.querySelector('.zoom-out');
  const btnIn = toolContainer.querySelector('.zoom-in');
  const ranger = toolContainer.querySelector('.ranger');

  if (btnOut) {
    btnOut.onclick = () => setZoom(settings.zoom - settings.step);
  }
  if (btnIn) {
    btnIn.onclick = () => setZoom(settings.zoom + settings.step);
  }
  if (ranger) {
    ranger.oninput = (e) => setZoom(parseFloat(e.target.value) * 100);
  }

  // Initialize
  setZoom(settings.zoom);
}
