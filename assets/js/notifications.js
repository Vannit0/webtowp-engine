/**
 * WebToWP Engine - Notifications JavaScript
 */

(function($) {
    'use strict';

    const W2WPNotifications = {
        container: null,
        modalContainer: null,

        init: function() {
            this.container = $('#w2wp-notification-container');
            this.modalContainer = $('#w2wp-modal-container');
            
            if (this.container.length === 0) {
                this.container = $('<div id="w2wp-notification-container"></div>').appendTo('body');
            }
            
            if (this.modalContainer.length === 0) {
                this.modalContainer = $('<div id="w2wp-modal-container"></div>').appendTo('body');
            }

            this.bindEvents();
        },

        bindEvents: function() {
            const self = this;

            // Cerrar toast al hacer clic en el botón de cerrar
            $(document).on('click', '.w2wp-toast-close', function() {
                const $toast = $(this).closest('.w2wp-toast');
                self.dismissToast($toast);
            });

            // Cerrar modal
            $(document).on('click', '.w2wp-modal-close, .w2wp-modal-overlay', function() {
                self.closeModal();
            });

            // Botones de modal
            $(document).on('click', '.w2wp-modal-button', function() {
                const action = $(this).data('action');
                if (action === 'dismiss') {
                    self.closeModal();
                }
            });

            // Dismiss persistent notification via AJAX
            $(document).on('click', '.w2wp-persistent-dismiss', function(e) {
                e.preventDefault();
                const $notification = $(this).closest('.w2wp-persistent-notification');
                const notificationId = $notification.data('id');
                self.dismissPersistent(notificationId, $notification);
            });
        },

        showToast: function(message, type = 'info', duration = 5000) {
            const icons = {
                success: '✅',
                error: '❌',
                warning: '⚠️',
                info: 'ℹ️'
            };

            const $toast = $(`
                <div class="w2wp-toast ${type}">
                    <span class="w2wp-toast-icon">${icons[type]}</span>
                    <div class="w2wp-toast-content">
                        <p class="w2wp-toast-message">${message}</p>
                    </div>
                    <button class="w2wp-toast-close" type="button">&times;</button>
                    ${duration > 0 ? `<div class="w2wp-toast-progress"><div class="w2wp-toast-progress-bar" style="animation-duration: ${duration}ms;"></div></div>` : ''}
                </div>
            `);

            this.container.append($toast);

            if (duration > 0) {
                setTimeout(() => {
                    this.dismissToast($toast);
                }, duration);
            }

            return $toast;
        },

        dismissToast: function($toast) {
            $toast.addClass('dismissing');
            setTimeout(() => {
                $toast.remove();
            }, 300);
        },

        showModal: function(title, message, type = 'info', actions = []) {
            const icons = {
                success: '✅',
                error: '❌',
                warning: '⚠️',
                info: 'ℹ️'
            };

            let actionsHTML = '';
            if (actions.length > 0) {
                actionsHTML = '<div class="w2wp-modal-footer">';
                actions.forEach(action => {
                    const buttonClass = action.primary ? 'w2wp-modal-button-primary' : 'w2wp-modal-button-secondary';
                    const dataAction = action.dismiss ? 'data-action="dismiss"' : '';
                    const href = action.url ? `href="${action.url}"` : '';
                    actionsHTML += `<a ${href} class="w2wp-modal-button ${buttonClass}" ${dataAction}>${action.text}</a>`;
                });
                actionsHTML += '</div>';
            }

            const modalHTML = `
                <div class="w2wp-modal-overlay"></div>
                <div class="w2wp-modal">
                    <div class="w2wp-modal-header">
                        <span class="w2wp-modal-icon">${icons[type]}</span>
                        <h3 class="w2wp-modal-title">${title}</h3>
                        <button class="w2wp-modal-close" type="button">&times;</button>
                    </div>
                    <div class="w2wp-modal-body">
                        ${message}
                    </div>
                    ${actionsHTML}
                </div>
            `;

            this.modalContainer.html(modalHTML).addClass('active');
        },

        closeModal: function() {
            this.modalContainer.removeClass('active');
            setTimeout(() => {
                this.modalContainer.empty();
            }, 300);
        },

        dismissPersistent: function(notificationId, $notification) {
            if (typeof w2wpNotifications === 'undefined') {
                return;
            }

            $.ajax({
                url: w2wpNotifications.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'w2wp_dismiss_notification',
                    nonce: w2wpNotifications.nonce,
                    notification_id: notificationId
                },
                success: function(response) {
                    if (response.success) {
                        $notification.fadeOut(300, function() {
                            $(this).remove();
                        });
                    }
                }
            });
        },

        // Métodos de conveniencia
        success: function(message, duration = 5000) {
            return this.showToast(message, 'success', duration);
        },

        error: function(message, duration = 8000) {
            return this.showToast(message, 'error', duration);
        },

        warning: function(message, duration = 6000) {
            return this.showToast(message, 'warning', duration);
        },

        info: function(message, duration = 5000) {
            return this.showToast(message, 'info', duration);
        },

        confirm: function(title, message, onConfirm, onCancel) {
            const actions = [
                {
                    text: 'Confirmar',
                    primary: true,
                    callback: onConfirm
                },
                {
                    text: 'Cancelar',
                    dismiss: true,
                    callback: onCancel
                }
            ];

            this.showModal(title, message, 'warning', actions);
        }
    };

    // Inicializar cuando el DOM esté listo
    $(document).ready(function() {
        W2WPNotifications.init();
        
        // Exponer globalmente
        window.W2WPNotifications = W2WPNotifications;
    });

})(jQuery);
