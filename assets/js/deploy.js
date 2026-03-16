jQuery(document).ready(function($) {
    $('#wp-admin-bar-w2wp_deploy a').on('click', function(e) {
        e.preventDefault();
        
        const $button = $(this);
        const originalText = $button.text();
        
        $button.text('⏳ Desplegando...');
        $button.css('pointer-events', 'none');
        
        $.ajax({
            url: w2wpDeploy.ajaxUrl,
            type: 'POST',
            data: {
                action: 'w2wp_trigger_deploy',
                nonce: w2wpDeploy.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice(response.data.message, 'success');
                } else {
                    showNotice(response.data.message, 'error');
                }
                
                $button.text(originalText);
                $button.css('pointer-events', 'auto');
            },
            error: function(xhr, status, error) {
                showNotice('Error de conexión: ' + error, 'error');
                $button.text(originalText);
                $button.css('pointer-events', 'auto');
            }
        });
    });
    
    function showNotice(message, type) {
        const noticeClass = type === 'error' ? 'w2wp-deploy-notice error' : 'w2wp-deploy-notice';
        const icon = type === 'error' ? '⚠️' : '✅';
        
        const $notice = $('<div class="' + noticeClass + '">' + icon + ' ' + message + '</div>');
        
        $('body').append($notice);
        
        setTimeout(function() {
            $notice.fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
    }
});
