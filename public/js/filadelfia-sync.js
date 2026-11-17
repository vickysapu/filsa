/**
 * Filadelfia Data Sync SDK v1.0
 * Usage: <script src="https://your-server.com/js/filadelfia-sync.js"
 *                data-token="dap_xk9m2p_..."
 *                data-table="events"
 *                data-target="#events-container"
 *                data-refresh="300"></script>
 */
(function() {
    'use strict';

    const CONFIG = {
        apiUrl: window.FILADELFIA_API_URL || 'https://your-server.com/api/v1/data',
        defaultRefresh: 300, // 5 minutes
        localStorageKey: 'filadelfia_sync_cache',
        maxCacheAge: 3600000, // 1 hour
    };

    class FiladelfiaSync {
        constructor(options) {
            this.token = options.token;
            this.table = options.table;
            this.target = options.target;
            this.refreshInterval = options.refresh || CONFIG.defaultRefresh;
            this.columns = options.columns || '*';
            this.onData = options.onData || null;
            this.onError = options.onError || null;
            
            this.timer = null;
            this.init();
        }

        init() {
            this.fetchData();
            if (this.refreshInterval > 0) {
                this.startAutoRefresh();
            }
            this.setupUnloadHandler();
        }

        async fetchData() {
            try {
                const params = new URLSearchParams({
                    columns: this.columns,
                    per_page: 50,
                    t: Date.now() // Cache busting
                });

                const response = await fetch(`${CONFIG.apiUrl}/${this.table}?${params}`, {
                    headers: {
                        'Authorization': `Bearer ${this.token}`,
                        'Accept': 'application/json',
                        'X-Requested-With': 'FiladelfiaSync/1.0'
                    }
                });

                if (!response.ok) {
                    const error = await response.json().catch(() => ({}));
                    throw new Error(error.error || `HTTP ${response.status}`);
                }

                let data = await response.json();
                
                // Decrypt if encrypted
                if (data.encrypted && data.data) {
                    // Note: Decryption should be done server-side for security
                    // This is a placeholder - in production, use HTTPS + server-side rendering
                    console.warn('Encrypted data received - decryption should be handled server-side');
                }

                const result = data.encrypted ? data.data : data;

                // Cache to localStorage
                this.cacheData(result);

                // Render or callback
                if (this.onData && typeof this.onData === 'function') {
                    this.onData(result);
                } else if (this.target) {
                    this.renderToDOM(result);
                }

                return result;

            } catch (error) {
                console.error('FiladelfiaSync error:', error);
                
                // Fallback to cached data
                const cached = this.getCachedData();
                if (cached && this.target) {
                    this.renderToDOM(cached);
                    console.log('Using cached data');
                }

                if (this.onError && typeof this.onError === 'function') {
                    this.onError(error);
                }

                throw error;
            }
        }

        cacheData(data) {
            try {
                const cache = {
                    data: data,
                    timestamp: Date.now(),
                    table: this.table
                };
                localStorage.setItem(`${CONFIG.localStorageKey}_${this.table}`, JSON.stringify(cache));
            } catch (e) {
                console.warn('Failed to cache data:', e);
            }
        }

        getCachedData() {
            try {
                const cached = localStorage.getItem(`${CONFIG.localStorageKey}_${this.table}`);
                if (!cached) return null;
                
                const { data, timestamp } = JSON.parse(cached);
                // Check if cache is still fresh
                if (Date.now() - timestamp < CONFIG.maxCacheAge) {
                    return data;
                }
            } catch (e) {
                console.warn('Failed to read cache:', e);
            }
            return null;
        }

        renderToDOM(data) {
            const target = document.querySelector(this.target);
            if (!target) {
                console.warn(`Target element not found: ${this.target}`);
                return;
            }

            // Auto-detect data type and render
            if (Array.isArray(data)) {
                // List of items (e.g., events)
                target.innerHTML = data.map(item => this.renderItem(item)).join('');
            } else if (typeof data === 'object' && data.data) {
                // Paginated response
                target.innerHTML = data.data.map(item => this.renderItem(item)).join('');
            } else {
                // Single item or unknown
                target.innerHTML = this.renderItem(data);
            }
        }

        renderItem(item) {
            // Simple default renderer - can be overridden via onData callback
            if (typeof item === 'string' || typeof item === 'number') {
                return `<div>${item}</div>`;
            }
            
            // Object: render as card
            const fields = Object.entries(item).slice(0, 5); // Limit to 5 fields
            return `
                <div class="filadelfia-item" style="border:1px solid #eee;padding:1rem;margin:0.5rem 0;border-radius:8px;">
                    ${fields.map(([key, value]) => `
                        <div style="margin:0.25rem 0;">
                            <strong style="color:#666;">${key}:</strong> 
                            <span>${this.escapeHtml(value)}</span>
                        </div>
                    `).join('')}
                </div>
            `;
        }

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        startAutoRefresh() {
            this.timer = setInterval(() => this.fetchData(), this.refreshInterval * 1000);
        }

        stopAutoRefresh() {
            if (this.timer) {
                clearInterval(this.timer);
                this.timer = null;
            }
        }

        setupUnloadHandler() {
            window.addEventListener('beforeunload', () => this.stopAutoRefresh());
        }

        destroy() {
            this.stopAutoRefresh();
            // Clean up event listeners if needed
        }
    }

    // Auto-initialize from script tag attributes
    function autoInit() {
        const scripts = document.querySelectorAll('script[src*="filadelfia-sync.js"]');
        
        scripts.forEach(script => {
            const options = {
                token: script.dataset.token,
                table: script.dataset.table,
                target: script.dataset.target,
                refresh: parseInt(script.dataset.refresh) || CONFIG.defaultRefresh,
                columns: script.dataset.columns,
            };

            if (!options.token || !options.table) {
                console.error('FiladelfiaSync: token and table are required');
                return;
            }

            const instance = new FiladelfiaSync(options);
            script.dataset.instanceId = `filadelfia_${Date.now()}`;
            window[script.dataset.instanceId] = instance;
        });
    }

    window.FiladelfiaSync = FiladelfiaSync;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', autoInit);
    } else {
        autoInit();
    }
})();