document.addEventListener('DOMContentLoaded', function() {

    const pluginSplitClose = document.getElementById('plugin-split-close');

    if(pluginSplitClose) {
        pluginSplitClose.addEventListener('click', function () {
            let nonceElement = document.getElementById('hts_close_plugin_split_nonce');

            if (!nonceElement) {
                console.error('Nonce element not found.');
                return;
            }

            let nonce = nonceElement.value;

            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'action': 'hostinger_dismiss_plugin_split_notice',
                    'nonce': nonce
                }),
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    document.getElementById('hostinger-plugin-split-notice').style.display = 'none';
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        });
    }
});
