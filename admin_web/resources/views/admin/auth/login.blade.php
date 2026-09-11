<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Panel Web</title>
    <link rel="icon" href="data:,">
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-full flex items-center justify-center p-4 bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-slate-100" x-data="loginPage()">

    <div class="max-w-md w-full">
        
        <!-- Login Card -->
        <div class="bg-slate-900/90 backdrop-blur-xl border border-slate-800 rounded-3xl p-8 shadow-2xl relative overflow-hidden">
            
            <div class="mb-6 text-center">
                <div class="w-14 h-14 rounded-full bg-emerald-500/10 border border-emerald-500/30 shadow-lg shadow-emerald-500/20 flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-user-shield text-emerald-400 text-xl"></i>
                </div>
                <h2 class="text-xl font-bold text-white">Admin Web Panel</h2>
                <p class="text-[12px] text-slate-400 mt-1.5 leading-[18px]">Enter your admin credentials to access live management console.</p>
            </div>

            <!-- Alert messages -->
            @if(session('success'))
                <div class="mb-5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 p-3.5 text-xs font-semibold text-emerald-400 flex items-center space-x-2">
                    <i class="fa-solid fa-circle-check text-emerald-500"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 rounded-xl bg-red-500/10 border border-red-500/20 p-3.5 text-xs text-red-400">
                    @foreach($errors->all() as $err)
                        <div class="flex items-center space-x-2">
                            <i class="fa-solid fa-circle-exclamation text-red-500"></i>
                            <span>{{ $err }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('admin.login.post') }}" method="POST" class="space-y-4" autocomplete="off">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 text-xs">
                            <i class="fa-regular fa-envelope"></i>
                        </div>
                        <input type="email" id="login_email" name="email" value="" required placeholder="Enter email" autocomplete="off" class="w-full text-xs rounded-xl bg-slate-950 border border-slate-800 pl-10 pr-3 py-3 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 text-xs">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <input type="password" id="login_password" name="password" required placeholder="Enter password" autocomplete="new-password" class="w-full text-xs rounded-xl bg-slate-950 border border-slate-800 pl-10 pr-10 py-3 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                        <button type="button" onclick="togglePass('login_password')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300">
                            <i class="fa-regular fa-eye text-xs"></i>
                        </button>
                    </div>
                    <div class="flex justify-end mt-1.5">
                        <button type="button" @click="forgotModalOpen = true" class="text-xs text-emerald-400 hover:text-emerald-300 hover:underline font-medium">Forgot Password?</button>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center space-x-2 text-xs text-slate-400 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded bg-slate-950 border-slate-800 text-emerald-600 focus:ring-emerald-500">
                        <span>Remember this device</span>
                    </label>
                </div>

                <button type="submit" class="w-full mt-2 py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs uppercase tracking-wider transition shadow-lg shadow-emerald-600/30 flex items-center justify-center space-x-2">
                    <span>Sign In</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>

        </div>

        <p class="text-center text-xs text-slate-500 mt-6">&copy; {{ date('Y') }} Admin Panel Web &bull; Hyperlocal System</p>
    </div>

    <!-- DYNAMIC FORGOT PASSWORD MODAL (Instant DB update) -->
    <div x-show="forgotModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-md transition-opacity" @click="forgotModalOpen = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-slate-900 border border-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full p-6 text-slate-100">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <h3 class="text-base font-bold text-white flex items-center space-x-2">
                        <i class="fa-solid fa-key text-emerald-400"></i>
                        <span>Forgot / Reset Password</span>
                    </h3>
                    <button @click="forgotModalOpen = false" class="text-slate-400 hover:text-slate-200"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <div x-show="resetSuccessMsg" class="mt-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-xs text-emerald-400 font-semibold" x-text="resetSuccessMsg"></div>
                <div x-show="resetErrorMsg" class="mt-4 p-3 rounded-xl bg-red-500/10 border border-red-500/20 text-xs text-red-400 font-semibold" x-text="resetErrorMsg"></div>

                <form @submit.prevent="submitResetPassword" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Admin Email Address <span class="text-red-400">*</span></label>
                        <input type="email" x-model="resetForm.email" required class="w-full text-xs rounded-xl bg-slate-950 border border-slate-800 px-3 py-2.5 text-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Enter New Password <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <input type="password" id="forgot_new_pass" x-model="resetForm.password" required minlength="6" placeholder="At least 6 characters" class="w-full text-xs rounded-xl bg-slate-950 border border-slate-800 px-3 py-2.5 pr-10 text-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <button type="button" onclick="togglePass('forgot_new_pass')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-200">
                                <i class="fa-regular fa-eye text-xs"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Confirm New Password <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <input type="password" id="forgot_confirm_pass" x-model="resetForm.password_confirmation" required minlength="6" placeholder="Repeat new password" class="w-full text-xs rounded-xl bg-slate-950 border border-slate-800 px-3 py-2.5 pr-10 text-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <button type="button" onclick="togglePass('forgot_confirm_pass')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-200">
                                <i class="fa-regular fa-eye text-xs"></i>
                            </button>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2 pt-3">
                        <button type="button" @click="forgotModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300">Cancel</button>
                        <button type="submit" :disabled="loading" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-xs font-bold text-white shadow-lg shadow-emerald-600/30 flex items-center space-x-1.5">
                            <span x-text="loading ? 'Updating...' : 'Save & Update Password'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function loginPage() {
            return {
                forgotModalOpen: false,
                loading: false,
                resetSuccessMsg: '',
                resetErrorMsg: '',
                resetForm: {
                    email: '',
                    password: '',
                    password_confirmation: '',
                },
                fillDefaultCreds() {
                    document.getElementById('login_email').value = 'admin@gmail.com';
                    document.getElementById('login_password').value = 'admin@123';
                },
                async submitResetPassword() {
                    this.resetErrorMsg = '';
                    this.resetSuccessMsg = '';
                    if (this.resetForm.password !== this.resetForm.password_confirmation) {
                        this.resetErrorMsg = 'New Password and Confirmation Password do not match!';
                        return;
                    }
                    this.loading = true;
                    try {
                        const response = await fetch("{{ route('admin.password.reset') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.resetForm)
                        });
                        const data = await response.json();
                        if (response.ok && data.success) {
                            this.resetSuccessMsg = data.message;
                            document.getElementById('login_password').value = this.resetForm.password;
                            setTimeout(() => {
                                this.forgotModalOpen = false;
                                this.resetSuccessMsg = '';
                            }, 1800);
                        } else {
                            this.resetErrorMsg = data.message || 'Unable to reset password. Please verify your email.';
                        }
                    } catch (err) {
                        this.resetErrorMsg = 'An unexpected error occurred. Please try again.';
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }

        function togglePass(id) {
            const el = document.getElementById(id);
            el.type = el.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>
