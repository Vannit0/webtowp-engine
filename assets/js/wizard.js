/**
 * WebToWP Engine - Onboarding Wizard JavaScript
 */

jQuery(document).ready(function($) {
    'use strict';

    // Guardar datos del formulario en localStorage
    const wizardData = {
        save: function(step, data) {
            const currentData = this.load();
            currentData[step] = data;
            localStorage.setItem('w2wp_wizard_data', JSON.stringify(currentData));
        },

        load: function() {
            const data = localStorage.getItem('w2wp_wizard_data');
            return data ? JSON.parse(data) : {};
        },

        clear: function() {
            localStorage.removeItem('w2wp_wizard_data');
        }
    };

    // Auto-guardar datos del formulario
    $('.webtowp-wizard-form input, .webtowp-wizard-form select, .webtowp-wizard-form textarea').on('change', function() {
        const step = getCurrentStep();
        const formData = {};
        
        $('.webtowp-wizard-form input, .webtowp-wizard-form select, .webtowp-wizard-form textarea').each(function() {
            const $field = $(this);
            const name = $field.attr('name');
            
            if (name) {
                if ($field.is(':checkbox')) {
                    formData[name] = $field.is(':checked');
                } else {
                    formData[name] = $field.val();
                }
            }
        });

        wizardData.save(step, formData);
    });

    // Restaurar datos guardados
    function restoreFormData() {
        const step = getCurrentStep();
        const data = wizardData.load()[step];

        if (data) {
            Object.keys(data).forEach(function(name) {
                const $field = $('[name="' + name + '"]');
                
                if ($field.is(':checkbox')) {
                    $field.prop('checked', data[name]);
                } else {
                    $field.val(data[name]);
                }
            });
        }
    }

    function getCurrentStep() {
        const urlParams = new URLSearchParams(window.location.search);
        return parseInt(urlParams.get('step')) || 1;
    }

    // Restaurar datos al cargar
    restoreFormData();

    // Copiar API Key
    window.copyApiKey = function() {
        const apiKey = $('#generated-api-key').text();
        
        // Crear elemento temporal para copiar
        const $temp = $('<textarea>');
        $('body').append($temp);
        $temp.val(apiKey).select();
        document.execCommand('copy');
        $temp.remove();

        // Mostrar feedback
        const $button = $('.copy-button');
        const originalText = $button.text();
        $button.text('✅ Copiado!');
        
        setTimeout(function() {
            $button.text(originalText);
        }, 2000);
    };

    // Validación de formularios
    $('.webtowp-wizard-navigation .webtowp-button-primary').on('click', function(e) {
        const step = getCurrentStep();
        
        if (step === 4) { // Paso de deployment
            const webhookUrl = $('[name="w2wp_webhook_url"]').val();
            const frontendUrl = $('[name="w2wp_frontend_url"]').val();

            if (webhookUrl && !isValidUrl(webhookUrl)) {
                e.preventDefault();
                alert('Por favor, introduce una URL de webhook válida');
                return false;
            }

            if (frontendUrl && !isValidUrl(frontendUrl)) {
                e.preventDefault();
                alert('Por favor, introduce una URL de frontend válida');
                return false;
            }
        }
    });

    function isValidUrl(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }

    // Animaciones de progreso
    $('.webtowp-wizard-progress-bar').each(function() {
        const $bar = $(this);
        const width = $bar.css('width');
        $bar.css('width', '0');
        
        setTimeout(function() {
            $bar.css('width', width);
        }, 300);
    });

    // Toggle de módulos
    $('.module-card input[type="checkbox"]').on('change', function() {
        const $card = $(this).closest('.module-card');
        
        if ($(this).is(':checked')) {
            $card.css({
                'border-color': '#667eea',
                'background': '#f0f6fc'
            });
        } else {
            $card.css({
                'border-color': '#e2e8f0',
                'background': '#f8fafc'
            });
        }
    });

    // Inicializar estado de módulos
    $('.module-card input[type="checkbox"]:checked').each(function() {
        $(this).closest('.module-card').css({
            'border-color': '#667eea',
            'background': '#f0f6fc'
        });
    });

    // Limpiar datos al completar
    $('form[action*="complete_onboarding"]').on('submit', function() {
        wizardData.clear();
    });

    // Confirmación al omitir
    $('form[action*="skip_onboarding"]').on('submit', function(e) {
        if (!confirm('¿Estás seguro de que quieres omitir la configuración inicial?')) {
            e.preventDefault();
            return false;
        }
    });

    // Keyboard navigation
    $(document).on('keydown', function(e) {
        const step = getCurrentStep();
        
        // Flecha derecha o Enter = Siguiente
        if (e.key === 'ArrowRight' || (e.key === 'Enter' && !$(e.target).is('input, textarea'))) {
            const $nextButton = $('.webtowp-wizard-navigation .webtowp-button-primary');
            if ($nextButton.length && step < 5) {
                window.location.href = $nextButton.attr('href');
            }
        }
        
        // Flecha izquierda = Anterior
        if (e.key === 'ArrowLeft' && step > 1) {
            const $prevButton = $('.webtowp-wizard-navigation .webtowp-button-secondary');
            if ($prevButton.length) {
                window.location.href = $prevButton.attr('href');
            }
        }
    });

    // Tooltip para campos de formulario
    $('[data-tooltip]').hover(
        function() {
            const tooltip = $(this).attr('data-tooltip');
            const $tooltip = $('<div class="wizard-tooltip">' + tooltip + '</div>');
            $('body').append($tooltip);
            
            const offset = $(this).offset();
            $tooltip.css({
                top: offset.top - $tooltip.outerHeight() - 10,
                left: offset.left + ($(this).outerWidth() / 2) - ($tooltip.outerWidth() / 2)
            });
        },
        function() {
            $('.wizard-tooltip').remove();
        }
    );

    // Animación de entrada para cards
    $('.feature-card, .module-card, .next-step-card').each(function(index) {
        $(this).css({
            'animation-delay': (index * 0.1) + 's',
            'animation': 'fadeInUp 0.5s ease-out forwards'
        });
    });
});
