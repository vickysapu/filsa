/**
 * LUXURY CORE JS - CLEAN EDITION
 * Minimalist Component Handler
 */

document.addEventListener('DOMContentLoaded', () => {
    
    // 1. SIDEBAR TOGGLE
    const sidebarToggle = document.querySelector('.lux-sidebar-toggle');
    const sidebar = document.querySelector('.lux-sidebar');
    const mainContent = document.querySelector('.lux-main-content');
    const overlay = document.querySelector('.lux-sidebar-overlay');

    function toggleSidebar() {
        if (window.innerWidth <= 768) {
            sidebar?.classList.toggle('active');
            overlay?.classList.toggle('active');
        } else {
            sidebar?.classList.toggle('hidden');
            mainContent?.classList.toggle('expanded');
        }
    }

    sidebarToggle?.addEventListener('click', toggleSidebar);
    overlay?.addEventListener('click', toggleSidebar);

    // 2. MODAL SYSTEM
    const modalTriggers = document.querySelectorAll('[data-lux-modal]');
    const modalOverlays = document.querySelectorAll('.lux-modal-overlay');

    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', () => {
            const modalId = trigger.getAttribute('data-lux-modal');
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        });
    });

    modalOverlays.forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });

        const closeBtn = overlay.querySelector('.lux-modal-close');
        closeBtn?.addEventListener('click', () => {
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && overlay.classList.contains('active')) {
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });

    // 3. DROPDOWN SYSTEM
    const dropdowns = document.querySelectorAll('.lux-dropdown');

    dropdowns.forEach(dropdown => {
        const trigger = dropdown.querySelector('.lux-dropdown-trigger');
        
        trigger?.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdowns.forEach(d => {
                if (d !== dropdown) d.classList.remove('active');
            });
            dropdown.classList.toggle('active');
        });
    });

    document.addEventListener('click', () => {
        dropdowns.forEach(d => d.classList.remove('active'));
    });

    const tabContainers = document.querySelectorAll('.lux-tabs');

    tabContainers.forEach(container => {
        const tabs = container.querySelectorAll('.lux-tab');
        const contents = container.querySelectorAll('.lux-tab-content');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const target = tab.getAttribute('data-tab');
                
                tabs.forEach(t => t.classList.remove('active'));
                contents.forEach(c => c.classList.remove('active'));

                tab.classList.add('active');
                const content = document.getElementById(target);
                content?.classList.add('active');
            });
        });
    });

    // 5. ALERTS AUTO-CLOSE
    const alerts = document.querySelectorAll('.lux-alert');
    
    alerts.forEach(alert => {
        const closeBtn = alert.querySelector('.lux-alert-close');
        
        closeBtn?.addEventListener('click', () => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 200);
        });

        if (alert.hasAttribute('data-auto-close')) {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 200);
            }, 5000);
        }
    });

    // 6. TOAST NOTIFICATION
    window.LuxToast = {
        container: null,
        
        init() {
            if (!this.container) {
                this.container = document.createElement('div');
                this.container.className = 'lux-toast-container';
                document.body.appendChild(this.container);
            }
        },

        show(message, type = 'info', duration = 3000) {
            this.init();
            
            const toast = document.createElement('div');
            toast.className = `lux-toast lux-toast-${type}`;
            toast.innerHTML = `
                <span>${message}</span>
                <button class="lux-toast-close">&times;</button>
            `;
            
            this.container.appendChild(toast);
            
            toast.querySelector('.lux-toast-close').addEventListener('click', () => {
                toast.remove();
            });
            
            setTimeout(() => toast.remove(), duration);
        },

        success(message) { this.show(message, 'success'); },
        warning(message) { this.show(message, 'warning'); },
        danger(message) { this.show(message, 'danger'); },
        info(message) { this.show(message, 'info'); }
    };

    // 7. ACCORDION SYSTEM
    const accordions = document.querySelectorAll('.lux-accordion-header');

    accordions.forEach(header => {
        header.addEventListener('click', () => {
            const content = header.nextElementSibling;
            const isActive = header.classList.contains('active');
            
            accordions.forEach(h => {
                h.classList.remove('active');
                h.nextElementSibling.classList.remove('active');
            });
            
            if (!isActive) {
                header.classList.add('active');
                content.classList.add('active');
            }
        });
    });

    // 8. FORM VALIDATION
    const inputs = document.querySelectorAll('.lux-input, .lux-textarea, .lux-select');

    inputs.forEach(input => {
        input.addEventListener('blur', () => {
            if (input.hasAttribute('required') && !input.value) {
                input.classList.add('lux-input-danger');
            } else {
                input.classList.remove('lux-input-danger');
            }
        });

        input.addEventListener('input', () => {
            input.classList.remove('lux-input-danger');
        });
    });

    // 9. CONFIRMATION DIALOG
    window.LuxConfirm = {
        show(message, callback) {
            const overlay = document.createElement('div');
            overlay.className = 'lux-modal-overlay active';
            overlay.innerHTML = `
                <div class="lux-modal lux-modal-sm">
                    <div class="lux-modal-header">
                        <h3 class="lux-modal-title">Konfirmasi</h3>
                        <button class="lux-modal-close">&times;</button>
                    </div>
                    <div class="lux-modal-body">
                        <p>${message}</p>
                    </div>
                    <div class="lux-modal-footer">
                        <button class="lux-btn lux-btn-ghost" id="lux-confirm-cancel">Batal</button>
                        <button class="lux-btn lux-btn-danger" id="lux-confirm-ok">Hapus</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(overlay);
            document.body.style.overflow = 'hidden';

            overlay.querySelector('.lux-modal-close').addEventListener('click', () => this.close(overlay, false));
            overlay.querySelector('#lux-confirm-cancel').addEventListener('click', () => this.close(overlay, false));
            overlay.querySelector('#lux-confirm-ok').addEventListener('click', () => {
                this.close(overlay, true);
                if (callback) callback();
            });

            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) this.close(overlay, false);
            });
        },

        close(overlay, confirmed) {
            overlay.classList.remove('active');
            document.body.style.overflow = '';
            setTimeout(() => overlay.remove(), 200);
        }
    };

    document.querySelectorAll('[data-lux-confirm]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const message = btn.getAttribute('data-lux-confirm') || 'Apakah Anda yakin?';
            const href = btn.getAttribute('href');
            
            LuxConfirm.show(message, () => {
                if (href) window.location.href = href;
            });
        });
    });

    console.log('Luxury Core System Loaded - Clean Edition');

    function setupModals() {
        document.querySelectorAll('[data-modal-target]').forEach(trigger => {
            trigger.addEventListener('click', () => {
                const modalId = trigger.getAttribute('data-modal-target');
                const modal = document.querySelector(modalId);
                if (modal) openModal(modal);
            });
        });

        document.querySelectorAll('[data-modal-close]').forEach(closeBtn => {
            closeBtn.addEventListener('click', () => {
                const modal = closeBtn.closest('.lux-modal-overlay');
                if (modal) closeModal(modal);
            });
        });

        document.querySelectorAll('.lux-modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) {
                    closeModal(overlay);
                }
            });
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const activeModal = document.querySelector('.lux-modal-overlay.active');
                if (activeModal) closeModal(activeModal);
            }
        });
    }

    function openModal(modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        const firstInput = modal.querySelector('input, select, textarea');
        if(firstInput) setTimeout(() => firstInput.focus(), 100);
    }

    function closeModal(modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    setupModals();

    console.log("Luxury Core System Loaded.");
    
    
});

