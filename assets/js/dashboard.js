/**
 * WebToWP Engine - Dashboard JavaScript
 */

jQuery(document).ready(function($) {
    'use strict';

    // Animaciones de entrada para las cards
    $('.webtowp-card, .webtowp-stat-card').each(function(index) {
        $(this).css({
            'animation-delay': (index * 0.1) + 's'
        }).addClass('webtowp-fade-in');
    });

    // Hover effects para quick links
    $('.webtowp-grid a > div').hover(
        function() {
            $(this).css({
                'transform': 'translateY(-5px)',
                'box-shadow': '0 10px 25px rgba(0, 0, 0, 0.1)',
                'border-color': '#667eea'
            });
        },
        function() {
            $(this).css({
                'transform': 'translateY(0)',
                'box-shadow': 'none',
                'border-color': 'transparent'
            });
        }
    );

    // Auto-refresh de estadísticas cada 5 minutos
    if (typeof w2wpDashboard !== 'undefined') {
        setInterval(function() {
            refreshDashboardStats();
        }, 300000); // 5 minutos
    }

    function refreshDashboardStats() {
        $.ajax({
            url: w2wpDashboard.ajaxUrl,
            type: 'POST',
            data: {
                action: 'w2wp_refresh_dashboard',
                nonce: w2wpDashboard.nonce
            },
            success: function(response) {
                if (response.success) {
                    console.log('Dashboard stats refreshed');
                    // Actualizar valores sin recargar la página
                    updateStatsUI(response.data);
                }
            }
        });
    }

    function updateStatsUI(data) {
        // Actualizar contadores con animación
        $('.webtowp-stat-value').each(function() {
            const $this = $(this);
            const newValue = data[$this.data('stat')];
            if (newValue !== undefined) {
                animateValue($this, parseInt($this.text()), newValue, 1000);
            }
        });
    }

    function animateValue($element, start, end, duration) {
        const range = end - start;
        const increment = range / (duration / 16);
        let current = start;
        
        const timer = setInterval(function() {
            current += increment;
            if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
                current = end;
                clearInterval(timer);
            }
            $element.text(Math.round(current));
        }, 16);
    }

    // Tooltips
    $('[data-tooltip]').hover(
        function() {
            const tooltip = $(this).attr('data-tooltip');
            $(this).append('<div class="webtowp-tooltip-content">' + tooltip + '</div>');
        },
        function() {
            $(this).find('.webtowp-tooltip-content').remove();
        }
    );

    // Confirmación para acciones destructivas
    $('[data-confirm]').on('click', function(e) {
        const message = $(this).attr('data-confirm');
        if (!confirm(message)) {
            e.preventDefault();
            return false;
        }
    });

    // Progress bar animation
    $('.webtowp-progress-bar').each(function() {
        const $bar = $(this);
        const width = $bar.css('width');
        $bar.css('width', '0');
        setTimeout(function() {
            $bar.css('width', width);
        }, 100);
    });
});
