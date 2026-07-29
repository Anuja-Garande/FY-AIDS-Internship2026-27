<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<div class="bg-gradient-to-b from-white to-slate-50 py-20 border-b border-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <span class="text-blue-600 font-semibold text-sm tracking-wide uppercase px-3 py-1 bg-blue-50 rounded-full">Zeal Employment Society Portal</span>
        <h1 class="mt-4 text-4xl sm:text-5xl font-extrabold text-slate-900 tracking-tight">
            Automated Workforce Tracking & <br><span class="text-indigo-600">Smart Leave Management Infrastructure.</span>
        </h1>
        <p class="mt-4 max-w-2xl mx-auto text-lg text-slate-500">
            A secure, unified solution custom-built for our Narhe ecosystem. Streamlining employee absences efficiently across diverse structural contractual configurations.
        </p>
        <div class="mt-8">
            <a href="auth/login.php" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-xl text-white bg-blue-600 hover:bg-blue-700 shadow-md transition">
                Enter Workplace Portal
            </a>
        </div>
    </div>
</div>

<!-- Class Selection Layout Blocks -->
<div class="max-w-7xl mx-auto px-4 py-16 sm:px-6 lg:px-8">
    <div class="text-center mb-12">
        <h2 class="text-2xl font-bold text-slate-900">Comprehensive Class Allocations Supported</h2>
        <p class="text-slate-500 text-sm mt-1">Granular leaves mapped seamlessly across all employment configurations at Zeal.</p>
    </div>
    
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-4">
        <?php
        $types = [
            'Head' => 'bg-purple-50 text-purple-700 border-purple-200',
            'Full-Time' => 'bg-blue-50 text-blue-700 border-blue-200',
            'Part-Time' => 'bg-green-50 text-green-700 border-green-200',
            'Temporary' => 'bg-amber-50 text-amber-700 border-amber-200',
            'Contract' => 'bg-orange-50 text-orange-700 border-orange-200',
            'Freelancer' => 'bg-pink-50 text-pink-700 border-pink-200',
            'Intern' => 'bg-teal-50 text-teal-700 border-teal-200'
        ];
        foreach($types as $name => $style) {
            echo "<div class='border rounded-xl p-4 text-center shadow-sm font-semibold text-sm {$style}'>{$name}</div>";
        }
        ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>