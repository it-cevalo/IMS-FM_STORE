{{-- Help Panel Overlay --}}
<div id="help-overlay" onclick="helpPanel.close()" style="
    display:none;
    position:fixed;inset:0;
    background:rgba(0,0,0,.25);
    z-index:1049;
"></div>

{{-- Help Panel --}}
<div id="help-panel" role="dialog" aria-label="Panduan" style="
    position:fixed;top:0;right:0;
    width:320px;height:100%;
    background:#fff;
    box-shadow:-4px 0 20px rgba(0,0,0,.15);
    z-index:1050;
    display:flex;flex-direction:column;
    transform:translateX(100%);
    transition:transform .25s ease;
    font-family:inherit;
">
    {{-- Header --}}
    <div style="
        display:flex;align-items:center;justify-content:space-between;
        padding:14px 16px;
        background:#4e73df;
        color:#fff;
        flex-shrink:0;
    ">
        <span style="font-weight:700;font-size:.95rem;" id="help-panel-title">Panduan</span>
        <button onclick="helpPanel.close()" style="
            background:none;border:none;color:#fff;
            font-size:1.3rem;line-height:1;cursor:pointer;padding:0 4px;
        " title="Tutup">&times;</button>
    </div>

    {{-- Body --}}
    <div id="help-panel-body" style="
        padding:16px;overflow-y:auto;flex:1;
        font-size:.875rem;line-height:1.6;color:#333;
    ">
        {{-- Loading state --}}
        <div id="help-panel-loading" style="text-align:center;padding:24px 0;color:#888;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" style="animation:help-spin 1s linear infinite;vertical-align:middle;">
                <circle cx="12" cy="12" r="10" stroke-opacity=".25"/>
                <path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"/>
            </svg>
            <span style="margin-left:8px;">Memuat panduan...</span>
        </div>

        {{-- Content (hidden until loaded) --}}
        <div id="help-panel-content" style="display:none;">
            <p style="font-size:.8rem;color:#888;margin:0 0 12px;">Langkah-langkah:</p>
            <ol id="help-steps" style="margin:0;padding-left:20px;"></ol>
            <div id="help-tips-wrap" style="
                display:none;
                margin-top:16px;
                background:#fffbea;
                border-left:3px solid #f6c23e;
                padding:10px 12px;
                border-radius:0 4px 4px 0;
            ">
                <p style="font-weight:700;margin:0 0 6px;font-size:.8rem;color:#856404;">
                    💡 Tips
                </p>
                <ul id="help-tips" style="margin:0;padding-left:18px;"></ul>
            </div>
        </div>

        {{-- No guide state --}}
        <div id="help-panel-empty" style="display:none;text-align:center;padding:24px 0;color:#888;">
            Belum ada panduan untuk halaman ini.
        </div>
    </div>
</div>

<style>
@keyframes help-spin {
    to { transform: rotate(360deg); }
}
#help-steps li {
    margin-bottom: 8px;
}
#help-tips li {
    margin-bottom: 4px;
    font-size: .82rem;
    color: #555;
}
</style>

<script>
(function () {
    var _cache  = {};
    var _loaded = false;

    window.helpPanel = {
        open: function () {
            var routeKey = document.querySelector('meta[name="help-route"]');
            if (!routeKey) return;

            document.getElementById('help-overlay').style.display = 'block';
            document.getElementById('help-panel').style.transform  = 'translateX(0)';

            if (!_loaded) {
                _loaded = true;
                helpPanel._load(routeKey.content);
            }
        },

        close: function () {
            document.getElementById('help-overlay').style.display = 'none';
            document.getElementById('help-panel').style.transform  = 'translateX(100%)';
        },

        _load: function (routeKey) {
            if (_cache[routeKey]) {
                helpPanel._render(_cache[routeKey]);
                return;
            }

            // Convert dot-notation to URL-safe key (dots → double-underscore)
            var urlKey  = routeKey.replace(/\./g, '__');
            var baseUrl = document.querySelector('meta[name="app-url"]');
            var url     = (baseUrl ? baseUrl.content : '') + '/help/' + urlKey;

            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                _cache[routeKey] = data;
                helpPanel._render(data);
            })
            .catch(function () {
                document.getElementById('help-panel-loading').style.display = 'none';
                document.getElementById('help-panel-empty').style.display   = 'block';
            });
        },

        _render: function (data) {
            document.getElementById('help-panel-loading').style.display = 'none';

            if (!data || !data.steps || data.steps.length === 0) {
                document.getElementById('help-panel-empty').style.display = 'block';
                return;
            }

            // Title
            if (data.title) {
                document.getElementById('help-panel-title').textContent = data.title;
            }

            // Steps
            var stepsEl = document.getElementById('help-steps');
            stepsEl.innerHTML = '';
            data.steps.forEach(function (s) {
                var li = document.createElement('li');
                li.innerHTML = s;
                stepsEl.appendChild(li);
            });

            // Tips
            var tipsWrap = document.getElementById('help-tips-wrap');
            var tipsEl   = document.getElementById('help-tips');
            if (data.tips && data.tips.length > 0) {
                tipsEl.innerHTML = '';
                data.tips.forEach(function (t) {
                    var li = document.createElement('li');
                    li.innerHTML = t;
                    tipsEl.appendChild(li);
                });
                tipsWrap.style.display = 'block';
            } else {
                tipsWrap.style.display = 'none';
            }

            document.getElementById('help-panel-content').style.display = 'block';
        }
    };

    // Keyboard: Escape closes panel
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') helpPanel.close();
    });
})();
</script>
