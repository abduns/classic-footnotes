/* Footnote and Reference — frontend behavior: favicon fallback + grouping + right-side flyout drawer popover */
(function () {
    'use strict';

    var config = {
        tooltipTrigger: 'hover',
        tooltipDelay: 300,
        tooltipPosition: 'auto',
        tooltipWidth: 340,
        enableAnimations: true,
        animationSpeed: 'normal',
        keyboardNav: true,
        reducedMotion: false,
        newTab: true,
        linkRel: 'noopener nofollow'
    };

    function motionBehavior() {
        return (config.reducedMotion || config.enableAnimations === false) ? 'auto' : 'smooth';
    }

    function ready(fn) {
        if (document.readyState !== 'loading') {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    }

    ready(function () {
        faviconFallback();
        groupFootnotes();
        setupPopover();
        setupDrawer();
    });

    /**
     * If a favicon fails to load, drop the image and reveal the number.
     */
    function faviconFallback() {
        var imgs = document.querySelectorAll('img.csfn-chip__favicon, img.csfn-source__favicon, img.csfn-sources-bar__favicon');

        Array.prototype.forEach.call(imgs, function (img) {
            function fail() {
                var chip = img.closest('.csfn-chip');
                if (chip) {
                    var fallback = chip.getAttribute('data-csfn-fallback') || 'number';
                    var text = chip.querySelector('.csfn-chip__text');

                    chip.classList.remove('csfn-chip--has-icon');
                    if (fallback === 'number' && text) {
                        text.textContent = chip.getAttribute('data-csfn-fallback-label') || chip.getAttribute('data-csfn-n') || text.textContent;
                    } else if (fallback === 'globe') {
                        var globe = chip.querySelector('.csfn-chip__globe-fallback');
                        if (globe) {
                            globe.hidden = false;
                            chip.classList.add('csfn-chip--has-icon');
                        }
                    }
                }

                var marker = img.closest('.csfn-source__marker');
                if (marker) {
                    var source = img.closest('.csfn-source');
                    var n = source ? source.id.replace('csfn-src-', '') : '';
                    var fallbackNum = document.createElement('span');
                    fallbackNum.className = 'csfn-source__num';
                    fallbackNum.textContent = n;
                    marker.textContent = '';
                    marker.appendChild(fallbackNum);
                }

                if (img.parentNode) {
                    img.parentNode.removeChild(img);
                }
            }

            if (img.complete && img.naturalWidth === 0) {
                fail();
            } else {
                img.addEventListener('error', fail);
            }
        });
    }

    /**
     * Group consecutive footnote chips (e.g. [1][2] -> [spatie +1]) to avoid clutter.
     */
    function groupFootnotes() {
        var cites = document.querySelectorAll('.csfn-cite');
        if (!cites.length) return;

        var i = 0;
        while (i < cites.length) {
            var leader = cites[i];
            var leaderChip = leader.querySelector('.csfn-chip');
            if (!leaderChip) {
                i++;
                continue;
            }

            var group = [leaderChip];
            var nextIndex = i + 1;

            // Scan for consecutive siblings (separated only by empty nodes or whitespace)
            while (nextIndex < cites.length) {
                var nextCite = cites[nextIndex];
                var isConsecutive = false;

                // Traverse siblings between leader and nextCite to ensure no non-whitespace text exists
                var currNode = leader.nextSibling;
                while (currNode && currNode !== nextCite) {
                    if (currNode.nodeType === Node.ELEMENT_NODE) {
                        break; // found another element in between
                    }
                    if (currNode.nodeType === Node.TEXT_NODE && currNode.textContent.trim()) {
                        break; // found actual text in between
                    }
                    currNode = currNode.nextSibling;
                }

                if (currNode === nextCite) {
                    isConsecutive = true;
                }

                if (isConsecutive) {
                    var nextChip = nextCite.querySelector('.csfn-chip');
                    if (nextChip) {
                        group.push(nextChip);
                    }
                    leader = nextCite; // Move pointer to check the next one
                    nextIndex++;
                } else {
                    break;
                }
            }

            if (group.length > 1) {
                var leaderEl = group[0];
                var count = group.length - 1;
                
                var labelText = leaderEl.getAttribute('data-csfn-label') || '';
                var textEl = leaderEl.querySelector('.csfn-chip__text');
                if (textEl) {
                    textEl.textContent = labelText + ' +' + count;
                }

                // Collect citation numbers
                var ns = group.map(function(chip) {
                    return chip.getAttribute('data-csfn-n');
                });
                leaderEl.setAttribute('data-csfn-group-ns', JSON.stringify(ns));

                // Hide consecutive citation tags
                for (var g = 1; g < group.length; g++) {
                    var citeParent = group[g].closest('.csfn-cite');
                    if (citeParent) {
                        citeParent.style.display = 'none';
                    }
                }
            }

            i = nextIndex;
        }
    }

    /**
     * Build one shared tooltip element with pagination and wire it to every chip.
     */
    function setupPopover() {
        var chips = document.querySelectorAll('.csfn-chip');
        if (!chips.length) {
            return;
        }

        var tip = document.createElement('div');
        tip.className = 'csfn-tooltip';
        tip.setAttribute('role', 'tooltip');
        document.body.appendChild(tip);

        var active = null;
        var hideTimeout = null;
        var currentPage = 0;
        var sources = [];

        function hide() {
            hideTimeout = setTimeout(function() {
                tip.classList.remove('is-visible');
                active = null;
                currentPage = 0;
                sources = [];
            }, 100);
        }

        function cancelHide() {
            if (hideTimeout) {
                clearTimeout(hideTimeout);
                hideTimeout = null;
            }
        }

        function renderPage() {
            if (sources.length === 0) return;

            tip.innerHTML = '';
            var source = sources[currentPage];

            // Create header with favicon and source count
            var header = document.createElement('div');
            header.className = 'csfn-tooltip__header';

            // Favicon or fallback
            if (source.favicon) {
                var favicon = document.createElement('img');
                favicon.className = 'csfn-tooltip__favicon';
                favicon.src = source.favicon;
                favicon.alt = '';
                favicon.width = 16;
                favicon.height = 16;
                header.appendChild(favicon);
            } else {
                var fallbackIcon = document.createElement('span');
                fallbackIcon.className = 'csfn-tooltip__icon-fallback';
                fallbackIcon.innerHTML = '<svg viewBox="0 0 24 24" width="16" height="16"><path fill="currentColor" d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm6.93 6h-2.95a15.7 15.7 0 0 0-1.38-3.56A8.03 8.03 0 0 1 18.93 8ZM12 4c.83 1.2 1.48 2.54 1.91 4h-3.82c.43-1.46 1.08-2.8 1.91-4ZM4.26 14a7.96 7.96 0 0 1 0-4h3.38a16.5 16.5 0 0 0 0 4H4.26Zm.81 2h2.95c.34 1.27.8 2.46 1.38 3.56A8.03 8.03 0 0 1 5.07 16Zm2.95-8H5.07a8.03 8.03 0 0 1 4.33-3.56A15.7 15.7 0 0 0 8.02 8ZM12 20c-.83-1.2-1.48-2.54-1.91-4h3.82c-.43 1.46-1.08 2.8-1.91 4Zm2.34-6H9.66a14.4 14.4 0 0 1 0-4h4.68a14.4 14.4 0 0 1 0 4Zm.32 5.56c.58-1.1 1.04-2.29 1.38-3.56h2.95a8.03 8.03 0 0 1-4.33 3.56ZM16.36 14a16.5 16.5 0 0 0 0-4h3.38a7.96 7.96 0 0 1 0 4h-3.38Z"/></svg>';
                header.appendChild(fallbackIcon);
            }

            // Source count text
            var countText = document.createElement('span');
            countText.className = 'csfn-tooltip__count';
            countText.textContent = sources.length === 1 ? '1 source' : sources.length + ' sources';
            header.appendChild(countText);

            // Pagination controls (only if multiple sources)
            if (sources.length > 1) {
                var pagination = document.createElement('div');
                pagination.className = 'csfn-tooltip__pagination';

                var prevBtn = document.createElement('button');
                prevBtn.className = 'csfn-tooltip__nav-btn';
                prevBtn.innerHTML = '<svg viewBox="0 0 24 24" width="14" height="14"><path fill="currentColor" d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>';
                prevBtn.disabled = currentPage === 0;
                prevBtn.onclick = function(e) {
                    e.stopPropagation();
                    if (currentPage > 0) {
                        currentPage--;
                        renderPage();
                    }
                };

                var pageIndicator = document.createElement('span');
                pageIndicator.className = 'csfn-tooltip__page-indicator';
                pageIndicator.textContent = (currentPage + 1) + '/' + sources.length;

                var nextBtn = document.createElement('button');
                nextBtn.className = 'csfn-tooltip__nav-btn';
                nextBtn.innerHTML = '<svg viewBox="0 0 24 24" width="14" height="14"><path fill="currentColor" d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>';
                nextBtn.disabled = currentPage === sources.length - 1;
                nextBtn.onclick = function(e) {
                    e.stopPropagation();
                    if (currentPage < sources.length - 1) {
                        currentPage++;
                        renderPage();
                    }
                };

                pagination.appendChild(prevBtn);
                pagination.appendChild(pageIndicator);
                pagination.appendChild(nextBtn);
                header.appendChild(pagination);
            }

            tip.appendChild(header);

            // Create content body
            var body = document.createElement('div');
            body.className = 'csfn-tooltip__body';

            // Title (clickable if URL exists)
            if (source.title) {
                if (source.url) {
                    var titleLink = document.createElement('a');
                    titleLink.className = 'csfn-tooltip__title csfn-tooltip__title--link';
                    titleLink.href = source.url;
                    if (config.newTab) {
                        titleLink.target = '_blank';
                    }
                    if (config.linkRel) {
                        titleLink.rel = config.linkRel;
                    }
                    titleLink.textContent = source.title;
                    titleLink.onclick = function(e) {
                        e.stopPropagation();
                    };
                    body.appendChild(titleLink);
                } else {
                    var titleSpan = document.createElement('span');
                    titleSpan.className = 'csfn-tooltip__title';
                    titleSpan.textContent = source.title;
                    body.appendChild(titleSpan);
                }
            }

            // Domain
            if (source.domain) {
                var domain = document.createElement('span');
                domain.className = 'csfn-tooltip__domain';
                domain.textContent = source.domain;
                body.appendChild(domain);
            }

            // Note
            if (source.note) {
                var note = document.createElement('span');
                note.className = 'csfn-tooltip__note';
                note.textContent = source.note;
                body.appendChild(note);
            }

            tip.appendChild(body);
        }

        function show(chip) {
            cancelHide();
            
            var nsStr = chip.getAttribute('data-csfn-group-ns');
            var ns = nsStr ? JSON.parse(nsStr) : [chip.getAttribute('data-csfn-n')];

            // Collect all sources
            sources = [];
            ns.forEach(function (n) {
                var pop = document.getElementById('csfn-pop-' + n);
                if (!pop) return;

                var titleEl = pop.querySelector('.csfn-pop__title');
                var domainEl = pop.querySelector('.csfn-pop__domain');
                var noteEl = pop.querySelector('.csfn-pop__note');

                sources.push({
                    title: titleEl ? titleEl.textContent : '',
                    domain: domainEl ? domainEl.textContent : '',
                    note: noteEl ? noteEl.textContent : '',
                    url: pop.getAttribute('data-csfn-url') || '',
                    favicon: pop.getAttribute('data-csfn-favicon') || ''
                });
            });

            if (sources.length === 0) return;

            currentPage = 0;
            active = chip;
            renderPage();
            position(chip, tip);
            tip.classList.add('is-visible');
        }

        // Keep tooltip visible when hovering over it
        tip.addEventListener('mouseenter', cancelHide);
        tip.addEventListener('mouseleave', hide);

        Array.prototype.forEach.call(chips, function (chip) {
            var showTimeout = null;
            
            if (config.tooltipTrigger === 'click') {
                // Click trigger
                chip.addEventListener('click', function (e) {
                    e.preventDefault();
                    if (active === chip) {
                        hide();
                    } else {
                        show(chip);
                    }
                });
            } else {
                // Hover trigger (default) with delay
                chip.addEventListener('mouseenter', function () {
                    if (showTimeout) clearTimeout(showTimeout);
                    var delay = parseInt(config.tooltipDelay, 10);
                    if (isNaN(delay)) {
                        delay = 300;
                    }
                    showTimeout = setTimeout(function() {
                        show(chip);
                    }, delay);
                });
                
                chip.addEventListener('mouseleave', function() {
                    if (showTimeout) {
                        clearTimeout(showTimeout);
                        showTimeout = null;
                    }
                    hide();
                });
            }
            
            // Focus/blur for accessibility
            chip.addEventListener('focus', function () { show(chip); });
            chip.addEventListener('blur', hide);
        });

        if (config.keyboardNav) {
            document.addEventListener('keydown', function (e) {
                if (!active) return;

                if (e.key === 'Escape') {
                    cancelHide();
                    tip.classList.remove('is-visible');
                    active = null;
                    currentPage = 0;
                    sources = [];
                } else if (e.key === 'ArrowLeft' && currentPage > 0) {
                    e.preventDefault();
                    currentPage--;
                    renderPage();
                } else if (e.key === 'ArrowRight' && currentPage < sources.length - 1) {
                    e.preventDefault();
                    currentPage++;
                    renderPage();
                }
            });
        }

        window.addEventListener('scroll', function () {
            if (active) {
                position(active, tip);
            }
        }, true);
    }

    /**
     * Position the tooltip centered above or below the chip.
     */
    function position(chip, tip) {
        var r = chip.getBoundingClientRect();

        // Make measurable first.
        tip.style.left = '-9999px';
        tip.style.top = '0px';
        tip.classList.add('is-visible');

        var tw = tip.offsetWidth;
        var th = tip.offsetHeight;

        var left = r.left + r.width / 2 - tw / 2;
        left = Math.max(8, Math.min(left, window.innerWidth - tw - 8));

        var top = r.top - th - 8;
        var preferBottom = config.tooltipPosition === 'bottom';
        var preferTop = config.tooltipPosition === 'top';
        if (preferBottom) {
            // Force bottom position
            top = r.bottom + 8;
        } else if (preferTop) {
            // Force top position (but flip if no space)
            if (top < 8) {
                top = r.bottom + 8;
            }
        } else {
            // Auto: flip to bottom if no space at top
            if (top < 8) {
                top = r.bottom + 8;
            }
        }

        tip.style.left = Math.round(left) + 'px';
        tip.style.top = Math.round(top) + 'px';
    }

    /**
     * Set up the right-side drawer panel and the bottom "Sources Footer Bar" triggers.
     */
    function setupDrawer() {
        var drawer = document.getElementById('csfn-drawer');
        var closeBtn = document.querySelector('.csfn-drawer__close');
        var barTrigger = document.getElementById('csfn-sources-bar-trigger');
        var chips = document.querySelectorAll('.csfn-chip');

        if (!drawer) return;

        function openDrawer() {
            drawer.classList.add('is-open');
            if (barTrigger) {
                barTrigger.setAttribute('aria-expanded', 'true');
            }
        }

        function closeDrawer() {
            drawer.classList.remove('is-open');
            if (barTrigger) {
                barTrigger.setAttribute('aria-expanded', 'false');
            }
        }

        // Toggle drawer on sources bar click
        if (barTrigger) {
            barTrigger.addEventListener('click', function(e) {
                e.preventDefault();
                openDrawer();
            });
        }

        // Close drawer on close button click
        if (closeBtn) {
            closeBtn.addEventListener('click', closeDrawer);
        }

        // When the tooltip uses click, it takes priority over opening the drawer.
        if (config.tooltipTrigger !== 'click') {
            Array.prototype.forEach.call(chips, function (chip) {
                chip.addEventListener('click', function (e) {
                    e.preventDefault();
                    openDrawer();

                    var nsStr = chip.getAttribute('data-csfn-group-ns');
                    var ns = nsStr ? JSON.parse(nsStr) : [chip.getAttribute('data-csfn-n')];

                    if (ns.length > 0) {
                        var firstCard = document.getElementById('csfn-src-' + ns[0]);
                        if (firstCard) {
                            setTimeout(function() {
                                firstCard.scrollIntoView({ behavior: motionBehavior(), block: 'center' });
                            }, 100);
                        }

                        // Highlight all matching citation cards in the drawer
                        ns.forEach(function(n) {
                            var card = document.getElementById('csfn-src-' + n);
                            if (card) {
                                card.classList.add('is-highlighted');
                                setTimeout(function() {
                                    card.classList.remove('is-highlighted');
                                }, 2000);
                            }
                        });
                    }
                });
            });
        }

        // Handle back links to content (close drawer and scroll/highlight text chip)
        var backLinks = drawer.querySelectorAll('.csfn-source__back');
        Array.prototype.forEach.call(backLinks, function (back) {
            back.addEventListener('click', function(e) {
                e.preventDefault();
                closeDrawer();

                var refId = back.getAttribute('href');
                var refEl = document.querySelector(refId);
                if (refEl) {
                    setTimeout(function() {
                        refEl.scrollIntoView({ behavior: motionBehavior(), block: 'center' });
                        
                        var chip = refEl.querySelector('.csfn-chip');
                        if (chip) {
                            chip.classList.add('is-highlighted');
                            setTimeout(function() {
                                chip.classList.remove('is-highlighted');
                            }, 2000);
                        }
                    }, 300);
                }
            });
        });

        // Handle Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && drawer.classList.contains('is-open')) {
                closeDrawer();
            }
        });
    }
})();
