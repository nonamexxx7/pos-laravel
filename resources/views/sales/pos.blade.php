@extends('layouts.app')
@section('title', 'ขายสินค้า (POS)')
@section('content')
<div class="row g-3" id="app">
    <div class="col-lg-7">
        <div class="card mb-3"><div class="card-body">
            <div class="input-group mb-2">
                <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                <input id="search" class="form-control" placeholder="ค้นหาชื่อ หรือ สแกน/พิมพ์รหัสสินค้าแล้วกด Enter" autofocus autocomplete="off">
            </div>
            <div id="cats" class="d-flex flex-wrap gap-1"></div>
        </div></div>
        <div id="grid" class="row g-2"></div>
    </div>

    <div class="col-lg-5">
        <div class="card"><div class="card-header bg-white fw-bold d-flex justify-content-between">
            <span><i class="bi bi-cart3"></i> ตะกร้าสินค้า</span><button class="btn btn-sm btn-outline-danger" onclick="clearCart()">ล้าง</button></div>
            <div class="card-body p-0" style="max-height:280px;overflow:auto">
                <table class="table table-sm mb-0 align-middle"><tbody id="cart"></tbody></table>
            </div>
            <div class="card-body border-top">
                <div class="mb-2">
                    <select id="member" class="form-select form-select-sm">
                        <option value="">ลูกค้าทั่วไป (ไม่ใช้สมาชิก)</option>
                        @foreach ($members as $m)<option value="{{ $m->id }}" data-points="{{ $m->points }}">{{ $m->code }} {{ $m->name }} ({{ $m->phone }})</option>@endforeach
                    </select>
                </div>
                <div id="pointsBox" class="mb-2 d-none">
                    <div class="input-group input-group-sm"><span class="input-group-text">ใช้แต้ม (มี <span id="pts">0</span>)</span>
                        <input type="number" id="usePoints" min="0" value="0" class="form-control"></div>
                </div>
                <div class="d-flex justify-content-between"><span>รวมเป็นเงิน</span><span id="subtotal">0.00</span></div>
                <div class="d-flex justify-content-between text-danger"><span>ส่วนลดจากแต้ม</span><span id="discount">0.00</span></div>
                <div class="d-flex justify-content-between fs-4 fw-bold"><span>ยอดสุทธิ</span><span id="total">0.00</span></div>
                <hr>
                <div class="btn-group w-100 mb-2" id="methods">
                    @foreach ($methods as $k => $v)
                        <input type="radio" class="btn-check" name="method" id="m_{{ $k }}" value="{{ $k }}" @checked($loop->first)>
                        <label class="btn btn-outline-primary btn-sm" for="m_{{ $k }}">{{ $v }}</label>
                    @endforeach
                </div>
                <div id="cashBox">
                    <div class="input-group mb-1"><span class="input-group-text">รับเงิน</span><input type="number" id="received" min="0" step="0.01" class="form-control"></div>
                    <div class="d-flex gap-1 mb-2" id="quick"></div>
                    <div class="d-flex justify-content-between"><span>เงินทอน</span><b id="change" class="text-success">0.00</b></div>
                </div>
                <div id="refBox" class="d-none mb-2"><input id="reference" class="form-control form-control-sm" placeholder="เลขอ้างอิง / 4 ตัวท้ายบัตร (ถ้ามี)"></div>
                <div id="err" class="text-danger small mt-2"></div>
                <button id="payBtn" class="btn btn-success btn-lg w-100 mt-2" onclick="checkout()"><i class="bi bi-check-circle"></i> ชำระเงิน</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const products = @json($products);
const categories = ['ทั้งหมด', ...@json($categories), 'อื่น ๆ'].filter((c, i, a) => a.indexOf(c) === i);
let cart = {}, activeCat = 'ทั้งหมด';
const $ = id => document.getElementById(id);
const fmt = n => Number(n).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

function renderCats() {
    $('cats').innerHTML = categories.map(c => `<button class="btn btn-sm ${c === activeCat ? 'btn-primary' : 'btn-outline-primary'}" onclick="setCat('${c}')">${c}</button>`).join('');
}
function setCat(c) { activeCat = c; renderCats(); renderGrid(); }
function renderGrid() {
    const q = $('search').value.trim().toLowerCase();
    const list = products.filter(p => (activeCat === 'ทั้งหมด' || p.category === activeCat) && (!q || p.name.toLowerCase().includes(q) || p.sku.toLowerCase().includes(q)));
    $('grid').innerHTML = list.map(p => `
        <div class="col-6 col-md-4"><div class="card h-100 ${p.stock <= 0 ? 'opacity-50' : ''}" role="button" onclick="add(${p.id})">
            <div class="card-body p-2"><div class="small text-muted">${p.sku}</div><div class="fw-semibold">${p.name}</div>
            <div class="d-flex justify-content-between"><span class="text-primary fw-bold">฿${fmt(p.price)}</span><span class="small ${p.stock <= 5 ? 'text-danger' : 'text-muted'}">เหลือ ${p.stock}</span></div></div></div></div>`).join('')
        || '<div class="text-muted text-center py-4">ไม่พบสินค้า</div>';
}
function add(id) {
    const p = products.find(x => x.id === id);
    const cur = cart[id] || 0;
    if (cur + 1 > p.stock) { $('err').textContent = `สินค้า ${p.name} คงเหลือไม่พอ`; return; }
    $('err').textContent = '';
    cart[id] = cur + 1;
    renderCart();
}
function setQty(id, d) {
    const p = products.find(x => x.id === id);
    const n = (cart[id] || 0) + d;
    if (n > p.stock) { $('err').textContent = `สินค้า ${p.name} คงเหลือไม่พอ`; return; }
    if (n <= 0) delete cart[id]; else cart[id] = n;
    renderCart();
}
function clearCart() { cart = {}; renderCart(); }
function sums() {
    const subtotal = Object.entries(cart).reduce((s, [id, q]) => s + products.find(p => p.id == id).price * q, 0);
    const opt = $('member').selectedOptions[0];
    const maxPts = opt && opt.dataset.points ? Math.min(+opt.dataset.points, Math.floor(subtotal)) : 0;
    let use = Math.min(Math.max(+$('usePoints').value || 0, 0), maxPts);
    return { subtotal, use, total: subtotal - use, maxPts };
}
function renderCart() {
    const rows = Object.entries(cart).map(([id, q]) => {
        const p = products.find(x => x.id == id);
        return `<tr><td class="ps-3">${p.name}<div class="small text-muted">฿${fmt(p.price)}</div></td>
            <td class="text-nowrap"><button class="btn btn-sm btn-light" onclick="setQty(${p.id},-1)">−</button> <b>${q}</b> <button class="btn btn-sm btn-light" onclick="setQty(${p.id},1)">+</button></td>
            <td class="text-end pe-3">${fmt(p.price * q)}</td></tr>`;
    }).join('');
    $('cart').innerHTML = rows || '<tr><td class="text-center text-muted py-4">ยังไม่มีสินค้าในตะกร้า</td></tr>';
    updateTotals();
}
function updateTotals() {
    const s = sums();
    $('subtotal').textContent = fmt(s.subtotal);
    $('discount').textContent = (s.use > 0 ? '-' : '') + fmt(s.use);
    $('total').textContent = fmt(s.total);
    const m = document.querySelector('[name=method]:checked').value;
    $('cashBox').classList.toggle('d-none', m !== 'cash');
    $('refBox').classList.toggle('d-none', m === 'cash');
    const nice = [...new Set([s.total, Math.ceil(s.total / 20) * 20, Math.ceil(s.total / 100) * 100, Math.ceil(s.total / 500) * 500, Math.ceil(s.total / 1000) * 1000])].filter(v => v > 0);
    $('quick').innerHTML = nice.slice(0, 4).map(v => `<button type="button" class="btn btn-sm btn-outline-secondary" onclick="$('received').value=${v};updateTotals()">${fmt(v)}</button>`).join('');
    $('change').textContent = fmt(Math.max(0, (+$('received').value || 0) - s.total));
}
async function checkout() {
    const s = sums(), method = document.querySelector('[name=method]:checked').value;
    $('err').textContent = '';
    if (!Object.keys(cart).length) { $('err').textContent = 'กรุณาเลือกสินค้า'; return; }
    if (method === 'cash' && (+$('received').value || 0) < s.total) { $('err').textContent = 'จำนวนเงินที่รับน้อยกว่ายอดที่ต้องชำระ'; return; }
    $('payBtn').disabled = true;
    const res = await fetch('{{ route('pos.store') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({
            items: Object.entries(cart).map(([id, qty]) => ({ product_id: +id, qty })),
            member_id: $('member').value || null, use_points: s.use, method,
            received: +$('received').value || 0, reference: $('reference').value || null,
        }),
    });
    const data = await res.json();
    if (res.ok) { location.href = data.receipt; return; }
    $('err').textContent = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'เกิดข้อผิดพลาด');
    $('payBtn').disabled = false;
}
$('search').addEventListener('input', renderGrid);
$('search').addEventListener('keydown', e => {
    if (e.key !== 'Enter') return;
    const q = e.target.value.trim().toLowerCase();
    const hit = products.find(p => p.sku.toLowerCase() === q) || (products.filter(p => p.name.toLowerCase().includes(q)).length === 1 && products.find(p => p.name.toLowerCase().includes(q)));
    if (hit) { add(hit.id); e.target.value = ''; renderGrid(); }
});
$('member').addEventListener('change', () => {
    const opt = $('member').selectedOptions[0];
    $('pointsBox').classList.toggle('d-none', !opt.value);
    $('pts').textContent = opt.dataset.points || 0;
    $('usePoints').value = 0;
    updateTotals();
});
['usePoints', 'received'].forEach(i => $(i).addEventListener('input', updateTotals));
document.querySelectorAll('[name=method]').forEach(r => r.addEventListener('change', updateTotals));
renderCats(); renderGrid(); renderCart();
</script>
@endpush
