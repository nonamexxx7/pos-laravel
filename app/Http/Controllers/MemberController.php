<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $members = Member::query()
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('name', 'like', "%$s%")->orWhere('phone', 'like', "%$s%")->orWhere('code', 'like', "%$s%")))
            ->latest()->paginate(15)->withQueryString();

        return view('members.index', compact('members'));
    }

    public function create(): View
    {
        return view('members.form', ['member' => new Member]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['code'] = Member::nextCode();
        Member::create($data);

        return redirect()->route('members.index')->with('success', 'เพิ่มสมาชิกเรียบร้อย');
    }

    public function show(Member $member): View
    {
        $sales = $member->sales()->with('payment')->latest()->paginate(10);

        return view('members.show', compact('member', 'sales'));
    }

    public function edit(Member $member): View
    {
        return view('members.form', compact('member'));
    }

    public function update(Request $request, Member $member): RedirectResponse
    {
        $member->update($this->validated($request, $member));

        return redirect()->route('members.index')->with('success', 'แก้ไขข้อมูลสมาชิกเรียบร้อย');
    }

    public function destroy(Member $member): RedirectResponse
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $member->delete();

        return redirect()->route('members.index')->with('success', 'ลบสมาชิกเรียบร้อย');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Member $member = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'digits_between:9,10', Rule::unique('members', 'phone')->ignore($member)],
            'email' => ['nullable', 'email'],
        ]);
    }
}
