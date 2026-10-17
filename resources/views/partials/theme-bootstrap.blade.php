<script>
    (function () {
        function normalizeHex(hex) {
            var value = String(hex || '').replace('#', '').trim();

            if (/^[0-9a-f]{3}$/i.test(value)) {
                value = value.split('').map(function (character) { return character + character; }).join('');
            }

            return /^[0-9a-f]{6}$/i.test(value) ? '#' + value.toUpperCase() : null;
        }

        function hexChannels(hex) {
            var normalized = normalizeHex(hex) || '#FF0080';

            return [
                parseInt(normalized.slice(1, 3), 16),
                parseInt(normalized.slice(3, 5), 16),
                parseInt(normalized.slice(5, 7), 16),
            ];
        }

        function channelString(hex) {
            return hexChannels(hex).join(' ');
        }

        function relativeLuminance(hex) {
            var channels = hexChannels(hex).map(function (channel) {
                var value = channel / 255;

                return value <= 0.04045
                    ? value / 12.92
                    : Math.pow((value + 0.055) / 1.055, 2.4);
            });

            return (0.2126 * channels[0]) + (0.7152 * channels[1]) + (0.0722 * channels[2]);
        }

        function contrastRatio(first, second) {
            var firstLuminance = relativeLuminance(first);
            var secondLuminance = relativeLuminance(second);
            var lightest = Math.max(firstLuminance, secondLuminance);
            var darkest = Math.min(firstLuminance, secondLuminance);

            return (lightest + 0.05) / (darkest + 0.05);
        }

        function mixColor(hex, target, amount) {
            var source = hexChannels(hex);
            var destination = hexChannels(target);
            var mixed = source.map(function (channel, index) {
                return Math.round(channel + ((destination[index] - channel) * amount));
            });

            return '#' + mixed.map(function (channel) {
                return channel.toString(16).padStart(2, '0');
            }).join('').toUpperCase();
        }

        function applyTheme(settings) {
            var root = document.documentElement;
            var primary = normalizeHex(settings.primary) || '#FF0080';
            var primaryLight = normalizeHex(settings.primaryLight) || '#FFE6F0';
            var darkText = '#1A0E14';
            var lightText = '#FFFFFF';
            var onPrimary = contrastRatio(primary, darkText) >= contrastRatio(primary, lightText)
                ? darkText
                : lightText;
            var hover = mixColor(primary, onPrimary === darkText ? lightText : '#000000', 0.1);

            root.classList.toggle('light-mode', settings.mode === 'light');
            root.style.setProperty('--pd-primary', primary);
            root.style.setProperty('--pd-gold', primary);
            root.style.setProperty('--pd-gold-rgb', channelString(primary));
            root.style.setProperty('--pd-gold-light-rgb', channelString(primaryLight));
            root.style.setProperty('--pd-gold-hover-rgb', channelString(hover));
            root.style.setProperty('--pd-primary-hover', hover);
            root.style.setProperty('--pd-gold-light', primaryLight);
            root.style.setProperty('--pd-primary-on', onPrimary);
        }

        var fallback = { mode: 'light', primary: '#FF0080', primaryLight: '#FFE6F0', preset: 'boss-light' };

        try {
            applyTheme(@json($siteTheme));
        } catch (error) {
            applyTheme(fallback);
        }

        window.pdApplyTheme = applyTheme;
    }());
</script>
