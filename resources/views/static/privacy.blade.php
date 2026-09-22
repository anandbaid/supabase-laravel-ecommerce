@extends('layouts.app')
@section('title', "Privacy Policy - Let's Shop")

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold mb-2">Privacy Policy</h1>
    <p class="text-gray-500 mb-8">Your Privacy Matters</p>

    <div class="bg-white rounded-xl shadow-sm p-6 sm:p-8 space-y-8">
        <section>
            <h2 class="font-semibold text-lg mb-2">1. Information We Collect</h2>
            <p class="text-gray-600 text-sm leading-relaxed">
                We collect information you provide directly to us, such as your name, email address,
                shipping address, and payment details, when you create an account or place an order.
            </p>
        </section>
        <section>
            <h2 class="font-semibold text-lg mb-2">2. How We Use Your Information</h2>
            <ul class="text-gray-600 text-sm leading-relaxed list-disc list-inside space-y-1">
                <li>Process your orders and deliver products.</li>
                <li>Communicate order updates and customer service.</li>
                <li>Send promotional offers (only with your consent).</li>
                <li>Improve and personalize your shopping experience.</li>
            </ul>
        </section>
        <section>
            <h2 class="font-semibold text-lg mb-2">3. Cookies</h2>
            <p class="text-gray-600 text-sm leading-relaxed">
                This site uses cookies to remember your cart, preferences, and login session.
                You can disable cookies in your browser, though some features may not work correctly.
            </p>
        </section>
        <section>
            <h2 class="font-semibold text-lg mb-2">4. Sharing Your Information</h2>
            <p class="text-gray-600 text-sm leading-relaxed">
                We never sell your personal information. We only share it with trusted service providers
                (like payment processors and delivery partners) as needed to fulfil your order.
            </p>
        </section>
        <section>
            <h2 class="font-semibold text-lg mb-2">5. Your Rights</h2>
            <p class="text-gray-600 text-sm leading-relaxed">
                You can access, update, or delete your account information at any time from your
                <a href="{{ route('account.edit') }}" class="text-blue-600 hover:underline">Account settings</a>,
                or contact us if you need help.
            </p>
        </section>
        <p class="text-xs text-gray-400 pt-4 border-t">Last updated: {{ now()->format('F Y') }}</p>
    </div>
</div>
@endsection
