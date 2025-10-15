<?php
/**
 * EstacionaFácil - Footer Padrão
 * 
 * Footer utilizado em todas as páginas do painel
 * Inclui scripts JavaScript e informações do sistema
 * 
 * @author Dante Testa (https://dantetesta.com.br)
 * @version 1.0.0
 * @date 14/10/2025 19:54
 */

// Prevenir acesso direto
if (!defined('ESTACIONAFACIL')) {
    die('Acesso negado');
}
?>
        </main>
        
        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 p-4 mt-8 no-print">
            <div class="text-center text-gray-600 text-sm">
                <p>
                    <?php echo htmlspecialchars($systemName ?? SYSTEM_NAME); ?> v<?php echo SYSTEM_VERSION; ?> 
                    &copy; <?php echo date('Y'); ?> - 
                    Desenvolvido por 
                    <a href="<?php echo SYSTEM_AUTHOR_URL; ?>" target="_blank" class="text-blue-600 hover:underline font-semibold">
                        <?php echo SYSTEM_AUTHOR; ?>
                    </a>
                </p>
            </div>
        </footer>
    </div>
    
    <!-- Scripts JavaScript -->
    <script>
        /**
         * Atualiza data e hora em tempo real
         */
        function updateDateTime() {
            const now = new Date();
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            const dateTimeString = now.toLocaleDateString('pt-BR', options);
            const element = document.getElementById('currentDateTime');
            if (element) {
                element.textContent = dateTimeString;
            }
        }
        
        // Atualizar a cada minuto
        updateDateTime();
        setInterval(updateDateTime, 60000);
        
        /**
         * Toggle do menu mobile
         */
        function toggleMobileMenu() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileMenuOverlay');
            
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }
        
        /**
         * Toggle do menu do usuário
         */
        function toggleUserMenu() {
            const menu = document.getElementById('userMenu');
            menu.classList.toggle('hidden');
        }
        
        // Fechar menu do usuário ao clicar fora
        document.addEventListener('click', function(event) {
            const userMenu = document.getElementById('userMenu');
            const userButton = event.target.closest('button[onclick="toggleUserMenu()"]');
            
            if (!userButton && !userMenu.contains(event.target)) {
                userMenu.classList.add('hidden');
            }
        });
        
        /**
         * Confirmação antes de excluir
         */
        function confirmDelete(message = 'Tem certeza que deseja excluir este registro?') {
            return confirm(message);
        }
        
        /**
         * Formata valor monetário
         */
        function formatMoney(value) {
            return 'R$ ' + parseFloat(value).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
        
        /**
         * Formata placa de veículo
         */
        function formatPlate(input) {
            let value = input.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            
            // Formato antigo: ABC-1234
            if (value.length <= 7 && /^[A-Z]{0,3}[0-9]{0,4}$/.test(value)) {
                if (value.length > 3) {
                    value = value.slice(0, 3) + '-' + value.slice(3);
                }
            }
            // Formato Mercosul: ABC1D23
            else if (value.length > 7) {
                value = value.slice(0, 7);
            }
            
            input.value = value;
        }
        
        /**
         * Formata telefone
         */
        function formatPhone(input) {
            let value = input.value.replace(/\D/g, '');
            
            if (value.length <= 10) {
                // Fixo: (11) 3456-7890
                value = value.replace(/^(\d{2})(\d{4})(\d{4})$/, '($1) $2-$3');
            } else {
                // Celular: (11) 98765-4321
                value = value.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
            }
            
            input.value = value;
        }
        
        /**
         * Formata valor monetário no input
         */
        function formatMoneyInput(input) {
            let value = input.value.replace(/\D/g, '');
            value = (parseInt(value) / 100).toFixed(2);
            input.value = value;
        }
        
        /**
         * Validação de formulário
         */
        function validateForm(formId) {
            const form = document.getElementById(formId);
            if (!form) return true;
            
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('border-red-500');
                    isValid = false;
                } else {
                    field.classList.remove('border-red-500');
                }
            });
            
            if (!isValid) {
                alert('Por favor, preencha todos os campos obrigatórios.');
            }
            
            return isValid;
        }
        
        /**
         * Máscara de data (dd/mm/aaaa)
         */
        function maskDate(input) {
            let value = input.value.replace(/\D/g, '');
            
            if (value.length >= 8) {
                value = value.slice(0, 8);
                value = value.replace(/^(\d{2})(\d{2})(\d{4})$/, '$1/$2/$3');
            } else if (value.length >= 4) {
                value = value.replace(/^(\d{2})(\d{2})/, '$1/$2/');
            } else if (value.length >= 2) {
                value = value.replace(/^(\d{2})/, '$1/');
            }
            
            input.value = value;
        }
        
        /**
         * Auto-submit do formulário após delay
         */
        let searchTimeout;
        function autoSearch(formId, delay = 500) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                document.getElementById(formId).submit();
            }, delay);
        }
        
        /**
         * Imprimir página
         */
        function printPage() {
            window.print();
        }
        
        /**
         * Exportar tabela para CSV
         */
        function exportTableToCSV(tableId, filename = 'relatorio.csv') {
            const table = document.getElementById(tableId);
            if (!table) return;
            
            let csv = [];
            const rows = table.querySelectorAll('tr');
            
            rows.forEach(row => {
                const cols = row.querySelectorAll('td, th');
                const rowData = Array.from(cols).map(col => {
                    return '"' + col.textContent.trim().replace(/"/g, '""') + '"';
                });
                csv.push(rowData.join(','));
            });
            
            const csvContent = csv.join('\n');
            const blob = new Blob(['\ufeff' + csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            
            if (link.download !== undefined) {
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', filename);
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }
        
        /**
         * Mostrar loading
         */
        function showLoading(message = 'Carregando...') {
            const loading = document.createElement('div');
            loading.id = 'loadingOverlay';
            loading.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
            loading.innerHTML = `
                <div class="bg-white rounded-lg p-6 flex items-center space-x-4">
                    <i class="fas fa-spinner fa-spin text-blue-600 text-3xl"></i>
                    <span class="text-gray-800 font-semibold">${message}</span>
                </div>
            `;
            document.body.appendChild(loading);
        }
        
        /**
         * Esconder loading
         */
        function hideLoading() {
            const loading = document.getElementById('loadingOverlay');
            if (loading) {
                loading.remove();
            }
        }
        
        /**
         * Notificação toast
         */
        function showToast(message, type = 'info') {
            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                warning: 'bg-yellow-500',
                info: 'bg-blue-500'
            };
            
            const icons = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-circle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };
            
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3 z-50 slide-in`;
            toast.innerHTML = `
                <i class="fas ${icons[type]} text-xl"></i>
                <span>${message}</span>
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
        
        /**
         * Copiar para clipboard
         */
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                showToast('Copiado para a área de transferência!', 'success');
            }).catch(() => {
                showToast('Erro ao copiar', 'error');
            });
        }
        
        /**
         * Atalhos de teclado
         */
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + P = Imprimir
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                printPage();
            }
            
            // ESC = Fechar modais/menus
            if (e.key === 'Escape') {
                document.getElementById('userMenu')?.classList.add('hidden');
                toggleMobileMenu();
            }
        });
        
        /**
         * Prevenir múltiplos submits
         */
        document.querySelectorAll('form').forEach(form => {
            let submitted = false;
            form.addEventListener('submit', function(e) {
                if (submitted) {
                    e.preventDefault();
                    return false;
                }
                submitted = true;
                
                // Resetar após 3 segundos (caso haja erro)
                setTimeout(() => {
                    submitted = false;
                }, 3000);
            });
        });
        
        /**
         * Auto-focus no primeiro input
         */
        document.addEventListener('DOMContentLoaded', function() {
            const firstInput = document.querySelector('input:not([type="hidden"]):not([readonly])');
            if (firstInput && !document.querySelector('input[autofocus]')) {
                firstInput.focus();
            }
        });
        
        /**
         * Smooth scroll para âncoras
         */
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
        
        /**
         * Lazy loading de imagens
         */
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        observer.unobserve(img);
                    }
                });
            });
            
            document.querySelectorAll('img.lazy').forEach(img => {
                imageObserver.observe(img);
            });
        }
    </script>
    
    <!-- Scripts adicionais da página -->
    <?php if (isset($additionalScripts)): ?>
        <?php echo $additionalScripts; ?>
    <?php endif; ?>
</body>
</html>
