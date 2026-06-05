(function () {
    const config = window.medcheckAccessibility || {};
    let darkMode = Boolean(config.darkMode);
    let fontSize = Number.parseInt(config.fontSize || 16, 10);
    const endpoint = config.endpoint || "save-accessibility.php";
    const darkToggle = document.getElementById("darkModeToggle");

    function clampFontSize(size) {
        return Math.min(24, Math.max(12, Number.parseInt(size || 16, 10)));
    }

    function applySettings() {
        fontSize = clampFontSize(fontSize);
        document.body.classList.toggle("dark-mode", darkMode);
        document.documentElement.style.setProperty("--medcheck-font-size", fontSize + "px");

        if (darkToggle) {
            darkToggle.checked = darkMode;
        }
    }

    function saveSettings() {
        const body = new URLSearchParams();
        body.set("dark_mode", darkMode ? "1" : "0");
        body.set("font_size", String(fontSize));

        fetch(endpoint, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body,
        }).catch(() => {});
    }

    window.increaseFont = function () {
        fontSize = clampFontSize(fontSize + 1);
        applySettings();
        saveSettings();
    };

    window.decreaseFont = function () {
        fontSize = clampFontSize(fontSize - 1);
        applySettings();
        saveSettings();
    };

    window.resetFont = function () {
        fontSize = 16;
        applySettings();
        saveSettings();
    };

    if (darkToggle) {
        darkToggle.addEventListener("change", function () {
            darkMode = this.checked;
            applySettings();
            saveSettings();
        });
    }

    applySettings();
})();
