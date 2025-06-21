// Courier Dash JavaScript Application
// Main application JavaScript file

class CourierDashApp {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.initializeComponents();
        this.setupAjaxDefaults();
    }

    setupEventListeners() {
        // Global click handler for dynamic content
        document.addEventListener('click', (e) => {
            // Handle dropdown toggles
            if (e.target.matches('[data-dropdown-toggle]')) {
                this.toggleDropdown(e.target.getAttribute('data-dropdown-toggle'));
            }

            // Handle modal triggers
            if (e.target.matches('[data-modal-toggle]')) {
                this.toggleModal(e.target.getAttribute('data-modal-toggle'));
            }

            // Handle tab switching
            if (e.target.matches('[data-tab-target]')) {
                this.switchTab(e.target);
            }
        });

        // Form validation
        document.addEventListener('submit', (e) => {
            if (e.target.matches('form[data-validate]')) {
                if (!this.validateForm(e.target)) {
                    e.preventDefault();
                }
            }
        });

        // Auto-hide alerts
        this.setupAlertAutoHide();
    }

    initializeComponents() {
        // Initialize tooltips
        this.initTooltips();
        
        // Initialize form enhancements
        this.initFormEnhancements();
        
        // Initialize search functionality
        this.initSearch();
        
        // Initialize real-time notifications
        this.initNotifications();
    }

    setupAjaxDefaults() {
        // Set default headers for AJAX requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (csrfToken) {
            // Add CSRF token to all AJAX requests
            const originalFetch = window.fetch;
            window.fetch = function(url, options = {}) {
                if (options.method && options.method.toUpperCase() !== 'GET') {
                    options.headers = {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        ...options.headers
                    };
                }
                return originalFetch(url, options);
            };
        }
    }

    // Toast Notifications
    showToast(message, type = 'info', duration = 3000) {
        const toastContainer = document.getElementById('toast-container') || this.createToastContainer();
        
        const toast = document.createElement('div');
        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            warning: 'bg-yellow-500',
            info: 'bg-blue-500'
        };
        
        toast.className = `${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg opacity-0 transform translate-x-full transition-all duration-300 mb-2`;
        toast.innerHTML = `
            <div class="flex items-center justify-between">
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;
        
        toastContainer.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.remove('opacity-0', 'translate-x-full');
        }, 100);
        
        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-x-full');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }

    createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'fixed top-4 right-4 z-50 space-y-2';
        document.body.appendChild(container);
        return container;
    }

    // Modal Management
    toggleModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            if (modal.classList.contains('hidden')) {
                this.showModal(modalId);
            } else {
                this.hideModal(modalId);
            }
        }
    }

    showModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            // Focus first input
            const firstInput = modal.querySelector('input, textarea, select, button');
            if (firstInput) {
                firstInput.focus();
            }
        }
    }

    hideModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }

    // Dropdown Management
    toggleDropdown(dropdownId) {
        const dropdown = document.getElementById(dropdownId);
        if (dropdown) {
            const isHidden = dropdown.classList.contains('hidden');
            
            // Close all other dropdowns
            document.querySelectorAll('[data-dropdown]').forEach(d => {
                if (d !== dropdown) {
                    d.classList.add('hidden');
                }
            });
            
            if (isHidden) {
                dropdown.classList.remove('hidden');
            } else {
                dropdown.classList.add('hidden');
            }
        }
    }

    // Tab Management
    switchTab(tabElement) {
        const targetId = tabElement.getAttribute('data-tab-target');
        const tabGroup = tabElement.closest('[data-tab-group]');
        
        if (tabGroup && targetId) {
            // Hide all tab content in group
            tabGroup.querySelectorAll('[data-tab-content]').forEach(content => {
                content.classList.add('hidden');
            });
            
            // Remove active class from all tabs
            tabGroup.querySelectorAll('[data-tab-target]').forEach(tab => {
                tab.classList.remove('border-blue-500', 'text-blue-600');
                tab.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Show target content
            const targetContent = document.getElementById(targetId);
            if (targetContent) {
                targetContent.classList.remove('hidden');
            }
            
            // Add active class to clicked tab
            tabElement.classList.remove('border-transparent', 'text-gray-500');
            tabElement.classList.add('border-blue-500', 'text-blue-600');
        }
    }

    // Form Validation
    validateForm(form) {
        let isValid = true;
        const errors = [];

        // Clear previous errors
        form.querySelectorAll('.error-message').forEach(error => error.remove());
        form.querySelectorAll('.border-red-500').forEach(input => {
            input.classList.remove('border-red-500');
        });

        // Validate required fields
        form.querySelectorAll('[required]').forEach(input => {
            if (!input.value.trim()) {
                this.showFieldError(input, 'This field is required');
                isValid = false;
            }
        });

        // Validate email fields
        form.querySelectorAll('input[type="email"]').forEach(input => {
            if (input.value && !this.isValidEmail(input.value)) {
                this.showFieldError(input, 'Please enter a valid email address');
                isValid = false;
            }
        });

        // Validate phone fields
        form.querySelectorAll('input[type="tel"]').forEach(input => {
            if (input.value && !this.isValidPhone(input.value)) {
                this.showFieldError(input, 'Please enter a valid phone number');
                isValid = false;
            }
        });

        // Custom validation rules
        const customRules = form.getAttribute('data-validation-rules');
        if (customRules) {
            try {
                const rules = JSON.parse(customRules);
                Object.keys(rules).forEach(fieldName => {
                    const field = form.querySelector(`[name="${fieldName}"]`);
                    if (field && !this.validateCustomRule(field.value, rules[fieldName])) {
                        this.showFieldError(field, rules[fieldName].message || 'Invalid value');
                        isValid = false;
                    }
                });
            } catch (e) {
                console.error('Invalid validation rules:', e);
            }
        }

        return isValid;
    }

    showFieldError(input, message) {
        input.classList.add('border-red-500');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message text-red-500 text-sm mt-1';
        errorDiv.textContent = message;
        
        input.parentNode.appendChild(errorDiv);
    }

    isValidEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }

    isValidPhone(phone) {
        const regex = /^[\+]?[1-9][\d]{0,15}$/;
        return regex.test(phone.replace(/[\s\-\(\)]/g, ''));
    }

    validateCustomRule(value, rule) {
        switch (rule.type) {
            case 'min_length':
                return value.length >= rule.value;
            case 'max_length':
                return value.length <= rule.value;
            case 'pattern':
                return new RegExp(rule.value).test(value);
            case 'numeric':
                return !isNaN(value) && isFinite(value);
            default:
                return true;
        }
    }

    // Initialize tooltips
    initTooltips() {
        document.querySelectorAll('[data-tooltip]').forEach(element => {
            element.addEventListener('mouseenter', (e) => {
                this.showTooltip(e.target);
            });
            
            element.addEventListener('mouseleave', (e) => {
                this.hideTooltip();
            });
        });
    }

    showTooltip(element) {
        const text = element.getAttribute('data-tooltip');
        const tooltip = document.createElement('div');
        tooltip.id = 'tooltip';
        tooltip.className = 'absolute bg-gray-900 text-white text-sm rounded py-1 px-2 z-50 pointer-events-none';
        tooltip.textContent = text;
        
        document.body.appendChild(tooltip);
        
        const rect = element.getBoundingClientRect();
        tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
        tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
    }

    hideTooltip() {
        const tooltip = document.getElementById('tooltip');
        if (tooltip) {
            tooltip.remove();
        }
    }

    // Form enhancements
    initFormEnhancements() {
        // Auto-resize textareas
        document.querySelectorAll('textarea[data-auto-resize]').forEach(textarea => {
            textarea.addEventListener('input', () => {
                textarea.style.height = 'auto';
                textarea.style.height = textarea.scrollHeight + 'px';
            });
        });

        // Character counters
        document.querySelectorAll('input[data-max-length], textarea[data-max-length]').forEach(input => {
            const maxLength = parseInt(input.getAttribute('data-max-length'));
            const counter = document.createElement('div');
            counter.className = 'text-sm text-gray-500 mt-1 text-right';
            counter.textContent = `0/${maxLength}`;
            
            input.parentNode.appendChild(counter);
            
            input.addEventListener('input', () => {
                const currentLength = input.value.length;
                counter.textContent = `${currentLength}/${maxLength}`;
                
                if (currentLength > maxLength * 0.9) {
                    counter.classList.add('text-yellow-500');
                } else {
                    counter.classList.remove('text-yellow-500');
                }
                
                if (currentLength >= maxLength) {
                    counter.classList.add('text-red-500');
                } else {
                    counter.classList.remove('text-red-500');
                }
            });
        });
    }

    // Search functionality
    initSearch() {
        const searchInputs = document.querySelectorAll('input[data-search]');
        searchInputs.forEach(input => {
            let timeout;
            input.addEventListener('input', (e) => {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    this.performSearch(e.target);
                }, 300);
            });
        });
    }

    performSearch(input) {
        const query = input.value.trim();
        const target = input.getAttribute('data-search');
        
        if (query.length >= 2) {
            fetch(`/api/search?q=${encodeURIComponent(query)}&type=${target}`)
                .then(response => response.json())
                .then(data => {
                    this.displaySearchResults(data, input);
                })
                .catch(error => {
                    console.error('Search error:', error);
                });
        } else {
            this.hideSearchResults(input);
        }
    }

    displaySearchResults(results, input) {
        let resultsContainer = input.parentNode.querySelector('.search-results');
        if (!resultsContainer) {
            resultsContainer = document.createElement('div');
            resultsContainer.className = 'search-results absolute top-full left-0 right-0 bg-white border border-gray-300 rounded-b-lg shadow-lg z-10 max-h-60 overflow-y-auto';
            input.parentNode.appendChild(resultsContainer);
        }
        
        if (results.length > 0) {
            resultsContainer.innerHTML = results.map(result => `
                <div class="p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0" onclick="selectSearchResult('${result.id}', '${result.title}', this)">
                    <div class="font-medium">${result.title}</div>
                    <div class="text-sm text-gray-600">${result.subtitle || ''}</div>
                </div>
            `).join('');
        } else {
            resultsContainer.innerHTML = '<div class="p-3 text-gray-500 text-center">No results found</div>';
        }
        
        resultsContainer.style.display = 'block';
    }

    hideSearchResults(input) {
        const resultsContainer = input.parentNode.querySelector('.search-results');
        if (resultsContainer) {
            resultsContainer.style.display = 'none';
        }
    }

    // Notifications
    initNotifications() {
        if ('Notification' in window) {
            // Request permission for notifications
            if (Notification.permission === 'default') {
                Notification.requestPermission();
            }
        }

        // Poll for new notifications every 30 seconds
        setInterval(() => {
            this.checkForNotifications();
        }, 30000);
    }

    checkForNotifications() {
        if (document.hidden) return; // Don't check if page is not visible
        
        fetch('/api/notifications/check')
            .then(response => response.json())
            .then(data => {
                if (data.new_notifications && data.new_notifications.length > 0) {
                    this.handleNewNotifications(data.new_notifications);
                }
            })
            .catch(error => {
                console.error('Notification check error:', error);
            });
    }

    handleNewNotifications(notifications) {
        notifications.forEach(notification => {
            // Show toast notification
            this.showToast(notification.title, notification.type || 'info');
            
            // Show browser notification if permitted
            if (Notification.permission === 'granted') {
                new Notification(notification.title, {
                    body: notification.message,
                    icon: '/public/assets/images/logo.png'
                });
            }
            
            // Update notification count in header
            this.updateNotificationCount();
        });
    }

    updateNotificationCount() {
        const badge = document.querySelector('.notification-badge');
        if (badge) {
            fetch('/api/notifications/count')
                .then(response => response.json())
                .then(data => {
                    if (data.count > 0) {
                        badge.textContent = data.count > 9 ? '9+' : data.count;
                        badge.style.display = 'flex';
                    } else {
                        badge.style.display = 'none';
                    }
                });
        }
    }

    setupAlertAutoHide() {
        document.querySelectorAll('.alert-auto-hide').forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
    }

    // Utility functions
    formatCurrency(amount, currency = 'USD') {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency
        }).format(amount);
    }

    formatDate(date, options = {}) {
        const defaultOptions = {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        };
        
        return new Intl.DateTimeFormat('en-US', { ...defaultOptions, ...options }).format(new Date(date));
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}

// Global functions for backward compatibility
function showToast(message, type = 'info') {
    window.courierApp.showToast(message, type);
}

function selectSearchResult(id, title, element) {
    const input = element.closest('.relative').querySelector('input');
    input.value = title;
    input.setAttribute('data-selected-id', id);
    window.courierApp.hideSearchResults(input);
}

// Initialize the app when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.courierApp = new CourierDashApp();
});

// Close dropdowns when clicking outside
document.addEventListener('click', (e) => {
    if (!e.target.closest('[data-dropdown-toggle]') && !e.target.closest('[data-dropdown]')) {
        document.querySelectorAll('[data-dropdown]').forEach(dropdown => {
            dropdown.classList.add('hidden');
        });
    }
});

// Handle escape key for modals and dropdowns
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        // Close modals
        document.querySelectorAll('.modal:not(.hidden)').forEach(modal => {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        });
        
        // Close dropdowns
        document.querySelectorAll('[data-dropdown]:not(.hidden)').forEach(dropdown => {
            dropdown.classList.add('hidden');
        });
    }
});

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CourierDashApp;
}
