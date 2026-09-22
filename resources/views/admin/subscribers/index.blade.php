@extends('layouts.admin')
@section('page-title', 'Newsletter Subscribers')

@section('content')
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-left">
            <tr>
                <th class="px-4 py-3"><span class="inline-flex items-center gap-1.5"><i data-lucide="mail" class="w-3.5 h-3.5"></i> Email</span></th>
                <th class="px-4 py-3"><span class="inline-flex items-center gap-1.5"><i data-lucide="calendar" class="w-3.5 h-3.5"></i> Subscribed</span></th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($subscribers as $s)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium">{{ $s->email }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $s->created_at->format('M d, Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="px-4 py-12 text-center text-gray-400">
                        <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2"></i>
                        No subscribers yet.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $subscribers->links() }}</div>
@endsection
