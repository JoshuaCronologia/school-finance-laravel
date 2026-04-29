@php
$_map = ['asset'=>'badge-info','liability'=>'badge-warning','equity'=>'badge-success','revenue'=>'badge-success','expense'=>'badge-danger'];
@endphp
@forelse($children as $child)
@php $badge = $_map[$child->account_type ?? ''] ?? 'badge-neutral'; @endphp
<tr class="cursor-pointer hover:bg-primary-50/50" onclick="window.location='{{ route('gl.accounts.show', $child) }}'">
    <td class="pl-10 font-mono text-sm text-secondary-600">{{ $child->account_code }}</td>
    <td>{{ $child->account_name }}</td>
    <td><span class="badge {{ $badge }}">{{ ucfirst($child->account_type ?? '-') }}</span></td>
    <td class="text-right font-medium {{ $child->balance > 0 ? 'text-green-600' : ($child->balance < 0 ? 'text-red-600' : 'text-secondary-400') }}">
        {{ $child->balance != 0 ? '₱' . number_format(abs($child->balance), 2) : '-' }}
    </td>
    <td onclick="event.stopPropagation()">
        <button onclick="openEditModal({{ json_encode(['id'=>$child->id,'account_code'=>$child->account_code,'account_name'=>$child->account_name,'account_type'=>$child->account_type,'normal_balance'=>$child->normal_balance,'parent_id'=>$child->parent_id,'notes'=>$child->notes ?? '']) }})"
            class="text-primary-600 hover:text-primary-700 text-sm font-medium">Edit</button>
    </td>
</tr>
@empty
<tr><td colspan="5" class="pl-10 py-3 text-sm text-secondary-400">No sub-accounts found.</td></tr>
@endforelse
