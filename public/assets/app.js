/**
 * CED Portal - Enhanced JavaScript
 * FluxBB-inspired UX improvements
 */

(function() {
    'use strict';

    // ============================================================================
    // UTILITY FUNCTIONS
    // ============================================================================

    function $(selector, context = document) {
        return context.querySelector(selector);
    }

    function $$(selector, context = document) {
        return Array.from(context.querySelectorAll(selector));
    }

    // ============================================================================
    // MODAL SYSTEM
    // ============================================================================

    class Modal {
        constructor(title, content, actions) {
            this.modal = this.create(title, content, actions);
        }

        create(title, content, actions) {
            const modal = document.createElement('div');
            modal.className = 'c-modal c-modal--active';
            modal.setAttribute('role', 'dialog');
            modal.setAttribute('aria-modal', 'true');
            modal.setAttribute('aria-labelledby', 'modal-title');

            let actionsHTML = '';
            if (actions && actions.length) {
                actionsHTML = '<div class="c-modal__footer">';
                actions.forEach(action => {
                    actionsHTML += `<button type="button" class="c-btn ${action.class || ''}" data-action="${action.action}">${action.label}</button>`;
                });
                actionsHTML += '</div>';
            }

            modal.innerHTML = `
                <div class="c-modal__content">
                    <div class="c-modal__header">
                        <h2 id="modal-title" class="c-modal__title">${title}</h2>
                        <button type="button" class="c-modal__close" aria-label="Close">&times;</button>
                    </div>
                    <div class="c-modal__body">
                        ${content}
                    </div>
                    ${actionsHTML}
                </div>
            `;

            document.body.appendChild(modal);

            // Event listeners
            $('.c-modal__close', modal).addEventListener('click', () => this.close());
            modal.addEventListener('click', (e) => {
                if (e.target === modal) this.close();
            });

            // Action buttons
            if (actions) {
                $$('[data-action]', modal).forEach(btn => {
                    btn.addEventListener('click', () => {
                        const action = actions.find(a => a.action === btn.dataset.action);
                        if (action && action.callback) {
                            action.callback();
                        }
                    });
                });
            }

            return modal;
        }

        close() {
            if (this.modal) {
                this.modal.remove();
            }
        }

        static confirm(title, message, onConfirm, onCancel) {
            const modal = new Modal(title, message, [
                {
                    label: 'Cancel',
                    action: 'cancel',
                    class: 'c-btn',
                    callback: () => {
                        modal.close();
                        if (onCancel) onCancel();
                    }
                },
                {
                    label: 'Confirm',
                    action: 'confirm',
                    class: 'c-btn--primary',
                    callback: () => {
                        modal.close();
                        if (onConfirm) onConfirm();
                    }
                }
            ]);
            return modal;
        }

        static alert(title, message, onClose) {
            const modal = new Modal(title, message, [
                {
                    label: 'OK',
                    action: 'ok',
                    class: 'c-btn--primary',
                    callback: () => {
                        modal.close();
                        if (onClose) onClose();
                    }
                }
            ]);
            return modal;
        }
    }

    // ============================================================================
    // CONFIRMATION DIALOGS FOR DANGEROUS ACTIONS
    // ============================================================================

    function initConfirmDialogs() {
        // Intercept form submissions that require confirmation
        $$('form[data-confirm]').forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                const message = form.dataset.confirm || 'Are you sure?';
                Modal.confirm('Confirm Action', message, () => {
                    form.submit();
                });
            });
        });

        // Intercept link clicks that require confirmation
        $$('a[data-confirm]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const message = link.dataset.confirm || 'Are you sure?';
                Modal.confirm('Confirm Action', message, () => {
                    window.location.href = link.href;
                });
            });
        });
    }

    // ============================================================================
    // FORM VALIDATION ENHANCEMENTS
    // ============================================================================

    function initFormValidation() {
        $$('.c-form').forEach(form => {
            // Real-time validation feedback
            $$('input, textarea, select', form).forEach(field => {
                field.addEventListener('blur', () => {
                    validateField(field);
                });

                field.addEventListener('input', () => {
                    // Clear error on input
                    const errorMsg = field.parentElement.querySelector('.c-form__error');
                    if (errorMsg) {
                        errorMsg.remove();
                        field.classList.remove('c-form__input--error');
                    }
                });
            });

            // Validate on submit
            form.addEventListener('submit', (e) => {
                let isValid = true;
                $$('input, textarea, select', form).forEach(field => {
                    if (!validateField(field)) {
                        isValid = false;
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    // Focus first error
                    const firstError = $('.c-form__input--error', form);
                    if (firstError) firstError.focus();
                }
            });
        });
    }

    function validateField(field) {
        // Remove existing error
        const existingError = field.parentElement.querySelector('.c-form__error');
        if (existingError) existingError.remove();
        field.classList.remove('c-form__input--error');

        // Check validity
        if (!field.checkValidity()) {
            field.classList.add('c-form__input--error');
            const error = document.createElement('div');
            error.className = 'c-form__error';
            error.textContent = field.validationMessage;
            field.parentElement.appendChild(error);
            return false;
        }

        return true;
    }

    // ============================================================================
    // TOAST NOTIFICATIONS
    // ============================================================================

    const Toast = {
        show(message, type = 'info', duration = 3000) {
            const toast = document.createElement('div');
            toast.className = `c-alert c-alert--${type}`;
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 2000;
                min-width: 250px;
                max-width: 400px;
                animation: slideIn 0.3s ease;
            `;
            toast.textContent = message;
            toast.setAttribute('role', 'alert');

            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, duration);
        },

        success(message) {
            this.show(message, 'success');
        },

        error(message) {
            this.show(message, 'error');
        },

        warning(message) {
            this.show(message, 'warning');
        },

        info(message) {
            this.show(message, 'info');
        }
    };

    // ============================================================================
    // TABLE ENHANCEMENTS
    // ============================================================================

    function initTableEnhancements() {
        // Sortable tables
        $$('.c-table__header--sortable').forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => {
                sortTable(header);
            });
        });

        // Row highlighting
        $$('.c-table__row').forEach(row => {
            row.addEventListener('mouseenter', () => {
                row.style.backgroundColor = '#f0f0f0';
            });
            row.addEventListener('mouseleave', () => {
                row.style.backgroundColor = '';
            });
        });
    }

    function sortTable(header) {
        const table = header.closest('.c-table');
        const tbody = $('.c-table__body', table);
        const rows = Array.from($$('.c-table__row', tbody));
        const columnIndex = Array.from(header.parentElement.children).indexOf(header);
        const currentOrder = header.dataset.order || 'asc';
        const newOrder = currentOrder === 'asc' ? 'desc' : 'asc';

        rows.sort((a, b) => {
            const aValue = a.children[columnIndex].textContent.trim();
            const bValue = b.children[columnIndex].textContent.trim();

            if (newOrder === 'asc') {
                return aValue.localeCompare(bValue, undefined, { numeric: true });
            } else {
                return bValue.localeCompare(aValue, undefined, { numeric: true });
            }
        });

        // Clear existing order indicators
        $$('.c-table__header--sortable', table).forEach(h => {
            h.dataset.order = '';
            h.textContent = h.textContent.replace(' ↑', '').replace(' ↓', '');
        });

        // Set new order
        header.dataset.order = newOrder;
        header.textContent += newOrder === 'asc' ? ' ↑' : ' ↓';

        // Re-append rows
        rows.forEach(row => tbody.appendChild(row));
    }

    // ============================================================================
    // MOBILE MENU TOGGLE
    // ============================================================================

    function initMobileMenu() {
        const nav = $('.c-nav');
        if (!nav) return;

        // Create mobile menu toggle button (if needed in future)
        if (window.innerWidth <= 767) {
            // Mobile menu logic can be added here if needed
        }
    }

    // ============================================================================
    // ACCESSIBILITY IMPROVEMENTS
    // ============================================================================

    function initAccessibility() {
        // Skip to main content link
        const skipLink = document.createElement('a');
        skipLink.href = '#main';
        skipLink.textContent = 'Skip to main content';
        skipLink.className = 'u-sr-only';
        skipLink.style.cssText = `
            position: absolute;
            top: -40px;
            left: 0;
            background: #000;
            color: white;
            padding: 8px;
            z-index: 100;
        `;
        skipLink.addEventListener('focus', () => {
            skipLink.style.top = '0';
        });
        skipLink.addEventListener('blur', () => {
            skipLink.style.top = '-40px';
        });
        document.body.insertBefore(skipLink, document.body.firstChild);

        // Add main ID to main element
        const main = $('.c-main');
        if (main) main.id = 'main';
    }

    // ============================================================================
    // LOADING STATES
    // ============================================================================

    function initLoadingStates() {
        $$('form').forEach(form => {
            form.addEventListener('submit', () => {
                const submitBtn = $('button[type="submit"]', form);
                if (submitBtn && !form.dataset.noLoading) {
                    submitBtn.disabled = true;
                    const originalText = submitBtn.textContent;
                    submitBtn.textContent = 'Loading...';
                    submitBtn.dataset.originalText = originalText;
                }
            });
        });
    }

    // ============================================================================
    // INITIALIZE ON DOM READY
    // ============================================================================

    document.addEventListener('DOMContentLoaded', () => {
        initConfirmDialogs();
        initFormValidation();
        initTableEnhancements();
        initMobileMenu();
        initAccessibility();
        initLoadingStates();

        // Make Modal and Toast available globally
        window.Modal = Modal;
        window.Toast = Toast;
    });

    // ============================================================================
    // CSS ANIMATIONS
    // ============================================================================

    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);

})();
