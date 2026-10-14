/**
 * session-timeout.js
 * Cierra la sesión automáticamente después de un tiempo de inactividad.
 */
(function () {
    'use strict';

    // ============================================
    // 🔥 CONFIGURACIÓN
    // ============================================
    var config = window.SESSION_CONFIG || {};
    var TIMEOUT_MS = (config.timeoutSeconds || 1800) * 1000;
    var LOGOUT_URL = config.logoutUrl || '/site/logout';
    var LOGIN_URL = config.loginUrl || '/site/login';
    var WARNING_SECONDS = 60; // Aviso 60s antes del cierre

    // ============================================
    // 🔥 VARIABLES INTERNAS
    // ============================================
    var inactivityTimer = null;
    var warningTimer = null;
    var warningShown = false;
    var isRedirecting = false;

    // ============================================
    // 🔥 UTILIDADES
    // ============================================
    function log() {
        if (window.console && console.log) {
            console.log.apply(console, ['[SessionTimeout]'].concat(Array.prototype.slice.call(arguments)));
        }
    }

    /**
     * Detecta si una respuesta (texto) es en realidad la página de login
     * (Yii redirige al login cuando la sesión expira)
     */
    function isLoginPageResponse(text) {
        if (!text || typeof text !== 'string') return false;
        // Heurística: busca indicios de la página de login
        return (
            text.indexOf('login-form') !== -1 ||
            text.indexOf('Iniciar sesión') !== -1 ||
            text.indexOf('login-button') !== -1
        );
    }

    /**
     * Cierra la sesión: destruye la sesión del servidor y redirige al login.
     */
    function doLogout() {
        if (isRedirecting) return;
        isRedirecting = true;

        log('Sesión expirada. Cerrando sesión...');

        try {
            var csrfToken = document.querySelector('meta[name="csrf-token"]');
            var csrfParam = document.querySelector('meta[name="csrf-param"]');

            if (csrfToken && csrfParam) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = LOGOUT_URL;

                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = csrfParam.getAttribute('content');
                input.value = csrfToken.getAttribute('content');

                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            } else {
                window.location.href = LOGOUT_URL;
            }
        } catch (e) {
            window.location.href = LOGOUT_URL;
        }
    }

    /**
     * Redirige al login sin pasar por logout (para casos AJAX)
     */
    function redirectToLogin() {
        if (isRedirecting) return;
        isRedirecting = true;
        log('Redirigiendo al login por sesión expirada');
        window.location.href = LOGIN_URL;
    }

    /**
     * Muestra un aviso visual de que la sesión está por expirar.
     */
    function showWarning() {
        if (warningShown) return;
        warningShown = true;

        log('Advertencia: sesión por expirar');

        var banner = document.createElement('div');
        banner.id = 'session-timeout-banner';
        banner.style.cssText = [
            'position: fixed',
            'top: 20px',
            'right: 20px',
            'background: #fff3cd',
            'color: #664d03',
            'border: 1px solid #ffecb5',
            'border-radius: 8px',
            'padding: 12px 18px',
            'box-shadow: 0 4px 12px rgba(0,0,0,0.15)',
            'z-index: 99999',
            'font-family: sans-serif',
            'font-size: 14px',
            'max-width: 320px',
            'display: flex',
            'align-items: center',
            'gap: 10px'
        ].join(';');

        banner.innerHTML =
            '<i class="fas fa-exclamation-triangle" style="color:#ffc107;font-size:20px;"></i>' +
            '<div>' +
                '<strong>Sesión por expirar</strong><br>' +
                '<small>Tu sesión se cerrará en ' + WARNING_SECONDS + ' segundos por inactividad.</small>' +
            '</div>';

        document.body.appendChild(banner);
    }

    function hideWarning() {
        var banner = document.getElementById('session-timeout-banner');
        if (banner && banner.parentNode) {
            banner.parentNode.removeChild(banner);
        }
        warningShown = false;
    }

    // ============================================
    // 🔥 CONTROL DEL TEMPORIZADOR
    // ============================================
    function resetTimer() {
        if (isRedirecting) return;

        if (inactivityTimer) clearTimeout(inactivityTimer);
        if (warningTimer) clearTimeout(warningTimer);

        hideWarning();

        if (TIMEOUT_MS > WARNING_SECONDS * 1000) {
            warningTimer = setTimeout(showWarning, TIMEOUT_MS - WARNING_SECONDS * 1000);
        }

        inactivityTimer = setTimeout(doLogout, TIMEOUT_MS);
    }

    // ============================================
    // 🔥 EVENTOS DE ACTIVIDAD (con throttle)
    // ============================================
    var throttleTimer = null;

    function onActivity() {
        if (throttleTimer) return;

        throttleTimer = setTimeout(function () {
            throttleTimer = null;
            resetTimer();
        }, 1000);
    }

    var activityEvents = [
        'mousedown', 'mousemove', 'keypress',
        'scroll', 'touchstart', 'click', 'keydown'
    ];

    function bindActivityEvents() {
        activityEvents.forEach(function (eventName) {
            document.addEventListener(eventName, onActivity, true);
        });
    }

    // ============================================
    // 🔥 DETECCIÓN DE SESIÓN EXPIRADA EN AJAX
    // ============================================
    function bindAjaxListener() {

        // --------------------------------------------
        // fetch()
        // --------------------------------------------
        var originalFetch = window.fetch;
        if (originalFetch) {
            window.fetch = function () {
                return originalFetch.apply(this, arguments).then(function (response) {
                    // 🔥 Caso 1: Yii redirige al login (302/redirect)
                    // El navegador sigue el redirect, así que la URL final puede ser login
                    if (response.redirected && response.url.indexOf('login') !== -1) {
                        log('AJAX detectó redirección al login (fetch)');
                        redirectToLogin();
                        return response;
                    }

                    // 🔥 Caso 2: status 401/403
                    if (response.status === 401 || response.status === 403) {
                        log('AJAX detectó status ' + response.status + ' (fetch)');
                        redirectToLogin();
                        return response;
                    }

                    // 🔥 Caso 3: respuesta HTML que contiene la página de login
                    var contentType = response.headers.get('Content-Type') || '';
                    if (contentType.indexOf('text/html') !== -1) {
                        var clone = response.clone();
                        clone.text().then(function (text) {
                            if (isLoginPageResponse(text)) {
                                log('AJAX detectó página de login en respuesta (fetch)');
                                redirectToLogin();
                            }
                        }).catch(function () {});
                    }

                    return response;
                });
            };
        }

        // --------------------------------------------
        // XMLHttpRequest (jQuery, etc.)
        // --------------------------------------------
        if (window.XMLHttpRequest) {
            var originalOpen = XMLHttpRequest.prototype.open;
            XMLHttpRequest.prototype.open = function (method, url) {
                var self = this;
                this.addEventListener('load', function () {
                    // 🔥 Detectar redirect al login
                    if (self.responseURL && self.responseURL.indexOf('login') !== -1 && url.indexOf('login') === -1) {
                        log('AJAX detectó redirección al login (XHR)');
                        redirectToLogin();
                        return;
                    }

                    // 🔥 Detectar status 401/403
                    if (self.status === 401 || self.status === 403) {
                        log('AJAX detectó status ' + self.status + ' (XHR)');
                        redirectToLogin();
                        return;
                    }

                    // 🔥 Detectar HTML de login en respuesta
                    if (self.responseText && isLoginPageResponse(self.responseText)) {
                        log('AJAX detectó página de login en respuesta (XHR)');
                        redirectToLogin();
                    }
                });
                return originalOpen.apply(this, arguments);
            };
        }
    }

    // ============================================
    // 🔥 INICIALIZACIÓN
    // ============================================
    function init() {
        log('Inicializando. Timeout:', TIMEOUT_MS / 1000, 'segundos');
        bindActivityEvents();
        bindAjaxListener();
        resetTimer();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // API pública
    window.SessionTimeout = {
        reset: resetTimer,
        logout: doLogout,
        warning: showWarning
    };
})();