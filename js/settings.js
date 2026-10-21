/**
 * This file is part of the Unsplash App
 * and licensed under the AGPL.
 *
 * Modified for DailySplash (see NOTICE).
 */
(function () {
    const timers = {};
    let saveUrl;

    function initialize() {
        const root = document.getElementById('dailysplash-settings');
        if (!root) {
            return;
        }
        saveUrl = root.dataset.save;

        document.getElementById('dailysplash-refresh').addEventListener('click', (e) => {
            e.target.disabled = true;
            setValue('refresh', true).finally(() => {
                e.target.disabled = false;
            });
        });

        for (const setting of document.querySelectorAll('#dailysplash-settings [data-setting]')) {
            setting.addEventListener('change', (e) => {
                const key = e.target.dataset.setting;
                const value = e.target.type === 'checkbox' ? e.target.checked : e.target.value;

                if (key === 'style/tint') {
                    document.getElementById('dailysplash-style-color-strength').disabled = !value;
                }
                setValue(key, value);
            });
        }
    }

    /**
     * Update configuration value
     */
    function setValue(key, value) {
        return fetch(saveUrl, {
            method: 'POST',
            redirect: 'error',
            headers: {
                'requesttoken': OC.requestToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({key, value}),
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                return response.json();
            })
            .then((data) => {
                showState(data);
                showMessage(data.lastError && key === 'refresh' ? 'error' : 'success');
            })
            .catch((e) => {
                console.error(e);
                showMessage('error');
            });
    }

    function showState(data) {
        document.getElementById('dailysplash-cache').hidden = !data.isCached;
        for (const source of document.querySelectorAll('.dailysplash-source')) {
            source.hidden = source.dataset.provider !== data.provider;
        }

        const preview = document.getElementById('dailysplash-preview');
        preview.hidden = !data.imageURL;
        if (data.imageURL) {
            preview.src = data.imageURL;
        }
        document.getElementById('dailysplash-image-date').textContent = data.imageDate || '—';

        const error = document.getElementById('dailysplash-last-error');
        error.hidden = !data.lastError;
        error.querySelector('span').textContent = data.lastError || '';
    }

    /**
     * Show save success/fail message
     */
    function showMessage(type) {
        const element = document.querySelector(`#dailysplash-settings .msg.${type}`);
        element.classList.add('active');
        clearTimeout(timers[type]);
        timers[type] = setTimeout(() => {
            element.classList.remove('active');
        }, 1000);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }
})();
