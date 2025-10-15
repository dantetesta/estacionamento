/**
 * EstacionaFácil - Scripts Principais
 * 
 * @author Dante Testa (https://dantetesta.com.br)
 * @version 1.0.0
 * @date 14/10/2025 19:54
 */

// Inicialização quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    console.log('EstacionaFácil v1.0.0 - Sistema carregado');
    
    // Auto-focus no primeiro input visível
    autoFocusFirstInput();
    
    // Inicializar tooltips
    initTooltips();
    
    // Prevenir múltiplos submits
    preventMultipleSubmits();
});

/**
 * Auto-focus no primeiro input da página
 */
function autoFocusFirstInput() {
    const firstInput = document.querySelector('input:not([type="hidden"]):not([readonly]):not([disabled])');
    if (firstInput && !document.querySelector('input[autofocus]')) {
        setTimeout(() => firstInput.focus(), 100);
    }
}

/**
 * Inicializar tooltips
 */
function initTooltips() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

/**
 * Prevenir múltiplos submits de formulários
 */
function preventMultipleSubmits() {
    document.querySelectorAll('form').forEach(form => {
        let submitted = false;
        form.addEventListener('submit', function(e) {
            if (submitted) {
                e.preventDefault();
                return false;
            }
            submitted = true;
            
            // Resetar após 3 segundos
            setTimeout(() => {
                submitted = false;
            }, 3000);
        });
    });
}

/**
 * Mostrar tooltip
 */
function showTooltip(e) {
    const text = e.target.getAttribute('data-tooltip');
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = text;
    document.body.appendChild(tooltip);
    
    const rect = e.target.getBoundingClientRect();
    tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
}

/**
 * Esconder tooltip
 */
function hideTooltip() {
    const tooltip = document.querySelector('.tooltip');
    if (tooltip) {
        tooltip.remove();
    }
}

// Exportar funções globais
window.EstacionaFacil = {
    version: '1.0.0',
    author: 'Dante Testa',
    website: 'https://dantetesta.com.br'
};
