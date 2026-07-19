<footer class="main-footer">
    <div class="footer-container">
        <p>&copy; <?php echo date('Y'); ?> <span class="footer-highlight">ikoowwll</span> SIM HIMATIF | UAS Praktikum Pemrograman Web</p>
    </div>
</footer>

<script>
    // Immediate check to prevent flash of dark mode on load
    (function() {
        const theme = localStorage.getItem('theme') || 'dark';
        if (theme === 'light') {
            document.body.classList.add('light-mode');
        }
    })();

    document.addEventListener('DOMContentLoaded', function() {
        const navbar = document.querySelector('.navbar');
        const toggleBtn = document.createElement('button');
        toggleBtn.className = 'theme-toggle-btn';
        toggleBtn.id = 'theme-toggle';
        
        function setToggleState(isLight) {
            if (isLight) {
                // Sun icon for Light Mode (shows sun, click to change to dark)
                toggleBtn.innerHTML = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="theme-icon"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>`;
                toggleBtn.title = 'Aktifkan Mode Gelap';
            } else {
                // Moon icon for Dark Mode (shows moon, click to change to light)
                toggleBtn.innerHTML = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="theme-icon"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>`;
                toggleBtn.title = 'Aktifkan Mode Terang';
            }
        }

        // Initialize toggle state based on active class
        setToggleState(document.body.classList.contains('light-mode'));

        toggleBtn.addEventListener('click', function() {
            const willBeLight = !document.body.classList.contains('light-mode');
            if (willBeLight) {
                document.body.classList.add('light-mode');
                localStorage.setItem('theme', 'light');
            } else {
                document.body.classList.remove('light-mode');
                localStorage.setItem('theme', 'dark');
            }
            setToggleState(willBeLight);

            // Dispatch custom event for Chart.js updates if loaded
            window.dispatchEvent(new CustomEvent('themechanged', { 
                detail: { theme: willBeLight ? 'light' : 'dark' } 
            }));
        });

        if (navbar) {
            const navLinks = navbar.querySelector('div:last-child');
            if (navLinks) {
                // Prepend toggle button to links container
                navLinks.insertBefore(toggleBtn, navLinks.firstChild);
            }
        } else {
            // No navbar (e.g. login, register, lupa_password), place it floating at top right
            toggleBtn.classList.add('theme-toggle-floating');
            document.body.appendChild(toggleBtn);
        }
    });
</script>
