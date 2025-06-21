<div class="bg-white rounded-xl shadow-2xl p-8">
    <div class="text-center mb-8">
        <div class="mx-auto w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
            </svg>
        </div>
        <h2 class="text-3xl font-bold text-gray-900">Create Account</h2>
        <p class="mt-2 text-gray-600">Join Courier Dash today</p>
    </div>

    <?php if(isset($_GET['error'])): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
            <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/register" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                Full Name
            </label>
            <input id="name" 
                   name="name" 
                   type="text" 
                   required 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                   placeholder="Enter your full name"
                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                Email Address
            </label>
            <input id="email" 
                   name="email" 
                   type="email" 
                   required 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                   placeholder="Enter your email"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                Password
            </label>
            <input id="password" 
                   name="password" 
                   type="password" 
                   required 
                   minlength="8"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                   placeholder="Create a password">
            <p class="mt-1 text-sm text-gray-500">Minimum 8 characters</p>
        </div>

        <div>
            <label for="password_confirm" class="block text-sm font-medium text-gray-700 mb-2">
                Confirm Password
            </label>
            <input id="password_confirm" 
                   name="password_confirm" 
                   type="password" 
                   required 
                   minlength="8"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                   placeholder="Confirm your password">
        </div>

        <div class="flex items-start">
            <div class="flex items-center h-5">
                <input id="terms" 
                       name="terms" 
                       type="checkbox" 
                       required
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
            </div>
            <div class="ml-3 text-sm">
                <label for="terms" class="text-gray-700">
                    I agree to the 
                    <a href="/terms" class="text-blue-600 hover:text-blue-500">Terms of Service</a>
                    and 
                    <a href="/privacy" class="text-blue-600 hover:text-blue-500">Privacy Policy</a>
                </label>
            </div>
        </div>

        <button type="submit" 
                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Create Account
        </button>

        <div class="text-center">
            <p class="text-sm text-gray-600">
                Already have an account?
                <a href="/login" class="font-medium text-blue-600 hover:text-blue-500">
                    Sign in here
                </a>
            </p>
        </div>
    </form>
</div>

<script>
// Password confirmation validation
document.getElementById('password_confirm').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirm = this.value;
    
    if(password !== confirm) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});

document.getElementById('password').addEventListener('input', function() {
    const confirm = document.getElementById('password_confirm');
    if(confirm.value) {
        confirm.dispatchEvent(new Event('input'));
    }
});
</script>
