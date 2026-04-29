<form action="{{ route('reports.fee-account-mappings.save') }}" method="POST">
    @csrf
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-secondary-100 text-sm">
            <thead class="bg-secondary-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-secondary-500">Fee Name</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-secondary-500 w-96">Account</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-secondary-500 w-24">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-secondary-50 bg-white">
                @foreach($fees as $fee)
                @php $mapped = $mappings->get($fee->id); @endphp
                <tr class="{{ $mapped ? 'bg-green-50/30' : '' }}">
                    <td class="px-4 py-2 font-medium text-secondary-800">
                        {{ $fee->name }}
                        <input type="hidden" name="mappings[{{ $fee->id }}][finance_fee_id]" value="{{ $fee->id }}">
                        <input type="hidden" name="mappings[{{ $fee->id }}][finance_fee_name]" value="{{ $fee->name }}">
                    </td>
                    <td class="px-4 py-2">
                        <select name="mappings[{{ $fee->id }}][account_id]" class="form-input text-sm w-full">
                            <option value="">-- Not Mapped --</option>
                            @foreach($revenueAccounts as $acct)
                            <option value="{{ $acct->id }}" {{ $mapped && $mapped->account_id == $acct->id ? 'selected' : '' }}>
                                {{ $acct->account_code }} — {{ $acct->account_name }}
                            </option>
                            @endforeach
                        </select>
                    </td>
                    <td class="px-4 py-2 text-center">
                        @if($mapped)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Mapped</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-secondary-100 text-secondary-500">Unmapped</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t border-secondary-100 bg-secondary-50 flex justify-end">
        <button type="submit" class="btn-primary text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            Save {{ $group->name }}
        </button>
    </div>
</form>
