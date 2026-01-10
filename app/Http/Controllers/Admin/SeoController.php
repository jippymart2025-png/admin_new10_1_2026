<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoPage;
use Illuminate\Http\Request;

class SeoController extends Controller
{
    public function index() {
        $pages = SeoPage::paginate(20);
        return view('admin.seo.index', compact('pages'));
    }

    public function edit($id) {
        $page = SeoPage::findOrFail($id);
        return view('admin.seo.edit', compact('page'));
    }

    public function update(Request $request, $id) {
        $page = SeoPage::findOrFail($id);
        $page->update($request->all());
        return redirect()->route('seo.index')->with('success', 'SEO Updated');
    }
}
