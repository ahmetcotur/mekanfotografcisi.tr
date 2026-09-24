// Minimal lightbox: any <a data-lightbox="group" href="big.jpg"> opens in a
// full-screen <dialog> with prev/next (buttons, arrow keys, swipe).
(function () {
    const links = Array.from(document.querySelectorAll('a[data-lightbox]'));
    if (!links.length) return;

    const dialog = document.createElement('dialog');
    dialog.className = 'm-0 h-full max-h-none w-full max-w-none bg-ink/95 p-0 text-white backdrop:bg-transparent';
    dialog.setAttribute('aria-label', 'Fotoğraf görüntüleyici');
    dialog.innerHTML = `
        <div class="relative flex h-full w-full items-center justify-center p-4 md:p-12">
            <img class="max-h-full max-w-full rounded-lg object-contain" alt="">
            <button type="button" data-lb="close" class="absolute right-4 top-4 rounded-full bg-white/10 p-3 hover:bg-white/20" aria-label="Kapat">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
            <button type="button" data-lb="prev" class="absolute left-4 top-1/2 -translate-y-1/2 rounded-full bg-white/10 p-3 hover:bg-white/20" aria-label="Önceki">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
            </button>
            <button type="button" data-lb="next" class="absolute right-4 top-1/2 -translate-y-1/2 rounded-full bg-white/10 p-3 hover:bg-white/20" aria-label="Sonraki">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
            </button>
            <p data-lb="counter" class="absolute bottom-4 left-1/2 -translate-x-1/2 text-sm text-white/70"></p>
        </div>`;
    document.body.appendChild(dialog);

    const img = dialog.querySelector('img');
    const counter = dialog.querySelector('[data-lb="counter"]');
    let group = [];
    let index = 0;

    function show(i) {
        index = (i + group.length) % group.length;
        const link = group[index];
        img.src = link.href;
        img.alt = link.dataset.title || '';
        counter.textContent = `${index + 1} / ${group.length}`;
        const multi = group.length > 1;
        dialog.querySelector('[data-lb="prev"]').hidden = !multi;
        dialog.querySelector('[data-lb="next"]').hidden = !multi;
    }

    links.forEach(link => link.addEventListener('click', e => {
        e.preventDefault();
        group = links.filter(l => l.dataset.lightbox === link.dataset.lightbox);
        show(group.indexOf(link));
        dialog.showModal();
    }));

    dialog.addEventListener('click', e => {
        const action = e.target.closest('[data-lb]')?.dataset.lb;
        if (action === 'close' || e.target === dialog || e.target === dialog.firstElementChild) dialog.close();
        if (action === 'prev') show(index - 1);
        if (action === 'next') show(index + 1);
    });
    dialog.addEventListener('keydown', e => {
        if (e.key === 'ArrowLeft') show(index - 1);
        if (e.key === 'ArrowRight') show(index + 1);
    });
    let startX = null;
    dialog.addEventListener('touchstart', e => { startX = e.touches[0].clientX; }, { passive: true });
    dialog.addEventListener('touchend', e => {
        if (startX === null) return;
        const dx = e.changedTouches[0].clientX - startX;
        if (Math.abs(dx) > 50) show(index + (dx < 0 ? 1 : -1));
        startX = null;
    });
})();
