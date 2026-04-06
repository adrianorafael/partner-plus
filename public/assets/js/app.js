/**
 * Partner Plus - JavaScript principal
 * Vanilla JS para interações de UI.
 */

'use strict';

// -------------------------------------------------------
// Toggle visibilidade de senha
// -------------------------------------------------------
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;
    input.type = input.type === 'password' ? 'text' : 'password';
}

// -------------------------------------------------------
// Máscaras de input
// -------------------------------------------------------
function maskCNPJ(value) {
    let v = value.replace(/\D/g, '').substring(0, 14);
    v = v.replace(/^(\d{2})(\d)/, '$1.$2');
    v = v.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
    v = v.replace(/\.(\d{3})(\d)/, '.$1/$2');
    v = v.replace(/(\d{4})(\d)/, '$1-$2');
    return v;
}

function maskPhone(value) {
    let v = value.replace(/\D/g, '').substring(0, 11);
    if (v.length <= 10) {
        return v.replace(/^(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
    }
    return v.replace(/^(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
}

// Aplicar máscaras automaticamente ao carregar
document.addEventListener('DOMContentLoaded', () => {

    // CNPJ
    document.querySelectorAll('[name="cnpj"]').forEach(input => {
        input.addEventListener('input', function () {
            this.value = maskCNPJ(this.value);
        });
    });

    // Telefone
    document.querySelectorAll('[name="phone"], [name="contact_phone"]').forEach(input => {
        input.addEventListener('input', function () {
            this.value = maskPhone(this.value);
        });
    });

    // -------------------------------------------------------
    // Fechar dropdowns ao clicar fora
    // -------------------------------------------------------
    document.addEventListener('click', (e) => {
        document.querySelectorAll('[data-dropdown]').forEach(wrapper => {
            if (!wrapper.contains(e.target)) {
                wrapper.querySelector('.dropdown-menu')?.classList.add('hidden');
            }
        });
    });

    // -------------------------------------------------------
    // Auto-dismiss flash messages após 5 segundos
    // -------------------------------------------------------
    document.querySelectorAll('[data-flash]').forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity 0.3s ease';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 300);
        }, 5000);
    });

    // -------------------------------------------------------
    // Confirmação de ações destrutivas (já via onsubmit inline)
    // -------------------------------------------------------

    // -------------------------------------------------------
    // Validação de data final >= data inicial
    // -------------------------------------------------------
    const startDateInput = document.getElementById('start_date');
    const endDateInput   = document.getElementById('end_date');
    if (startDateInput && endDateInput) {
        startDateInput.addEventListener('change', function () {
            endDateInput.min = this.value;
            if (endDateInput.value && endDateInput.value < this.value) {
                endDateInput.value = this.value;
            }
        });
        // Definir mínimo inicial
        if (startDateInput.value) {
            endDateInput.min = startDateInput.value;
        }
    }

    // -------------------------------------------------------
    // Highlight da linha ativa na nav
    // -------------------------------------------------------
    const currentPath = window.location.pathname;
    document.querySelectorAll('nav a[href]').forEach(link => {
        const href = link.getAttribute('href');
        if (href && href !== '/' && currentPath.startsWith(new URL(href, window.location.origin).pathname)) {
            link.classList.add('active-nav');
        }
    });

    // -------------------------------------------------------
    // Mobile menu toggle
    // -------------------------------------------------------
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu    = document.getElementById('mobile-menu');
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    }

});

// -------------------------------------------------------
// Feedback visual de submit (evita duplo clique)
// -------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function () {
            const btn = this.querySelector('button[type="submit"]');
            if (btn && !btn.dataset.noDisable) {
                btn.disabled = true;
                const original = btn.innerHTML;
                btn.innerHTML = '<svg class="w-4 h-4 animate-spin inline mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Aguarde...';

                // Re-enable após 10s (fallback para erros de validação)
                setTimeout(() => {
                    btn.disabled = false;
                    btn.innerHTML = original;
                }, 10000);
            }
        });
    });
});
