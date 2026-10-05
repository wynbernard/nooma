<!-- Toast Container -->
<div id="toastContainer" class="pointer-events-none fixed right-5 top-5 z-[100] flex w-[calc(100%-2.5rem)] max-w-sm flex-col gap-3" aria-live="polite" aria-atomic="false"></div>

<script>
(() => {
    const toastContainer = document.getElementById("toastContainer");

    window.showToast = function (message, type = "success", duration = 4500) {
        if (!toastContainer) {
            return;
        }

        // Configure variant styles based on type
        const variants = {
            success: {
                border: "border-emerald-100",
                accent: "bg-emerald-500",
                iconBg: "bg-emerald-50 text-emerald-600",
                text: "text-gray-900",
                svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m4.5 12.75 6 6 9-13.5"></path>'
            },
            error: {
                border: "border-red-100",
                accent: "bg-red-500",
                iconBg: "bg-red-50 text-red-600",
                text: "text-gray-900",
                svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>'
            },
            warning: {
                border: "border-amber-100",
                accent: "bg-amber-500",
                iconBg: "bg-amber-50 text-amber-600",
                text: "text-gray-900",
                svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z"></path>'
            },
            info: {
                border: "border-blue-100",
                accent: "bg-blue-500",
                iconBg: "bg-blue-50 text-blue-600",
                text: "text-gray-900",
                svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"></path>'
            }
        };

        const config = variants[type] || variants.success;
        const defaultMessages = { success: "Done successfully.", error: "Something went wrong.", warning: "Please check your input.", info: "Here is some info." };

        // Create Toast Element
        const toast = document.createElement("div");
        toast.className = `pointer-events-auto relative overflow-hidden flex items-start gap-3.5 rounded-2xl border ${config.border} bg-white/95 backdrop-blur-md p-4 shadow-xl shadow-gray-950/5 transition-all duration-300 ease-out translate-x-4 opacity-0`;
        toast.setAttribute("role", type === "error" ? "alert" : "status");

        // Left Accent Bar Indicator
        const accentBar = document.createElement("div");
        accentBar.className = `absolute left-0 inset-y-0 w-1.5 ${config.accent}`;

        // Icon Container
        const iconWrapper = document.createElement("div");
        iconWrapper.className = `flex h-7 w-7 shrink-0 items-center justify-center rounded-xl ${config.iconBg} mt-0.5`;
        iconWrapper.innerHTML = `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">${config.svg}</svg>`;

        // Message Text
        const messageText = document.createElement("div");
        messageText.className = `min-w-0 flex-1 pt-0.5 text-xs font-semibold ${config.text} leading-relaxed break-words`;
        messageText.textContent = String(message || defaultMessages[type]);

        // Close Action Button
        const closeButton = document.createElement("button");
        closeButton.type = "button";
        closeButton.className = "-mr-1 -mt-1 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-900/10";
        closeButton.setAttribute("aria-label", "Dismiss notification");
        closeButton.innerHTML = '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';

        let dismissed = false;
        let timeout;
        
        const dismiss = () => {
            if (dismissed) return;
            dismissed = true;
            clearTimeout(timeout);
            toast.classList.add("translate-x-4", "opacity-0", "scale-95");
            setTimeout(() => toast.remove(), 250);
        };

        timeout = setTimeout(dismiss, duration);
        closeButton.addEventListener("click", dismiss);

        // Append structure
        toast.append(accentBar, iconWrapper, messageText, closeButton);
        toastContainer.appendChild(toast);

        // Trigger Entry Animation Frame
        requestAnimationFrame(() => {
            toast.classList.remove("translate-x-4", "opacity-0");
        });
    };
})();
</script>