@extends('layouts.app')

@section('title', 'Secure Login - Gayatri Enterprises')

@section('content')

<style>
    @keyframes slideDown { from { opacity: 0; transform: translateY(-30px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes slideUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes scaleIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-10px); } }
    @keyframes fill-bar { from { width: 0%; } to { width: 100%; } }
    @keyframes bounce-subtle { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-4px); } }
    @keyframes shake { 0%, 100% { transform: translateX(0); } 20%, 60% { transform: translateX(-6px); } 40%, 80% { transform: translateX(6px); } }
    
    /* Beautiful scanning/biometric avatar rings animations */
    @keyframes spin-slow { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    @keyframes spin-reverse { from { transform: rotate(360deg); } to { transform: rotate(0deg); } }
    
    @keyframes glow-pulse-blue {
        0%, 100% { box-shadow: 0 0 15px 2px rgba(59, 130, 246, 0.25); opacity: 0.8; }
        50% { box-shadow: 0 0 35px 10px rgba(59, 130, 246, 0.5); opacity: 1; }
    }
    @keyframes glow-pulse-green {
        0%, 100% { box-shadow: 0 0 15px 2px rgba(16, 185, 129, 0.25); opacity: 0.8; }
        50% { box-shadow: 0 0 35px 10px rgba(16, 185, 129, 0.5); opacity: 1; }
    }
    @keyframes glow-pulse-purple {
        0%, 100% { box-shadow: 0 0 15px 2px rgba(139, 92, 246, 0.25); opacity: 0.8; }
        50% { box-shadow: 0 0 35px 10px rgba(139, 92, 246, 0.5); opacity: 1; }
    }
    
    .animate-slide-down { animation: slideDown 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    .animate-slide-up { animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    .animate-scale-in { animation: scaleIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    .animate-fade-in { animation: fadeIn 0.4s ease-out forwards; }
    .animate-float { animation: float 6s ease-in-out infinite; }
    .animate-bounce-subtle { animation: bounce-subtle 3s ease-in-out infinite; }
    .animate-shake { animation: shake 0.4s ease-in-out; }
    
    .animate-spin-slow { animation: spin-slow 10s linear infinite; }
    .animate-spin-reverse { animation: spin-reverse 6s linear infinite; }
    
    .theme-glow { transition: all 0.5s ease; }
    .theme-glow-blue { animation: glow-pulse-blue 2.5s infinite ease-in-out; }
    .theme-glow-green { animation: glow-pulse-green 2.5s infinite ease-in-out; }
    .theme-glow-purple { animation: glow-pulse-purple 2.5s infinite ease-in-out; }
    
    .glass-card { 
        background: rgba(255, 255, 255, 0.96); 
        backdrop-filter: blur(16px); 
        border: 1px solid rgba(226, 232, 240, 0.8); 
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.08), 0 0 0 1px rgba(15, 23, 42, 0.02);
    }
    
    .dynamic-accent {
        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Custom Input Styles */
    .premium-input {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .premium-input:focus {
        transform: translateY(-1px);
        box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.1), 0 0 0 3px rgba(59, 130, 246, 0.15);
    }

    /* Welcome Morph Styles */
    .morph-container {
        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }
</style>

<!-- Hero Section -->
<section class="relative bg-slate-900 text-white py-20 md:py-24 overflow-hidden">
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-blue-600 rounded-full opacity-25 blur-3xl animate-float"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-blue-600 rounded-full opacity-15 blur-3xl animate-float" style="animation-delay: 2s;"></div>
    </div>
    <div class="relative max-w-7xl mx-auto px-4 text-center animate-slide-down">
        <h1 class="text-4xl md:text-5xl font-bold mb-4 brand-font tracking-tight">Gayatri Enterprises</h1>
        <p class="text-slate-400 max-w-2xl mx-auto text-base md:text-lg">Secure Access Client & Staff Portal</p>
    </div>
</section> 

<!-- Login Form Section -->
<section class="min-h-[75vh] flex items-center justify-center bg-gradient-to-br from-slate-50 via-blue-50/20 to-slate-50 py-16 px-4">
    <div class="max-w-md w-full animate-scale-in">
        
        <div id="login-card" class="glass-card rounded-3xl p-8 md:p-10 relative overflow-hidden">
            
            <!-- Dynamic Top Accent Bar -->
            <div id="accent-bar" class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-blue-600 to-blue-400 dynamic-accent"></div>

            <!-- ==========================================
                 LOGIN FORM PANEL (VISIBLE INITIALY)
                 ========================================== -->
            <div id="login-panel" class="morph-container space-y-6">
                <!-- Header -->
                <div class="text-center mb-6">
                    <div id="logo-container" class="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg transform transition duration-500 dynamic-accent hover:scale-105 animate-bounce-subtle">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl md:text-3xl font-bold text-slate-800 brand-font" id="main-greeting">Welcome Back</h2>
                    <p class="mt-1.5 text-sm text-slate-500" id="sub-greeting">Please enter your credentials to login</p>
                </div>

                <!-- Inputs Form Group -->
                <div class="space-y-4">
                    <!-- Mobile Number -->
                    <div class="space-y-1.5">
                        <label for="phone" class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Mobile Number</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <span class="text-slate-400 font-bold text-sm tracking-wide">+91</span>
                            </div>
                            <input id="phone" name="phone" type="tel" required maxlength="10" autocomplete="tel"
                                oninput="handlePhoneInput()" onkeypress="handleKeypress(event)"
                                class="premium-input appearance-none rounded-xl block w-full pl-14 pr-4 py-3.5 border border-slate-200 placeholder-slate-400 text-slate-800 focus:outline-none focus:border-blue-500 transition bg-white/70 text-sm font-semibold" 
                                placeholder="Enter 10 digit number">
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="space-y-1.5">
                        <label for="password" class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Password</label>
                        <div class="relative">
                            <input id="password" type="password" required onkeypress="handleKeypress(event)" autocomplete="current-password"
                                class="premium-input appearance-none rounded-xl block w-full px-4 py-3.5 pr-12 border border-slate-200 placeholder-slate-400 text-slate-800 focus:outline-none focus:border-blue-500 transition bg-white/70 text-sm font-semibold" 
                                placeholder="Enter secure password">
                            <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 transition">
                                <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Message & Feedback Panel -->
                <p id="message" class="text-center text-xs font-bold min-h-[16px] transition-all duration-300"></p>

                <!-- Login Button -->
                <button onclick="submitLogin()" id="loginBtn" 
                    class="w-full flex justify-center items-center gap-2 py-3.5 px-4 text-sm font-bold rounded-xl text-white bg-blue-600 hover:bg-blue-700 transition transform hover:scale-[1.01] active:scale-[0.99] shadow-lg shadow-blue-500/10 hover:shadow-blue-500/20 dynamic-accent">
                    Login to Portal
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                    </svg>
                </button>

                <!-- Security Notice -->
                <div class="pt-4 border-t border-slate-100 flex items-start gap-2.5 text-[11px] text-slate-400">
                    <svg class="w-4 h-4 text-blue-500 flex-shrink-0 mt-0.5 dynamic-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <p class="leading-relaxed">This connection is fully encrypted. Multiple failed attempts will result in temporary lock.</p>
                </div>
            </div>

            <!-- ==========================================
                 WELCOME MORPH PANEL (VISIBLE ON SUCCESS)
                 ========================================== -->
            <div id="welcome-panel" class="hidden morph-container text-center py-6 space-y-6 animate-scale-in">
                
                <!-- Pulsing Avatar Ring Assembly (Biometric scanning theme) -->
                <div class="relative w-32 h-32 mx-auto flex items-center justify-center">
                    <!-- Outer pulse shadow aura -->
                    <div id="welcome-glow-aura" class="absolute inset-0 rounded-full theme-glow theme-glow-blue"></div>
                    
                    <!-- Inner Rotating ring 1 (dashed) -->
                    <div id="welcome-ring" class="absolute inset-1 rounded-full border-2 border-dashed border-blue-500/60 animate-spin-slow"></div>
                    
                    <!-- Inner Rotating ring 2 (dotted - opposite direction) -->
                    <div id="welcome-ring-reverse" class="absolute inset-3 rounded-full border-2 border-dotted border-blue-400/40 animate-spin-reverse"></div>
                    
                    <!-- Rounded Avatar Holder -->
                    <div class="w-24 h-24 rounded-full overflow-hidden bg-slate-50 border-4 border-white shadow-2xl relative z-10 flex items-center justify-center transform hover:scale-105 transition duration-300">
                        <!-- Img Element (Shown if user has photo) -->
                        <img id="welcome-avatar-img" class="hidden w-full h-full object-cover" src="" alt="Profile Photo">
                        <!-- Initial / Text Element (Fallback) -->
                        <div id="welcome-avatar-txt" class="w-full h-full flex items-center justify-center text-3xl font-bold text-white bg-gradient-to-br from-blue-600 to-blue-400">
                            U
                        </div>
                    </div>
                </div>

                <!-- Text Greeting -->
                <div>
                    <h3 class="text-2xl md:text-3xl font-bold text-slate-800 brand-font">Welcome Back</h3>
                    <h4 id="welcome-name" class="text-xl font-bold text-blue-600 mt-1">Valued User</h4>
                    <p id="welcome-role" class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-2">Connecting...</p>
                </div>

                <!-- Loading Charge Indicator -->
                <div class="max-w-[220px] mx-auto space-y-2">
                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden relative">
                        <div id="welcome-progress" class="absolute top-0 left-0 h-full bg-gradient-to-r from-blue-600 to-blue-400 rounded-full" style="width: 0%; transition: width 1.5s cubic-bezier(0.1, 0.8, 0.2, 1);"></div>
                    </div>
                </div>

            </div>

        </div>

        <!-- Help Section -->
        <div class="text-center mt-6 text-sm text-slate-600 animate-fade-in" style="animation-delay: 0.3s;">
            <p>Need help accessing your account?</p>
            <a href="mailto:info@gayatrient.com" class="text-blue-600 hover:text-blue-700 font-bold transition">Contact Support</a>
        </div>

    </div>
</section>

<script>
    const csrfToken = "{{ csrf_token() }}";
    let identifiedRole = 'default';

    // Theme Config based on User Roles
    const themes = {
        default: {
            bar: 'from-blue-600 to-blue-400',
            logo: 'bg-blue-600 hover:bg-blue-700 text-white shadow-blue-500/10',
            btn: 'bg-blue-600 hover:bg-blue-700 shadow-blue-500/10 hover:shadow-blue-500/20 focus:ring-blue-500',
            accentText: 'text-blue-600',
            ringColor: 'border-blue-500/60',
            ringReverseColor: 'border-blue-400/40',
            glowClass: 'theme-glow-blue'
        },
        admin: {
            bar: 'from-sky-600 to-blue-500',
            logo: 'bg-sky-600 hover:bg-sky-700 text-white shadow-sky-500/10',
            btn: 'bg-sky-600 hover:bg-sky-700 shadow-sky-500/10 hover:shadow-sky-500/20 focus:ring-sky-500',
            accentText: 'text-sky-600',
            ringColor: 'border-sky-500/60',
            ringReverseColor: 'border-sky-400/40',
            glowClass: 'theme-glow-blue'
        },
        staff: {
            bar: 'from-purple-600 to-indigo-500',
            logo: 'bg-purple-600 hover:bg-purple-700 text-white shadow-purple-500/10',
            btn: 'bg-purple-600 hover:bg-purple-700 shadow-purple-500/10 hover:shadow-purple-500/20 focus:ring-purple-500',
            accentText: 'text-purple-600',
            ringColor: 'border-purple-500/60',
            ringReverseColor: 'border-purple-400/40',
            glowClass: 'theme-glow-purple'
        },
        client: {
            bar: 'from-emerald-600 to-teal-500',
            logo: 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-emerald-500/10',
            btn: 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-500/10 hover:shadow-emerald-500/20 focus:ring-emerald-500',
            accentText: 'text-emerald-600',
            ringColor: 'border-emerald-500/60',
            ringReverseColor: 'border-emerald-400/40',
            glowClass: 'theme-glow-green'
        }
    };

    // Trigger checkRole on 10 digits
    function handlePhoneInput() {
        const phone = document.getElementById('phone').value;
        
        // Clean characters except numbers
        document.getElementById('phone').value = phone.replace(/\D/g, '');

        if (phone.length === 10) {
            silentlyCheckRole(phone);
        } else {
            // Revert to default theme if number edited
            if (identifiedRole !== 'default') {
                applyDynamicTheme('default');
                document.getElementById('message').innerText = '';
                identifiedRole = 'default';
            }
        }
    }

    // Call Login on pressing Enter
    function handleKeypress(event) {
        if (event.key === 'Enter') {
            submitLogin();
        }
    }

    // Dynamic Theme Applicator
    function applyDynamicTheme(role) {
        const theme = themes[role] || themes.default;
        
        // 1. Accent Bar
        const bar = document.getElementById('accent-bar');
        bar.className = `absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r ${theme.bar} dynamic-accent`;
        
        // 2. Logo Container
        const logo = document.getElementById('logo-container');
        logo.className = `w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg transform transition duration-500 dynamic-accent hover:scale-105 animate-bounce-subtle ${theme.logo}`;
        
        // 3. Login Button
        const btn = document.getElementById('loginBtn');
        btn.className = `w-full flex justify-center items-center gap-2 py-3.5 px-4 text-sm font-bold rounded-xl text-white transition transform hover:scale-[1.01] active:scale-[0.99] shadow-lg dynamic-accent ${theme.btn}`;

        // 4. Welcome Rings
        const ring = document.getElementById('welcome-ring');
        ring.className = `absolute inset-1 rounded-full border-2 border-dashed animate-spin-slow ${theme.ringColor}`;
        
        const ringReverse = document.getElementById('welcome-ring-reverse');
        ringReverse.className = `absolute inset-3 rounded-full border-2 border-dotted animate-spin-reverse ${theme.ringReverseColor}`;

        // 5. Glow Aura
        const glowAura = document.getElementById('welcome-glow-aura');
        glowAura.className = `absolute inset-0 rounded-full theme-glow ${theme.glowClass}`;
    }

    // Silent background account verification
    function silentlyCheckRole(phone) {
        const msg = document.getElementById('message');

        fetch("{{ route('check.role') }}", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrfToken },
            body: JSON.stringify({ phone: phone })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                identifiedRole = data.role;
                applyDynamicTheme(data.role);
                msg.innerText = '';
                
                // Focus password immediately
                document.getElementById('password').focus();
            } else {
                msg.className = "text-center text-xs font-bold min-h-[16px] text-red-600 flex items-center justify-center gap-1";
                msg.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg> ${data.message}`;
                shakeCard();
            }
        })
        .catch(error => {
            console.error("CheckRole silently failed", error);
        });
    }

    // Unified password check & morph transition
    function submitLogin() {
        const phone = document.getElementById('phone').value;
        const password = document.getElementById('password').value;
        const btn = document.getElementById('loginBtn');
        const msg = document.getElementById('message');

        if (!phone || phone.length !== 10) {
            msg.className = "text-center text-xs font-bold min-h-[16px] text-red-600 flex items-center justify-center gap-1";
            msg.innerHTML = 'Please enter a valid 10-digit number.';
            shakeCard();
            document.getElementById('phone').focus();
            return;
        }

        if (!password) {
            msg.className = "text-center text-xs font-bold min-h-[16px] text-red-600 flex items-center justify-center gap-1";
            msg.innerHTML = 'Please enter password.';
            shakeCard();
            document.getElementById('password').focus();
            return;
        }

        // Show spinner inside login button
        btn.disabled = true;
        btn.innerHTML = '<svg class="animate-spin h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

        fetch("{{ route('verify.password') }}", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrfToken },
            body: JSON.stringify({ phone: phone, password: password })
        })
        .then(async response => {
            let data = {};
            try {
                data = await response.json();
            } catch(e) {
                data = { message: 'Unexpected server response.' };
            }
            
            if (response.status === 429) {
                let seconds = 60;
                const headerValue = response.headers.get('Retry-After');
                if (headerValue) seconds = parseInt(headerValue);
                startThrottleTimer(seconds);
                return;
            }

            if (data.status === 'success') {
                msg.innerText = '';
                playMorphTransition(data);
            } else {
                btn.disabled = false;
                btn.innerHTML = `Login to Portal <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>`;
                
                msg.className = "text-center text-xs font-bold min-h-[16px] text-red-600 flex items-center justify-center gap-1";
                msg.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg> ${data.message || 'Incorrect password.'}`;
                shakeCard();
            }
        })
        .catch(error => {
            console.error("Unified login error:", error);
            btn.disabled = false;
            btn.innerHTML = 'Login to Portal';
            msg.className = "text-center text-xs font-bold min-h-[16px] text-red-600 flex items-center justify-center gap-1";
            msg.innerHTML = 'Network error. Please try again.';
            shakeCard();
        });
    }

    // Execute Morph Transition
    function playMorphTransition(data) {
        const loginPanel = document.getElementById('login-panel');
        const welcomePanel = document.getElementById('welcome-panel');
        const welcomeName = document.getElementById('welcome-name');
        const welcomeRole = document.getElementById('welcome-role');
        const welcomeAvatarImg = document.getElementById('welcome-avatar-img');
        const welcomeAvatarTxt = document.getElementById('welcome-avatar-txt');
        const welcomeProgress = document.getElementById('welcome-progress');

        // Apply successful role design tokens to welcome screen colors
        const activeTheme = themes[data.role] || themes.default;
        welcomeName.className = `text-2xl font-bold mt-1 ${activeTheme.accentText}`;
        welcomeProgress.className = `absolute top-0 left-0 h-full bg-gradient-to-r ${activeTheme.bar} rounded-full`;

        // 1. Setup Personalized greeting
        welcomeName.innerText = data.name || "User";
        welcomeRole.innerText = "CONNECTING...";

        // 2. Setup Profile photo or Initials Fallback
        if (data.profile_photo) {
            welcomeAvatarImg.src = data.profile_photo;
            welcomeAvatarImg.classList.remove('hidden');
            welcomeAvatarTxt.classList.add('hidden');
        } else {
            const initial = (data.name ? data.name.charAt(0) : 'U').toUpperCase();
            welcomeAvatarTxt.innerText = initial;
            welcomeAvatarTxt.className = `w-full h-full flex items-center justify-center text-4xl font-bold text-white bg-gradient-to-br ${activeTheme.bar}`;
            welcomeAvatarImg.classList.add('hidden');
            welcomeAvatarTxt.classList.remove('hidden');
        }

        // 3. Fade out login inputs smoothly, then toggle panels
        loginPanel.style.opacity = '0';
        loginPanel.style.transform = 'translateY(-10px)';

        setTimeout(() => {
            loginPanel.classList.add('hidden');
            welcomePanel.classList.remove('hidden');
            welcomePanel.style.opacity = '0';
            welcomePanel.style.transform = 'translateY(10px)';
            
            // Trigger fade-in of welcome screen
            setTimeout(() => {
                welcomePanel.style.opacity = '1';
                welcomePanel.style.transform = 'translateY(0)';
                
                // Charge progress bar
                setTimeout(() => {
                    welcomeProgress.style.width = '100%';
                    
                    // Final redirect after bar completely charges
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1400);

                }, 100);
            }, 50);

        }, 300);
    }

    // Timer for brute force protection
    function startThrottleTimer(seconds) {
        const btn = document.getElementById('loginBtn');
        const msg = document.getElementById('message');
        btn.disabled = true;
        
        let timeLeft = seconds;
        const interval = setInterval(() => {
            msg.innerHTML = `<span class="flex items-center justify-center gap-1 text-red-600"><svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Blocked. Try in ${timeLeft}s</span>`;
            timeLeft--;
            
            if (timeLeft < 0) {
                clearInterval(interval);
                btn.disabled = false;
                btn.innerHTML = 'Login to Portal';
                msg.innerText = '';
            }
        }, 1000);
    }

    // Eye Password toggler
    function togglePassword() {
        const input = document.getElementById('password');
        const eye = document.getElementById('eye-icon');
        
        if (input.type === 'password') {
            input.type = 'text';
            eye.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>';
        } else {
            input.type = 'password';
            eye.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
        }
    }

    // Shake Card Animation on Errors
    function shakeCard() {
        const card = document.getElementById('login-card');
        card.classList.add('animate-shake');
        setTimeout(() => card.classList.remove('animate-shake'), 400);
    }
</script>

@endsection