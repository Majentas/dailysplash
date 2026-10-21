/**
 * This file is part of the Unsplash App
 * and licensed under the AGPL.
 *
 * Modified for DailySplash (see NOTICE).
 */
(function () {
    function initialize() {
        fetch(OC.generateUrl('/apps/dailysplash/metadata'), {
            method: 'GET',
            headers: {'Accept': 'application/json'},
        })
            .then((response) => response.ok ? response.json() : null)
            .then((data) => {
                if (data && data.source) {
                    addMetadataToDOM(data);
                }
            })
            .catch((e) => {
                console.error(e);
            });
    }

    function paragraph(text, extraClass) {
        const p = document.createElement('p');
        p.className = 'dailysplash-metadata-text' + (extraClass ? ' ' + extraClass : '');
        p.textContent = text;
        return p;
    }

    /**
     * Attribution box (bottom right). Only text and an outbound link the visitor
     * may click: nothing is loaded from the provider.
     */
    function addMetadataToDOM(data) {
        const div = document.createElement('div');
        const info = document.createElement('div');
        const link = document.createElement('a');

        //dash-panel for dashboard, guest-box for login
        div.className = 'dash-panel guest-box dailysplash-floating-bottom-right';
        info.className = 'dailysplash-icon-info';

        link.appendChild(paragraph(data.source + ((data.description || data.author) ? ':' : ''), 'dailysplash-metadata-title'));
        if (data.description) {
            link.appendChild(paragraph(data.description));
        }
        if (data.author || data.license) {
            link.appendChild(paragraph('- ' + [data.author, data.license].filter(Boolean).join(' · ')));
        }
        if (typeof data.attribution === 'string' && data.attribution.startsWith('https://')) {
            link.href = data.attribution;
            link.target = '_blank';
            link.rel = 'noreferrer noopener';
        }

        div.appendChild(info);
        div.appendChild(link);
        document.body.appendChild(div);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }
})();
